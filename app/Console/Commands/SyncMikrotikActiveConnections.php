<?php

namespace App\Console\Commands;

use App\Models\Infrastructure\Network\AuthServerModel;
use App\Services\v1\monitoring\MikrotikConnectionSyncService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

#[Signature('mikrotik:sync-connections')]
#[Description('Actualiza las conexiones activas PPPoe en la base de datos.')]
class SyncMikrotikActiveConnections extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(MikrotikConnectionSyncService $service): int
    {
        try {
            $authServer = AuthServerModel::query()->findOrFail(env('MK_MAIN'));

            $synced = $service->sync(
                host: $authServer->ip,
                user: $authServer->user,
                pass: $authServer->secret,
                port: (int)$authServer->port
            );
            $this->info("Sincronizadas: " . count($synced));
            return self::SUCCESS;
        } catch (\Throwable $e) {
            Log::channel('monitoring_sync')
                ->error("[MONITORING] Fallo al sincronizar las conexiones activas: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
