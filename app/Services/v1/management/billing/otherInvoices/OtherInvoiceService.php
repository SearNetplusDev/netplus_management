<?php

namespace App\Services\v1\management\billing\otherInvoices;

use App\Enums\v1\Accounting\TaxRate;
use App\Models\Billing\OtherInvoiceDetailModel;
use App\Models\Billing\OtherInvoiceModel;
use App\Models\Clients\ClientModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OtherInvoiceService
{

    /***
     * Crea una factura a partir de datos manuales.
     *
     * @param int $type
     * @param array $data
     * @param int $userId
     * @return OtherInvoiceModel
     * @throws \Throwable
     */

    public function createFromManualData(int $type, array $data, int $userId): OtherInvoiceModel
    {
        $client = ClientModel::query()
            ->with('corporate_info')
            ->findOrFail($data['client_id']);

        $hasRetainedIva = $client->corporate_info?->retained_iva ?? false;

        [$iva, $ivaRetenido, $total] = $this->calculateAmounts(
            amount: (float)$data['totals']['total'],
            discount: (float)$data['totals']['discount'],
            retainedIva: $hasRetainedIva,
        );


        return DB::transaction(function () use ($type, $data, $userId, $iva, $ivaRetenido, $total) {
            $otherInvoice = OtherInvoiceModel::create([
                'document_type_id' => $type,
                'client_id' => $data['client_id'],
                'payment_condition' => (int)$data['payment_condition'],
                'payment_method_id' => (int)$data['payment_method'],
                'subtotal' => (float)$data['totals']['total'] ?? 0,
                'iva' => $iva ?? 0,
                'iva_retenido' => $ivaRetenido ?? 0,
                'discount_amount' => (float)$data['totals']['discount'] ?? 0,
                'total_amount' => $total ?? 0,
                'issue_date' => Carbon::now(),
                'created_by' => $userId,
                'status_id' => true,
            ]);

            foreach ($data['items'] as $item) {
                $unitPrice = (float)$item['unit_price'] / TaxRate::VALOR_NETO->value();
                $subtotal = (float)$item['unit_price'] * (int)$item['quantity'];
                $lineNeto = $unitPrice * (int)$item['quantity'];
                $lineIVA = (float)$unitPrice * TaxRate::IVA->value();

                OtherInvoiceDetailModel::create([
                    'other_invoice_id' => $otherInvoice->id,
                    'description' => $item['description'],
                    'item_type' => (int)$item['item_type'],
                    'quantity' => (int)$item['quantity'],
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                    'iva' => $lineIVA,
                    'total' => $lineNeto + $lineIVA,
                ]);
            }

            return $otherInvoice;
        });
    }

    private function calculateAmounts(float $amount, float $discount, bool $retainedIva): array
    {
        $totalConDescuento = $amount - $discount;
        $neto = $totalConDescuento / TaxRate::VALOR_NETO->value();
        $iva = $neto * TaxRate::IVA->value();
        $ivaRetenido = $retainedIva ? $neto * TaxRate::IVA_RETENIDO->value() : 0;
        $total = $neto + $iva - $ivaRetenido;

        return [$iva, $ivaRetenido, $total];
    }
}
