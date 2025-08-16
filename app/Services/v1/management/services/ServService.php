<?php

namespace App\Services\v1\management\services;

use App\DTOs\v1\management\services\ServiceDTO;
use App\Models\Clients\ClientModel;
use App\Models\Services\ServiceModel;

class ServService
{
    public function create(ServiceDTO $DTO): ServiceModel
    {
        return ServiceModel::query()->create($DTO->toArray());
    }

    public function read(int $id): ServiceModel
    {
        return ServiceModel::query()->find($id);
    }

    public function update(ServiceModel $model, ServiceDTO $DTO): ServiceModel
    {
        $model->update($DTO->toArray());
        return $model;
    }

    public function clientServices(int $id): ClientModel
    {
        return ClientModel::query()->with([
            'services.node:id,name',
            'services.equipment:id,name',
            'services.state:id,name',
            'services.municipality:id,name',
            'services.district:id,name',
            'services.internet.profile',
        ])->find($id);
    }
}
