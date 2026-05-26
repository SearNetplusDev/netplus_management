<?php

namespace App\Http\Controllers\v1\management\dashboard;

use App\Http\Controllers\Controller;
use App\Models\Clients\ClientModel;
use App\Models\Configuration\Clients\ClientTypeModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function clientsByType(): JsonResponse
    {
//        $data = ClientModel::query()
//            ->where('clients.status_id', true)
//            ->join('config_client_types', 'config_client_types.id', '=', 'clients.client_type_id')
//            ->select('config_client_types.name', DB::raw('COUNT(clients.id) as total'))
//            ->groupBy('config_client_types.name')
//            ->get();
//
//        return response()->json([
//            'labels' => $data->pluck('name'),
//            'data' => $data->pluck('total'),
//        ]);

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
}
