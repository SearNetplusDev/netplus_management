<?php

namespace App\Http\Controllers\v1\management\Monitoring;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\management\general\GeneralResource;
use App\Models\Infrastructure\Network\AuthServerModel;
use App\Models\Monitoring\ActiveConnectionModel;
use App\Models\Services\ServiceInternetModel;
use App\Services\v1\management\DataViewerService;
use App\Services\v1\monitoring\MikrotikConnectionSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InternetController extends Controller
{
    public function data(Request $request, DataViewerService $service): JsonResponse
    {
        $query = ActiveConnectionModel::query()
            ->with([
                'internet_service.service.client.dui',
                'internet_service.service.client.branch',
                'internet_service.service.client.mobile',
                'internet_service.service.client.financial_status.status',
            ]);

        return $service->handle($request, $query, [
            'financial_status' => fn($q, $data) => $q->whereHas('internet_service.service.client.financial_status', function ($q) use ($data) {
                return $q->whereIn('status_id', $data);
            }),
        ]);
    }

    /**
     * Busca un usuario PPPoE en equipo Mikrotik, y obtiene perfil, datos de navegación y datos generales de conexión.
     *
     * @param Request $request
     * @param MikrotikConnectionSyncService $service
     * @return JsonResponse
     * @throws \RouterOS\Exceptions\ClientException
     * @throws \RouterOS\Exceptions\ConfigException
     */
    public function pppoeInfo(Request $request, MikrotikConnectionSyncService $service): JsonResponse
    {
        $pppoe = trim($request->pppoe_user);
//        $user = ServiceInternetModel::query()
//            ->with(['service.node.auth_server'])
//            ->where('user', $pppoe)
//            ->first();
//        $server = $user->service->node->auth_server;
        $server = AuthServerModel::query()->findOrFail(config('mikrotik.main_server'));
        $data = $service->findActiveConnectionByUser(
            host: $server->ip,
            user: $server->user,
            pass: $server->secret,
            pppoeUser: $pppoe,
            port: $server->port,
        );

        return response()->json([
            'response' => new GeneralResource($data),
        ]);
    }
}
