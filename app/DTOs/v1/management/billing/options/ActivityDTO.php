<?php

namespace App\DTOs\v1\management\billing\options;

use Spatie\DataTransferObject\DataTransferObject;

class ActivityDTO extends DataTransferObject
{
    public function __construct(
        public readonly string $name,
        public readonly string $code,
        public readonly int    $status_id,
    )
    {

    }

    public function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? '',
            code: $data['code'] ?? '',
            status_id: $data['status_id'] ?? 0,
        );
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
