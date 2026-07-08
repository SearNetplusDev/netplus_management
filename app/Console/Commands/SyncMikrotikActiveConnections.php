<?php

namespace App\Console\Commands;

use App\Jobs\monitoring\SyncConnectionsJob;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('mikrotik:sync-connections')]
#[Description('Actualiza las conexiones activas PPPoe en la base de datos.')]
class SyncMikrotikActiveConnections extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        SyncConnectionsJob::dispatch((int)env('MK_MAIN'));

        $this->info("Job de sincronización despachado a la cola.");
        return self::SUCCESS;
    }
}
