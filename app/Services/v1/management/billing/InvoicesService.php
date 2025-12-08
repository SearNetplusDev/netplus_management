<?php

namespace App\Services\v1\management\billing;

use App\Models\Billing\InvoiceModel;
use App\Models\Clients\ClientModel;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPDF;
use Carbon\Carbon;

class InvoicesService
{
    public function getClientInvoices(int $clientId): ClientModel
    {
        return ClientModel::query()
            ->with(['invoices.financial_status', 'invoices.period'])
            ->find($clientId);
    }

    public function getInvoiceData(int $invoiceId): DomPDF
    {
        $invoice = InvoiceModel::query()
            ->with([
                'client.corporate_info',
                'client.mobile',
                'client.email',
                'client.branch.state',
                'client.branch.municipality',
                'client.branch.district',
                'items.service.state',
                'items.service.municipality',
                'items.service.district',
                'period'
            ])
            ->findOrFail($invoiceId);

//        return $invoice;
        $items = [];

        foreach ($invoice->items as $index => $item) {
            $unitPrice = $item->unit_price + $item->iva;
            $el = [
                'index' => $index + 1,
                'quantity' => $item->quantity,
                'description' => $item->description,
                'unit_price' => number_format($unitPrice, 2) ?? 0,
                'discount' => 0,
                'total' => number_format($unitPrice * $item->quantity, 2) ?? 0,
            ];
            $items[] = $el;
        }
        $data = [
            'branch_name' => $invoice->client?->branch?->name,
            'branch_address' => $invoice->client?->branch?->address,
            'branch_state' => $invoice->client?->branch?->state?->name,
            'branch_district' => $invoice->client?->branch?->district?->name,
            'branch_phone' => $invoice->client?->branch?->mobile,
            'client_name' => ucwords("{$invoice->client?->name} {$invoice->client?->surname}"),
            'client_address' => $invoice->items->first()?->service?->address,
            'client_state' => $invoice->items->first()?->service?->state?->name,
            'client_district' => $invoice->items->first()?->service?->district?->name,
            'client_mobile' => $invoice->client?->mobile?->number ?? '',
            'client_email' => $invoice->client?->email?->email ?? '',
            'invoice_issued' => Carbon::parse($invoice->period?->period_start)->toDateString(),
            'invoice_overdue' => Carbon::parse($invoice->period?->due_date)->toDateString(),
            'invoice_period' => $invoice->period?->name,
            'invoice_status' => $invoice->billing_status_id,
            'items' => $items,
            'subtotal' => number_format($invoice->subtotal, 2) ?? 0,
            'discounts' => 0,
            'iva' => number_format($invoice->iva, 2) ?? 0,
            'detained_iva' => number_format($invoice->detained_iva, 2) ?? 0,
            'total' => number_format($invoice->total_amount, 2) ?? 0,
        ];

//        return $data;

        return Pdf::loadView('v1.management.pdf.billing.invoices.invoice', ['data' => $data])
            ->setPaper('A4', 'portrait');
    }
}
