<?php

namespace App\Services\v1\management\supports;

use App\Contracts\v1\Supports\SupportTicketInterface;
use App\Strategies\v1\Supports\Tickets\ContractTicket;
use PHPUnit\Framework\Attributes\Ticket;

class TicketFactory
{
    protected static array $map = [
        1 => ContractTicket::class,
        2 => ContractTicket::class,
        6 => ContractTicket::class,
        7 => ContractTicket::class,

        3 => Ticket::class,
        4 => Ticket::class,
        5 => Ticket::class,
        8 => Ticket::class,
        9 => Ticket::class,
    ];

    public static function make(int $type): SupportTicketInterface
    {
        if (!isset(self::$map[$type])) {
            throw new \InvalidArgumentException('Tipo de ticket no disponible.');
        }

        return app(self::$map[$type]);
    }
}
