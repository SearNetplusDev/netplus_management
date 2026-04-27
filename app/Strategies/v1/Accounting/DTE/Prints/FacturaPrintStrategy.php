<?php

namespace App\Strategies\v1\Accounting\DTE\Prints;

use App\Models\Accounting\DTEModel;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPDF;

readonly class FacturaPrintStrategy extends BasePrint
{
    protected function generate(DTEModel $model): DomPDF
    {
        $client = $this->getClientInfo($model->client_id, [
            'dui',
            'mobile',
            'email',
            'address',
        ]);

        $address = $client->address?->address;
        $address .= ", {$client->address?->district?->name}";
        $address .= ", {$client->address?->municipality?->name}";
        $address .= ", {$client->address?->state?->name}.";

        $clientData = [
            'name' => ucwords("{$client->name} {$client->surname}"),
            'dui' => str_replace('-', '', $client->dui?->number) ?? '',
            'address' => $address,
            'phone' => $client->mobile?->number ?? '',
            'email' => $client->email?->email ?? '',
        ];

        return Pdf::loadView($this->getView(), [
            'qrCode' => $this->buildQrCode(
                generationCode: $model->generation_code,
                date: $model->generation_datetime,
            ),
            'data' => $model->json_body,
            'receptionStamp' => $model->reception_stamp,
            'clientData' => $clientData,
        ])
            ->setPaper('A4', 'portrait');
    }

    protected function getView(): string
    {
        return 'v1.management.pdf.accounting.dte.factura';
    }
}
