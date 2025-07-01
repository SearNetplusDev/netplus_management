<?php

namespace App\DTOs\v1\management\billing\options;

use Spatie\DataTransferObject\DataTransferObject;

class DocumentTypeDTO extends DataTransferObject
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $code = null,
        public readonly ?bool   $status_id,
    )
    {

    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'code' => $this->code,
            'status_id' => $this->status_id,
        ];
    }
}
