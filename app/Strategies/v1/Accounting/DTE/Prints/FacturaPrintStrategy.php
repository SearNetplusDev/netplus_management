<?php

namespace App\Strategies\v1\Accounting\DTE\Prints;

use App\Contracts\v1\Accounting\DTE\DTEPrinterInterface;
use App\Models\Accounting\DTEModel;
use App\Services\v1\management\accounting\DTE\DTEPrintService;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPDF;

readonly class FacturaPrintStrategy implements DTEPrinterInterface
{
    public function __construct(private readonly DTEPrintService $printService)
    {

    }

    /***
     * Genera el PDF con los datos del documento emitido.
     *
     * @param DTEModel $dte
     * @return DomPDF
     */
    public function print(DTEModel $dte): DomPDF
    {
        $qrCode = $this->printService->qrCode(
            generationCode: $dte->generation_code,
            date: $dte->generation_datetime,
        );

        return Pdf::loadView('v1.management.pdf.accounting.dte.factura', [
            'qrCode' => $qrCode,
            'data' => $dte->json_body,
            'receptionStamp' => $dte->reception_stamp,
        ])
            ->setPaper('A4', 'portrait');
    }
}
