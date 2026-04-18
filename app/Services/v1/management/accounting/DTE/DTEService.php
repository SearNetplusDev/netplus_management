<?php

namespace App\Services\v1\management\accounting\DTE;

use App\Contexts\Accounting\DTEContext;
use App\Enums\v1\Billing\DocumentTypes;
use App\Services\v1\management\billing\otherInvoices\OtherInvoiceService;
use Illuminate\Support\Facades\Auth;

class DTEService
{
    public function __construct(
        private DTEContext          $context,
        private OtherInvoiceService $otherInvoiceService,
    )
    {
    }

    /***
     * Genera el JSON con el DTE según estrategia, almacena la factura en caso de generarse manualmente.
     *
     * @param int $documentId
     * @param array $data
     * @return array
     * @throws \Throwable
     */
    public function generate(int $documentId, array $data): array
    {
        $type = DocumentTypes::from($documentId);
        $this->context->setStrategy($type->strategy());
        $json = $this->context->execute($data);

        if (($data['source'] ?? '') === 'manual') {
            $userId = Auth::user()->id ?? throw new \RuntimeException("Usuario no autenticado");
            $this->otherInvoiceService->createFromManualData(
                type: $documentId,
                data: $data,
                userId: $userId
            );
        }
        return $json;
    }
}
