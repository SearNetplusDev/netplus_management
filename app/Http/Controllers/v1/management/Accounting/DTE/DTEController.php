<?php

namespace App\Http\Controllers\v1\management\Accounting\DTE;

use App\Http\Controllers\Controller;
use App\Services\v1\management\accounting\DTE\DTEService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DTEController extends Controller
{
    public function __construct(private DTEService $dteService)
    {
    }

    public function store(Request $request, int $documentId, ?int $paymentId = null): JsonResponse
    {
        $request->merge(['payment_id' => $paymentId]);

        return response()->json(
            $this->dteService->generate($documentId, $request->all()),
        );
    }
}
