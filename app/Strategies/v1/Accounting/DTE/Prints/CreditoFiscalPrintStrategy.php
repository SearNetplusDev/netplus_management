<?php

namespace App\Strategies\v1\Accounting\DTE\Prints;

use App\Contracts\v1\Accounting\DTE\DTEPrinterInterface;
use App\Models\Accounting\DTEModel;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPDF;

readonly class CreditoFiscalPrintStrategy extends BasePrint
{
    /***
     * Genera el pdf basado en el ID del DTE generado.
     *
     * @param DTEModel $model
     * @return DomPDF
     */
    protected function generate(DTEModel $model): DomPDF
    {
        $client = $this->getClientInfo($model->client_id, [
            'corporate_info.activity',
            'email',
        ]);
        $address = $client->corporate_info?->address;
        $address .= ", {$client->corporate_info?->district?->name}";
        $address .= ", {$client->corporate_info?->municipality?->name}";
        $address .= ", {$client->corporate_info?->state?->name}.";
        $clientData = [
            'name' => ucwords($client->corporate_info?->invoice_alias),
            'nit' => str_replace('-', '', $client->corporate_info?->nit),
            'nrc' => str_replace('-', '', $client->corporate_info?->nrc),
            'giro' => $client->corporate_info?->activity?->name,
            'address' => $address,
            'phone' => str_replace('-', '', $client->corporate_info?->phone_number),
            'email' => $client->email?->email,
        ];
        $condition = $this->condition($model->json_body['resumen']['condicionOperacion']);

        return Pdf::loadView($this->getView(), [
            'qrCode' => $this->buildQrCode(
                generationCode: $model->generation_code,
                date: $model->generation_datetime,
            ),
            'data' => $model->json_body,
            'receptionStamp' => $model->reception_stamp,
            'clientData' => $clientData,
            'condition' => $condition,
        ])
            ->setPaper('A4', 'portrait');
    }

    /***
     * Ruta de la vista a renderizar
     *
     * @return string
     */
    protected function getView(): string
    {
        return 'v1.management.pdf.accounting.dte.credito_fiscal';
    }
}
