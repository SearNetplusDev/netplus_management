<?php

namespace App\Jobs\monitoring;

use App\Models\Infrastructure\Network\AuthServerModel;
use App\Services\v1\monitoring\MikrotikConnectionSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncConnectionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;
    public int $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(private readonly int $authServerId)
    {
        //
    }

    /**
     * Evita que se acumulen jobs si el anterior sigue corriendo.
     *
     * @return array
     */
    public function middleware(): array
    {
        return [new WithoutOverlapping('mikrotik-sync-connections')->releaseAfter(30)];
    }

    /**
     * Execute the job.
     *
     * @param MikrotikConnectionSyncService $service
     * @return void
     * @throws Throwable
     * @throws \RouterOS\Exceptions\ClientException
     * @throws \RouterOS\Exceptions\ConfigException
     */
    public function handle(MikrotikConnectionSyncService $service): void
    {
        $authServer = AuthServerModel::query()->findOrFail($this->authServerId);
        $synced = $service->sync(
            host: $authServer->ip,
            user: $authServer->user,
            pass: $authServer->secret,
            port: (int)$authServer->port
        );
        Log::channel('monitoring_sync')
            ->info("[SUCCESS] Sincronización completada. Conexiones activas: " . count($synced));
    }

    public function failed(Throwable $e): void
    {
        Log::channel('monitoring_sync')
            ->error("[FAILED] Fallo al sincronizar las conexiones activas: " . $e->getMessage());
    }
}
