<?php

namespace App\Services\v1\accounting\DTE;

use App\Contexts\Accounting\DTEContext;
use App\Enums\v1\Billing\DocumentTypes;

class DTEService
{
    public function __construct(private DTEContext $context)
    {
    }

    public function generate(int $documentId, array $data): array
    {
        $type = DocumentTypes::from($documentId);
        $this->context->setStrategy($type->strategy());

        return $this->context->execute($data);
    }
}
