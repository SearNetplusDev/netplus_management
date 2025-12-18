<?php

namespace App\Services\v1\management\billing\background;

use App\Enums\v1\General\BillingStatus;
use App\Enums\v1\General\CommonStatus;
use App\Models\Billing\InvoiceModel;
use App\Models\Infrastructure\Network\AuthServerModel;
use App\Models\Services\ServiceModel;
use App\Services\v1\network\MikrotikInternetService;

class OverdueServiceCutService
{
    public function __construct(private MikrotikInternetService $mikrotikInternetService)
    {

    }

    /***
     * Procesa el corte de los servicios morosos
     * @return array
     */
    public function cutOverdueClients(): array
    {
        $invoices = InvoiceModel::query()
            ->with('client.active_services')
            ->where('billing_status_id', BillingStatus::OVERDUE->value)
            ->where('status_id', CommonStatus::ACTIVE->value)
            ->whereDoesntHave('extensions', function ($q) {
                $q->where('status_id', CommonStatus::ACTIVE->value)
                    ->whereDate('extended_due_date', '>=', now());
            })
            ->get();

        $results = [
            'total' => 0,
            'cut' => 0,
            'errors' => []
        ];

        foreach ($invoices->groupBy('client_id') as $clientId => $clientInvoices) {
            foreach ($clientInvoices->first()->client->active_services as $service) {
                try {
                    $this->disableService($service);
                    $results['cut']++;
                } catch (\Throwable $e) {
                    $results['errors'][] = "Cliente {$clientId}: {$e->getMessage()}";
                }
                $results['total']++;
            }
        }

        return $results;
    }

    /***
     * Desactiva el usuario a nivel de mikrotik
     * @param ServiceModel $service
     * @return void
     * @throws \RouterOS\Exceptions\ClientException
     * @throws \RouterOS\Exceptions\ConfigException
     */
    private function disableService(ServiceModel $service): void
    {
        $server = $this->getServerData($service->node_id)->toArray();
        $this->mikrotikInternetService->disableUser($server, $service->internet?->user);
    }

    /***
     * Obtiene los datos del servidor de autenticación
     * @param int $id
     * @return AuthServerModel
     */
    private function getServerData(int $id): AuthServerModel
    {
        return AuthServerModel::query()->findOrFail($id);
    }
}
