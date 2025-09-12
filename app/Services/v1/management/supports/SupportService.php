<?php

namespace App\Services\v1\management\supports;

use App\Models\Supports\SupportModel;

class SupportService
{
    public function create(array $data)/*: array*/
    {
        $ticket = $this->createTicket();
        $data['ticket_number'] = $ticket;
        return $data;
    }

    private function createTicket(): string
    {
        $prefix = 'NTP-';
        $total = SupportModel::query()->withTrashed()->count();
        $totalLength = 10;
        $zeroFill = max(0, $totalLength - strlen($total));
        $filling = str_repeat('0', $zeroFill);
        $prefix .= $filling . ($total + 1);
        return $prefix;
    }
}
