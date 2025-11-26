<?php

namespace App\Services\v1\management\billing;

use App\Enums\v1\Billing\InvoiceType;
use App\Enums\v1\Clients\ClientTypes;
use App\Enums\v1\General\BillingStatus;
use App\Enums\v1\General\CommonStatus;
use App\Models\Billing\InvoiceModel;
use App\Models\Billing\PeriodModel;
use App\Models\Clients\ClientModel;
use App\Models\Services\ServiceModel;
use App\Models\Services\ServicePlanChangeModel;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BillingService
{
    /***
     * Genera las facturas de un período
     * @param PeriodModel $period
     * @param bool $allClients
     * @return array
     * @throws \Throwable
     */
    public function generateInvoicesForPeriod(PeriodModel $period, bool $allClients = false): array
    {
        $clients = $this->getBillableClients($period, $allClients);
        $results = [
            'generated' => 0,
            'errors' => [],
            'total_clients' => $clients->count(),
        ];

        foreach ($clients as $client) {
            try {
                DB::transaction(function () use ($client, $period, &$results) {
                    // Verificar si el cliente requiere facturación separada
                    $separateServices = $client->services->where('separate_billing', true);
                    $consolidatedServices = $client->services->where('separate_billing', false);

                    // Generar facturas separadas para servicios con facturación independiente
                    foreach ($separateServices as $service) {
                        if ($service->status_id != CommonStatus::ACTIVE->value) continue;

                        if ($this->invoiceExistsForService($client, $period, $service)) continue;

                        $invoiceData = $this->calculateInvoiceDataForService($client, $period, $service);

                        if ($invoiceData['total_amount'] > 0) {
                            $this->createInvoice($client, $period, $invoiceData, InvoiceType::INDIVIDUAL->value);
                            $results['generated']++;
                        }
                    }

                    // Generar factura consolidada para servicios que lo requieran
                    if ($consolidatedServices->isNotEmpty()) {
                        if (!$this->invoiceExists($client, $period)) {
                            $invoiceData = $this->calculateInvoiceData($client, $period, $consolidatedServices);

                            if ($invoiceData['total_amount'] > 0) {
                                $this->createInvoice($client, $period, $invoiceData, InvoiceType::CONSOLIDATED->value);
                                $results['generated']++;
                            }
                        }
                    }
                });
            } catch (\Exception $e) {
                $results['errors'][] = "Cliente {$client->id}: {$e->getMessage()}";
            }
        }

        return $results;
    }

    /***
     * Obteniendo clientes a los que se le puede generar factura
     * @param PeriodModel $period
     * @param bool $allClients
     * @return Collection
     */
    private function getBillableClients(PeriodModel $period, bool $allClients = false): Collection
    {
        $query = ClientModel::query()
            ->with([
                'services.internet.profile',
                'client_type',
                'corporate_info'
            ])
            ->where([
                ['status_id', CommonStatus::ACTIVE->value],
                ['client_type_id', '!=', ClientTypes::FREE->value]
            ]);

        if (!$allClients) {
            $query->whereHas('services', function ($q) use ($period) {
                $q->where('status_id', CommonStatus::ACTIVE->value)
                    ->where(function ($q) use ($period) {
                        $q->where('installation_date', '<=', $period->period_end)
                            ->orWhereNull('installation_date');
                    });
            });
        }

        return $query->get();
    }

    /***
     * Verificando que exista una factura para el cliente y el período
     * @param ClientModel $client
     * @param PeriodModel $period
     * @return bool
     */
    private function invoiceExists(ClientModel $client, PeriodModel $period): bool
    {
        return InvoiceModel::query()
            ->where([
                ['client_id', $client->id],
                ['billing_period_id', $period->id],
                ['invoice_type', 2] // Consolidada
            ])
            ->exists();
    }

    /***
     * Verificando que exista una factura para un servicio específico
     * @param ClientModel $client
     * @param PeriodModel $period
     * @param ServiceModel $service
     * @return bool
     */
    private function invoiceExistsForService(ClientModel $client, PeriodModel $period, ServiceModel $service): bool
    {
        return InvoiceModel::query()
            ->where([
                ['client_id', $client->id],
                ['billing_period_id', $period->id],
                ['invoice_type', InvoiceType::INDIVIDUAL->value]
            ])
            ->exists();
    }

    /***
     * Calcula los datos de factura para un servicio específico
     * @param ClientModel $client
     * @param PeriodModel $period
     * @param ServiceModel $service
     * @return array
     */
    private function calculateInvoiceDataForService(
        ClientModel  $client,
        PeriodModel  $period,
        ServiceModel $service
    ): array
    {
        $items = [];
        $serviceAmount = $this->calculateServiceAmount($service, $period);

        if ($serviceAmount > 0) {
            $profile = $service->internet->profile ?? null;
            $netValue = $profile ? (float)$profile->net_value : 0;
            $iva = $this->calculateIvaFromNetValue($netValue, $serviceAmount);

            $items[] = [
                'service' => $service,
                'amount' => $serviceAmount,
                'net_value' => $netValue,
                'iva' => $iva,
                'description' => $this->getServiceDescription($service, $period),
            ];
        }

        $subtotal = collect($items)->sum('amount');
        $totalIva = collect($items)->sum('iva');
        $ivaRetenido = $this->calculateIvaRetenido($client, $subtotal);

        return [
            'total_amount' => $subtotal,
            'total_iva' => $totalIva,
            'iva_retenido' => $ivaRetenido,
            'items' => $items,
        ];
    }

    /***
     * Obtiene los items que se muestran en la factura
     * @param ClientModel $client
     * @param PeriodModel $period
     * @param Collection|null $services
     * @return array
     */
    private function calculateInvoiceData(ClientModel $client, PeriodModel $period, ?Collection $services = null): array
    {
        $services = $services ?? $client->services;
        $items = [];

        foreach ($services as $service) {
            if ($service->status_id != CommonStatus::ACTIVE->value) continue;

            $serviceAmount = $this->calculateServiceAmount($service, $period);

            if ($serviceAmount > 0) {
                $profile = $service->internet->profile ?? null;
                $netValue = $profile ? (float)$profile->net_value : 0;
                $iva = $this->calculateIvaFromNetValue($netValue, $serviceAmount);

                $items[] = [
                    'service' => $service,
                    'amount' => $serviceAmount,
                    'net_value' => $netValue,
                    'iva' => $iva,
                    'description' => $this->getServiceDescription($service, $period),
                ];
            }
        }

        $subtotal = collect($items)->sum('amount');
        $totalIva = collect($items)->sum('iva');
        $ivaRetenido = $this->calculateIvaRetenido($client, $subtotal);

        return [
            'total_amount' => $subtotal,
            'total_iva' => $totalIva,
            'iva_retenido' => $ivaRetenido,
            'items' => $items,
        ];
    }

    /***
     * Calcula el IVA retenido para clientes corporativos
     * @param ClientModel $client
     * @param float $subtotal
     * @return float
     */
    private function calculateIvaRetenido(ClientModel $client, float $subtotal): float
    {
        // Verificar si es cliente corporativo
        if ($client->client_type_id != ClientTypes::CORPORATE->value) {
            return 0;
        }

        // Verificar si tiene información financiera y retained_iva es true
        $corporateInfo = $client->corporate_info;
        if (!$corporateInfo || !$corporateInfo->retained_iva) {
            return 0;
        }

        // Calcular 1% del subtotal
        return round($subtotal * 0.01, 8);
    }

    /***
     * Calcula el IVA basado en el net_value del perfil
     * @param float $netValue
     * @param float $serviceAmount
     * @return float
     */
    private function calculateIvaFromNetValue(float $netValue, float $serviceAmount): float
    {
        if ($netValue <= 0) {
            return 0;
        }

        // Calcular el porcentaje de IVA basado en net_value
        // IVA = (serviceAmount * 13%) ajustado proporcionalmente
        return round($serviceAmount * 0.13, 8);
    }

    /***
     * Calcula el total de las facturas
     * @param ServiceModel $service
     * @param PeriodModel $period
     * @return float
     */
    public function calculateServiceAmount(ServiceModel $service, PeriodModel $period): float
    {
        $profile = $service->internet->profile ?? null;
        if (!$profile || $profile->price <= 0) return 0;

        $monthlyPrice = (float)$profile->net_value;
        $periodStart = Carbon::parse($period->period_start);
        $periodEnd = Carbon::parse($period->period_end);

        //  Días de consumo por instalación
        if ($service->installation_date) {
            $installationDate = Carbon::parse($service->installation_date);

            if ($installationDate->between($periodStart, $periodEnd)) {
                return $this->calculateProportionalAmount($installationDate, $periodEnd, $monthlyPrice);
            }
        }

        //  Días de consumo por desinstalación
        if ($service->deleted_at || $service->status_id == CommonStatus::INACTIVE->value) {
            $uninstallationDate = Carbon::parse($service->updated_at);

            if ($uninstallationDate->between($periodStart, $periodEnd)) {
                return $this->calculateProportionalAmount($periodStart, $uninstallationDate, $monthlyPrice);
            }

            if ($uninstallationDate->lt($periodStart)) return 0;
        }

        //  Cambio de perfil durante el período
        $planChanges = ServicePlanChangeModel::query()
            ->where('service_id', $service->id)
            ->whereBetween('change_date', [$periodStart, $periodEnd])
            ->orderBy('change_date')
            ->get();
        if ($planChanges->isNotEmpty()) {
            return $this->calculateAmountWithPlanChanges($service, $period, $planChanges, $monthlyPrice);
        }
        return $monthlyPrice;
    }

    /***
     * Calcula días de consumo
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param float $monthlyPrice
     * @return float
     */
    public function calculateProportionalAmount(Carbon $startDate, Carbon $endDate, float $monthlyPrice): float
    {
        $daysInMonth = $startDate->copy()->startOfMonth()->daysInMonth;
        $daysActive = $startDate->diffInDays($endDate) + 1;
        $dailyRate = $monthlyPrice / $daysInMonth;

        return round($dailyRate * $daysActive, 8);
    }

    /***
     * Calcula días de consumo cuando hay renovaciones
     * @param ServiceModel $service
     * @param PeriodModel $period
     * @param Collection $planChanges
     * @param float $currentPrice
     * @return float
     */
    private function calculateAmountWithPlanChanges(
        ServiceModel $service,
        PeriodModel  $period,
        Collection   $planChanges,
        float        $currentPrice
    ): float
    {
        $totalAmount = 0;
        $periodStart = Carbon::parse($period->period_start);
        $periodEnd = Carbon::parse($period->period_end);
        $currentDate = $periodStart->copy();
        $currentProfilePrice = $currentPrice;

        foreach ($planChanges as $change) {
            $changeDate = Carbon::parse($change->change_date);

            //  Calculando días con el perfil anterior
            if ($currentDate->lt($changeDate)) {
                $daysAmount = $this->calculateProportionalAmount($currentDate, $changeDate->copy()->subDay(), $currentProfilePrice);
                $totalAmount += $daysAmount;
            }
            $currentDate = $changeDate;
            $currentProfilePrice = (float)($change->new_internet_profile->price ?? 0);
        }

        //  Calculando días restantes
        if ($currentDate->lt($periodEnd)) {
            $daysAmount = $this->calculateProportionalAmount($currentDate, $periodEnd, $currentProfilePrice);
            $totalAmount += $daysAmount;
        }
        return $totalAmount;
    }

    /***
     * Obtiene la descripción de cada ítem de la factura.
     * @param ServiceModel $service
     * @param PeriodModel $period
     * @return string
     */
    private function getServiceDescription(ServiceModel $service, PeriodModel $period): string
    {
        $profile = $service->internet->profile ?? null;
        $profileName = $profile ? $profile->name : 'Servicio de internet';
        $description = "{$profileName}";

        //  Agregando información de los días de consumo cuando aplique
        if ($service->installation_date) {
            $installationDate = Carbon::parse($service->installation_date);
            $periodStart = Carbon::parse($period->period_start);

            if ($installationDate->gt($periodStart)) {
                $days = $installationDate->diffInDays($period->period_end) + 1;
                $description .= " ({$days} días)";
            }
        }
        return $description;
    }

    private function createInvoice(ClientModel $client, PeriodModel $period, array $invoiceData, int $invoiceType): InvoiceModel
    {
        $invoice = InvoiceModel::query()
            ->create([
                'client_id' => $client->id,
                'billing_period_id' => $period->id,
                'invoice_type' => $invoiceType,
                'subtotal' => $invoiceData['total_amount'],
                'iva' => $invoiceData['total_iva'],
                'iva_retenido' => $invoiceData['iva_retenido'],
                'total_amount' => $invoiceData['total_amount'] + $invoiceData['total_iva'] - $invoiceData['iva_retenido'],
                'paid_amount' => 0,
                'balance_due' => $invoiceData['total_amount'] + $invoiceData['total_iva'] - $invoiceData['iva_retenido'],
                'billing_status_id' => BillingStatus::ISSUED->value,
                'comments' => "Factura generada para el período {$period->name}"
            ]);

        foreach ($invoiceData['items'] as $item) {
            $invoice->items()->create([
                'invoice_id' => $invoice->id,
                'service_id' => $item['service']->id,
                'description' => $item['description'],
                'quantity' => 1,
                'unit_price' => $item['amount'],
                'subtotal' => $item['amount'],
                'iva' => $item['iva'],
                'iva_retenido' => 0,
                'total' => $item['amount'] + $item['iva'],
                'status_id' => CommonStatus::ACTIVE->value,
            ]);
        }

        return $invoice;
    }

    /***
     * Calcula el IVA
     * @param float $amount
     * @return float
     */
    private function calculateIva(float $amount): float
    {
        return round($amount * 0.13, 8);
    }

    /***
     * Estadísticas de facturación
     * @param PeriodModel $period
     * @return array
     */
    public function getBillingStatistics(PeriodModel $period): array
    {
        $invoices = InvoiceModel::query()
            ->where('billing_period_id', $period->id)
            ->get();

        return [
            'total_invoices' => $invoices->count(),
            'total_amount' => $invoices->sum('total_amount'),
            'pending_amount' => $invoices->sum('balance_due'),
            'paid_amount' => $invoices->sum('paid_amount'),
        ];
    }
}
