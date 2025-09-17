<?php

namespace App\Strategies\v1\Supports\Process;

use App\Contracts\v1\Supports\SupportTypeInterface;
use App\Models\Supports\SupportModel;

class EquipmentSale implements SupportTypeInterface
{

    public function handle(array $data, string $ticket): SupportModel
    {
        // TODO: Implement handle() method.
    }
}
