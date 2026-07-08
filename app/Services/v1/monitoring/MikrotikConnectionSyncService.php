<?php

namespace App\Services\v1\monitoring;

use App\Enums\v1\General\CommonStatus;
use App\Libraries\MikrotikAPI;
use App\Models\Monitoring\ActiveConnectionModel;
use App\Models\Services\ServiceInternetModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

readonly class MikrotikConnectionSyncService
{
    public function __construct(private readonly MikrotikAPI $api)
    {
    }

    /**
     * @param string $host
     * @param string $user
     * @param string $pass
     * @param int $port
     * @return array
     * @throws \RouterOS\Exceptions\ClientException
     * @throws \RouterOS\Exceptions\ConfigException
     * @throws \Throwable
     */
    public function sync(string $host, string $user, string $pass, int $port = 45000): array
    {
        $knownUsers = ServiceInternetModel::query()
            ->where('status_id', CommonStatus::ACTIVE)
            ->pluck('user')
            ->toArray();
        $syncedUsers = [];

        DB::transaction(function () use ($host, $user, $pass, $port, $knownUsers, &$syncedUsers) {
            foreach ($knownUsers as $pppoeUser) {
                $result = $this->api->executeQuery(
                    host: $host,
                    user: $user,
                    pass: $pass,
                    endpoint: '/ppp/active/print',
                    where: ['name' => $pppoeUser],
                    port: $port
                );
                $connection = $result[0] ?? null;

                if ($connection) {
                    ActiveConnectionModel::query()
                        ->updateOrCreate(
                            ['pppoe_user' => $pppoeUser],
                            [
                                'internet_service_id' => ServiceInternetModel::query()->where('user', $pppoeUser)->first()?->id,
                                'ip_address' => $connection['address'] ?? null,
                                'caller_id' => $connection['caller-id'] ?? null,
                                'uptime' => $connection['uptime'] ?? null,
                                'uptime_seconds' => isset($connection['uptime']) ? $this->parseUptimeToSeconds($connection['uptime']) : null,
                                'mikrotik_ref_id' => $connection['.id'] ?? null,
                                'last_synced_at' => now(),
                            ]
                        );
                    $syncedUsers[] = $pppoeUser;
                } else {
                    ActiveConnectionModel::query()->where('pppoe_user', $pppoeUser)->delete();
                }
            }
        });

        return $syncedUsers;


//        $connections = $this->api->getActivePPPConnections(host: $host, user: $user, pass: $pass, port: $port);
//        Log::channel('monitoring_sync')->info("[MONITORING] Respuesta de /ppp/active/print", [
//            'count' => count($connections),
//            'sample' => $connections[0] ?? null,
//        ]);
//        $syncedUsers = [];
//
//        DB::transaction(function () use ($connections, &$syncedUsers) {
//            foreach ($connections as $connection) {
//                $pppoeUser = $connection['name'] ?? null;
//
//                if (!$pppoeUser) continue;
//
//                $internetService = ServiceInternetModel::query()->where('user', $pppoeUser)->first();
//                $uptime = $connection['uptime'] ?? null;
//
//                ActiveConnectionModel::query()
//                    ->updateOrCreate(
//                        ['pppoe_user' => $pppoeUser],
//                        [
//                            'internet_service_id' => $internetService?->id,
//                            'ip_address' => $connection['address'] ?? null,
//                            'caller_id' => $connection['caller-id'] ?? null,
//                            'uptime' => $uptime,
//                            'uptime_seconds' => $uptime ? $this->parseUptimeToSeconds($uptime) : null,
//                            'mikrotik_ref_id' => $connection['.id'] ?? null,
//                            'last_synced_at' => now(),
//                        ]
//                    );
//                $syncedUsers[] = $pppoeUser;
//            }
//
//            //  Elimina las conexiones que ya no están activas en el AUTH SERVER.
//            ActiveConnectionModel::query()
//                ->whereNotIn('pppoe_user', $syncedUsers)
//                ->delete();
//        });
//
//        return $syncedUsers;
    }

    /**
     * Formatea el uptime retornado por RouterOS a segundos.
     *
     * @param string $uptime
     * @return int
     */
    private function parseUptimeToSeconds(string $uptime): int
    {
        // RouterOS formatea así: "1w2d3h4m5s", "4h5m6s", "5m6s", "6s"
        preg_match_all('/(\d+)([wdhms])/', $uptime, $matches, PREG_SET_ORDER);

        $seconds = 0;
        foreach ($matches as [, $value, $unit]) {
            $seconds += match ($unit) {
                'w' => $value * 604800,
                'd' => $value * 86400,
                'h' => $value * 3600,
                'm' => $value * 60,
                's' => (int)$value,
            };
        }

        return $seconds;
    }
}
