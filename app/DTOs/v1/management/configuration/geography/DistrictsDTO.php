<?php

namespace App\DTOs\v1\management\configuration\geography;

use Spatie\DataTransferObject\DataTransferObject;

class DistrictsDTO extends DataTransferObject
{
    public ?string $name;
    public ?int $municipality_id;
    public ?int $state_id;
    public ?bool $status_id;
}
