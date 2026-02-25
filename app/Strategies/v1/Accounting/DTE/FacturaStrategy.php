<?php

namespace App\Strategies\v1\Accounting\DTE;

use App\Contracts\v1\Accounting\DTE\DTEGeneratorInterface;

class FacturaStrategy implements DTEGeneratorInterface
{

    public function generate(array $data): array
    {
        return $this->buildBody($data);
    }

    private function buildBody(array $data): array
    {
        return [
            'identificacion' => [
                'version' => 1,
                'ambiente' => '01',
            ],
            'documentoRelacionado' => [],
        ];
    }
}
