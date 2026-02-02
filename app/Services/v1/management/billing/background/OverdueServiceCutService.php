<?php

namespace App\Services\v1\management\billing\background;

use App\Enums\v1\General\BillingStatus;
use App\Enums\v1\General\CommonStatus;
use App\Models\Billing\InvoiceModel;
use App\Models\Infrastructure\Network\AuthServerModel;
use App\Models\Services\ServiceInternetModel;
use App\Models\Services\ServiceModel;
use App\Services\v1\network\MikrotikInternetService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class OverdueServiceCutService
{
    public function __construct(private MikrotikInternetService $mikrotikInternetService)
    {

    }

    /***
     *  Procesa el corte de los servicios morosos
     * @param bool $dryRun
     * @return void
     */
    public function cutOverdueClients(bool $dryRun = false): void
    {
        Log::info('Iniciando corte de servicios morosos', [
            'dry_run' => $dryRun,
        ]);

        InvoiceModel::query()
            ->with('client.active_services')
            ->where('billing_status_id', BillingStatus::OVERDUE->value)
            ->where('status_id', CommonStatus::ACTIVE->value)
            ->whereDoesntHave('extensions', function ($q) {
                $q->where('status_id', CommonStatus::ACTIVE->value)
                    ->whereDate('extended_due_date', '>=', now());
            })
            ->chunkById(50, function ($invoices) use ($dryRun) {
                foreach ($invoices->groupBy('client_id') as $clientId => $clientInvoices) {
                    $services = $clientInvoices->first()->client->active_services ?? collect();

                    foreach ($services as $service) {
                        try {
                            if (!$dryRun) {
                                $this->disableService($service);
                            }
                        } catch (\Throwable $e) {
                            Log::error('Error cortando servicio por morosidad', [
                                'client_id' => $clientId,
                                'service_id' => $service->id,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                }
            });

        Log::info('Finalizando corte de servicios morosos');
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
        $credentials = $this->getCredentials($service->id);

        if (!$credentials) {
            throw new \RuntimeException("Credenciales no encontradas para servicio {$service->id}");
        }

        $this->mikrotikInternetService->updateUser($server, $credentials->user, [
            'profile' => $credentials->profile?->debt_profile
        ]);
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

    /***
     * Obtiene las credenciales y el perfil de cada servicio
     * @param int $serviceId
     * @return ServiceInternetModel
     */
    private function getCredentials(int $serviceId): ServiceInternetModel
    {
        return ServiceInternetModel::query()
            ->with('profile')
            ->where('service_id', $serviceId)
            ->first();
    }
}
