<?php

namespace App\Services\v1\imports;

use App\Imports\ClientsImport;
use App\Models\Clients\ClientModel;
use App\Models\Clients\DocumentModel;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ImportClientsService
{
    protected array $errors = [];
    protected int $documentTypeDui = 3;
    protected int $documentTypeNit = 4;
    protected int $phoneTypeMobile = 2;
    protected int $kinshipIdDefault = 24;
    protected int $technicianIdDefault = 1;

    /***
     * Lee el archivo .xlsx y manda a procesar los datos.
     * @return array
     * @throws \Throwable
     */
    public function importClients(): void
    {
        $path = storage_path('app/imports/clients.xlsx');
        $list = Excel::toCollection(new ClientsImport, $path)[0];

        foreach ($list as $index => $row) {
            $rowNumber = $index + 2;
            try {
                DB::transaction(function () use ($row, $rowNumber) {
                    $this->processRow($row, $rowNumber);
                });
            } catch (\Exception $e) {
                $this->errors[] = [
                    'row' => $rowNumber,
                    'error' => $e->getMessage(),
                    'data' => $row->toArray(),
                ];

                Log::channel('import_clients')
                    ->error("Error importando fila {$rowNumber}: {$e->getMessage()}", [
                        'row_data' => $row->toArray(),
                        'trace' => $e->getTraceAsString(),
                    ]);
            }
        }
    }


    /***
     * Manda a procesar cada fila del archivo .xlsx para crear cliente y servicio
     * @param Collection $row
     * @param int $rowNumber
     * @return void
     * @throws \Exception
     */
    private function processRow(Collection $row, int $rowNumber): void
    {
        if (empty($row['dui'])) {
            throw new \Exception("DUI no encontrado en la fila {$rowNumber}");
        }

        $client = $this->getOrCreateClient($row);
        $this->createService($client, $row);
    }

    /***
     * Busca dui en la BD, si no lo encuentra crea el cliente con sus respectivos datos.
     * Si lo encuentra retorna datos del cliente.
     * @param Collection $row
     * @return ClientModel
     */
    private function getOrCreateClient(Collection $row): ClientModel
    {
        $dui = $row['dui'];
        $existingClient = ClientModel::query()
            ->whereHas('dui', function ($query) use ($dui) {
                $query->where('number', $dui);
            })
            ->first();

        if ($existingClient) return $existingClient;

        $client = $this->createClient($row);
        $this->createClientDocuments($client, $row, $dui);
        $this->createClientAddress($client, $row);
        $this->createClientPhone($client, $row);
        $this->createClientReferences($client, $row);

        return $client;
    }

    /***
     * Almacena datos principales del cliente.
     * @param Collection $row
     * @return ClientModel
     */
    private function createClient(Collection $row): ClientModel
    {
        $nameParts = $this->splitFullName($row['name'] ?? '');

        return ClientModel::query()
            ->create([
                'name' => $this->parsedName($nameParts['first_names']),
                'surname' => $this->parsedName($nameParts['last_names']),
                'gender_id' => 1,
                'birthdate' => $this->getBirthDate($row['nit']),
                'marital_status_id' => $row['civil_status'],
                'branch_id' => 1,
                'client_type_id' => 1,
                'profession' => 'Otro',
                'country_id' => 59,
                'document_type_id' => 1,
                'legal_entity' => false,
                'status_id' => true,
            ]);
    }

    /***
     * Almacena en la BD el DUI y NIT del cliente.
     * @param ClientModel $client
     * @param Collection $row
     * @param string $dui
     * @return void
     */
    protected function createClientDocuments(ClientModel $client, Collection $row, string $dui): void
    {
        DocumentModel::query()
            ->create([
                'client_id' => $client->id,
                'document_type_id' => $this->documentTypeDui,
                'number' => $dui,
                'expiration_date' => Carbon::now()->addYears(8)->format('Y-m-d'),
                'status_id' => true,
            ]);

        if ($row['nit'] != 'HOMOLOGADO') {
            DocumentModel::query()
                ->create([
                    'client_id' => $client->id,
                    'document_type_id' => $this->documentTypeNit,
                    'number' => $row['nit'],
                    'expiration_date' => Carbon::now()->addYears(30)->format('Y-m-d'),
                    'status_id' => true,
                ]);
        }
    }

    /***
     * Retorna id del municipio según id de distrito.
     * @param int $districtId
     * @return int
     */
    private function getMunicipalityId(int $districtId): int
    {
        return match ($districtId) {
            234, 243, 230, 241 => 42,
            201 => 38,
            208, 209, 211 => 39,
            14 => 4,
            216 => 40,
            195 => 37,
            188 => 36,
        };
    }

    /***
     * Convierte a inicial mayúscula
     * @param string $name
     * @return string
     */
    public function parsedName(string $name): string
    {
        $lower = mb_strtolower($name);
        return (ucwords($lower));
    }

    /***
     * Divide los nombres completos en nombres y apellidos
     * @param string $fullName
     * @return array
     */
    public function splitFullName(string $fullName): array
    {
        $fullName = trim(preg_replace('/\s+/', ' ', $fullName));
        $particles = ['de la', 'de los', 'de las', 'del', 'de', 'vda. de', 'vda de'];
        $words = explode(' ', $fullName);
        $lastName = [];
        $firstName = [];

        while (!empty($words)) {
            $word = array_pop($words);
            $wordLower = strtolower($word);
            $isParticle = false;

            foreach ($particles as $particle) {
                $particleParts = explode(' ', $particle);
                $numParts = count($particleParts);

                if ($numParts > 1) {
                    $context = array_slice($lastName, 0, $numParts - 1);
                    $potentialPhrase = strtolower(implode(' ', array_merge([$word], $context)));

                    if ($potentialPhrase === $particle) {
                        array_unshift($lastName, $word);
                        $isParticle = true;
                        break;
                    }
                } else {
                    if ($wordLower === $particle) {
                        array_unshift($lastName, $word);
                        $isParticle = true;
                        break;
                    }
                }
            }

            if ($isParticle) continue;

            if (count($lastName) >= 2) {
                array_unshift($firstName, $word);

                while (!empty($words)) {
                    array_unshift($firstName, array_pop($words));
                }
                break;
            }

            array_unshift($lastName, $word);
        }

        return [
            'first_names' => trim(implode(' ', $firstName)),
            'last_names' => trim(implode(' ', $lastName)),
        ];
    }

    /***
     * Extrae la fecha de nacimiento según el NIT
     * @param string|null $number
     * @return string
     */
    public function getBirthDate(?string $number): string
    {
        $defaultDate = '1945-08-06';
        $document = strtoupper(trim($number ?? ''));

        if (empty($document) || $document === '0000-000000-000-0' || $document === 'HOMOLOGADO')
            $document = '1217-060845-106-7';
        if (!preg_match('/^\d{4}-\d{6}-\d{3}-\d{1}$/', $number)) return $defaultDate;

        $dateStr = explode('-', $document)[1];
        $day = (int)substr($dateStr, 0, 2);
        $month = (int)substr($dateStr, 2, 2);
        $year = (int)substr($dateStr, 4, 2);
        if (!checkdate($month, $day, 2000)) return $defaultDate;
        $fullYear = $year <= (int)date('y') ? 2000 + $year : 1900 + $year;
        return Carbon::createFromDate($fullYear, $month, $day)->toDateString();
    }

    /***
     * Para identificar si un cliente se encuentra duplicado
     * @param array $client
     * @return string
     */
    private function getClientKey(array $client): string
    {
        if (!empty($client['dui'])) return trim($client['dui']);

        return trim($client['phone']);
    }
}
