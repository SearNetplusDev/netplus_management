<?php

namespace App\Strategies\v1\Accounting\DTE;

use App\Enums\v1\Billing\DocumentTypes;

class NotaDebitoStrategy extends BaseNotaStrategy
{
    protected function documentType(): DocumentTypes
    {
        return DocumentTypes::NOTA_DEBITO;
    }

    protected function extraResumenFields(): array
    {
        return [
            'numPagoElectronico' => null
        ];
    }
}
