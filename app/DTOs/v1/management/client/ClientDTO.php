<?php

namespace App\DTOs\v1\management\client;

use Spatie\DataTransferObject\DataTransferObject;
use Carbon\Carbon;
use InvalidArgumentException;

class ClientDTO extends DataTransferObject
{
    public function __construct(
        public readonly ?string $name,
        public readonly ?string $surname,
        public readonly ?int    $gender_id,
        public readonly ?Carbon $birthdate,
        public readonly ?int    $marital_status_id,
        public readonly ?int    $branch_id,
        public readonly ?int    $client_type_id,
        public readonly ?string $profession,
        public readonly ?int    $country_id,
        public readonly ?int    $document_type_id,
        public readonly ?int    $legal_entity,
        public readonly ?int    $status_id,
        public readonly ?string $comments,
    )
    {
        $this->validate();
    }

    public static function fromArray(array $data): self
    {

        return new self(
            name: $data['name'] ?? null,
            surname: $data['surname'] ?? null,
            gender_id: $data['gender_id'] ?? null,
            birthdate: isset($data['birthdate']) ? Carbon::parse($data['birthdate']) : null,
            marital_status_id: $data['marital_status_id'] ?? null,
            branch_id: $data['branch_id'] ?? null,
            client_type_id: $data['client_type_id'] ?? null,
            profession: $data['profession'] ?? null,
            country_id: $data['country_id'] ?? null,
            document_type_id: $data['document_type_id'] ?? null,
            legal_entity: $data['legal_entity'] ?? null,
            status_id: $data['status_id'] ?? null,
            comments: $data['comments'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'surname' => $this->surname,
            'gender_id' => $this->gender_id,
            'birthdate' => $this->birthdate->format('Y-m-d'),
            'marital_status_id' => $this->marital_status_id,
            'branch_id' => $this->branch_id,
            'client_type_id' => $this->client_type_id,
            'profession' => $this->profession,
            'country_id' => $this->country_id,
            'document_type_id' => $this->document_type_id,
            'legal_entity' => $this->legal_entity,
            'status_id' => $this->status_id,
            'comments' => $this->comments,
        ];
    }

    public function getFullName(): ?string
    {
        if (!$this->name && !$this->surname) return null;

        return trim("{$this->name} {$this->surname}");
    }

    public function isActive(): bool
    {
        return $this->status_id === 1;
    }

    public function getAge(): ?int
    {
        return $this->birthdate?->age;
    }

    private function validate(): void
    {
        if ($this->name && strlen(trim($this->name)) < 2) {
            throw new InvalidArgumentException("El nombre debe tener al menos 2 caracteres");
        }
        if ($this->surname && strlen(trim($this->surname)) < 2) {
            throw new InvalidArgumentException("El apellido debe tener al menos 2 caracteres");
        }
        if ($this->birthdate && $this->birthdate->isFuture()) {
            throw new InvalidArgumentException('La fecha de nacimiento no puede ser mayor a la fecha actual');
        }
        if ($this->birthdate && $this->birthdate->age > 150) {
            throw new InvalidArgumentException('La edad de una persona no puede ser exceder los 150 años');
        }
        if ($this->profession && strlen(trim($this->profession)) > 100) {
            throw new InvalidArgumentException("La profesión u oficio no puede exceder los 100 caracteres");
        }
        if ($this->comments && strlen($this->comments) > 1000) {
            throw new InvalidArgumentException("El comentario no puede exceder los 1000 caracteres");
        }
    }
}
