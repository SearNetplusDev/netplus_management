<?php

namespace App\Services\v1\management\billing\background;

use App\Enums\v1\General\BillingStatus;
use App\Enums\v1\General\CommonStatus;
use App\Models\Billing\InvoiceModel;
use App\Models\Infrastructure\Network\AuthServerModel;
use App\Services\v1\network\MikrotikInternetService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

class OverdueServiceCutService
{
    /**
     * Procesa el corte de los servicios morosos.
     * Agrupa los servicios a cortar por servidor de autenticación para abrir una única conexión Mikrotik
     * por servidor en lugar de por servicio.
     *
     * @param bool $dryRun
     * @return void
     */
    public function cutOverdueClients(bool $dryRun = false): void
    {
        Log::channel('cuts')->info('Iniciando corte de servicios morosos', [
            'dryRun' => $dryRun,
        ]);

        $servicesByNode = [];

        InvoiceModel::query()
            ->with('client.active_services.internet.profile')
            ->where('billing_status_id', BillingStatus::OVERDUE->value)
            ->where('status_id', CommonStatus::ACTIVE->value)
            ->whereDoesntHave('extensions', function ($q) {
                $q->where('status_id', CommonStatus::ACTIVE->value)
                    ->whereDate('extended_due_date', '>=', Carbon::now());
            })
            ->chunkById(100, function ($invoices) use (&$servicesByNode) {
                foreach ($invoices->groupBy('client_id') as $clientId => $clientInvoices) {
                    $services = $clientInvoices->first()->client->active_services ?? collect();

                    foreach ($services as $service) {
                        $credentials = $service->internet;

                        if (!$credentials) {
                            Log::channel('cuts')->error("Credenciales no encontradas para el servicio {$service->id}", [
                                'client_id' => $clientId,
                            ]);
                            continue;
                        }

                        /**
                         * Indexando por service_id: si el mismo cliente cae en más de
                         * un chunk (varias facturas vencidas), el servicio no se duplica.
                         */

                        $servicesByNode[$service->node_id][$service->id] = [
                            'client_id' => $clientId,
                            'user' => $credentials->user,
                            'debt_profile' => $credentials->profile?->debt_profile,
                        ];
                    }
                }
            });

        $totalServices = array_sum(array_map('count', $servicesByNode));

        if ($dryRun) {
            Log::channel('cuts')->info("Dry-run: se cortarían los servicios", [
                'total_services' => $totalServices,
                'total_nodos' => count($servicesByNode),
            ]);
            return;
        }

        foreach ($servicesByNode as $nodeId => $services) {
            $this->cutServicesOnNode(nodeId: (int)$nodeId, services: $services);
        }

        Log::channel('cuts')->info("Finalizando corte de servicios morosos", [
            'total_servicios' => $totalServices,
            'total_nodos' => count($servicesByNode),
        ]);
    }

    /**
     * Corta todos los servicios de un mismo servidor Mikrotik en una sola conexión
     *
     * @param int $nodeId
     * @param array $services
     * @return void
     */
    private function cutServicesOnNode(int $nodeId, array $services): void
    {
        try {
            $server = AuthServerModel::query()->findOrFail($nodeId)->toArray();
        } catch (Throwable $e) {
            Log::channel('cuts')->error("No se pudo obtener el servidor Mikrotik para cortar servicios.", [
                'node_id' => $nodeId,
                'servicios' => array_keys($services),
                'error' => $e->getMessage(),
            ]);
            return;
        }

        $updates = [];

        foreach ($services as $serviceId => $data) {
            if (!$data['user']) {
                Log::channel('cuts')->error("Usuario PPPoE vacío para servicio {$serviceId}, se omite", [
                    'client_id' => $data['client_id'],
                    'node_id' => $nodeId,
                ]);
                continue;
            }

            $updates[] = [
                'name' => $data['user'],
                'data' => ['profile' => $data['debt_profile']],
            ];
        }

        if (empty($updates)) {
            return;
        }

        try {
            $mikrotikInternetService = app(MikrotikInternetService::class);
            $results = $mikrotikInternetService->updateMultipleUsers(server: $server, updates: $updates);

            foreach ($results as $username => $result) {
                if (!($result['ok'] ?? false)) {
                    Log::channel('cuts')->error("Error cortando servicio por morosidad", [
                        'node_id' => $nodeId,
                        'pppoe_user' => $username,
                        'error' => $result['error'] ?? 'desconocido',
                    ]);
                }
            }
        } catch (Throwable $e) {
            Log::channel('cuts')->error("Error al conectar con el nodo Mikrotik para el corte masivo", [
                'node_id' => $nodeId,
                'servicios' => array_keys($services),
                'error' => $e->getMessage(),
            ]);
        }
    }

}
