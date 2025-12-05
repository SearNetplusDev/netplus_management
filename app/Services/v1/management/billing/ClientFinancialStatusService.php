<?php

namespace App\Services\v1\management\billing;

use App\Enums\v1\General\BillingStatus;
use App\Enums\v1\General\CommonStatus;
use App\Models\Billing\ClientFinancialStatusModel;
use App\Models\Billing\InvoiceModel;
use App\Models\Clients\ClientModel;

class ClientFinancialStatusService
{
    /***
     * Actualiza o crea el estado financiero de un cliente
     * @param int $clientId
     * @return void
     */
    public function updateClientFinancialStatus(int $clientId): void
    {
        $client = ClientModel::query()->findOrFail($clientId);
        $invoices = InvoiceModel::query()
            ->where('client_id', $clientId)
            ->where('status_id', CommonStatus::ACTIVE->value)
            ->where('billing_status_id', '!=', BillingStatus::CANCELED->value)
            ->get();

        $currentBalance = $invoices->sum('balance_due');
        $totalPaidAmount = $invoices->sum('paid_amount');
        $totalInvoices = $invoices->count();
        $paidInvoices = $invoices->where('billing_status_id', BillingStatus::PAID->value)->count();
        $pendingInvoices = $invoices->whereIn('billing_status_id', [BillingStatus::ISSUED->value, BillingStatus::PENDING->value])->count();
        $overdueInvoices = $invoices->where('billing_status_id', BillingStatus::OVERDUE->value);
        $overdueInvoiceCount = $overdueInvoices->count();
        $overdueBalance = $overdueInvoices->sum('balance_due');
        $statusId = $this->determineFinancialStatus($overdueBalance, $currentBalance);

        ClientFinancialStatusModel::query()
            ->updateOrCreate(
                ['client_id' => $clientId],
                [
                    'current_balance' => $currentBalance,
                    'overdue_balance' => $overdueBalance,
                    'total_paid_amount' => $totalPaidAmount,
                    'total_invoices' => $totalInvoices,
                    'paid_invoices' => $paidInvoices,
                    'pending_invoices' => $pendingInvoices,
                    'overdue_invoices' => $overdueInvoiceCount,
                    'status_id' => $statusId,
                ]
            );
    }

    /***
     * Determina el estado financiero basado en la deuda
     * @param float $overdueBalance
     * @param float $currentBalance
     * @return int
     */
    private function determineFinancialStatus(float $overdueBalance, float $currentBalance): int
    {
        if ($overdueBalance == 0 && $currentBalance == 0) return BillingStatus::PAID->value;
        if ($overdueBalance > 0) return BillingStatus::OVERDUE->value;
        if ($currentBalance > 0) return BillingStatus::PENDING->value;
        return BillingStatus::PAID->value;
    }

    /***
     * Actualiza o creo estados financieros de muchos clientes
     * @param array $clientIds
     * @return void
     */
    public function updateMultipleClients(array $clientIds): void
    {
        foreach ($clientIds as $clientId) {
            $this->updateClientFinancialStatus($clientId);
        }
    }

    /***
     * Actualiza todos los clientes con facturas
     * @return array
     */
    public function updateAllClientsWithInvoices(): array
    {
        $clientsIds = InvoiceModel::query()->distinct('client_id')->pluck('client_id');
        $updated = 0;
        $errors = [];

        foreach ($clientsIds as $clientId) {
            try {
                $this->updateClientFinancialStatus($clientId);
                $updated++;
            } catch (\Exception $e) {
                $errors[] = "Cliente {$clientId}: {$e->getMessage()}";
            }
        }

        return [
            'updated' => $updated,
            'total' => $clientsIds->count(),
            'errors' => $errors
        ];
    }
}
