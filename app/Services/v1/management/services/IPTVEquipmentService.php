<?php

namespace App\Services\v1\management\services;

use App\DTOs\v1\management\services\ServiceIptvEquipmentDTO;
use App\Models\Infrastructure\Equipment\InventoryLogModel;
use App\Models\Infrastructure\Equipment\InventoryModel;
use App\Models\Services\ServiceIptvEquipmentModel;
use App\Models\Services\ServiceModel;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class IPTVEquipmentService
{
    public function list(int $serviceId): Collection
    {
        return ServiceIptvEquipmentModel::query()
            ->with([
                'equipment.type:id,name',
                'equipment.brand:id,name',
                'equipment.model:id,name',
            ])
            ->where('service_id', $serviceId)
            ->get();
    }

    public function assign(ServiceIptvEquipmentDTO $DTO): ServiceIptvEquipmentModel
    {
        $service = ServiceModel::query()
            ->with(['client', 'internet.profile'])
            ->find($DTO->service_id);

        $hasIPTV = $service->internet->profile->iptv;

        if (!$hasIPTV) {
            throw ValidationException::withMessages([
                'service' => ["Este servicio no posee IPTV"]
            ]);
        }

        $equipment = InventoryModel::query()
            ->where([
                ['id', $DTO->equipment_id],
                ['status_id', 2],
            ])
            ->first();

        $equipment->update(['status_id' => 3]);

        $message = 'Equipo asignado a ';
        $message .= $service->client?->name . ' ' . $service->client?->surname;
        $message .= ' en el servicio ID: ' . $service->id;
        $message .= ' mediante el módulo servicios';

        InventoryLogModel::query()->create([
            'equipment_id' => $equipment->id,
            'user_id' => Auth::user()->id,
            'technician_id' => null,
            'execution_date' => Carbon::today(),
            'service_id' => $service->id,
            'status_id' => 3,
            'description' => $message,
        ]);

        return ServiceIptvEquipmentModel::query()->create($DTO->toArray());
    }

    public function read(int $serviceId)
    {
        return ServiceIptvEquipmentModel::query()->find($serviceId);
    }

    public function update(ServiceIptvEquipmentModel $model, ServiceIptvEquipmentDTO $DTO): ServiceIptvEquipmentModel
    {
        /***
         * validar si el servicio tiene iptv disponible
         * verificar  que el servicio sea el mismo, si no a log.
         * verificar que el correo sea el mismo, de lo contrario a log.
         * verificar que sean las mismas contraseñas, si no, a log.
         ***/
    }

    public function delete(int $id): bool
    {

    }
}
