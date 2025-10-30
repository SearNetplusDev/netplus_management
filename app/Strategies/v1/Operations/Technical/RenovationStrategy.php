<?php

namespace App\Strategies\v1\Operations\Technical;

use App\Enums\v1\General\CommonStatus;
use App\Enums\v1\Supports\SupportStatus;
use App\Models\Clients\ClientModel;
use App\Models\Infrastructure\Network\NodeModel;
use App\Models\Management\Profiles\InternetModel;
use App\Models\Services\ServiceInternetModel;
use App\Models\Services\ServiceModel;
use App\Models\Supports\SupportModel;
use App\Services\v1\network\MikrotikInternetService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class RenovationStrategy extends BaseSupportStrategy
{
    /***
     * @param MikrotikInternetService $mikrotikInternetService
     */
    public function __construct(private MikrotikInternetService $mikrotikInternetService)
    {
    }

    /**
     * @throws ValidationException
     * */
    public function handle(SupportModel $model, array $params): SupportModel
    {
        $status = SupportStatus::tryFrom((int)$params['status']);
        if (!$status || !$status->isFinalized()) return $model;

        try {
            DB::transaction(function () use ($model, $params) {
                $this->ensureExistingService($model);
                $this->ensureServiceStatus($model);
                $this->updateInternetProfile($model, $params);
            });
        } catch (Throwable $e) {
            throw ValidationException::withMessages([
                'support' => "Error al procesar la renovación del servicio: {$e->getMessage()}"
            ]);
        }

        return $model->refresh();
    }

    private function updateInternetProfile(SupportModel $model, array $params): void
    {
        $service = ServiceModel::query()->findOrFail((int)$params['service']);
        $credentials = $this->getCredentials($service->id);
        $nodeChanged = $service->node_id !== (int)$params['node'];
        $profileChanged = $credentials->internet_profile_id !== (int)$params['profile'];

        if ($nodeChanged) $this->changeNode($service, $params);
        if ($profileChanged) $this->changeProfile();
    }

    private function changeNode(ServiceModel $service, array $params): void
    {
        $newNode = $this->getNode((int)$params['node']);
        $oldNode = $this->getNode($service->node_id);

        //  Actualizando servicio
        $service->update([
            'node_id' => (int)$params['node'],
            'equipment_id' => (int)$params['equipment'],
            'technician_id' => (int)$params['technician'],
            'latitude' => $params['latitude'],
            'longitude' => $params['longitude'],
            'state_id' => (int)$params['state'],
            'municipality_id' => (int)$params['municipality'],
            'district_id' => (int)$params['district'],
            'address' => $params['address'],
        ]);
        $oldServer = $oldNode->auth_server->toArray();

        //  Obteniendo Credenciales
        $credentials = $this->getCredentials($service->id);

        //  Eliminando credenciales en old server
        $this->mikrotikInternetService->deleteUser($oldServer, $credentials->user);

        //  Actualizando credenciales y creando nuevas
        $this->updateCredentials($newNode, $service, $credentials, $params);
    }

    private function changeProfile()
    {

    }

    private function updateCredentials(
        NodeModel             $node,
        ServiceModel          $service,
        ?ServiceInternetModel $credentials,
        array                 $params
    ): void
    {
        $user = $this->generateUser($node->prefix, $service->node_id);
        $client = $this->getClient($service->client_id);
        $password = $this->generateSecret($client);
        $comment = "{$client->name} {$client->surname}";
        $profile = $this->getProfile((int)$params['profile'])->toArray();
        $server = $node->auth_server->toArray();

        // Actualizando credenciales en la base de datos
        $credentials->update([
            'internet_profile_id' => (int)$params['profile'],
            'user' => $user,
            'password' => $password,
            'status_id' => CommonStatus::ACTIVE->value,
        ]);

        //  Creando credenciales en nuevo servidor
        $this->mikrotikInternetService->createUser($server, $profile, $user, $password, $comment);
    }

    /***
     * @param int $nodeId
     * @return NodeModel
     */
    private function getNode(int $nodeId): NodeModel
    {
        return NodeModel::query()->with('auth_server')->findOrFail($nodeId);
    }

    /***
     * @param int $clientId
     * @return ClientModel
     */
    private function getClient(int $clientId): ClientModel
    {
        return ClientModel::query()->select(['name', 'surname'])->findOrFail($clientId);
    }

    private function getCredentials(int $serviceId): ServiceInternetModel
    {
        return ServiceInternetModel::query()->where('service_id', $serviceId)->first();
    }

    /***
     * @param string $prefix
     * @param int $nodeId
     * @return string
     */
    private function generateUser(string $prefix, int $nodeId): string
    {
        $prefix = 'NetPlus' . $prefix;
        $count = ServiceModel::query()->where('node_id', $nodeId)->withTrashed()->count();
        return sprintf("%s%05d", $prefix, $count);
    }

    /***
     * @param object $client
     * @return string
     */
    private function generateSecret(object $client): string
    {
        $firstName = explode(' ', trim($client->name))[0];
        $firstSurname = explode(' ', trim($client->surname))[0];
        $name = $firstName . ' ' . $firstSurname;
        $ascii = transliterator_transliterate('Any-Latin; Latin-ASCII', $name);
        return preg_replace('/[^A-Za-z0-9_]/', '_', $ascii);
    }

    /***
     * @param int $profileId
     * @return InternetModel
     */
    public function getProfile(int $profileId): InternetModel
    {
        return InternetModel::query()->findOrFail($profileId);
    }
}
