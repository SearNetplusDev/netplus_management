<?php

namespace App\Http\Controllers\v1\management\dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\management\general\GeneralResource;
use App\Models\Configuration\Clients\ClientTypeModel;
use App\Models\Infrastructure\Network\AuthServerModel;
use App\Models\Management\Profiles\InternetModel;
use App\Models\Supports\SupportModel;
use App\Services\v1\management\dashboard\DashboardMikrotikService;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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

    public function supportsByDay(): JsonResponse
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now();

        $rows = SupportModel::query()
            ->where('status_id', 3)
            ->whereBetween('closed_at', [$startDate, $endDate])
            ->whereIn('type_id', [1, 2, 3, 4, 6, 7, 8])
            ->selectRaw("
                DATE(closed_at) as day,
                CASE
                    WHEN type_id IN (1,2) THEN 'Instalaciones'
                    WHEN type_id IN (3,4) THEN 'Soportes'
                    WHEN type_id IN (6,7) THEN 'Renovaciones'
                    WHEN type_id = 8 THEN 'Desinstalaciones'
                END as category,
                COUNT(*) AS total
            ")
            ->groupBy(
                DB::raw('DATE(closed_at)'),
                DB::raw("
                    CASE
                        WHEN type_id IN (1,2) THEN 'Instalaciones'
                        WHEN type_id IN (3,4) THEN 'Soportes'
                        WHEN type_id IN (6,7) THEN 'Renovaciones'
                        WHEN type_id = 8 THEN 'Desinstalaciones'
                    END
                ")
            )
            ->orderBy('day')
            ->get();

        //  Fechas para el eje X
        $period = CarbonPeriod::create(now()->startOfMonth(), now()->today());
        $categories = collect($period)->map(fn($date) => $date->day)->values();

        $period = CarbonPeriod::create($startDate, $endDate);

        //  Categorías que aparecerán como series
        $seriesName = [
            'Instalaciones',
            'Soportes',
            'Renovaciones',
            'Desinstalaciones',
        ];

        $series = collect($seriesName)->map(function ($category) use ($rows, $period) {
            return [
                'name' => $category,
                'data' => collect($period)
                    ->map(function ($date) use ($rows, $category) {
                        $record = $rows->first(function ($item) use ($date, $category) {
                            return $item->day === $date->format('Y-m-d') && $item->category === $category;
                        });

//                        return (int)$record?->total;
                        return (int)random_int(1, 50);
                    })->values()->all(),
            ];
        })->values();

        return response()->json([
            'categories' => $categories,
            'series' => $series,
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
