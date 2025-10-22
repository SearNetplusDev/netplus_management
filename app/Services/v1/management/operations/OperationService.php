<?php

namespace App\Services\v1\management\operations;

use App\Libraries\MikrotikAPI;
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

    /***
     * @return array
     * @throws \RouterOS\Exceptions\ClientException
     * @throws \RouterOS\Exceptions\ConfigException
     * @throws \RouterOS\Exceptions\QueryException
     */
    public function mikrotik(): array
    {
        $routerOs = new MikrotikAPI();
        return $routerOs->executeQuery(
            '192.168.1.86',
            'Eustass',
            'jg3kifei8s',
            '/ppp/profile/print',
        );
//        return $routerOs->listPPPPoeProfiles(
//            '192.168.1.86',
//            'Eustass',
//            'jg3kifei8s'
//        );
    }
}
