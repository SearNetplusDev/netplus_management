<?php

namespace App\Services\v1\management\accounting\DTE\events;

use App\Enums\v1\Accounting\DTE\EventTypes;
use App\Services\v1\management\accounting\DTE\DTESignatureService;

readonly class RefundOrchestratorService
{
    public function __construct(
        private readonly RefundStructureService $refundStructureService,
        private readonly DTESignatureService    $dteSignatureService,
    )
    {

    }

    /**
     * Procesa el envío del evento reembolso.
     *
     * @param int $dteId
     * @param int $dteType
     * @param array $items
     * @return mixed
     * @throws \Random\RandomException
     */
    public function process(int $dteId, int $dteType, array $items): array
    {
        // 1. Crear el JSON
        $json = $this->refundStructureService->createJson(
            dteId: $dteId,
            dteType: $dteType,
            items: $items
        );
        $result = $this->dteSignatureService->singAndSendEvent(dte: $json, eventType: EventTypes::RETORNO);

//        return $singDocument;
        return $result->haciendaResponse;
    }
}
