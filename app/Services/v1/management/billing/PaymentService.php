<?php

namespace App\Services\v1\management\billing;

use App\DTOs\v1\management\billing\payments\PaymentDTO;
use App\Enums\v1\General\BillingStatus;
use App\Enums\v1\General\CommonStatus;
use App\Models\Billing\DiscountModel;
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
     *  Registra pago, activa navegación de ser necesario y actualiza estado financiero
     * @param PaymentDTO $dto
     * @param array $invoiceIds
     * @param int|null $discountId
     * @return PaymentModel
     * @throws \Throwable
     */
    public function createPayment(PaymentDTO $dto, array $invoiceIds, ?int $discountId = null): PaymentModel
    {
        return DB::transaction(function () use ($dto, $invoiceIds, $discountId) {
            $payment = PaymentModel::query()->create($dto->toArray());
            $discount = $discountId ? DiscountModel::query()->withoutGlobalScopes()->findOrFail($discountId) : null;
            $invoices = InvoiceModel::query()
                ->whereIn('id', $invoiceIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($invoiceIds as $invoiceId) {
                if (!isset($invoices[$invoiceId])) {
                    throw ValidationException::withMessages([
                        'invoices' => "La factura {$invoiceId} no existe.",
                    ]);
                }

                $invoice = $invoices[$invoiceId];

                if ($invoice->balance_due <= 0) continue;
                $discountAmount = 0;

                if (
                    $discount && !$invoice->discounts()
                        ->withOutGlobalScopes()
                        ->where('discount_id', $discount->id)
                        ->exists()
                ) {
                    $discountAmount = $this->calculateDiscount($discount, $invoice->total_amount);
                    $invoice->discounts()->attach($discount->id, ['applied_amount' => $discountAmount]);
                }

                $totalDiscounts = $invoice->discounts()->sum('applied_amount');

                $maxPayable = max($invoice->total_amount - $totalDiscounts - $invoice->paid_amount, 0);
                $amountToPay = min($dto->amount, $maxPayable);

                if ($amountToPay <= 0) continue;

                $payment->invoices()->attach($invoice->id, ['amount_applied' => $amountToPay]);
                $invoice->increment('paid_amount', $amountToPay);
                $newBalance = max($invoice->total_amount - $invoice->paid_amount - $totalDiscounts, 0);
                
                $invoice->update([
                    'balance_due' => $newBalance,
                    'billing_status_id' => BillingStatus::PAID->value,
                ]);
            }

            return $payment->load('invoices.discounts');
        });
    }

    /***
     * Retorna el cálculo de descuento
     * @param DiscountModel $discount
     * @param float $base
     * @return float
     */
    private function calculateDiscount(DiscountModel $discount, float $base): float
    {
        if ($discount->percentage !== null && $discount->percentage !== 0.0) {
            return round($base * ($discount->percentage / 100), 2);
        }

        return min($discount->amount, $base);
    }
}
