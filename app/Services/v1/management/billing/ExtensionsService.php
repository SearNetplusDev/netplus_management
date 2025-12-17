<?php

namespace App\Services\v1\management\billing;

use App\Models\Billing\InvoiceModel;

class ExtensionsService
{
    public function invoiceExtensionData(int $invoiceId): array
    {
        $data = [];
        $invoice = InvoiceModel::with('extensions.user')->findOrFail($invoiceId);
//        return $invoice->extensions;
        $data['period'] = $invoice->period?->name;

        return $data;
    }
}
