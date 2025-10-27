<?php

namespace App\Services\v1\management\operations;

use App\Libraries\MikrotikAPI;
use App\Models\Infrastructure\Network\AuthServerModel;
use App\Models\Management\Profiles\InternetModel;
use App\Models\Services\ServiceInternetModel;
use App\Models\Services\ServiceModel;
use App\Models\Supports\SupportModel;

class OperationService
{
    public function getSupportData(int $id)
    {
        return SupportModel::query()
            ->with([
                'type:id,name',
                'details.profile',
                'client',
                'service.internet.profile',
                'service.internet_devices.equipment.type:id,name',
                'service.iptv_devices.equipment.type:id,name',
                'service.sold_devices.equipment.type:id,name',
                'service.node:id,name',
                'service.equipment:id,name',
            ])->find($id);
    }

    public function process(SupportModel $model, array $params)
    {
        $strategy = ProcessSupportFactory::make((int)$params['type']);
        return $strategy->process($model, $params);
    }
}
