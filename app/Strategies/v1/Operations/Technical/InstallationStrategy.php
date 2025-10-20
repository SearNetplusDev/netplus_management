<?php

namespace App\Strategies\v1\Operations\Technical;

use App\DTOs\v1\management\services\ServiceDTO;
use App\DTOs\v1\management\services\ServiceInternetDTO;
use App\Enums\v1\Supports\SupportStatus;
use App\Models\Clients\ClientModel;
use App\Models\Infrastructure\Network\NodeModel;
use App\Models\Services\ServiceInternetModel;
use App\Models\Services\ServiceModel;
use App\Models\Supports\SupportModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class InstallationStrategy extends BaseSupportStrategy
{
    /**
     * @throws ValidationException
     **/

    public function handle(SupportModel $model, array $params): SupportModel
    {
        /*****
         * To Do: Conectar con API de Mikrotik
         *****/

        $status = SupportStatus::tryFrom((int)$params['status']);
        if (!$status || !$status->isFinalized()) return $model;
        if ($model->service_id) return $model;

        $this->validateClientStatus((int)$params['client']);

        try {
            DB::transaction(function () use ($model, $params) {
                $service = $this->createService($params);
                $credentials = $this->createInternetCredentials($service, $params);
                $this->updateSupport($model, $service->id, $params);
            });

        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw ValidationException::withMessages([
                'process' => "No se pudo completar la instalación. Intenta nuevamente.",
            ]);
        }

        return $model->fresh();
    }

    /****
     *  Verifica que el cliente este activo
     * @throws ValidationException
     *****/
    private function validateClientStatus(int $clientId): void
    {
        $client = ClientModel::query()->select('status_id')->findOrFail($clientId);
        if ($client->status_id !== true) {
            throw ValidationException::withMessages([
                'client' => "El cliente debe estar activo para crearse el servicio."
            ]);
        }
    }

    /*****
     *  Crea el servicio principal
     *****/
    private function createService(array $params): ServiceModel
    {
        $DTO = new ServiceDTO(
            client_id: $params['client'],
            code: null,
            name: null,
            node_id: $params['node'],
            equipment_id: $params['equipment'],
            installation_date: Carbon::today(),
            technician_id: $params['technician'],
            latitude: $params['latitude'],
            longitude: $params['longitude'],
            state_id: $params['state'],
            municipality_id: $params['municipality'],
            district_id: $params['district'],
            address: $params['address'],
            separate_billing: 1,
            status_id: 1,
            comments: $params['comments'] ?? null,
        );
        return ServiceModel::query()->create($DTO->toArray());
    }

    /*****
     *  Crea las credenciales de internet para el servicio
     *****/
    private function createInternetCredentials(ServiceModel $service, array $params): ServiceInternetModel
    {
        $node = NodeModel::query()->select('prefix')->findOrFail((int)$params['node']);
        $client = ClientModel::query()->select(['name', 'surname'])->findOrFail((int)$params['client']);
        $user = $this->generateUser($node->prefix);
        $secret = $this->generateSecret($client);
        $DTO = new ServiceInternetDTO(
            internet_profile_id: (int)$params['profile'],
            service_id: $service->id,
            user: $user,
            secret: $secret,
            status_id: 1
        );
        return ServiceInternetModel::query()->create($DTO->toArray());
    }

    /*****
     *  Genera el usuario del servicio
     ******/
    private function generateUser(string $prefix): string
    {
        return 'NetPlus' . $prefix;
    }

    /*****
     *  Genera la contraseña del servicio
     *****/
    private function generateSecret(object $client): string
    {
        $firstName = explode(' ', trim($client->name))[0];
        $firstSurname = explode(' ', trim($client->surname))[0];
        $name = $firstName . '_' . $firstSurname;
        return transliterator_transliterate('Any-Latin; Latin-ASCII', $name);
    }

    /*****
     *  Actualiza el soporte con el ID del servicio creado
     *****/
    private function updateSupport(SupportModel $model, int $serviceId, array $params): void
    {
        $model->update([
            'service_id' => $serviceId,
            'comments' => $params['comments'] ?? $model->comments,
        ]);
    }
}
