<?php

namespace App\Http\Requests\v1\Management\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UsersRequest extends FormRequest
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
        $rules = [
            'name' => 'required|string|min:10',
            'email' => 'required|email|unique:users,email',
            'password' => 'nullable|min:8',
            'status' => 'required|boolean',
        ];
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $id = $this->route('id') ?? $this->input('id');
            $rules['email'] = "required|email|unique:users,email,{$id}";
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Debes ingresar el nombre del usuario.',
            'name.string' => 'Formato de nombre incorrecto.',
            'name.min' => 'Nombre de usuario debe tener 10 caracteres como mínimo.',
            'email.required' => 'Debes ingresar el email del usuario.',
            'email.email' => 'Formato de email incorrecto.',
            'email.unique' => 'Este email ya fue registrado.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'status.required' => 'Debes ingresar el estado del usuario.',
            'status.boolean' => 'Debes seleccionar un valor valido.',
        ];
    }
}
