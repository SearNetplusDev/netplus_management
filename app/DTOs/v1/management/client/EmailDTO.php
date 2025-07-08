<?php

namespace App\DTOs\v1\management\client;

use Spatie\DataTransferObject\DataTransferObject;

class EmailDTO extends DataTransferObject
{
    public function __construct(
        public readonly int    $client_id,
        public readonly string $email,
        public readonly int    $status_id,
    )
    {

    }

    public function fromArray(array $data): self
    {
        return new self(
            client_id: $data['client_id'] ?? 0,
            email: $data['email'],
            status_id: $data['status_id'] ?? 0,
        );
    }

    public function toArray(): array
    {
        return [
            'client_id' => $this->client_id ?? 0,
            'email' => $this->email ?? 'mail@mail.com',
            'status_id' => $this->status_id ?? 0,
        ];
    }
}
