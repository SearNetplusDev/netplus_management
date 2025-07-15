<?php

namespace App\DTOs\v1\management\configuration\geography;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\StringType;

class DistrictsDTO extends Data
{
    #[Required, StringType]
    public ?string $name;
    #[Required, IntegerType]
    public ?int $municipality_id;
    #[Required, IntegerType]
    public ?int $state_id;
    #[Required, IntegerType]
    public ?int $status_id;
}
