<?php

namespace App\Services\v1\management\supports;

use App\Contracts\v1\Supports\SupportTypeInterface;
use App\Strategies\v1\Supports\Process\ChangeAddress;
use App\Strategies\v1\Supports\Process\EquipmentSale;
use App\Strategies\v1\Supports\Process\Installation;
use App\Strategies\v1\Supports\Process\Maintenance;
use App\Strategies\v1\Supports\Process\Uninstall;
use InvalidArgumentException;

class SupportFactory
{
    public static function make(int $type): SupportTypeInterface
    {
        return match ($type) {
            1, 2, 6, 7 => new Installation(),
            3, 4 => new Maintenance(),
            5 => new ChangeAddress(),
            8 => new Uninstall(),
            9 => new EquipmentSale(),
            default => throw new InvalidArgumentException("Tipo de soporte no válido"),
        };
    }
}
