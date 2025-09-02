<?php

namespace App\Services\v1\management\infrastructure\equipments;

use App\DTOs\v1\management\infrastructure\equipments\InventoryLogDTO;
use App\Models\Configuration\Infrastructure\EquipmentStatusModel;
use App\Models\Infrastructure\Equipment\InventoryLogModel;
use App\Models\Infrastructure\Equipment\InventoryModel;
use App\Models\Infrastructure\Equipment\TypeModel;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Http\UploadedFile;
use App\Imports\InventoryImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use phpDocumentor\Reflection\Types\Boolean;

class InventoryService
{
    public function create(array $formData, UploadedFile $file): array
    {
        $baseData = [
            'brand_id' => $formData['brand'],
            'type_id' => $formData['type'],
            'model_id' => $formData['model'],
            'branch_id' => $formData['branch'],
            'status_id' => $formData['status'],
            'comments' => $formData['comments'] ?? null,
            'company_id' => $formData['company'],
        ];

        $import = new InventoryImport($baseData);

        try {
            Excel::import($import, $file);

            return [
                'success' => true,
                'results' => $import->getResults(),
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => "Error al procesar el archivo: " . $e->getMessage()
            ];
        }
    }

    public function read(int $id): InventoryModel
    {
        return InventoryModel::query()->with('last_technician')->find($id);
    }

    public function update(InventoryModel $inventoryModel, $data): InventoryModel
    {
        $inventoryModel->update([
            'type_id' => $data['type'],
            'brand_id' => $data['brand'],
            'model_id' => $data['model'],
            'branch_id' => $data['branch'],
            'status_id' => $data['status'],
            'mac_address' => $data['mac'],
            'serial_number' => $data['serial'],
            'comments' => $data['comments'] ?? null,
            'company_id' => $data['company'],
        ]);

        $status = EquipmentStatusModel::query()->find($data['status']);

        $message = match ((int)$data['status']) {
            1 => 'Equipo retornado a bodega',
            2, 3, 4, 5, 6 => 'Equipo ' . strtolower($status->name),
            7 => $status->name,
        };

        $DTO = new InventoryLogDTO(
            equipment_id: $inventoryModel->id,
            user_id: Auth::user()->id,
            technician_id: $data['technician'] ?? null,
            execution_date: Carbon::today(),
            service_id: null,
            status_id: (int)$data['status'],
            description: $message,
        );

        InventoryLogModel::query()->create($DTO->toArray());
        return $inventoryModel;
    }

    public function logs(int $id): InventoryModel
    {
        return InventoryModel::query()
            ->with([
                'brand:id,name',
                'type:id,name',
                'model:id,name',
                'logs.user:id,name',
                'logs.technician.user:id,name',
                'logs.status:id,name,badge_color',
            ])
            ->find($id);
    }

    public function internet_search(string $chars): Collection
    {
        $type = TypeModel::query()
            ->where('name', 'ILIKE', '%tv box%')
            ->where('status_id', 1)
            ->first();

        return InventoryModel::query()
            ->where('status_id', 2)
            ->whereNot('type_id', $type->id)
            ->whereRaw("REPLACE(mac_address, ':', '') ILIKE ?", ["%$chars%"])
            ->select(['id', 'mac_address as name'])
            ->get();
    }

    public function tvBoxSearch(string $chars): Collection
    {
        $type = TypeModel::query()
            ->where('name', 'ILIKE', '%tv box%')
            ->where('status_id', 1)
            ->first();

        return InventoryModel::query()
            ->where([
                ['status_id', 2],
                ['type_id', $type->id],
            ])
            ->whereRaw("REPLACE(mac_address, ':', '') ILIKE ?", ["%$chars%"])
            ->select(['id', 'mac_address as name'])
            ->get();
    }
}
