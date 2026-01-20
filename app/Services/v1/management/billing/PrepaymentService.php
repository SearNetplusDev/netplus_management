<?php

namespace App\Services\v1\management\billing;

use App\DTOs\v1\management\billing\prepayment\PrepaymentDTO;
use App\Models\Billing\PrepaymentModel;

class PrepaymentService
{
    public function __construct(private PaymentService $paymentService)
    {
    }

    public function createPrepayment(PrepaymentDTO $dto): PrepaymentModel
    {

    }
}
