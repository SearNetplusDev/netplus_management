<?php

namespace App\Http\Controllers\v1\management\services;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\v1\management\DataViewerService;
use App\Models\Clients\ClientModel;

class ServicesController extends Controller
{
    public function data(Request $request, DataViewerService $viewerService): JsonResponse
    {
        $clients = ClientModel::query()
            ->with([
                'branch',
                'client_type',
                'dui',
                'mobile',
                'address.state',
                'address.district',
                'services',
            ]);

        return $viewerService->handle($request, $clients, [
            'status' => fn($q, $data) => $q->whereIn('status_id', $data),
            'branch' => fn($q, $data) => $q->whereIn('branch_id', $data),
            'type' => fn($q, $data) => $q->whereIn('client_type_id', $data),
            'state' => fn($q, $data) => $q->whereHas('address', function ($q) use ($data) {
                return $q->where('state_id', $data);
            }),
            'district' => fn($q, $data) => $q->whereHas('address', function ($q) use ($data) {
                return $q->where('district_id', $data);
            })
        ]);
    }
}
