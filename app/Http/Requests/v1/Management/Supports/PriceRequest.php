<?php

namespace App\Http\Requests\v1\Management\Supports;

use App\DTOs\v1\management\supports\PriceDTO;
use App\Enums\v1\General\CommonStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PriceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => [
                'required',
                'integer',
                Rule::exists('supports_types', 'id')
                    ->where('status_id', CommonStatus::ACTIVE->value),
            ],
            'name' => 'required|string',
            'description' => 'nullable|string',
            'price' => 'required|decimal:2',
            'status' => 'required|in:0,1',
        ];
    }

    /**
     * Mensajes a mostrar cuando se incumpla alguna regla.
     *
     * @return string[]
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Tipo de soporte es requerido.',
            'type.integer' => 'Formato inválido.',
            'type.exists' => 'El tipo de soporte no existe.',

            'name.required' => 'Nombre es requerido.',
            'name.string' => 'Formato incorrecto.',

            'description.string' => 'Formato inválido.',

            'price.required' => 'Precio es requerido.',
            'price.decimal' => 'El precio debe ser decimal.',

            'status.required' => 'Estado es requerido.',
            'status.in' => 'Formato incorrecto.',
        ];
    }

    /**
     * Inserta los datos enviados al DTO.
     *
     * @return PriceDTO
     */
    public function toDTO(): PriceDTO
    {
        return new PriceDTO(
            type_id: $this->input('type'),
            name: $this->input('name'),
            description: $this->input('description'),
            price: $this->input('price'),
            status_id: $this->input('status'),
        );
    }
}
