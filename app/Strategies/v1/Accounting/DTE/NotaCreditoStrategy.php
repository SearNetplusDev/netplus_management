<?php

namespace App\Strategies\v1\Accounting\DTE;

use App\Enums\v1\Billing\DocumentTypes;

class NotaCreditoStrategy extends BaseNotaStrategy
{
    protected function documentType(): DocumentTypes
    {
        return DocumentTypes::NOTA_CREDITO;
    }
}
