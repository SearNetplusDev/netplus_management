<?php

namespace App\Strategies\v1\Operations\Technical;

use App\Contracts\v1\Supports\ProcessSupportInterface;
use App\DTOs\v1\management\services\ServiceDTO;
use App\Models\Services\ServiceModel;
use App\Models\Supports\SupportModel;
use Carbon\Carbon;

class InstallationStrategy implements ProcessSupportInterface
{

    public function process(array $params): SupportModel
    {
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

        dd($serviceDTO);

//        ServiceModel::query()->create($serviceDTO->toArray());
    }
}
