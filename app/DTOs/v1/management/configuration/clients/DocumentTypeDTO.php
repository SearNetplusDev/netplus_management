<?php

namespace App\DTOs\v1\management\configuration\clients;

use Spatie\DataTransferObject\DataTransferObject;

class DocumentTypeDTO extends DataTransferObject
{
    public ?string $name;
    public ?string $code;
    public ?bool $status_id;
}
