<?php

namespace App\Services\v1\management\configuration\branches;

use App\DTOs\v1\management\configuration\branches\BranchDTO;
use App\Models\Configuration\BranchModel;

class BranchService
{
    public function createBranch(BranchDTO $branchData): BranchModel
    {
        return BranchModel::create((array)$branchData);
    }

    public function updateBranch(BranchModel $branchModel, BranchDTO $branchData): BranchModel
    {
        $branchModel->fill((array)$branchData)->save();
        return $branchModel;
    }
}
