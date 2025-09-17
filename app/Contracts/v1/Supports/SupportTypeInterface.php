<?php

namespace App\Contracts\v1\Supports;

interface SupportTypeInterface
{
    public function handle(array $data, string $ticket): array;
}
