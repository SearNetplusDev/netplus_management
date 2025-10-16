<?php

namespace App\Contracts\v1\Supports;

use App\Models\Supports\SupportModel;

interface ProcessSupportInterface
{
    public function process(array $params): SupportModel;
}
