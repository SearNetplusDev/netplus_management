<?php

namespace App\Http\Controllers\v1\management\billing;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\management\general\GeneralResource;
use App\Services\v1\management\billing\ExtensionsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExtensionController extends Controller
{
    public function invoiceExtensionList(Request $request, ExtensionsService $service): JsonResponse
    {
        $extensions = $service->invoiceExtensionData($request->invoice_id);
        return response()->json([
            'data' => new GeneralResource($extensions),
        ]);
    }
}
