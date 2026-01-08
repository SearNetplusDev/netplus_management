<?php

namespace App\Services\v1\management\billing;

use App\DTOs\v1\management\billing\payments\PaymentDTO;
use App\Enums\v1\General\BillingStatus;
use App\Enums\v1\General\CommonStatus;
use App\Models\Billing\InvoiceModel;
use App\Models\Billing\PaymentModel;
use App\Models\Infrastructure\Network\AuthServerModel;
use App\Models\Services\ServiceInternetModel;
use App\Services\v1\management\billing\background\ClientFinancialStatusService;
use App\Services\v1\network\MikrotikInternetService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function __construct(
        private MikrotikInternetService      $mikrotikInternetService,
        private ClientFinancialStatusService $clientFinancialStatusService
    )
    {
    }

    /***
     * Registra pago, activa navegación de ser necesario y actualiza estado financiero
     * @param PaymentDTO $dto
     * @return PaymentModel
     * @throws \Throwable
     */
    public function createPayment(PaymentDTO $dto): PaymentModel
    {
        return DB::transaction(function () use ($dto) {
            $oldestOverdueInvoice = $this->getOldestOverdueInvoice($dto->client_id);
            $invoiceId = $oldestOverdueInvoice ? $oldestOverdueInvoice->id : $dto->invoice_id;
            $payment = PaymentModel::query()
                ->create([
                    'invoice_id' => $invoiceId,
                    'client_id' => $dto->client_id,
                    'payment_method_id' => $dto->payment_method_id,
                    'amount' => $dto->amount,
                    'payment_date' => $dto->payment_date,
                    'reference_number' => $dto->reference_number,
                    'user_id' => $dto->user_id,
                    'comments' => $dto->comments,
                    'status_id' => $dto->status_id,
                ]);

            $invoice = InvoiceModel::query()
                ->with([
                    'client.active_services.internet.profile', 'client.active_services.node'
                ])
                ->findOrFail($invoiceId);

            $total = round($invoice->total_amount, 2);

            $invoice->update([
                'paid_amount' => $dto->amount,
                'balance_due' => $total - $dto->amount,
                'billing_status_id' => BillingStatus::PAID->value,
            ]);

            $pendingOrOverdueInvoices = $this->getPendingOrOverdueInvoices($dto->client_id);

            if ($pendingOrOverdueInvoices->isEmpty()) {
                $this->enableClientServices($invoice->client?->active_services);
            }

            $this->clientFinancialStatusService->updateClientFinancialStatus($dto->client_id);

            return $payment->fresh();
        });
    }

    /***
     * Obtiene la factura vencida más antigua
     * @param int $clientId
     * @return InvoiceModel|null
     */
    private function getOldestOverdueInvoice(int $clientId): ?InvoiceModel
    {
        return InvoiceModel::query()
            ->with('period')
            ->where([
                ['client_id', $clientId],
                ['status_id', CommonStatus::ACTIVE->value],
                ['billing_status_id', BillingStatus::OVERDUE->value],
                ['balance_due', '>', 0]
            ])
            ->orderBy('billing_period_id', 'asc')
            ->first();
    }

    /***
     * Retorna todas las facturas vencidas o pendientes de un cliente
     * @param int $clientId
     * @return Collection
     */
    private function getPendingOrOverdueInvoices(int $clientId): Collection
    {
        return InvoiceModel::query()
            ->where([
                ['client_id', $clientId],
                ['status_id', CommonStatus::ACTIVE->value],
            ])
            ->whereIn('billing_status_id', [BillingStatus::PENDING->value, BillingStatus::OVERDUE->value])
            ->where('balance_due', '>', 0)
            ->get();
    }

    /***
     * Activa todos los servicios del cliente en Mikrotik
     * @param $services
     * @return void
     */
    private function enableClientServices($services): void
    {
        foreach ($services as $service) {
            try {
                $server = $this->getServerData($service->node_id);
                $credentials = $this->getCredentials($service->id);
                if ($credentials && $credentials->profile) {
                    $this->mikrotikInternetService->updateUser(
                        $server->toArray(),
                        $credentials->user,
                        ['profile' => $credentials->profile->mk_profile]
                    );
                }
            } catch (\Throwable $e) {
                throw ValidationException::withMessages([
                    'service' => "Ha ocurrido un error: {$e->getMessage()}"
                ]);
            }
        }
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
     * @return ServiceInternetModel|null
     */
    private function getCredentials(int $serviceId): ?ServiceInternetModel
    {
        return ServiceInternetModel::query()
            ->with('profile')
            ->where('service_id', $serviceId)
            ->first();
    }
}
