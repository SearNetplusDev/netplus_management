<?php

namespace App\Services\v1\management\billing;

use App\DTOs\v1\management\billing\payments\PaymentDTO;
use App\Enums\v1\General\BillingStatus;
use App\Enums\v1\General\CommonStatus;
use App\Models\Billing\InvoiceModel;
use App\Models\Billing\PaymentInvoiceModel;
use App\Models\Billing\PaymentModel;
use App\Models\Infrastructure\Network\AuthServerModel;
use App\Models\Services\ServiceInternetModel;
use App\Services\v1\management\billing\background\ClientFinancialStatusService;
use App\Services\v1\network\MikrotikInternetService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function __construct(
        private MikrotikInternetService      $mikrotikInternetService,
        private ClientFinancialStatusService $clientFinancialStatusService
    )
    {
    }

    /***
     * Registra pago, activa navegación de ser necesario y actualiza estado financiero
     * @param PaymentDTO $dto
     * @param array $invoiceIds
     * @return PaymentModel
     * @throws \Throwable
     */
    public function createPayment(PaymentDTO $dto, array $invoiceIds): PaymentModel
    {
        return DB::transaction(function () use ($dto, $invoiceIds) {
            $payment = PaymentModel::query()->create($dto->toArray());
            $invoices = InvoiceModel::query()->whereIn('id', $invoiceIds)->get()->keyBy('id');

            foreach ($invoiceIds as $invoiceId) {
                if (!isset($invoices[$invoiceId])) {
                    throw ValidationException::withMessages([
                        'invoices' => "La factura {$invoiceId} no existe."
                    ]);
                }

                $payment->invoices()->attach($invoiceId, ['amount_applied' => $invoices[$invoiceId]->total_amount]);
            }

            return $payment->load('invoices');
        });
    }
}
