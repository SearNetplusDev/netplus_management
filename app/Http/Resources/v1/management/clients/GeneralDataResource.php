<?php

namespace App\Http\Resources\v1\management\clients;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GeneralDataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'surname' => $this->surname,
            'gender_id' => $this->gender_id,
            'birthdate' => $this->birthdate,
            'marital_status_id' => $this->marital_status_id,
            'branch_id' => $this->branch_id,
            'client_type_id' => $this->client_type_id,
            'profession' => $this->profession,
            'country_id' => $this->country_id,
            'document_type_id' => $this->document_type_id,
            'legal_entity' => $this->legal_entity,
            'status_id' => $this->status_id,
            'comments' => $this->comments
        ];
    }
}
