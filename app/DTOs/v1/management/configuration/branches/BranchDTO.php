<?php

namespace App\DTOs\v1\management\configuration\branches;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\StringType;

class BranchDTO extends Data
{
    #[Required, StringType]
    public ?string $name;
    #[Required, StringType]
    public ?string $code;
    #[Required, StringType]
    public ?string $landline;
    #[Required, StringType]
    public ?string $mobile;
    #[Required, StringType]
    public ?string $address;
    #[Required, IntegerType]
    public ?int $state_id;
    #[Required, IntegerType]
    public ?int $municipality_id;
    #[Required, IntegerType]
    public ?int $district_id;
    #[Required, IntegerType]
    public ?int $country_id;
    #[Required, StringType]
    public ?string $badge_color;
    #[Required, IntegerType]
    public ?int $status_id;

}
