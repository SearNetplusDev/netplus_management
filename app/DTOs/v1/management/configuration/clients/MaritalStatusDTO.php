<?php

namespace App\DTOs\v1\management\configuration\clients;

use Spatie\DataTransferObject\DataTransferObject;

class MaritalStatusDTO extends DataTransferObject
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?bool   $status_id,
    )
    {

    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'status_id' => $this->status_id,
        ];
    }
}
