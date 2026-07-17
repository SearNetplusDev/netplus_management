<?php

namespace App\Http\Controllers\v1\management\dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\management\general\GeneralResource;
use App\Models\Configuration\Clients\ClientTypeModel;
use App\Models\Infrastructure\Network\AuthServerModel;
use App\Models\Management\Profiles\InternetModel;
use App\Services\v1\management\dashboard\DashboardMikrotikService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /**
     * Conteo de clientes según la categoría de este.
     *
     * @return JsonResponse
     */
    public function clientsByType(): JsonResponse
    {
        $types = ClientTypeModel::query()
            ->withCount([
                'clients as total_clients' => function ($query) {
                    $query->where('status_id', true);
                }
            ])
            ->orderBy('name')
            ->get();

        return response()->json([
            'labels' => $types->pluck('name'),
            'data' => $types->pluck('total_clients'),
        ]);
    }

    /**
     * Información del hardware del servidor de autenticación.
     *
     * @param DashboardMikrotikService $mikrotikService
     * @return JsonResponse
     */
    public function systemResources(DashboardMikrotikService $mikrotikService): JsonResponse
    {
        $server = $this->authServer();

        $data = $mikrotikService->getSystemResources(
            host: $server->ip,
            user: $server->user,
            pass: $server->secret,
            port: $server->port,
        );

        return response()->json([
            'data' => new GeneralResource($data),
        ]);
    }


    /**
     * Obtiene los 5 perfiles con más usuarios.
     *
     * @return JsonResponse
     */
    public function topInternetProfiles(): JsonResponse
    {
        $profiles = InternetModel::query()
            ->withCount([
                'service_internet as total_services' => function ($query) {
                    $query->where('status_id', true);
                }
            ])
            ->orderByDesc('total_services')
            ->limit(10)
            ->get(['id', 'name', 'price']);

        return response()->json([
            'labels' => $profiles->map(fn($profile) => sprintf(
                '%s ($%.2f)',
                $profile->name,
                $profile->price
            )),
            'data' => $profiles->pluck('total_services'),
        ]);
    }

    /**
     * Datos de tráfico de las interfaces.
     *
     * @param DashboardMikrotikService $mikrotikService
     * @return JsonResponse
     */
    public function interfaceTraffic(DashboardMikrotikService $mikrotikService): JsonResponse
    {
        $server = $this->authServer();
        $data = $mikrotikService->getMultipleInterfacesTraffic(
            host: $server->ip,
            user: $server->user,
            pass: $server->secret,
            port: $server->port,
        );

        return response()->json([
            'data' => new GeneralResource($data),
        ]);
    }

    /**
     * Obtiene las interfaces activas junto con el acumulado de ellas.
     *
     * @param DashboardMikrotikService $mikrotikService
     * @return JsonResponse
     */
    public function interfacesList(DashboardMikrotikService $mikrotikService): JsonResponse
    {
        $server = $this->authServer();
        $data = $mikrotikService->getInterfaceList(
            host: $server->ip,
            user: $server->user,
            pass: $server->secret,
            port: $server->port,
        );
        return response()->json([
            'data' => new GeneralResource($data),
        ]);
    }

    /**
     * Listado de sesiones activas en el dispositivo.
     *
     * @param DashboardMikrotikService $mikrotikService
     * @return JsonResponse
     */
    public function activeSessions(DashboardMikrotikService $mikrotikService): JsonResponse
    {
        $server = $this->authServer();
        $data = $mikrotikService->getActiveSessions(
            host: $server->ip,
            user: $server->user,
            pass: $server->secret,
            port: $server->port,
        );
        return response()->json([
            'data' => new GeneralResource($data),
        ]);
    }

    /**
     * Datos del servidor de autenticación.
     *
     * @return AuthServerModel
     */
    private function authServer(): AuthServerModel
    {
        return AuthServerModel::query()->findOrFail(config('mikrotik.main_server'));
    }
}
