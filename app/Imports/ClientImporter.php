<?php

namespace App\Imports;

use App\Models\Clients\AddressModel;
use App\Models\Clients\ClientModel;
use App\Models\Clients\DocumentModel;
use App\Models\Clients\PhoneModel;
use App\Models\Clients\ReferenceModel;
use App\Models\Services\ServiceInternetModel;
use App\Models\Services\ServiceModel;
use App\Services\v1\imports\ImportClientsService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use function Laravel\Prompts\number;

abstract class ClientImporter implements ToCollection, WithHeadingRow, WithChunkReading
{
    protected array $processedClients = [];
    protected array $errors = [];
    protected int $successCount = 0;
    protected int $errorCount = 0;
    protected int $genderId = 1;
    protected int $maritalStatusDefault = 1;
    protected int $branchId = 1;
    protected int $clientTypeId = 1;
    protected int $countryId = 59;
    protected int $documentTypeDui = 3;
    protected int $documentTypeNit = 4;
    protected int $phoneTypeMobile = 2;
    protected int $kinshipIdDefault = 24;
    protected int $technicianIdDefault = 1;
    protected int $equipmentIdDefault = 1;

    public function __construct(private ImportClientsService $importClientsService)
    {

    }

    /***
     * @param Collection $rows
     * @return void
     * @throws \Throwable
     */
    public function collection(Collection $collection): void
    {
        foreach ($collection as $index => $row) {
            $rowNumber = $index + 2;

            try {
                DB::transaction(function () use ($row, $rowNumber) {
                    $this->processRow($row, $rowNumber);
                });
                $this->successCount++;
            } catch (\Exception $e) {
                DB::rollBack();
                $this->errorCount++;
                $this->errors[] = [
                    'row' => $rowNumber,
                    'error' => $e->getMessage(),
                    'data' => $row->toArray(),
                ];

                Log::error("Error importando fila {$rowNumber}: " . $e->getMessage(), [
                    'row_data' => $row->toArray(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }
    }

    /***
     * @param Collection $row
     * @param int $rowNumber
     * @return void
     * @throws \Exception
     */
    protected function processRow(Collection $row, int $rowNumber): void
    {
        $dui = $this->cleanDUI($row['dui'] ?? null);
        if (empty($dui)) {
            throw new \Exception("DUI no encontrado en la fila {$rowNumber}");
        }
        $client = $this->getOrCreateClient($row, $dui);
        $this->createService($client, $row);
    }

    protected function getOrCreateClient(Collection $collection, string $dui): ClientModel
    {
        if (isset($this->processedClients[$dui])) {
            return $this->processedClients[$dui];
        }

        $existingClient = ClientModel::whereHas('dui', function ($query) use ($dui) {
            $query->where('number', $dui);
        })->first();

        if ($existingClient) {
            $this->processedClients[$dui] = $existingClient;
            return $existingClient;
        }

        $client = $this->createClient($collection);
        $this->createClientDocuments($client, $collection, $dui);
        $this->createClientAddress($client, $collection);
        $this->createClientPhone($client, $collection);
        $this->createClientReference($client, $collection);

        $this->processedClients[$dui] = $client;
        return $client;
    }

    /***
     * @param Collection $collection
     * @return ClientModel
     */
    protected function createClient(Collection $collection): ClientModel
    {
        $nameParts = $this->importClientsService->splitFullName($collection['name'] ?? '');

        return ClientModel::query()
            ->create([
                'name' => $this->importClientsService->parsedName($nameParts['first_names']),
                'surname' => $this->importClientsService->parsedName($nameParts['last_names']),
                'gender_id' => $this->genderId,
                'birthdate' => $this->importClientsService->getBirthDate($collection['nit']),
                'marital_status_id' => $collection['civil_status'],
                'branch_id' => $this->branchId,
                'client_type_id' => $this->clientTypeId,
                'profession' => 'Otro',
                'country_id' => $this->countryId,
                'document_type_id' => 1,
                'legal_entity' => false,
                'status_id' => true,
            ]);
    }

    /***
     * @param ClientModel $client
     * @param Collection $collection
     * @param string $dui
     * @return void
     */
    protected function createClientDocuments(ClientModel $client, Collection $collection, string $dui): void
    {
        DocumentModel::query()
            ->create([
                'client_id' => $client->id,
                'document_type_id' => $this->documentTypeDui,
                'number' => $dui,
                'expiration_date' => Carbon::now()->addYears(6)->format('Y-m-d'),
                'status_id' => true,
            ]);

        if ($collection['nit'] != 'HOMOLOGADO') {
            DocumentModel::query()
                ->create([
                    'client_id' => $client->id,
                    'document_type_id' => $this->documentTypeNit,
                    'number' => $dui,
                    'expiration_date' => Carbon::now()->addYears(30)->format('Y-m-d'),
                    'status_id' => true,
                ]);
        }
    }

    /***
     * @param ClientModel $client
     * @param Collection $collection
     * @return void
     */
    protected function createClientAddress(ClientModel $client, Collection $collection): void
    {
        $municipality = match ($collection['district']) {
            234, 243, 230, 241 => 42,
            201 => 38,
            208, 209, 211 => 39,
            14 => 4,
            216 => 40,
            195 => 37,
            188 => 36,
            default => 0,
        };

        AddressModel::query()
            ->create([
                'client_id' => $client->id,
                'neighborhood' => 'Agregar',
                'address' => $this->importClientsService->parsedName($collection['address']),
                'state_id' => $collection['state'],
                'municipality_id' => $municipality,
                'district_id' => $collection['district'],
                'country_id' => $this->countryId,
                'status_id' => true,
            ]);
    }

    /***
     * @param ClientModel $client
     * @param Collection $collection
     * @return void
     */
    protected function createClientPhone(ClientModel $client, Collection $collection): void
    {
        if (!empty($collection['phone'])) {
            PhoneModel::query()
                ->create([
                    'client_id' => $client->id,
                    'phone_type_id' => $this->phoneTypeMobile,
                    'number' => $collection['phone'],
                    'status_id' => true,
                ]);
        }
    }

    /***
     * @param ClientModel $client
     * @param Collection $collection
     * @return void
     */
    protected function createClientReference(ClientModel $client, Collection $collection): void
    {
        ReferenceModel::query()
            ->create([
                'client_id' => $client->id,
                'name' => $this->importClientsService->parsedName($collection['reference_name']),
                'dui' => $collection['reference_dui'],
                'mobile' => $collection['reference_phone'],
                'kinship_id' => $this->kinshipIdDefault,
                'status_id' => true,
            ]);
    }

    /***
     * @param ClientModel $client
     * @param Collection $collection
     * @return void
     */
    protected function createService(ClientModel $client, Collection $collection): void
    {
        $nodeName = strtolower($collection['node_name']);
        $node = match ($nodeName) {
            'altomiro' => 1,
            'san jacinto' => 2,
            'jucuaran' => 3,
            'chirilagua' => 4,
            'conective' => 5,
            'casitas' => 6,
            'toledo' => 7,
        };

        $municipality = match ($collection['district']) {
            234, 243, 230, 241 => 42,
            201 => 38,
            208, 209, 211 => 39,
            14 => 4,
            216 => 40,
            195 => 37,
            188 => 36,
            default => 0,
        };

        $service = ServiceModel::query()
            ->create([
                'client_id' => $client->id,
                'code' => null,
                'name' => null,
                'node_id' => $node,
                'equipment_id' => 1,
                'installation_date' => Carbon::parse($collection['installation_date'])->format('Y-m-d'),
                'technician_id' => $this->technicianIdDefault,
                'latitude' => 13.00000000,
                'longitude' => -88.00000000,
                'state_id' => $collection['state'],
                'municipality_id' => $municipality,
                'district_id' => $collection['district'],
                'address' => $this->importClientsService->parsedName($collection['address']),
                'separate_billing' => true,
                'status_id' => true,
            ]);

        $this->createInternetService($service, $collection);
    }

    /***
     * @param ServiceModel $service
     * @param Collection $collection
     * @return void
     */
    protected function createInternetService(ServiceModel $service, Collection $collection): void
    {
        ServiceInternetModel::query()
            ->create([
                'internet_profile_id' => 1,
                'service_id' => $service->id,
                'user' => $collection['pppoe_user'],
                'secret' => $collection['pppoe_password'],
                'status_id' => true,
            ]);
    }
}
