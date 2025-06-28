<?php

namespace App\DTOs\v1\management\configuration\branches;

use Spatie\DataTransferObject\DataTransferObject;

class BranchDTO extends DataTransferObject
{
    public ?string $name;
    public ?string $code;
    public ?string $landline;
    public ?string $mobile;
    public ?string $address;
    public ?int $state_id;
    public ?int $municipality_id;
    public ?int $district_id;
    public ?int $country_id;
    public ?string $badge_color;
    public ?bool $status_id;

}
