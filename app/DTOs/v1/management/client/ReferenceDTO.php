<?php

namespace App\DTOs\v1\management\client;

use Spatie\DataTransferObject\DataTransferObject;

class ReferenceDTO extends DataTransferObject
{
    public function __construct(
        public readonly int    $client_id,
        public readonly string $name,
        public readonly string $dui,
        public readonly string $mobile,
        public readonly int    $status_id,
    )
    {

    }

    public function fromArray(array $data): self
    {
        return new self(
            client_id: $data['client_id'] ?? 0,
            name: $data['name'] ?? '',
            dui: $data['dui'] ?? '',
            mobile: $data['mobile'] ?? '',
            status_id: $data['status_id'] ?? 0,
        );
    }

    public function toArray(): array
    {
        return [
            'client_id' => $this->client_id,
            'name' => $this->name,
            'dui' => $this->dui,
            'mobile' => $this->mobile,
            'status_id' => $this->status_id,
        ];
    }
}
