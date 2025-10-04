<?php

namespace App\Services\v1\management\operations;

use App\Models\Supports\SupportModel;

class OperationService
{
    public function getSupportData(int $id)
    {
        return SupportModel::query()
            ->with([
                'details.profile',
                'client',
                'service.internet.profile',
                'service.internet_devices',
                'service.iptv_devices.equipment',
                'service.node',
                'service.equipment',
            ])->find($id);
    }
}
