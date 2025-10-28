<?php

namespace App\Strategies\v1\Operations\Technical;


use App\DTOs\v1\management\services\ServiceDTO;
use App\DTOs\v1\management\services\ServiceInternetDTO;
use App\Enums\v1\General\CommonStatus;
use App\Enums\v1\Supports\SupportStatus;
use App\Models\Clients\ClientModel;
use App\Models\Infrastructure\Network\NodeModel;
use App\Models\Management\Profiles\InternetModel;
use App\Models\Services\ServiceInternetModel;
use App\Models\Services\ServiceModel;
use App\Models\Supports\SupportModel;
use App\Services\v1\network\MikrotikInternetService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class InstallationStrategy extends BaseSupportStrategy
{
    /***
     * @param MikrotikInternetService $mikrotikService
     */
    public function __construct(private MikrotikInternetService $mikrotikService)
    {

    }

    /***
     * @param SupportModel $model
     * @param array $params
     * @return SupportModel
     */
    public function handle(SupportModel $model, array $params): SupportModel
    {
        $status = SupportStatus::tryFrom((int)$params['status']);
        if (!$status || !$status->isFinalized()) return $model;
        if ($model->service_id) return $model;

        $this->validateClientStatus($params['client']);

        try {
            DB::transaction(function () use ($model, $params) {
                $service = $this->findOrCreateService($params);
                $this->handleCredentials($service, $params);
                $this->updateSupport($model, $service->id, $params);
            });

        } catch (Throwable $e) {
            throw ValidationException::withMessages([
                'process' => "No se pudo completar la instalación: {$e->getMessage()}"
            ]);
        }

        return $model;
    }

    /***
     * Verifica el estado del cliente
     * @param int $clientId
     * @return void
     */
    private function validateClientStatus(int $clientId): void
    {
        $client = ClientModel::query()->select('status_id')->findOrFail($clientId);
        $status = CommonStatus::from($client->status_id);

        if ($status->isInactive()) {
            throw ValidationException::withMessages([
                'client' => "Este cliente debe estar activo para crear el servicio."
            ]);
        }
    }

    /***
     * @param array $params
     * @return ServiceModel
     */
    private function findOrCreateService(array $params): ServiceModel
    {
        $service = ServiceModel::query()
            ->where('client_id', (int)$params['client'])
            ->where('status_id', CommonStatus::INACTIVE->value)
            ->oldest()
            ->first();

        return $service
            ? tap($service)->update([
                'status_id' => CommonStatus::ACTIVE->value,
                'installation_date' => Carbon::today(),
                'node_id' => $params['node'],
                'equipment_id' => $params['equipment'],
                'latitude' => $params['latitude'],
                'longitude' => $params['longitude'],
                'state_id' => $params['state'],
                'municipality_id' => $params['municipality'],
                'district_id' => $params['district'],
                'address' => $params['address'],
                'technician_id' => $params['technician'],
                'comments' => $params['comments'] ?? null,
            ])
            : $this->createService($params);
    }

    /***
     * @param array $params
     * @return ServiceModel
     */
    private function createService(array $params): ServiceModel
    {
        $dto = new ServiceDTO(
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
            separate_billing: CommonStatus::ACTIVE->value,
            status_id: CommonStatus::ACTIVE->value,
            comments: $params['comments'] ?? null,
        );

        return ServiceModel::query()->create($dto->toArray());
    }

    /***
     * @param ServiceModel $service
     * @param array $params
     * @return ServiceInternetModel
     */
    private function handleCredentials(ServiceModel $service, array $params): ServiceInternetModel
    {
        $credentials = ServiceInternetModel::query()
            ->where('service_id', $service->id)
            ->first();

        $node = NodeModel::query()->with('auth_server')->findOrFail((int)$params['node']);
        $server = $node->auth_server->toArray();
        $profile = InternetModel::query()->findOrFail((int)$params['profile']);
        $client = ClientModel::query()->select(['name', 'surname'])->findOrFail((int)$params['client']);
        $comment = "{$client->name} {$client->surname}";

        //  No encuentra credenciales, crearlas
        if (!$credentials) return $this->createInternetCredentials($service, $params);

        $nodeChanged = $service->node_id !== (int)$params['node'];
        $profileChanged = $credentials->internet_profile_id !== (int)$params['profile'];

        //  Hay credenciales, pero los nodos no coinciden eliminar credenciales y crear nuevas
        if ($nodeChanged) {
            try {
                //  Eliminando usuario de nodo antiguo.
                $oldNode = NodeModel::query()->with('auth_server')->findOrFail($service->node_id);
                $oldServer = $oldNode->auth_server->toArray();

                $this->mikrotikService->deleteUser($oldServer, $credentials->user);
            } catch (Throwable $e) {
                throw ValidationException::withMessages([
                    'mikrotik' => "Error al eliminar el usuario PPPoe: {$e->getMessage()}"
                ]);
            }
            $credentials->delete();

            return $this->createInternetCredentials($service, $params);
        }

        //  Hay credenciales, pero los perfiles no coinciden
        if ($profileChanged) {
            try {
                $this->mikrotikService->updateUser($server, $credentials->user, [
                    'profile' => $profile->mk_profile,
                    'comment' => $comment,
                ]);

                $credentials->update([
                    'internet_profile_id' => (int)$params['profile'],
                    'status_id' => CommonStatus::ACTIVE->value,
                ]);
            } catch (Throwable $e) {
                throw ValidationException::withMessages([
                    'mikrotik' => "Error al actualizar el usuario PPPoe: {$e->getMessage()}",
                ]);
            }
        }

        //  Credenciales inactivas
        if ($credentials->status_id === CommonStatus::INACTIVE->value) {
            try {
                //  Habilitar usuario
                $this->mikrotikService->enableUser($server, $credentials->user);
                $credentials->update([
                    'status_id' => CommonStatus::ACTIVE->value,
                ]);

            } catch (Throwable $e) {
                throw ValidationException::withMessages([
                    'mikrotik' => "Error al reactivar el usuario PPPoe: {$e->getMessage()}"
                ]);
            }
        }

        return $credentials->refresh();
    }

    /***
     * @param ServiceModel $service
     * @param array $params
     * @return ServiceInternetModel
     */
    private function createInternetCredentials(ServiceModel $service, array $params): ServiceInternetModel
    {
        $node = NodeModel::query()->with('auth_server')->findOrFail((int)$params['node']);
        $client = ClientModel::query()->select(['name', 'surname'])->findOrFail((int)$params['client']);

        $username = $this->generateUser($node->prefix, $node->id);
        $password = $this->generateSecret($client);

        $dto = new ServiceInternetDTO(
            internet_profile_id: (int)$params['profile'],
            service_id: $service->id,
            user: $username,
            secret: $password,
            status_id: CommonStatus::ACTIVE->value,
        );

        $server = $node->auth_server->toArray();
        $profile = InternetModel::query()->findOrFail((int)$params['profile'])->toArray();
        $comment = "{$client->name} {$client->surname}";

        $this->mikrotikService->createUser($server, $profile, $username, $password, $comment);

        return ServiceInternetModel::query()->create($dto->toArray());
    }

    /***
     * @param string $prefix
     * @param int $node
     * @return string
     */
    private function generateUser(string $prefix, int $node): string
    {
        $prefix = 'NetPlus' . $prefix;
        $services = ServiceModel::query()
            ->where('node_id', $node)
            ->withTrashed()
            ->count();
        $maxLength = 5;
        $zeroFill = max(0, $maxLength - strlen($services));
        $filling = str_repeat('0', $zeroFill);
        $prefix .= $filling . $services;
        return $prefix;
    }

    /***
     * @param object $client
     * @return string
     */
    private function generateSecret(object $client): string
    {
        $firstName = explode(' ', trim($client->name))[0];
        $firstSurname = explode(' ', trim($client->surname))[0];
        $name = $firstName . '_' . $firstSurname;
        return transliterator_transliterate('Any-Latin; Latin-ASCII', $name);
    }

    /***
     * @param SupportModel $model
     * @param int $serviceId
     * @param array $params
     * @return void
     */
    private function updateSupport(SupportModel $model, int $serviceId, array $params): void
    {
        $model->update([
            'service_id' => $serviceId,
            'comments' => $params['comments'] ?? $model->comments,
        ]);
    }
}
