<?php

namespace App\Services\v1\management\infrastructure\equipments;

use App\DTOs\v1\management\infrastructure\equipments\InventoryDTO;
use App\Models\Infrastructure\Equipment\InventoryModel;

class InventoryService
{
    public function singleCreate(InventoryDTO $inventoryDTO): InventoryModel
    {
        return InventoryModel::query()->create($inventoryDTO->toArray());
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
