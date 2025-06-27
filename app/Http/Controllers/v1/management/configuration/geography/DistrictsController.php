<?php

namespace App\Http\Controllers\v1\management\configuration\geography;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\management\configuration\geography\DistrictResource;
use App\Services\v1\management\configuration\geography\DistrictService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\v1\Management\Configuration\DistrictsRequest;
use App\Models\Configuration\DistrictModel;

class DistrictsController extends Controller
{
    public function data(Request $request): JsonResponse
    {
        $query = DistrictModel::query()->with(['municipality', 'state']);

        if ($request->has('e')) {
            foreach ($request->e as $filter) {
                if (!isset($filter['column'], $filter['data'])) continue;

                $data = json_decode($filter['data'], true);

                if (!is_array($data)) continue;

                match ($filter['column']) {
                    'status' => $query->whereIn('status_id', $data),
                    'state' => $query->whereIn('state_id', $data),
                    'municipality' => $query->whereIn('municipality_id', $data),
                    default => null,
                };
            }
        }
        $query = $query->orderByDesc('status_id')->advancedFilter();

        return response()->json(['collection' => $query]);
    }

    public function store(DistrictsRequest $request, DistrictService $districtService): JsonResponse
    {
        $district = $districtService->createDistrict($request->toDto());

        return response()->json([
            'saved' => (bool)$district,
            'district' => new DistrictResource($district)
        ]);
    }

    public function edit(Request $request): JsonResponse
    {
        return response()->json(['district' => DistrictModel::query()->findOrFail($request->id)]);
    }

    public function update(DistrictsRequest $request, DistrictModel $id, DistrictService $districtService): JsonResponse
    {
        $district = $districtService->updateDistrict($id, $request->toDto());
        return response()->json([
            'saved' => (bool)$district,
            'district' => new DistrictResource($district)
        ]);
    }
}
