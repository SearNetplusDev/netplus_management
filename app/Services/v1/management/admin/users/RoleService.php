<?php

namespace App\Services\v1\management\admin\users;

use App\DTOs\v1\management\admin\users\RoleDTO;
use App\Models\Management\RoleModel;

class RoleService
{
    public function create(RoleDTO $DTO): RoleModel
    {
        return RoleModel::query()->create($DTO->toArray());
    }

    public function read(int $id): RoleModel
    {
        return RoleModel::query()->find($id);
    }

    public function update(RoleModel $roleModel, RoleDTO $DTO): RoleModel
    {
        $roleModel->update($DTO->toArray());
        return $roleModel;
    }
}
