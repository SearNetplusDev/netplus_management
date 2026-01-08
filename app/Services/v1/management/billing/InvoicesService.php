<?php

namespace App\Services\v1\management\billing;

use App\Enums\v1\Billing\DocumentTypes;
use App\Enums\v1\Billing\InvoiceType;
use App\Enums\v1\Clients\ClientTypes;
use App\Enums\v1\General\BillingStatus;
use App\Enums\v1\General\CommonStatus;
use App\Models\Billing\InvoiceModel;
use App\Models\Clients\ClientModel;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPDF;
use Carbon\Carbon;

class InvoicesService
{
    /***
     * Retorna facturas de un cliente determinado en orden descendente
     * @param int $clientId
     * @return ClientModel
     */
    public function getClientInvoices(int $clientId): ClientModel
    {
        return ClientModel::query()
            ->with([
                'invoices' => fn($q) => $q->orderBy('id', 'desc'),
                'invoices.financial_status',
                'invoices.period'
            ])
            ->find($clientId);
    }

    /***
     * Obtiene los datos de la factura y genera el pdf
     * @param int $invoiceId
     * @return DomPDF
     */
    public function getInvoiceData(int $invoiceId): DomPDF
    {
        $invoice = InvoiceModel::query()
            ->with([
                'client.mobile',
                'client.email',
                'client.branch.state',
                'client.branch.municipality',
                'client.branch.district',
                'client.address.state',
                'client.address.municipality',
                'client.address.district',
                'items.service.state',
                'items.service.municipality',
                'items.service.district',
                'period'
            ])
            ->findOrFail($invoiceId);

        $isIndividual = $this->isIndividual($invoice);

        $isCCF = $invoice->client->client_type_id == ClientTypes::CORPORATE->value
            || $invoice->client->document_type_id == DocumentTypes::CREDITO_FISCAL->value;

        if ($isCCF) {
            $invoice->load('client.corporate_info');
            $data = $this->ccfData($invoice, $isIndividual);
            $view = 'v1.management.pdf.billing.invoices.ccf';
        } else {
            $data = $this->billData($invoice, $isIndividual);
            $view = 'v1.management.pdf.billing.invoices.invoice';
        }
        return Pdf::loadView($view, ['data' => $data])->setPaper('A4', 'portrait');
    }

    /***
     * Checa que la factura sea individual o consolidada
     * @param InvoiceModel $invoice
     * @return bool
     */
    private function isIndividual(InvoiceModel $invoice): bool
    {
        return $invoice->invoice_type == InvoiceType::INDIVIDUAL->value;
    }

    /***
     * Redondea números a 2 decimales
     * @param $v
     * @return string
     */
    private function money($v): string
    {
        return number_format($v ?? 0, 2);
    }

    /***
     * Recorre los items de la factura para extraer valor neto e iva
     * @param InvoiceModel $invoice
     * @param bool $includeIva
     * @return array
     */
    private function formatItems(InvoiceModel $invoice, bool $includeIva = false): array
    {
        return $invoice->items->map(function ($item, $i) use ($includeIva) {
            $price = $item->unit_price + ($includeIva ? ($item->iva ?? 0) : 0);

            return [
                'index' => $i + 1,
                'quantity' => $item->quantity,
                'description' => $item->description,
                'unit_price' => $this->money($price),
                'discount' => 0,
                'total' => $this->money($price * $item->quantity),
            ];
        })->toArray();
    }

    /***
     * Obtiene los datos que se repiten en todos los tipos de factura
     * @param InvoiceModel $invoice
     * @return array
     */
    private function commonData(InvoiceModel $invoice): array
    {
        return [
            'branch_name' => $invoice->client?->branch?->name,
            'branch_address' => $invoice->client?->branch?->address,
            'branch_state' => $invoice->client?->branch?->state?->name,
            'branch_district' => $invoice->client?->branch?->district?->name,
            'branch_phone' => $invoice->client?->branch?->mobile ?? '7626-6022',

            'invoice_issued' => Carbon::parse($invoice->period?->period_start)->toDateString(),
            'invoice_overdue' => Carbon::parse($invoice->period?->due_date ?? $invoice->period?->period_end)->toDateString(),
            'invoice_period' => $invoice->period?->name,
            'invoice_status' => $invoice->billing_status_id,

            'subtotal' => $this->money($invoice->subtotal),
            'discounts' => 0,
            'iva' => $this->money($invoice->iva),
            'detained_iva' => $this->money($invoice->iva_retenido ?? 0),
            'total' => $this->money($invoice->total_amount),
        ];
    }

    /***
     * Retorna campos cliente según el tipo de factura y el tipo de documento a emitir
     * @param InvoiceModel $invoice
     * @param bool $isIndividual
     * @param bool $isCCF
     * @return array
     */
    private function getClientFields(InvoiceModel $invoice, bool $isIndividual, bool $isCCF = false): array
    {
        if ($isCCF) {
            return [
                'client_name' => $invoice->client?->corporate_info?->invoice_alias,
                'client_activity' => $invoice->client?->corporate_info?->activity?->name,
                'client_nrc' => $invoice->client?->corporate_info?->nrc,
                'client_nit' => $invoice->client?->corporate_info?->nit,
                'client_address' => $invoice->client?->corporate_info?->address,
                'client_state' => $invoice->client?->corporate_info?->state?->name,
                'client_district' => $invoice->client?->corporate_info?->district?->name,
                'client_mobile' => $invoice->client?->corporate_info?->phone_number,
                'client_email' => $invoice->client?->email?->email,
            ];
        }

        if ($isIndividual) {
            $svc = $invoice->items->first()?->service;
            return [
                'client_name' => ucwords("{$invoice->client?->name} {$invoice->client?->surname}"),
                'client_address' => $svc?->address ?? '',
                'client_state' => $svc?->state?->name ?? '',
                'client_district' => $svc?->district?->name ?? '',
                'client_mobile' => $invoice->client?->mobile?->number ?? '',
                'client_email' => $invoice->client?->email?->email ?? '',
            ];
        }

        return [
            'client_name' => ucwords("{$invoice->client?->name} {$invoice->client?->surname}"),
            'client_address' => $invoice->client?->address?->address ?? '',
            'client_state' => $invoice->client?->address?->state?->name ?? '',
            'client_district' => $invoice->client?->address?->district?->name ?? '',
            'client_mobile' => $invoice->client?->mobile?->number ?? '',
            'client_email' => $invoice->client?->email?->email ?? '',
        ];
    }

    /***
     *  Obtiene los datos corporativos del cliente
     * @param InvoiceModel $invoice
     * @param bool $isIndividual
     * @return array
     */
    private function ccfData(InvoiceModel $invoice, bool $isIndividual): array
    {
        $isCCF = true;
        return array_merge(
            $this->commonData($invoice),
            $this->getClientFields($invoice, $isIndividual, $isCCF),
            ['items' => $this->formatItems($invoice, false)]
        );
    }

    /***
     * Obtiene los datos generales del cliente
     * @param InvoiceModel $invoice
     * @param bool $isIndividual
     * @return array
     */
    private function billData(InvoiceModel $invoice, bool $isIndividual): array
    {
        return array_merge(
            $this->commonData($invoice),
            $this->getClientFields($invoice, $isIndividual, false),
            ['items' => $this->formatItems($invoice, true)]
        );
    }

    /***
     * Retorna la fecha de corte del periodo de cada factura
     * @param int $id
     * @return string
     */
    public function getInvoiceDueDate(int $id): string
    {
        $invoice = InvoiceModel::query()->with(['period', 'extensions'])->findOrFail($id);

        if ($invoice->extensions->isNotEmpty()) {
            $lastExtension = $invoice->extensions->sortByDesc('id')->first();
            return Carbon::parse($lastExtension->extended_due_date)->toDateString();
        }
        return Carbon::parse($invoice->period?->cutoff_date ?? $invoice->period?->due_date)->toDateString();
    }

    public function clientPendingInvoices(int $clientId): array
    {
        $invoices = InvoiceModel::query()
            ->with(['period', 'items.service'])
            ->where('client_id', $clientId)
            ->whereIn('billing_status_id', [BillingStatus::OVERDUE->value, BillingStatus::PENDING->value])
            ->orderByRaw('CASE WHEN billing_status_id = ? THEN 0 ELSE 1 END', [BillingStatus::OVERDUE->value])
            ->orderBy('billing_period_id', 'ASC')
            ->get();

        return $invoices->map(function ($invoice) {
            $status = $invoice->billing_status_id === BillingStatus::OVERDUE->value ? 'Vencida' : 'Pendiente';
            $total = number_format($invoice->total_amount, 2);
            $independent = $invoice->items[0]->service?->separate_billing;

            $address = (bool)$independent ? $invoice->items[0]->service?->address ?? '' : 'Servicios consolidados';

            return [
                'id' => $invoice->id,
                'name' => "{$invoice->period?->name} ({$status}) - {$address}",
                'total' => $total,
            ];
        })->toArray();
    }

}
