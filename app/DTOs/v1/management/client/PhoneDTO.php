<?php

namespace App\DTOs\v1\management\client;

use Spatie\DataTransferObject\DataTransferObject;

class PhoneDTO extends DataTransferObject
{
    public function __construct(
        public readonly int    $client_id,
        public readonly int    $phone_type_id,
        public readonly string $number,
        public readonly int    $status_id
    )
    {

    }

    public function fromArray(array $data): self
    {
        return new self(
            client_id: $data['client_id'] ?? 0,
            phone_type_id: $data['phone_type_id'] ?? 0,
            number: $data['number'] ?? '',
            status_id: $data['status_id'] ?? 0
        );
    }

    public function toArray(): array
    {
        return [
            'client_id' => $this->client_id ?? 0,
            'phone_type_id' => $this->phone_type_id ?? 0,
            'number' => $this->number ?? '',
            'status_id' => $this->status_id ?? 0
        ];
    }
}
