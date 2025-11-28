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
                    $separateServices = $client->services->where('separate_billing', true);
                    $consolidatedServices = $client->services->where('separate_billing', false);

                    foreach ($separateServices as $service) {
                        if ($service->status_id != CommonStatus::ACTIVE->value) continue;

                        if ($this->invoiceExistsForService($client, $period, $service)) continue;

                        $invoiceData = $this->calculateInvoiceDataForService($client, $period, $service);

                        if ($invoiceData['total_amount'] > 0) {
                            $this->createInvoice($client, $period, $invoiceData, InvoiceType::INDIVIDUAL->value);
                            $results['generated']++;
                        }
                    }

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
                ['invoice_type', InvoiceType::CONSOLIDATED->value]
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
            ->whereHas('items', function ($q) use ($service) {
                $q->where('service_id', $service->id);
            })
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
        $items = $this->getServiceItems($service, $period);

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

            $serviceItems = $this->getServiceItems($service, $period);
            $items = array_merge($items, $serviceItems);
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
     * Obtiene los ítems de facturación para un servicio
     * Genera múltiples ítems si hay cambios de plan
     * @param ServiceModel $service
     * @param PeriodModel $period
     * @return array
     */
    private function getServiceItems(ServiceModel $service, PeriodModel $period): array
    {
        $periodStart = Carbon::parse($period->period_start);
        $periodEnd = Carbon::parse($period->period_end);

        // Verificar cambios de plan
        $planChanges = $this->checkPlanChanges($service->id, $periodStart, $periodEnd);

        if ($planChanges->isNotEmpty()) {
            return $this->getItemsWithPlanChanges($service, $period, $planChanges);
        }

        // Sin cambios de plan, generar un solo ítem
        $serviceAmount = $this->calculateServiceAmount($service, $period);

        if ($serviceAmount <= 0) {
            return [];
        }

        $profile = $service->internet->profile ?? null;
        $netValue = $profile ? (float)$profile->net_value : 0;
        $iva = $this->calculateIvaFromNetValue($netValue, $serviceAmount);

        return [[
            'service' => $service,
            'amount' => $serviceAmount,
            'net_value' => $netValue,
            'iva' => $iva,
            'description' => $this->getServiceDescription($service, $period),
        ]];
    }

    /***
     * Genera múltiples ítems cuando hay cambios de plan
     * @param ServiceModel $service
     * @param PeriodModel $period
     * @param Collection $planChanges
     * @return array
     */
    private function getItemsWithPlanChanges(
        ServiceModel $service,
        PeriodModel  $period,
        Collection   $planChanges
    ): array
    {
        $items = [];
        $periodStart = Carbon::parse($period->period_start);
        $periodEnd = Carbon::parse($period->period_end);
        $currentDate = $periodStart->copy();

        // Obtener el perfil inicial
        $firstChange = $planChanges->first();
        $currentProfile = $firstChange->old_internet_profile ?? $service->internet->profile;

        foreach ($planChanges as $change) {
            $changeDate = Carbon::parse($change->change_date);

            // Calcular días con el perfil actual hasta el día anterior al cambio
            if ($currentDate->lt($changeDate)) {
                $endDate = $changeDate->copy()->subDay();
                if ($endDate->gt($periodEnd)) {
                    $endDate = $periodEnd;
                }

                if ($currentDate->lte($endDate)) {
                    $profilePrice = $currentProfile ? (float)$currentProfile->net_value : 0;
                    $amount = $this->calculateProportionalAmount($currentDate, $endDate, $profilePrice);
                    $days = $currentDate->diffInDays($endDate) + 1;

                    if ($amount > 0) {
                        $iva = $this->calculateIvaFromNetValue($profilePrice, $amount);

                        $items[] = [
                            'service' => $service,
                            'amount' => $amount,
                            'net_value' => $profilePrice,
                            'iva' => $iva,
                            'description' => $this->getPlanChangeDescription(
                                $currentProfile,
                                $currentDate,
                                $endDate,
                                $days
                            ),
                        ];
                    }
                }
            }

            // Actualizar al nuevo perfil
            $currentDate = $changeDate;
            $currentProfile = $change->new_internet_profile;
        }

        // Calcular los días restantes después del último cambio
        if ($currentDate->lte($periodEnd)) {
            $profilePrice = $currentProfile ? (float)$currentProfile->net_value : 0;
            $amount = $this->calculateProportionalAmount($currentDate, $periodEnd, $profilePrice);
            $days = $currentDate->diffInDays($periodEnd) + 1;

            if ($amount > 0) {
                $iva = $this->calculateIvaFromNetValue($profilePrice, $amount);

                $items[] = [
                    'service' => $service,
                    'amount' => $amount,
                    'net_value' => $profilePrice,
                    'iva' => $iva,
                    'description' => $this->getPlanChangeDescription(
                        $currentProfile,
                        $currentDate,
                        $periodEnd,
                        $days
                    ),
                ];
            }
        }

        return $items;
    }

    /***
     * Genera la descripción para un ítem con cambio de plan
     * @param $profile
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param int $days
     * @return string
     */
    private function getPlanChangeDescription($profile, Carbon $startDate, Carbon $endDate, int $days): string
    {
        $profileName = $profile ? $profile->name : 'Servicio de internet';
        $startFormatted = $startDate->format('d/m/Y');
        $endFormatted = $endDate->format('d/m/Y');

        return "{$profileName} ({$days} días - del {$startFormatted} al {$endFormatted})";
    }

    /***
     * Calcula el IVA retenido para clientes corporativos
     * @param ClientModel $client
     * @param float $subtotal
     * @return float
     */
    private function calculateIvaRetenido(ClientModel $client, float $subtotal): float
    {
        if ($client->client_type_id != ClientTypes::CORPORATE->value) {
            return 0;
        }

        $corporateInfo = $client->corporate_info;
        if (!$corporateInfo || !$corporateInfo->retained_iva) {
            return 0;
        }

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
        if ($netValue <= 0) return 0;

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

        // Días de consumo por instalación
        if ($service->installation_date) {
            $installationDate = Carbon::parse($service->installation_date);

            if ($installationDate->between($periodStart, $periodEnd)) {
                return $this->calculateProportionalAmount($installationDate, $periodEnd, $monthlyPrice);
            }
        }

        // Días de consumo por desinstalación
        if ($service->deleted_at || $service->status_id == CommonStatus::INACTIVE->value) {
            $uninstallationDate = Carbon::parse($service->updated_at);

            if ($uninstallationDate->between($periodStart, $periodEnd)) {
                return $this->calculateProportionalAmount($periodStart, $uninstallationDate, $monthlyPrice);
            }

            if ($uninstallationDate->lt($periodStart)) return 0;
        }

        // Cambio de perfil durante el período
        $planChanges = $this->checkPlanChanges($service->id, $periodStart, $periodEnd);
        if ($planChanges->isNotEmpty()) {
            return $this->calculateAmountWithPlanChanges($service, $period, $planChanges);
        }

        return $monthlyPrice;
    }

    /***
     * Verifica si un servicio ha tenido renovaciones en un período
     * @param int $serviceId
     * @param Carbon $start
     * @param Carbon $end
     * @return Collection
     */
    private function checkPlanChanges(int $serviceId, Carbon $start, Carbon $end): Collection
    {
        return ServicePlanChangeModel::query()
            ->with(['old_internet_profile', 'new_internet_profile'])
            ->where('service_id', $serviceId)
            ->whereBetween('change_date', [$start, $end])
            ->orderBy('change_date')
            ->get();
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
     * @return float
     */
    private function calculateAmountWithPlanChanges(
        ServiceModel $service,
        PeriodModel  $period,
        Collection   $planChanges,
    ): float
    {
        $totalAmount = 0;
        $periodStart = Carbon::parse($period->period_start);
        $periodEnd = Carbon::parse($period->period_end);
        $currentDate = $periodStart->copy();

        $firstChange = $planChanges->first();
        $initialProfile = $firstChange->old_internet_profile ?? $service->internet->profile;
        $currentProfilePrice = $initialProfile ? (float)$initialProfile->net_value : 0;

        foreach ($planChanges as $change) {
            $changeDate = Carbon::parse($change->change_date);

            if ($currentDate->lt($changeDate)) {
                $endDate = $changeDate->copy()->subDay();
                if ($endDate->gt($periodEnd)) {
                    $endDate = $periodEnd;
                }

                if ($currentDate->lte($endDate)) {
                    $daysAmount = $this->calculateProportionalAmount($currentDate, $endDate, $currentProfilePrice);
                    $totalAmount += $daysAmount;
                }
            }

            $currentDate = $changeDate;
            $currentProfilePrice = $change->new_internet_profile ? (float)$change->new_internet_profile->net_value : 0;
        }

        if ($currentDate->lte($periodEnd)) {
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

        $periodStart = Carbon::parse($period->period_start);
        $periodEnd = Carbon::parse($period->period_end);

        // Instalación durante el período
        if ($service->installation_date) {
            $installationDate = Carbon::parse($service->installation_date);
            if ($installationDate->gt($periodStart)) {
                $days = $installationDate->diffInDays($periodEnd) + 1;
                $description .= " ({$days} días de consumo)";
                return $description;
            }
        }

        return $description;
    }

    private function createInvoice(
        ClientModel $client,
        PeriodModel $period,
        array       $invoiceData,
        int         $invoiceType
    ): InvoiceModel
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
            $iva_retenido = $this->calculateIvaRetenido($client, $item['amount']);

            $invoice->items()->create([
                'invoice_id' => $invoice->id,
                'service_id' => $item['service']->id,
                'description' => $item['description'],
                'quantity' => 1,
                'unit_price' => $item['amount'],
                'subtotal' => $item['amount'],
                'iva' => $item['iva'],
                'iva_retenido' => $iva_retenido,
                'total' => $item['amount'] + $item['iva'] - $iva_retenido,
                'status_id' => CommonStatus::ACTIVE->value,
            ]);
        }

        return $invoice;
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
