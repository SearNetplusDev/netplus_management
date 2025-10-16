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

class InstallationStrategy extends BaseSupportStrategy
{
    public function handle(SupportModel $model, array $params): SupportModel
    {
        /****
         * Creando Servicio
         ****/
        $status = SupportStatus::tryFrom((int)$params['status']);
        if (!$status || !$status->isFinalized()) return $model;
        if ($model->service_id) return $model;

        $serviceDTO = new ServiceDTO(
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
        $service = ServiceModel::query()->create($serviceDTO->toArray());

        /****
         * Creando credenciales de internet
         ****/

        $prefix = 'NetPlus';
        $node = NodeModel::query()
            ->select('prefix')
            ->findOrFail((int)$params['node']);
        $client = ClientModel::query()
            ->select(['name', 'surname'])
            ->findOrFail((int)$params['client']);
        $prefix .= $node->prefix;
        $secret = $this->generateSecret($client);

        $credentialsDTO = new ServiceInternetDTO(
            internet_profile_id: (int)$params['profile'],
            service_id: $service->id,
            user: $prefix,
            secret: $secret,
            status_id: 1
        );
        ServiceInternetModel::query()->create($credentialsDTO->toArray());

        /****
         * Actualizando Soporte
         ****/
        $model->service_id = $service->id;
        $model->comments = $params['comments'] ?? $model->comments;
        $model->save();

        return $model;

    }

    private function generateSecret(object $client): string
    {
        $firstName = explode(' ', trim($client->name))[0];
        $firstSurname = explode(' ', trim($client->surname))[0];
        $name = $firstName . '_' . $firstSurname;
        return transliterator_transliterate('Any-Latin; Latin-ASCII', $name);
    }
}
