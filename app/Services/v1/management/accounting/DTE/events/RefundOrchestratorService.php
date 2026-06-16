<?php

namespace App\Services\v1\management\accounting\DTE\events;

use App\Services\v1\management\accounting\DTE\DTESignatureService;

readonly class RefundOrchestratorService
{
    public function __construct(
        private readonly RefundStructureService $refundStructureService,
        private readonly DTESignatureService    $dteSignatureService,
    )
    {

    }

    public function process(int $dteId, int $dteType, array $items)/*: array*/
    {
        // 1. Crear el JSON
        $json = $this->refundStructureService->createJson(
            dteId: $dteId,
            dteType: $dteType,
            items: $items
        );
        $singDocument = $this->dteSignatureService->signDocument($json);

        return $singDocument;
    }
}
