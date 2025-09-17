<?php

namespace App\Services\v1\management\supports;

use App\DTOs\v1\management\supports\SupportDTO;
use App\Models\Supports\SupportModel;

class SupportService
{
    public function create(SupportDTO $DTO)/*: array*/
    {
        $ticket = $this->createTicket();

        //  Buscando estrategia correcta
        $strategy = SupportFactory::make((int)$DTO->type_id);
        return $strategy->handle($DTO->toArray(), $ticket);
    }

    public function read(int $id): SupportModel
    {
        return SupportModel::query()
            ->with(['client', 'service', 'details', 'contract'])
            ->findOrFail($id);
    }

    private function createTicket(): string
    {
        $prefix = 'NETPLUS_SPT-';
        $total = SupportModel::query()->withTrashed()->count();
        $totalLength = 10;
        $zeroFill = max(0, $totalLength - strlen($total));
        $filling = str_repeat('0', $zeroFill);
        $prefix .= $filling . ($total + 1);
        return $prefix;
    }
}
