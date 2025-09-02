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
        $hasIPTV = $this->hasIptv($DTO->service_id);
        $client = $this->client($DTO->service_id);

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
        $message .= $client;
        $message .= ' en el servicio ID: ' . $DTO->service_id;
        $message .= ' mediante el módulo servicios';

        InventoryLogModel::query()->create([
            'equipment_id' => $equipment->id,
            'user_id' => Auth::user()->id,
            'technician_id' => null,
            'execution_date' => Carbon::today(),
            'service_id' => $DTO->service_id,
            'status_id' => 3,
            'description' => $message,
        ]);

        return ServiceIptvEquipmentModel::query()->create($DTO->toArray());
    }

    public function read(int $serviceId)
    {
        return ServiceIptvEquipmentModel::query()->with('equipment')->find($serviceId);
    }

    public function update(ServiceIptvEquipmentModel $model, ServiceIptvEquipmentDTO $DTO): ServiceIptvEquipmentModel
    {
        /***
         * verificar que el correo sea el mismo, de lo contrario a log.
         * verificar que sean las mismas contraseñas, si no, a log.
         ***/
        $client = $this->client($DTO->service_id);
        $hasIPTV = $this->hasIptv($DTO->service_id);
        $message = null;

        if (!$hasIPTV) {
            throw ValidationException::withMessages([
                'service' => ["Este servicio no posee IPTV"]
            ]);
        }

        if ((int)$model->service_id !== (int)$DTO->service_id) {
            $message = "Equipo cambio del servicio con ID: {$model->service_id} al servicio con ID: {$DTO->service_id}";
        }

        if ($model->email !== $DTO->email) {
            $message = "El correo ha sido cambiado de {$model->email} a {$DTO->email}. Servicio: {$model->service_id}";
        }

        if ($model->email_password !== $DTO->email_password) {
            $message = "La contraseña del correo ha sido cambiada de {$model->email_password} a {$DTO->email_password}. Servicio: {$model->service_id}";
        }

        if ($model->iptv_password !== $DTO->iptv_password) {
            $message = "La contraseña del servicio iptv ha sido cambiada de {$model->iptv_password} a {$DTO->iptv_password}. Servicio: {$model->service_id}";
        }

        InventoryLogModel::query()->create([
            'equipment_id' => $DTO->equipment_id,
            'user_id' => Auth::user()->id,
            'technician_id' => null,
            'execution_date' => Carbon::today(),
            'service_id' => $DTO->service_id,
            'status_id' => 3,
            'description' => $message,
        ]);

        $model->update($DTO->toArray());
        return $model;
    }

    public function delete(int $id): bool
    {
        $iptv = ServiceIptvEquipmentModel::query()
            ->with(['equipment', 'service.client'])
            ->find($id);
        $message = "Equipo desviculado del servicio ID: {$iptv->service_id}";
        $message .= ' correspondiente al cliente: ';
        $message .= "{$iptv->service?->client?->name} {$iptv->service?->client?->surname}";
        $device = InventoryModel::query()->find($iptv->equipment_id);
        InventoryLogModel::query()->create([
            'equipment_id' => $iptv->equipment_id,
            'user_id' => Auth::user()->id,
            'technician_id' => null,
            'execution_date' => Carbon::today(),
            'service_id' => $iptv->service_id,
            'status_id' => 2,
            'description' => $message,
        ]);
        $device->update(['status_id' => 2]);
        return $iptv->delete();
    }

    private function client(int $serviceId): string
    {
        $service = ServiceModel::query()
            ->with(['client'])
            ->find($serviceId);

        return $service->client?->name . ' ' . $service->client?->surname;

    }

    private function hasIptv(int $serviceId): bool
    {
        $service = ServiceModel::query()
            ->with(['internet.profile'])
            ->find($serviceId);
        return $service->internet->profile->iptv;
    }
}
