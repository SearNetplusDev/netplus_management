<?php

namespace App\Http\Requests\v1\Management\Billing\Payments;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'client' => 'required|integer|exists:clients,id',
            'service' => 'required|integer|exists:services,id',
            'invoice' => 'required|integer|exists:billing_invoices,id',
            'amount' => 'required|decimal',
            'payment_method' => 'required|integer|exists:billing_payment_methods,id',
            'comments' => 'nullable|string|between:2,255',
        ];
    }

    public function messages(): array
    {
        return [
            'client.required' => 'No se ha especificado el cliente.',
            'client.integer' => 'Formato incorrecto para cliente.',
            'client.exists' => 'El cliente seleccionado no existe.',

            'service.required' => 'No se ha especificado el servicio.',
            'service.integer' => 'Formato incorrecto para servicio.',
            'service.exists' => 'El servicio seleccionado no existe.',

            'invoice.required' => 'Factura no especificada.',
            'invoice.integer' => 'Formato incorrecto para factura.',
            'invoice.exists' => 'La factura seleccionada no existe.',

            'amount.required' => 'El monto es obligatorio.',
            'amount.decimal' => 'El monto debe ser un número decimal.',

            'payment_method.required' => 'Método de pago no especificado.',
            'payment_method.integer' => 'Formato incorrecto.',
            'payment_method.exists' => 'Este método de pago no existe.',

            'comments.string' => 'Formato incorrecto para observaciones.',
            'comments.between' => 'Las observaciones deben tener entre 2 y 255 caracteres.',
        ];
    }
}
