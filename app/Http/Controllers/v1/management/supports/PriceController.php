<?php

namespace App\Http\Controllers\v1\management\supports;

use App\Http\Controllers\Controller;
use App\Http\Requests\v1\Management\Supports\PriceRequest;
use App\Http\Resources\v1\management\general\GeneralResource;
use App\Models\Supports\PriceModel;
use App\Services\v1\management\DataViewerService;
use App\Services\v1\management\supports\PriceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PriceController extends Controller
{
    /**
     * Lista y pagina todos los registros.
     *
     * @param Request $request
     * @param DataViewerService $dataViewerService
     * @return JsonResponse
     */
    public function data(Request $request, DataViewerService $dataViewerService): JsonResponse
    {
        $query = PriceModel::query();

        return $dataViewerService->handle($request, $query, [
            'status' => fn($q, $data) => $q->whereIn('status_id', $data),
        ]);
    }

    /**
     * Almacena un nuevo registro.
     *
     * @param PriceRequest $request
     * @param PriceService $priceService
     * @return JsonResponse
     */
    public function store(PriceRequest $request, PriceService $priceService): JsonResponse
    {
        $price = $priceService->create($request->toDTO());

        return response()->json([
            'saved' => (bool)$price,
            'data' => new GeneralResource($price),
        ]);
    }

    /**
     * Trae los datos de un registro basandose en ID.
     *
     * @param Request $request
     * @param PriceService $priceService
     * @return JsonResponse
     */
    public function read(Request $request, PriceService $priceService): JsonResponse
    {
        $data = $priceService->read($request->input('id'));

        return response()->json([
            'data' => new GeneralResource($data),
        ]);
    }

    /**
     * Actualiza los datos según id.
     *
     * @param PriceRequest $request
     * @param PriceModel $id
     * @param PriceService $priceService
     * @return JsonResponse
     */
    public function update(PriceRequest $request, PriceModel $id, PriceService $priceService): JsonResponse
    {
        $data = $priceService->update($id, $request->toDTO());

        return response()->json([
            'saved' => (bool)$data,
            'data' => new GeneralResource($data),
        ]);
    }
}
