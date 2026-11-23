<?php

namespace App\Services\v1\imports;

use App\Enums\v1\General\CommonStatus;
use App\Imports\ClientsImport;
use App\Models\Clients\AddressModel;
use App\Models\Clients\ClientModel;
use App\Models\Clients\ContractModel;
use App\Models\Clients\DocumentModel;
use App\Models\Clients\PhoneModel;
use App\Models\Clients\ReferenceModel;
use App\Models\Infrastructure\Equipment\InventoryModel;
use App\Models\Services\ServiceInternetModel;
use App\Models\Services\ServiceIptvEquipmentModel;
use App\Models\Services\ServiceModel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ImportClientsService
{
    protected array $errors = [];
    protected int $documentTypeDui = 3;
    protected int $documentTypeNit = 4;
    protected int $phoneTypeMobile = 2;
    protected int $kinshipIdDefault = 24;
    protected int $technicianIdDefault = 1;

    /**
     * Lee el archivo .xlsx y manda a procesar los datos.
     *
     * @return void
     * @throws \Throwable
     */
    public function importClients(): void
    {
        $path = storage_path('app/imports/clients.xlsx');
        $rawData = Excel::toCollection(new ClientsImport, $path)[0];
        $this->createMacAddress();

        foreach ($rawData as $index => $row) {
            $rowNumber = $index + 2;

            try {
                DB::transaction(function () use ($row, $rowNumber) {
                    $this->processRow(row: $row, rowNumber: $rowNumber);
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
//                        'trace' => $e->getTraceAsString(),
                    ]);
            }
        }
    }

    /**
     * Manda a procesar cada fila del archivo .xlsx para crear cliente y servicio.
     *
     * @param Collection $row
     * @param int $rowNumber
     * @return void
     * @throws \Exception
     */
    private function processRow(Collection $row, int $rowNumber): void
    {
        if (empty($row['dui']))
            throw new \Exception("DUI no encontrado en la fila {$rowNumber}");
        $client = $this->getOrCreateClient($row);
        $this->createService(client: $client, row: $row);
    }

    /**
     * Busca el dui en la BD, si no lo encuentra crea el cliente con sus respectivos datos.
     * Sí lo encuentra retorna los datos del cliente.
     *
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

        $client = $this->createClient(row: $row);
        $this->createClientDocs(client: $client, row: $row, dui: $dui);
        $this->createClientAddress(client: $client, row: $row);
        $this->createClientPhone(client: $client, row: $row);
        $this->createClientReferences(client: $client, row: $row);
        $this->createClientContract(client: $client, row: $row);

        return $client;
    }

    /**
     * Almacena los datos principales del cliente.
     *
     * @param Collection $row
     * @return ClientModel
     */
    private function createClient(Collection $row): ClientModel
    {
        return ClientModel::query()
            ->create([
                'name' => $this->stringParser($row['client_name']),
                'surname' => $this->stringParser($row['client_surname']),
                'gender_id' => $row['genre'],
                'birthdate' => $this->birthdayGenerator($row['nit']),
                'marital_status_id' => $row['civil_status'],
                'branch_id' => 1,
                'client_type_id' => $row['client_type'],
                'profession' => 'Otro',
                'country_id' => 59,
                'document_type_id' => 1,
                'legal_entity' => CommonStatus::INACTIVE->value,
                'status_id' => $row['client_status'],
                'comments' => $row['observation'],
            ]);
    }

    /**
     * Almacena el DUI y NIT del cliente.
     *
     * @param ClientModel $client
     * @param Collection $row
     * @param string $dui
     * @return void
     */
    private function createClientDocs(ClientModel $client, Collection $row, string $dui): void
    {
        DocumentModel::query()
            ->create([
                'client_id' => $client->id,
                'document_type_id' => $this->documentTypeDui,
                'number' => $dui,
                'expiration_date' => Carbon::now()->addYears(8)->format('Y-m-d'),
                'status_id' => CommonStatus::ACTIVE->value,
            ]);

        if ($row['nit'] != 'HOMOLOGADO') {
            DocumentModel::query()
                ->create([
                    'client_id' => $client->id,
                    'document_type_id' => $this->documentTypeNit,
                    'number' => $row['nit'] ?? '0000-000000-000-0',
                    'expiration_date' => Carbon::now()->addYears(30)->format('Y-m-d'),
                    'status_id' => CommonStatus::ACTIVE->value,
                ]);
        }
    }

    /**
     * Almacena la dirección del cliente.
     *
     * @param ClientModel $client
     * @param Collection $row
     * @return void
     */
    private function createClientAddress(ClientModel $client, Collection $row): void
    {
        AddressModel::query()
            ->create([
                'client_id' => $client->id,
                'neighborhood' => 'Caserío/Cantón/Barrio/Residencial/Colonia',
                'address' => mb_strtolower($row['address']),
                'state_id' => $row['state_id'],
                'municipality_id' => $row['municipality_id'],
                'district_id' => $row['district_id'],
                'country_id' => 59,
                'status_id' => CommonStatus::ACTIVE->value,
            ]);
    }

    /**
     * Almacena el número de teléfono del cliente.
     *
     * @param ClientModel $client
     * @param Collection $row
     * @return void
     */
    private function createClientPhone(ClientModel $client, Collection $row): void
    {
        if (!empty($row['phone'])) {
            PhoneModel::query()
                ->create([
                    'client_id' => $client->id,
                    'phone_type_id' => $this->phoneTypeMobile,
                    'number' => $row['phone'],
                    'country_code' => 'SV',
                    'status_id' => CommonStatus::ACTIVE->value,
                ]);
        }
    }

    /**
     * Almacena las referencias de cada cliente.
     *
     * @param ClientModel $client
     * @param Collection $row
     * @return void
     */
    private function createClientReferences(ClientModel $client, Collection $row): void
    {
        ReferenceModel::query()
            ->create([
                'client_id' => $client->id,
                'name' => $this->stringParser($row['reference_name'] ?? 'N/A'),
                'dui' => $row['reference_dui'] ?? '00000000-0',
                'mobile' => $row['reference_phone'] ?? '0000 0000',
                'kinship_id' => $this->kinshipIdDefault,
                'status_id' => CommonStatus::ACTIVE->value,
            ]);
    }

    /**
     * Crea los contratos de cada cliente.
     *
     * @param ClientModel $client
     * @param Collection $row
     * @return void
     */
    private function createClientContract(ClientModel $client, Collection $row): void
    {
        ContractModel::query()
            ->create([
                'client_id' => $client->id,
                'contract_date' => $this->dateParser($row['contract_begins']),
                'contract_end_date' => $this->dateParser($row['contract_ends']),
                'installation_price' => 25.00,
                'contract_amount' => 300,
                'contract_status_id' => 1,
                'status_id' => CommonStatus::ACTIVE->value,
            ]);
    }

    /**
     * Almacena servicio de cliente.
     *
     * @param ClientModel $client
     * @param Collection $row
     * @return void
     * @throws \Exception
     */
    private function createService(ClientModel $client, Collection $row): void
    {
        $service = ServiceModel::query()
            ->create([
                'client_id' => $client->id,
                'code' => null,
                'name' => null,
                'node_id' => $row['node'],
                'equipment_id' => $row['panel'],
                'installation_date' => $this->dateParser($row['installation_date']),
                'technician_id' => $this->technicianIdDefault,
                'latitude' => 13.00000000,
                'longitude' => -88.00000000,
                'state_id' => $row['state_id'],
                'municipality_id' => $row['municipality_id'],
                'district_id' => $row['district_id'],
                'address' => mb_strtolower($row['address']),
                'separate_billing' => CommonStatus::ACTIVE->value,
                'status_id' => $row['service_status'],
            ]);

        $this->createInternetService(service: $service, row: $row);

        if ($row['iptv'])
            $this->createIptvService(service: $service, row: $row);

    }

    /**
     * Almacena credenciales PPPoe y perfil de internet asignado a ese servicio.
     *
     * @param ServiceModel $service
     * @param Collection $row
     * @return void
     */
    private function createInternetService(ServiceModel $service, Collection $row): void
    {
        ServiceInternetModel::query()
            ->create([
                'internet_profile_id' => $row['internet_plan'],
                'service_id' => $service->id,
                'user' => $row['secret_user'],
                'secret' => $row['secret_password'],
                'status_id' => $row['service_status'],
            ]);
    }

    /**
     * Genera las credenciales de IPTV para los clientes que tengan este servicio.
     *
     * @param ServiceModel $service
     * @param Collection $row
     * @return void
     * @throws \Exception
     */
    private function createIptvService(ServiceModel $service, Collection $row): void
    {
        // Buscar un equipo disponible
        $equipment = InventoryModel::query()
            ->where('brand_id', 5)
            ->where('type_id', 3)
            ->where('model_id', 5)
            ->where('branch_id', 1)
            ->where('status_id', 3)
            ->whereDoesntHave('on_iptv_service')
            ->lockForUpdate()
            ->first();

        if (!$equipment) {
            throw new \Exception(
                "No hay equipos STB disponibles para el servicio {$service->id}"
            );
        }

        ServiceIptvEquipmentModel::query()->create([
            'equipment_id' => $equipment->id,
            'service_id' => $service->id,
            'email' => $row['iptv_mail'] ?? 'Not Set',
            'email_password' => $row['iptv_password'],
            'iptv_password' => $row['iptv_activation_password'] ?? 'Not Set',
            'status_id' => $row['service_status'],
        ]);

        Log::channel('import_clients')->info('Equipo IPTV asignado correctamente', [
            'service_id' => $service->id,
            'equipment_id' => $equipment->id,
            'mac_address' => $equipment->mac_address,
        ]);
    }

    private function birthdayGenerator(?string $docNumber): string
    {
        $defaultDate = '1945-08-06';
        $document = strtoupper(trim($docNumber ?? ''));

        if (empty($document) || $document === '0000-000000-000-0' || $document === 'HOMOLOGADO')
            $document = '1217-060845-106-7';

        if (!preg_match('/^\d{4}-\d{6}-\d{3}-\d$/', $docNumber)) return $defaultDate;

        $dateStr = explode('-', $document)[1];
        $day = (int)substr($dateStr, 0, 2);
        $month = (int)substr($dateStr, 2, 2);
        $year = (int)substr($dateStr, 4, 2);

        if (!checkdate($month, $day, 2000)) return $defaultDate;
        $fullYear = $year <= (int)date('y') ? 2000 + $year : 1900 + $year;
        return Carbon::createFromDate($fullYear, $month, $day)->toDateString();
    }

    private function stringParser(?string $text): string
    {
        $texto = mb_strtolower($text);
        return ucwords($texto);
    }

    /**
     * Crea 215 equipos STB con direcciones MAC únicas.
     *
     * @return void
     * @throws \Random\RandomException
     * @throws \Throwable
     */
    private function createMacAddress(): void
    {
        $amount = 215;

        $equipment = [];

        // Obtener MAC existentes
        $existingMacs = InventoryModel::withTrashed()
            ->pluck('mac_address')
            ->map(fn($mac) => strtoupper(trim($mac)))
            ->flip()
            ->all();

        // Obtener seriales existentes
        $existingSerials = InventoryModel::withTrashed()
            ->pluck('serial_number')
            ->map(fn($serial) => strtoupper(trim($serial)))
            ->flip()
            ->all();

        while (count($equipment) < $amount) {

            $macBytes = [];

            for ($j = 0; $j < 6; $j++) {
                $macBytes[] = random_int(0, 255);
            }

            // MAC unicast y localmente administrada
            $macBytes[0] = ($macBytes[0] & 0xFC) | 0x02;

            $macAddress = strtoupper(
                implode(':', array_map(
                    fn($byte) => sprintf('%02X', $byte),
                    $macBytes
                ))
            );

            $macSerial = str_replace(':', '', $macAddress);

            // Evitar MAC y seriales duplicados
            if (
                isset($existingMacs[$macAddress]) ||
                isset($existingSerials[$macSerial])
            ) {
                continue;
            }

            // Reservar valores generados
            $existingMacs[$macAddress] = true;
            $existingSerials[$macSerial] = true;

            $equipment[] = [
                'brand_id' => 5,
                'type_id' => 3,
                'model_id' => 5,
                'branch_id' => 1,
                'mac_address' => $macAddress,
                'serial_number' => $macSerial,
                'registration_date' => Carbon::today(),
                'status_id' => 3,
                'comments' => 'Generado aleatoriamente desde script',
                'company_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insertar los 215 equipos una sola vez
        DB::transaction(function () use ($equipment) {
            InventoryModel::query()->insert($equipment);
        });

        Log::channel('import_clients')->info('Equipos generados correctamente', [
            'total' => count($equipment),
        ]);
    }

    /**
     * Formatea las fechas, pasandolas de excel a Carbon.
     *
     * @param mixed $val
     * @return string|null
     */
    private function dateParser(mixed $val): ?string
    {
        if ($val === null || trim((string)$val) === '') return '2025-01-01';

        try {
            if (is_numeric($val)) {
                return Date::excelToDateTimeObject((float)$val)->format('Y-m-d');
            }

            return Carbon::parse($val)->format('Y-m-d');
        } catch (\Throwable $e) {
            throw new \InvalidArgumentException("Fecha inválida recibida desde excel: {$val}");
        }
    }
}
