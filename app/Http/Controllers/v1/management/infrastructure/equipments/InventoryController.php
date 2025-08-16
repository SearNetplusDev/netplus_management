<?php

namespace App\Http\Controllers\v1\management\infrastructure\equipments;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\v1\Management\Infrastructure\Equipments\InventoryRequest;
use Illuminate\Http\JsonResponse;
use App\Services\v1\management\DataViewerService;
use App\Services\v1\management\infrastructure\equipments\InventoryService;
use App\Models\Infrastructure\Equipment\InventoryModel;
use App\Http\Resources\v1\management\infrastructure\equipments\InventoryResource;

class InventoryController extends Controller
{
    public function data(Request $request, DataViewerService $dataViewer): JsonResponse
    {
        $query = InventoryModel::query()
            ->with([
                'brand:id,name',
                'type:id,name',
                'model:id,name',
                'branch:id,name',
                'technician',
                'status:id,name,badge_color',
            ]);

        return $dataViewer->handle($request, $query, [
            'brand' => fn($q, $data) => $q->whereIn('brand_id', $data),
            'type' => fn($q, $data) => $q->whereIn('type_id', $data),
            'model' => fn($q, $data) => $q->whereIn('model_id', $data),
            'branch' => fn($q, $data) => $q->whereIn('branch_id', $data),
            'technician' => fn($q, $data) => $q->whereIn('technician_id', $data),
            'status' => fn($q, $data) => $q->whereIn('status_id', $data),
        ]);
    }

    public function singleStore(InventoryRequest $request, InventoryService $service): JsonResponse
    {
        $equipment = $service->singleCreate($request->toDTO());

        return response()->json([
            'saved' => (bool)$equipment,
            'equipment' => new InventoryResource($equipment),
        ]);
    }

    public function read(Request $request, InventoryService $service): JsonResponse
    {
        return response()->json([
            'equipment' => new InventoryResource($service->read($request->input('id')))
        ]);
    }

    public function update(InventoryRequest $request, InventoryModel $id, InventoryService $service): JsonResponse
    {
        $equipment = $service->update($id, $request->toDTO());

        return response()->json([
            'saved' => (bool)$equipment,
            'equipment' => new InventoryResource($equipment),
        ]);
    }
}
