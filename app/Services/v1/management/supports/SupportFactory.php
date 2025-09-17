<?php

namespace App\Services\v1\management\supports;

use App\Contracts\v1\Supports\SupportTypeInterface;
use App\Strategies\v1\Supports\Installation;
use InvalidArgumentException;

class SupportFactory
{
    public static function make(int $type): SupportTypeInterface
    {
        return match ($type) {
            1, 2 => new Installation(),
            3, 4 => 'Soporte',
            5 => 'Cambio de domicilio',
            6, 7 => 'Renovación',
            8 => 'Desinstalación',
            9 => 'Venta de equipo',
            default => throw new InvalidArgumentException("Tipo de soporte no válido"),
        };
    }
}
