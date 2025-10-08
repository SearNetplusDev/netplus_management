<?php

namespace App\Services\v1\management\operations;

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
                'service.internet_devices',
                'service.iptv_devices.equipment',
                'service.node:id,name',
                'service.equipment:id,name',
            ])->find($id);
    }
}
