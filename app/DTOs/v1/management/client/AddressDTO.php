<?php

namespace App\DTOs\v1\management\client;

use Spatie\DataTransferObject\DataTransferObject;

class AddressDTO extends DataTransferObject
{
    public function __construct(
        public readonly int    $client_id,
        public readonly string $neighborhood,
        public readonly string $address,
        public readonly int    $state_id,
        public readonly int    $municipality_id,
        public readonly int    $district_id,
        public readonly int    $country_id,
        public readonly int    $status_id,
    )
    {

    }

    public function fromArray(array $data): self
    {
        return new self(
            client_id: $data['client_id'] ?? 0,
            neighborhood: $data['neighborhood'] ?? '',
            address: $data['address'] ?? '',
            state_id: $data['state_id'] ?? 0,
            municipality_id: $data['municipality_id'] ?? 0,
            district_id: $data['district_id'] ?? 0,
            country_id: $data['country_id'] ?? 0,
            status_id: $data['status_id'] ?? 0,
        );
    }

    public function toArray(): array
    {
        return [
            'client_id' => $this->client_id,
            'neighborhood' => $this->neighborhood,
            'address' => $this->address,
            'state_id' => $this->state_id,
            'municipality_id' => $this->municipality_id,
            'district_id' => $this->district_id,
            'country_id' => $this->country_id,
            'status_id' => $this->status_id,
        ];
    }
}
