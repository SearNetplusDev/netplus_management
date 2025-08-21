<?php

namespace App\Services\v1\management\services;

use App\DTOs\v1\management\services\ServiceEquipmentDTO;
use App\DTOs\v1\management\infrastructure\equipments\InventoryLogDTO;
use App\Models\Infrastructure\Equipment\InventoryLogModel;
use App\Models\Infrastructure\Equipment\InventoryModel;
use App\Models\Services\ServiceEquipmentModel;
use App\Models\Services\ServiceModel;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;


class EquipmentService
{
    public function serviceEquipment(int $serviceId): Collection
    {
        return ServiceEquipmentModel::query()
            ->with([
                'equipment.type:id,name',
                'equipment.brand:id,name',
                'equipment.model:id,name',
            ])
            ->where("service_id", $serviceId)
            ->get();
    }

    public function assignEquipment(ServiceEquipmentDTO $DTO): ServiceEquipmentModel
    {
        $service = ServiceModel::query()
            ->with('client')
            ->find($DTO->service_id);

        $equipment = InventoryModel::query()->find($DTO->equipment_id);

        $exists = ServiceEquipmentModel::query()
            ->where('service_id', $DTO->service_id)
            ->whereHas('equipment', function ($query) use ($equipment) {
                $query->where('type_id', $equipment->type_id);
            })
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'equipment' => ["Este servicio ya posee un equipo de la categoría {$equipment->type->name}"]
            ]);
        }

        $equipment->update(['status_id' => 3]);

        $message = 'Equipo asignado a ';
        $message .= $service->client->name . ' ' . $service->client->surname;
        $message .= ' mediante el módulo servicios';

        $logDTO = new InventoryLogDTO(
            equipment_id: $DTO->equipment_id,
            user_id: Auth::user()->id,
            technician_id: null,
            execution_date: Carbon::today(),
            service_id: $DTO->service_id,
            status_id: 3,
            description: $message,
        );
        InventoryLogModel::query()->create($logDTO->toArray());

        return ServiceEquipmentModel::query()->create($DTO->toArray());
    }
}
