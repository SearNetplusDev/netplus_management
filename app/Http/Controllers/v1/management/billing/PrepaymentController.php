<?php

namespace App\Http\Controllers\v1\management\billing;

use App\DTOs\v1\management\billing\prepayment\PrepaymentDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\Management\Billing\Prepayments\PrepaymentRequest;
use App\Http\Resources\v1\management\general\GeneralResource;
use App\Services\v1\management\billing\PrepaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class PrepaymentController extends Controller
{
    public function store(PrepaymentRequest $request, PrepaymentService $service): JsonResponse
    {
        $dto = new PrepaymentDTO(
            client_id: $request->client,
            amount: $request->amount,
            payment_method_id: $request->payment_method,
            payment_date: $request->payment_date,
            user_id: Auth::user()->id,
            reference_number: $request->reference_number,
            comments: $request->comments,
            status_id: $request->status,
        );
        $prepayment = $service->createPrepayment($dto);

        return response()->json([
            'saved' => (bool)$prepayment,
            'prepayment' => new GeneralResource($prepayment),
        ]);
    }
}
