<?php

namespace App\Strategies\v1\Supports\Tickets;

use App\Contracts\v1\Supports\SupportTicketInterface;
use App\Models\Supports\SupportModel;
use Barryvdh\DomPDF\Facade\Pdf;

class GenericTicket implements SupportTicketInterface
{

    public function render(SupportModel $support): string
    {
        $pdf = Pdf::loadView('v1.management.pdf.supports.generic', [
            'data' => [
                'type' => $support->type?->name,
                'ticket' => $support->ticket_number,
                'client' => "{$support->client?->name} {$support->client?->surname}",
            ],
        ])->setPaper('A4', 'portrait');

        return $pdf->stream();
    }
}
