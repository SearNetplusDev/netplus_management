<?php

namespace App\Services\v1\management\billing;

use App\Enums\v1\General\BillingStatus;
use App\Enums\v1\General\CommonStatus;
use App\Models\Billing\InvoiceModel;
use App\Models\Billing\PeriodModel;
use Carbon\Carbon;

class InvoiceStatusService
{
    /***
     * Actualiza los estados de las facturas según las fechas del período
     * @param int|null $periodId <<Si se proporciona, solo actualiza el período>>
     * @return array
     */
    public function updateInvoiceStatuses(?int $periodId = null): array
    {
        $results = [
            'updated' => 0,
            'total' => 0,
            'errors' => []
        ];

        $query = InvoiceModel::query()
            ->with('period')
            ->where('status_id', CommonStatus::ACTIVE->value)
            ->whereIn('billing_status_id', [
                BillingStatus::ISSUED->value,
                BillingStatus::PENDING->value,
                BillingStatus::OVERDUE->value,
            ]);

        if ($periodId) $query->where('billing_period_id', $periodId);

        $invoices = $query->get();
        $results['total'] = $invoices->count();
        $today = Carbon::today();

        foreach ($invoices as $invoice) {
            try {
                $period = $invoice->period;
                $newStatus = $this->determineInvoiceStatus($invoice, $period, $today);

                if ($newStatus && $invoice->billing_status_id !== $newStatus) {
                    $invoice->update(['billing_status_id' => $newStatus]);
                    $results['updated']++;
                }
            } catch (\Exception $e) {
                $results['errors'][] = "Factura {$invoice->id}: {$e->getMessage()}";
            }
        }

        return $results;
    }

    /***
     * Determina el estado correcto de una factura según fechas
     * @param InvoiceModel $invoice
     * @param PeriodModel $period
     * @param Carbon $today
     * @return int|null
     */
    private function determineInvoiceStatus(
        InvoiceModel $invoice,
        PeriodModel  $period,
        Carbon       $today
    ): ?int
    {
        if (in_array($invoice->billing_status_id, [
            BillingStatus::PENDING->value,
            BillingStatus::CANCELED->value,
        ])) return null;

        if ($invoice->balance_due <= 0) return BillingStatus::PAID->value;

        $issueDate = Carbon::parse($period->issue_date);
        $periodStart = Carbon::parse($period->period_start);
        $dueDate = Carbon::parse($period->due_date);

        if ($today->lt($periodStart)) {
            return BillingStatus::ISSUED->value;
        } elseif ($today->gte($periodStart) && $today->lte($dueDate)) {
            return BillingStatus::PENDING->value;
        } elseif ($today->gt($dueDate)) {
            return BillingStatus::OVERDUE->value;
        }

        return null;
    }

    /***
     * Actualiza el estado de una factura específica
     * @param int $invoiceId
     * @return bool
     */
    public function updateSingleInvoiceStatus(int $invoiceId): bool
    {
        $invoice = InvoiceModel::query()->with('period')->findOrFail($invoiceId);
        $today = Carbon::today();
        $newStatus = $this->determineInvoiceStatus($invoice, $invoice->period, $today);

        if ($newStatus && $invoice->billing_status_id !== $newStatus) {
            $invoice->update(['billing_status_id' => $newStatus]);
            return true;
        }

        return false;
    }

    /***
     * Actualiza estados de facturas de un período específico
     * @param int $periodId
     * @return array
     */
    public function updatePeriodInvoices(int $periodId): array
    {
        return $this->updateInvoiceStatuses($periodId);
    }
}
