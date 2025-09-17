<?php

namespace App\Strategies\v1\Supports;

use App\Contracts\v1\Supports\SupportTypeInterface;
use App\DTOs\v1\management\supports\SupportDTO;
use App\Models\Management\Profiles\InternetModel;
use App\Models\Supports\TypeModel;
use Carbon\Carbon;

class Installation implements SupportTypeInterface
{

    public function handle(array $data, string $ticket): array
    {
        /****
         * Creando Contrato
         ****/
        $begins = Carbon::parse($data['contract_date']);
        $ends = Carbon::parse($data['contract_end_date']);

        //  Obteniendo el costo de instalación
        $type = TypeModel::query()->findOrFail((int)$data['type_id']);

        //  Obteniendo el precio del plan adquirido
        $profile = InternetModel::query()->findOrFail((int)$data['internet_profile_id']);
        $duration = $begins->diffinMonths($ends, false);
        $total = $profile->price * $duration;

        $contractData = [
            'client_id' => (int)$data['client_id'],
            'contract_date' => $begins->toDateString(),
            'contract_end_date' => $ends->toDateString(),
            'installation_price' => $type?->price,
            'contract_amount' => $total,
            'contract_status_id' => 1,
            'status_id' => 1,
        ];

        /****
         * Creando Soporte
         ****/
        $creationDate = Carbon::parse($data['creation_date']);

        $supportData = [
            'type_id' => $data['type_id'],
            'ticket_number' => $ticket,
            'client_id' => $data['client_id'],
            'contract_id' => $contractData['contract_amount'],
            'service_id' => $data['service_id'],
            'branch_id' => $data['branch_id'],
            'creation_date' => $creationDate->toDateTimeString(),
            'due_date' => $creationDate->addHours(72)->toDateTimeString(),
            'description' => $data['description'],
            'technician_id' => $data['technician_id'],
            'state_id' => $data['state_id'],
            'municipality_id' => $data['municipality_id'],
            'district_id' => $data['district_id'],
            'address' => $data['address'],
            'closed_at' => null,
            'solution' => $data['solution'],
            'comments' => $data['comments'],
            'user_id' => (int)$data['user_id'],
            'status_id' => (int)$data['status_id'],
            'breached_sla' => false,
            'resolution_time' => null,
        ];

        /****
         * Agregando detalles al Soporte
         ****/

        return [
            'contract' => $contractData,
            'support' => $supportData,
        ];
    }
}
