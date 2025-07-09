<?php

namespace App\DTOs\v1\management\configuration\clients;

use Spatie\DataTransferObject\DataTransferObject;

class KinshipDTO extends DataTransferObject
{
    public function __construct(
        public readonly string $name,
        public readonly int    $status_id
    )
    {

    }

    public function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? '',
            status_id: $data['status_id'] ?? 0,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name ?? '',
            'status_id' => $this->status_id ?? 0
        ];
    }
}
