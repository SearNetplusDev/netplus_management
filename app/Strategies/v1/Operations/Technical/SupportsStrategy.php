<?php

namespace App\Strategies\v1\Operations\Technical;

use App\Contracts\v1\Supports\ProcessSupportInterface;
use App\Models\Supports\SupportModel;

class SupportsStrategy implements ProcessSupportInterface
{

    public function process(array $params): SupportModel
    {
        // TODO: Implement process() method.
    }
}
