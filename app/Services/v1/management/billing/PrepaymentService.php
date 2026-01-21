<?php

namespace App\Services\v1\management\billing;

use App\DTOs\v1\management\billing\prepayment\PrepaymentDTO;
use App\Enums\v1\General\BillingStatus;
use App\Enums\v1\General\CommonStatus;
use App\Models\Billing\ClientFinancialStatusModel;
use App\Models\Billing\InvoiceModel;
use App\Models\Billing\PrepaymentModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PrepaymentService
{
    public function __construct(private PaymentService $paymentService)
    {
    }

    /***
     * Registra un abono
     * @param PrepaymentDTO $dto
     * @return PrepaymentModel
     * @throws \Throwable
     */
    public function createPrepayment(PrepaymentDTO $dto): PrepaymentModel
    {
        return DB::transaction(function () use ($dto) {
            $prepayment = PrepaymentModel::query()
                ->create([
                    'client_id' => $dto->client_id,
                    'amount' => $dto->amount,
                    'remaining_amount' => $dto->amount,
                    'payment_method_id' => $dto->payment_method_id,
                    'reference_number' => $dto->reference_number,
                    'payment_date' => $dto->payment_date,
                    'user_id' => $dto->user_id,
                    'comments' => $dto->comments,
                    'status_id' => $dto->status_id,
                ]);

            $this->updateClientPrepaymentBalance($dto->client_id);

            return $prepayment->load('client');
        });
    }

    /***
     * Aplica abonos disponibles a facturas pendientes automáticamente
     * @param int $clientId
     * @param array|null $invoiceIds
     * @return array
     * @throws \Throwable
     */
    public function applyPrepaymentsToInvoices(int $clientId, ?array $invoiceIds = null): array
    {
        return DB::transaction(function () use ($clientId, $invoiceIds) {
            //  Obteniendo abonos disponibles
            $prepayments = PrepaymentModel::query()
                ->where([
                    ['client_id', $clientId],
                    ['status_id', CommonStatus::ACTIVE->value],
                ])
                ->where('remaining_amount', '>', 0)
                ->orderBy('payment_date', 'asc')
                ->lockForUpdate()
                ->get();

            if ($prepayments->isEmpty()) {
                throw ValidationException::withMessages([
                    'prepayments' => 'No hay abonos disponibles para este cliente.',
                ]);
            }

            //  Obteniendo facturas a pagar
            $invoicesQuery = InvoiceModel::query()
                ->where('client_id', $clientId)
                ->where('balance_due', '>', 0)
                ->whereIn('billing_status_id', [
                    BillingStatus::ISSUED->value,
                    BillingStatus::PENDING->value,
                    BillingStatus::OVERDUE->value,
                    BillingStatus::PARTIALLY_PAID->value,
                ]);

            if ($invoiceIds) $invoicesQuery->whereIn('id', $invoiceIds);

            $invoices = $invoicesQuery->orderByRaw('CASE WHEN billing_status_id = ? THEN 0 ELSE 1 END', [BillingStatus::OVERDUE->value])
                ->orderBy('billing_period_id', 'asc')
                ->lockForUpdate()
                ->get();

            if ($invoices->isEmpty()) {
                throw ValidationException::withMessages([
                    'invoices' => 'No hay facturas pendientes para aplicar abonos.'
                ]);
            }

            $results = [
                'invoices_paid' => 0,
                'total_applied' => 0,
                'prepayments_used' => 0,
                'details' => [],
            ];

            foreach ($invoices as $invoice) {
                if ($prepayments->sum('remaining_amount') <= 0) break;

                $invoiceBalance = round($invoice->balance_due, 2);
                $appliedToInvoice = 0;

                foreach ($prepayments as $prepayment) {
                    if ($prepayment->remaining_amount <= 0) continue;
                    if ($invoiceBalance <= 0) break;

                    $amountToApply = min($prepayment->remaining_amount, $invoiceBalance);

                    //  Registrar aplicación de abono
                    $prepayment->invoices()->attach($invoice->id, [
                        'amount_applied' => $amountToApply,
                        'applied_at' => Carbon::now(),
                    ]);

                    //  Aplicando abono
                    $prepayment->decrement('remaining_amount', $amountToApply);

                    // Actualizar factura
                    $invoice->increment('paid_amount', $amountToApply);
                    $invoiceBalance -= $amountToApply;
                    $appliedToInvoice += $amountToApply;

                    $results['total_applied'] += $amountToApply;

                    if ($prepayment->remaining_amount <= 0) $results['prepayments_used']++;
                }

                //  Actualizar estado de la factura
                $newBalance = round(max($invoice->total_amount - $invoice->paid_amount, 0), 2);
                $newStatus = $newBalance <= 0
                    ? BillingStatus::PAID->value
                    : ($invoice->paid_amount > 0
                        ? BillingStatus::PARTIALLY_PAID->value
                        : $invoice->billing_status_id);

                $invoice->update([
                    'balance_due' => $newBalance,
                    'billing_status_id' => $newStatus,
                ]);

                if ($newBalance <= 0) $results['invoices_paid']++;

                $results['details'][] = [
                    'invoice_id' => $invoice->id,
                    'amount_applied' => $appliedToInvoice,
                    'new_balance' => $newBalance,
                    'status' => $newStatus,
                ];
            }

            // Actualizando balance de abonos del cliente
            $this->updateClientPrepaymentBalance($clientId);

            // Activando servicios si corresponde
            $this->paymentService->updateInternetAccessForPaidServices($clientId);

            return $results;
        });
    }

    /***
     * Obtiene abonos disponibles de un cliente
     * @param int $clientId
     * @return array
     */
    public function getClientPrepayments(int $clientId): array
    {
        $prepayments = PrepaymentModel::query()
            ->with(['payment_method', 'applications.invoice.period'])
            ->where('client_id', $clientId)
            ->orderBy('payment_date', 'desc')
            ->get();

        $totalPrepayments = $prepayments->sum('amount');
        $totalRemaining = $prepayments->sum('remaining_amount');
        $totalApplied = $totalPrepayments - $totalRemaining;

        return [
            'prepayments' => $prepayments,
            'summary' => [
                'total_prepayments' => number_format($totalPrepayments, 2),
                'total_applied' => number_format($totalApplied, 2),
                'total_remaining' => number_format($totalRemaining, 2),
            ],
        ];
    }

    /***
     * Actualiza el balance de abonos en el estado financiero del cliente
     * @param int $clientId
     * @return void
     */
    private function updateClientPrepaymentBalance(int $clientId): void
    {
        $totalRemaining = PrepaymentModel::query()
            ->where([
                ['client_id', $clientId],
                ['status_id', CommonStatus::ACTIVE->value],
            ])
            ->sum('remaining_amount');

        ClientFinancialStatusModel::query()
            ->updateOrCreate(
                ['client_id' => $clientId],
                ['prepayment_balance' => $totalRemaining]
            );
    }

    /***
     * Cancela un abono
     * @param int $prepaymentId
     * @return PrepaymentModel
     * @throws \Throwable
     */
    public function cancelPrepayment(int $prepaymentId): PrepaymentModel
    {
        return DB::transaction(function () use ($prepaymentId) {
            $prepayment = PrepaymentModel::query()->findOrFail($prepaymentId);

            if ($prepayment->applications()->exists()) {
                throw ValidationException::withMessages([
                    'prepayment' => 'No se puede cancelar un abono que ya ha sido aplicado.',
                ]);
            }

            $prepayment->update(['status_id' => CommonStatus::INACTIVE->value]);

            $this->updateClientPrepaymentBalance($prepayment->client_id);

            return $prepayment;
        });
    }
}
