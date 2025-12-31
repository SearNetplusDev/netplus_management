<?php

namespace App\Services\v1\management\billing;

use App\DTOs\v1\management\billing\payments\PaymentDTO;
use App\Models\Billing\PaymentModel;

class PaymentService
{
    /***
     * Registra pago, activa navegación de ser necesario y actualiza estado financiero
     * @param PaymentDTO $dto
     * @return PaymentModel
     */
    public function createPayment(PaymentDTO $dto): PaymentModel
    {
        return PaymentModel::query()->create($dto->toArray());
    }
}
