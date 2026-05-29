<?php

namespace App\Strategies\v1\Accounting\DTE\Prints;

use App\Models\Accounting\DTEModel;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPDF;

readonly class FacturaSujetoExcluidoPrintStrategy extends BasePrint
{

    protected function generate(DTEModel $model): DomPDF
    {
        $client = $this->getClientInfo($model->client_id, [
            'dui',
            'nit',
            'mobile',
            'address',
        ]);
        $address = $client->address?->address;
        $address .= ", {$client->address?->district?->name}";
        $address .= ", {$client->address?->municipality?->name}";
        $address .= ", {$client->address?->state?->name}.";
        $name = "{$client->name} {$client->surname}";
        if (!empty($client->dui)) {
            $docType = 'DUI';
            $docNumber = $client->dui?->number;
        } else {
            $docType = 'NIT';
            $docNumber = $client->nit?->number;
        }
        $clientData = [
            'name' => ucwords($name),
            'docType' => $docType,
            'docNumber' => str_replace('-', '', $docNumber),
            'phone' => str_replace('-', '', $client->mobile?->number),
            'address' => $address,
            'email' => $client->email?->email,
        ];
        $condition = $this->condition($model->json_body['resumen']['condicionOperacion']);

        return Pdf::loadView($this->getView(), [
            'qrCode' => $this->buildQrCode(
                generationCode: $model->generation_code,
                date: $model->generation_datetime
            ),
            'data' => $model->json_body,
            'receptionStamp' => $model->reception_stamp,
            'clientData' => $clientData,
            'condition' => $condition,
            'invalidated' => $model->invalidation()->exists(),
        ])->setPaper('a4', 'portrait');
    }

    private function getView(): string
    {
        return 'v1.management.pdf.accounting.dte.factura_sujeto_excluido';
    }
}
