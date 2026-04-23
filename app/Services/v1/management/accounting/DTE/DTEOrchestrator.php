<?php

namespace App\Services\v1\management\accounting\DTE;

use App\DTOs\v1\management\accounting\dte\DTEDTO;
use App\Enums\v1\Accounting\InvoiceCategories;
use App\Models\Accounting\DTEModel;
use App\Models\Billing\PaymentModel;
use App\Services\v1\management\billing\otherInvoices\OtherInvoiceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Throwable;

readonly class DTEOrchestrator
{
    public function __construct(
        private DTEService          $dteService,
        private OtherInvoiceService $otherInvoiceService,
    )
    {
    }

    /***
     * @param int $documentId
     * @param array $data
     * @return DTEModel
     * @throws Throwable
     */
    public function process(int $documentId, array $data): DTEModel
    {
        $json = $this->dteService->generate($documentId, $data);
        $source = $data['source'] ?? 'payment';
        $paymentId = null;
        $clientId = $data['client_id'] ?? null;
        $invoiceIds = null;
        $otherInvoiceId = null;
        $category = InvoiceCategories::INVOICE;
        $userId = Auth::id() ?? throw new \RuntimeException("Usuario no autenticado");

        switch ($source) {
            //  Escenario 1: Pago registrado.
            case 'payment':
                $payment = PaymentModel::query()->with('invoices')->findOrFail($data['payment']);
                $clientId = $payment->client_id;
                $paymentId = $payment->id;
                $invoiceIds = $payment->invoices->pluck('id')->toArray();
                break;

            //  Escenario 2: Selección de facturas.
            case 'invoices':
                $invoiceIds = $data['items'];
                break;

            //  Escenario 3: Llenado de formulario
            case 'manual':
                $otherInvoice = $this->otherInvoiceService->createFromManualData(
                    type: $documentId,
                    data: $data,
                    userId: $userId,
                );
                $otherInvoiceId = $otherInvoice->id;
                $category = InvoiceCategories::OTHER_INVOICE;
                break;

            default:
                throw new \InvalidArgumentException("Origen no soportado: {$source}");
        }

        $dto = new DTEDTO(
            client_id: (int)$clientId,
            document_type_id: $documentId,
            control_number: $json['identificacion']['numeroControl'],
            generation_code: $json['identificacion']['codigoGeneracion'],
            reception_stamp: strtoupper(Str::random(40)),
            generation_datetime: Carbon::now(),
            total_amount: (float)$json['resumen']['totalPagar'],
            payment_id: $paymentId,
            invoice_category: $category,
            invoice_ids: $invoiceIds,
            other_invoice_id: $otherInvoiceId,
            user_id: $userId,
            status_id: true,
            json_body: $json,
        );

        return $this->dteService->storeDTE($dto);
    }
}
