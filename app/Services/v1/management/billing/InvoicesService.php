<?php

namespace App\Services\v1\management\billing;

use App\Models\Clients\ClientModel;
use Illuminate\Support\Collection;

class InvoicesService
{
    public function getClientInvoices(int $clientId): ClientModel
    {
        return ClientModel::query()
            ->with(['invoices.financial_status', 'invoices.period'])
            ->find($clientId);
    }
}
