<?php

namespace App\DTOs\v1\management\configuration\clients;

use Spatie\DataTransferObject\DataTransferObject;

class ContractStatusDTO extends DataTransferObject
{
    public function __construct(
        public readonly string $name,
        public readonly int    $status_id,
        public readonly string $badge_color,
    )
    {

    }

    public function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? '',
            status_id: $data['status_id'] ?? 0,
            badge_color: $data['badge_color'] ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name ?? '',
            'status_id' => $this->status_id ?? 0,
            'badge_color' => $this->badge_color ?? '',
        ];
    }
}
