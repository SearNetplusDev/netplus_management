<?php

namespace App\Strategies\v1\Supports\Tickets;

use App\Contracts\v1\Supports\SupportTicketInterface;
use App\Models\Supports\SupportModel;
use Barryvdh\DomPDF\Facade\Pdf;

class GenericTicket implements SupportTicketInterface
{

    public function render(SupportModel $support): string
    {
        $pdf = Pdf::loadView('', [
            'support' => $support,
            'client' => $support->client,
        ]);

        return $pdf->stream();
    }
}
