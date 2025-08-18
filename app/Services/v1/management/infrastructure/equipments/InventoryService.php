<?php

namespace App\Services\v1\management\infrastructure\equipments;

use App\DTOs\v1\management\infrastructure\equipments\InventoryDTO;
use App\Models\Infrastructure\Equipment\InventoryModel;
use Illuminate\Http\UploadedFile;
use App\Imports\InventoryImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;

class InventoryService
{
    public function create(array $formData, UploadedFile $file): array
    {
        $baseData = [
            'brand_id' => $formData['brand'],
            'type_id' => $formData['type'],
            'model_id' => $formData['model'],
            'branch_id' => $formData['branch'],
            'user_id' => Auth::user()->id,
            'status_id' => $formData['status'],
            'comments' => $formData['comments'] ?? null,
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
        return InventoryModel::query()->find($id);
    }

    public function update(InventoryModel $inventoryModel, InventoryDTO $DTO): InventoryModel
    {
        $inventoryModel->update($inventoryModel->toArray());
        return $inventoryModel;
    }
}
