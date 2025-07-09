<?php

namespace App\Http\Controllers\v1\management\clients;

use App\Http\Controllers\Controller;
use App\Services\v1\management\client\ClientGeneralDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\v1\Management\Clients\ClientRequest;
use App\Models\Clients\ClientModel;
use App\Services\v1\management\DataViewerService;
use App\Http\Resources\v1\management\clients\GeneralDataResource;

class ClientsController extends Controller
{
    public function data(Request $request, DataViewerService $dataViewerService): JsonResponse
    {
        $query = ClientModel::query()->with([
            'branch',
            'client_type',
            'dui',
            'mobile',
            'address.state',
            'address.district'
        ]);

        return $dataViewerService->handle($request, $query, [
            'status' => fn($q, $data) => $query->whereIn('status_id', $data),
            'branch' => fn($q, $data) => $data->whereIn('branch_id', $data),
            'type' => fn($q, $data) => $data->whereIn('client_type_id', $data),
        ]);
    }

    public function store(ClientRequest $request, ClientGeneralDataService $clientGeneralDataService): JsonResponse
    {
        $client = $clientGeneralDataService->createClient($request->toDTO());
        return response()->json([
            'saved' => (bool)$client,
            'client' => new GeneralDataResource($client),
        ]);
    }

    public function edit(Request $request): JsonResponse
    {
        return response()->json(['client' => ClientModel::query()->findOrFail($request->id)]);
    }

    public function update(ClientRequest $request, ClientModel $id, ClientGeneralDataService $clientGeneralDataService): JsonResponse
    {
        $client = $clientGeneralDataService->updateClient($id, $request->toDTO());

        return response()->json([
            'saved' => (bool)$client,
            'client' => new GeneralDataResource($client),
        ]);
    }
}
