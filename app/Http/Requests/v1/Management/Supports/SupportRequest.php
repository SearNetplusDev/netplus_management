<?php

namespace App\Http\Requests\v1\Management\Supports;

use App\Models\Infrastructure\Network\EquipmentModel;
use App\Models\Services\ServiceModel;
use Illuminate\Foundation\Http\FormRequest;

class SupportRequest extends FormRequest
{
    //  Constantes para tipos de soporte (mejor práctica para evitar magic numbers)
    private const SUPPORT_TYPES = [
        'INTERNET_INSTALLATION' => 1,
        'IPTV_INSTALLATION' => 2,
        'INTERNET_SUPPORT' => 3,
        'IPTV_SUPPORT' => 4,
        'CHANGE_ADDRESS' => 5,
        'INTERNET_RENEWAL' => 6,
        'IPTV_RENEWAL' => 7,
        'UNINSTALLATION' => 8,
        'EQUIPMENT_SALE' => 9,
    ];

    //  Estados que requieren técnico y solución
    private const STATUS_REQUIRING_TECHNICIAN = [2, 3];

    //  Tipos que requieren detalles de instalación/contrato
    private const TYPES_REQUIRING_CONTRACT_DETAILS = [
        self::SUPPORT_TYPES['INTERNET_INSTALLATION'],
        self::SUPPORT_TYPES['IPTV_INSTALLATION'],
        self::SUPPORT_TYPES['CHANGE_ADDRESS'],
        self::SUPPORT_TYPES['INTERNET_RENEWAL'],
        self::SUPPORT_TYPES['IPTV_RENEWAL'],
    ];

    //  Tipos que requieren servicio existente
    private const TYPES_REQUIRING_SERVICE = [
        self::SUPPORT_TYPES['INTERNET_SUPPORT'],
        self::SUPPORT_TYPES['IPTV_SUPPORT'],
        self::SUPPORT_TYPES['CHANGE_ADDRESS'],
        self::SUPPORT_TYPES['INTERNET_RENEWAL'],
        self::SUPPORT_TYPES['IPTV_RENEWAL'],
        self::SUPPORT_TYPES['UNINSTALLATION'],
        self::SUPPORT_TYPES['EQUIPMENT_SALE'],
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            //  Campos requeridos en todos los soportes
            'type' => ['required', 'integer', 'exists:supports_types,id'],
            'client' => ['required', 'integer', 'exists:clients,id'],
            'branch' => ['required', 'integer', 'exists:config_branches,id'],
            'state' => ['required', 'integer', 'exists:config_states,id'],
            'municipality' => ['required', 'integer', 'exists:config_municipalities,id'],
            'district' => ['required', 'integer', 'exists:config_districts,id'],
            'status' => ['required', 'integer', 'exists:supports_status,id'],
            'description' => ['required', 'string'],
            'address' => ['required', 'string'],

            //  Campos condicionales según estado de soporte
            'technician' => ['nullable', 'integer', 'exists:technicians,id'],

            //  Campos condicionales según soporte
            'service' => ['nullable', 'integer', 'exists:services,id'],
            'solution' => ['nullable', 'string'],
            'comments' => ['nullable', 'string'],
            'profile' => ['nullable', 'integer', 'exists:management_internet_profiles,id'],
            'initial_date' => ['nullable', 'date'],
            'final_date' => ['nullable', 'date'],
            'node' => ['nullable', 'integer', 'exists:infrastructure_nodes,id'],
            'equipment' => ['nullable', 'integer', 'exists:infrastructure_equipment,id'],
        ];
    }

    public function withValidator($validator): void
    {
        //  Validación condicional para campos requeridos según tipo de soporte
        $validator->sometimes(
            ['profile', 'node', 'equipment'],
            'required',
            function ($input) {
                return in_array((int)$input->type, self::TYPES_REQUIRING_CONTRACT_DETAILS);
            }
        );

        //  Validación condicional para servicio según tipo de soporte
        $validator->sometimes(
            'service',
            'required',
            function ($input) {
                return in_array((int)$input->type, self::TYPES_REQUIRING_SERVICE);
            }
        );

        //  Validación condicional para técnico y solución según estado
        $validator->sometimes(
            ['technician', 'solution'],
            'required',
            function ($input) {
                return in_array((int)$input->status, self::STATUS_REQUIRING_TECHNICIAN);
            }
        );

        //  Validación adicional: si hay fecha final, debe haber fecha inicial
        $validator->sometimes(
            'initial_date',
            'required',
            function ($input) {
                return !empty($input->final_date);
            }
        );

        //  Validación adicional: Sí hay equipo debe haber nodo
        $validator->sometimes(
            'node',
            'required',
            function ($input) {
                return !empty($input->equipment);
            }
        );

        //  Validación personalizada para verificar que el servicio pertenece al cliente
        $validator->after(function ($validator) {
            $this->validateServiceBelongsToClient($validator);
            $this->validateEquipmentBelongsToNode($validator);
        });
    }

    //  Valída que el servicio pertenezca al cliente
    private function validateServiceBelongsToClient($validator): void
    {
        if ($this->filled(['client', 'service'])) {
            $serviceExists = ServiceModel::query()
                ->where([
                    ['id', $this->input('service')],
                    ['client_id', $this->input('client')],
                ])
                ->exists();

            if (!$serviceExists) {
                $validator->errors()->add('service', 'El servicio seleccionado no pertenece al cliente');
            }
        }
    }

    //  Valída que el equipo pertenezca al nodo seleccionado
    private function validateEquipmentBelongsToNode($validator): void
    {
        if ($this->filled(['node', 'equipment'])) {
            $equipmentExists = EquipmentModel::query()
                ->where([
                    ['id', $this->input('equipment')],
                    ['node_id', $this->input('node')],
                ])
                ->exists();

            if (!$equipmentExists) {
                $validator->errors()->add('equipment', 'El equipo seleccionado no pertenece al nodo');
            }
        }
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Tipo de soporte es un campo obligatorio.',
            'type.integer' => 'Formato inválido.',
            'type.exists' => 'El tipo de soporte no existe.',

            'client.required' => 'Cliente es un campo obligatorio.',
            'client.integer' => 'Formato inválido.',
            'client.exists' => 'El cliente seleccionado no existe.',

            'branch.required' => 'Sucursal es un campo obligatorio.',
            'branch.integer' => 'Formato inválido.',
            'branch.exists' => 'La sucursal seleccionada no existe.',

            'state.required' => 'Departamento es un campo obligatorio.',
            'state.integer' => 'Formato inválido.',
            'state.exists' => 'El departamento seleccionado no existe.',

            'municipality.required' => 'Municipio es un campo obligatorio.',
            'municipality.integer' => 'Formato inválido.',
            'municipality.exists' => 'El municipio seleccionado no existe.',

            'district.required' => 'Distrito es un campo obligatorio.',
            'district.integer' => 'Formato inválido.',
            'district.exists' => 'El distrito seleccionado no existe.',

            'status.required' => 'Estado de soporte es un campo obligatorio.',
            'status.integer' => 'Formato inválido.',
            'status.exists' => 'El estado seleccionado no existe.',

            'description.required' => 'Descripción es un campo obligatorio.',
            'description.string' => 'Solo se admiten letras.',

            'address.required' => 'Dirección es un campo obligatorio.',
            'address.string' => 'Solo se admiten letras.',

            'technician.required' => 'Técnico es un campo obligatorio.',
            'technician.integer' => 'Formato inválido',
            'technician.exists' => 'El técnico seleccionado no existe.',

            'service.required' => 'Servicio es un campo obligatorio.',
            'service.integer' => 'Formato inválido.',
            'service.exists' => 'El servicio seleccionado no existe.',

            'solution.required' => 'Solución es un campo obligatorio.',
            'solution.string' => 'Solo se admiten letras.',

            'comments.string' => 'Solo se admiten letras.',

            'profile.required' => 'Perfil es un campo obligatorio.',
            'profile.integer' => 'Formato inválido.',
            'profile.exists' => 'El perfil seleccionado no existe.',

            'initial_date.date' => 'No es una fecha.',

            'final_date.date' => 'No es una fecha.',

            'node.required' => 'Nodo es un campo obligatorio.',
            'node.integer' => 'Formato inválido.',
            'node.exists' => 'El nodo seleccionado no existe.',

            'equipment.required' => 'Equipo es un campo obligatorio.',
            'equipment.integer' => 'Formato inválido.',
            'equipment.exists' => 'El equipo seleccionado no existe.',
        ];
    }
}
