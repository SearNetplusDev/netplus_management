<?php

namespace App\Strategies\v1\Supports\Process;

use App\Contracts\v1\Supports\SupportTypeInterface;
use App\Models\Clients\ContractModel;
use App\Models\Management\Profiles\InternetModel;
use App\Models\Supports\SupportDetailModel;
use App\Models\Supports\SupportModel;
use App\Models\Supports\TypeModel;
use Carbon\Carbon;

class Installation implements SupportTypeInterface
{

    public function handle(array $data, string $ticket): SupportModel
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
        $contract = ContractModel::query()->create($contractData);

        /****
         * Creando Soporte
         ****/
        $creationDate = Carbon::parse($data['creation_date']);

        $supportData = [
            'type_id' => $data['type_id'],
            'ticket_number' => $ticket,
            'client_id' => $data['client_id'],
            'contract_id' => $contract->id,
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
        $support = SupportModel::query()->create($supportData);

        /****
         * Agregando detalles al Soporte
         ****/
        $supportDetails = [
            'support_id' => $support->id,
            'type_id' => $data['type_id'],
            'internet_profile_id' => $data['internet_profile_id'],
            'node_id' => $data['node_id'],
            'equipment_id' => $data['equipment_id'],
        ];
        SupportDetailModel::query()->create($supportDetails);

        return $support;
    }
}
