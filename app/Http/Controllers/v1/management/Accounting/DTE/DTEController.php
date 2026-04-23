<?php

namespace App\Http\Controllers\v1\management\Accounting\DTE;

use App\Http\Controllers\Controller;
use App\Models\Accounting\DTEModel;
use App\Services\v1\management\accounting\DTE\DTEOrchestrator;
use App\Services\v1\management\DataViewerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DTEController extends Controller
{
    public function __construct(private readonly DTEOrchestrator $dteOrchestrator)
    {

    }

    /***
     * Data Grid con los DTE emitidos.
     *
     * @param Request $request
     * @param DataViewerService $dataViewerService
     * @return JsonResponse
     */
    public function data(Request $request, DataViewerService $dataViewerService): JsonResponse
    {
        $query = DTEModel::query()
            ->with([
                'client',
                'dte_type',
                'user',
            ]);

        return $dataViewerService->handle($request, $query, [
            'status' => fn($q, $data) => $q->whereIn('status_id', $data),
            'type' => fn($q, $data) => $q->whereIn('document_type_id', $data),
            'user' => fn($q, $data) => $q->whereIn('user_id', $data),
        ]);
    }

    /***
     * @param Request $request
     * @param int $documentId
     * @return JsonResponse
     * @throws \Throwable
     */
    public function store(Request $request, int $documentId): JsonResponse
    {
        return response()->json(
            $this->dteOrchestrator->process($documentId, $request->all())
        );
    }
}
