<?php

namespace App\Console\Commands;

use App\Enums\v1\General\BillingStatus;
use App\Enums\v1\General\CommonStatus;
use App\Models\Billing\InvoiceModel;
use App\Models\Billing\PrepaymentModel;
use App\Services\v1\management\billing\PrepaymentService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ApplyPrepayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:apply-prepayments
                                {--client= : ID del cliente}
                                {--dry-run : Simular sin aplicar cambios}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aplica los abonos disponibles a las facturas pendientes de los clientes.';

    public function __construct(private PrepaymentService $service)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Iniciando aplicación de abonos...');
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) $this->warn('MODO SIMULACIÓN - NO SE APLICARÁN CAMBIOS REALES.');

        //  Obtener clientes con abonos disponibles
        $clientsWithPrepayments = $this->getClientsWithAvailablePrepayments();

        if ($clientsWithPrepayments->isEmpty()) {
            $this->info('No hay clientes con abonos disponibles.');
            return self::SUCCESS;
        }

        $this->info("Procesando {$clientsWithPrepayments->count()} clientes...}");

        $stats = [
            'clients_processed' => 0,
            'total_invoices_paid' => 0,
            'total_amount_applied' => 0,
            'clients_with_errors' => 0,
        ];

        $progressBar = $this->output->createProgressBar($clientsWithPrepayments->count());
        $progressBar->start();

        foreach ($clientsWithPrepayments as $clientData) {
            try {
                if ($isDryRun) {
                    $result = $this->simulateApplication($clientData->client_id);
                } else {
                    $result = $this->service->applyPrepaymentsToInvoices($clientData->client_id);
                }

                $stats['clients_processed']++;
                $stats['total_invoices_paid'] += $result['invoices_paid'];
                $stats['total_amount_applied'] += $result['total_applied'];

                $this->logClientResult($clientData->client_id, $result, $isDryRun);
            } catch (\Throwable $e) {
                $stats['clients_with_errors']++;
                $this->error("\nError en cliente {$clientData->client_id}: {$e->getMessage()}");
                Log::error("Error aplicando abonos al cliente {$clientData->client_id}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        //  Mostrar resumen
        $this->displaySummary($stats, $isDryRun);

        return self::SUCCESS;
    }

    /***
     * Retorna los clientes con abonos disponibles
     * @return Collection
     */
    private function getClientsWithAvailablePrepayments(): Collection
    {
        $clientId = $this->option('client');
        $query = PrepaymentModel::query()
            ->select('client_id')
            ->selectRaw('SUM(remaining_amount)')
            ->where('status_id', CommonStatus::ACTIVE->value)
            ->where('remaining_amount', '>', 0)
            ->groupBy('client_id')
            ->havingRaw('SUM(remaining_amount) > 0');
//            ->having('total_remaining', '>', 0);

        if ($clientId) $query->where('client_id', $clientId);

        return $query->get();
    }

    /***
     * Simulacro de aplicación de abono
     * @param int $clientId
     * @return array
     */
    private function simulateApplication(int $clientId): array
    {
        //  Obtener abonos disponibles
        $totalPrepayment = PrepaymentModel::query()
            ->where([
                ['client_id', $clientId],
                ['status_id', CommonStatus::ACTIVE->value],
            ])
            ->where('remaining_amount', '>', 0)
            ->sum('remaining_amount');

        //  Obtener facturas pendientes
        $pendingInvoices = InvoiceModel::query()
            ->where('client_id', $clientId)
            ->where('balance_due', '>', 0)
            ->whereIn('billing_status_id', [
                BillingStatus::ISSUED->value,
                BillingStatus::PENDING->value,
                BillingStatus::OVERDUE->value,
                BillingStatus::PARTIALLY_PAID->value,
            ])
            ->orderByRaw('CASE WHEN billing_status_id = ? THEN 0 ELSE 1 END', [BillingStatus::OVERDUE->value])
            ->orderBy('billing_period_id', 'asc')
            ->get();

        $simulatedApplied = 0;
        $simulatedInvoicesPaid = 0;
        $remaining = $totalPrepayment;

        foreach ($pendingInvoices as $invoice) {
            if ($remaining <= 0) break;

            $toApply = min($remaining, $invoice->balance_due);
            $simulatedApplied += $toApply;
            $remaining -= $toApply;

            if ($toApply >= $invoice->balance_due) $simulatedInvoicesPaid++;
        }

        return [
            'invoices_paid' => $simulatedInvoicesPaid,
            'total_applied' => $simulatedApplied,
            'prepayments_used' => 0,
            'details' => [],
        ];
    }

    /***
     * Registra el log por cada abono
     * @param int $clientId
     * @param array $result
     * @param bool $isDryRun
     * @return void
     */
    private function logClientResult(int $clientId, array $result, bool $isDryRun): void
    {
        if ($result['total_applied'] > 0) {
            $mode = $isDryRun ? '[SIMULACIÓN]' : '[PRODUCCIÓN]';
            Log::info("{$mode} Abonos aplicados al cliente {$clientId}", [
                'invoices_paid' => $result['invoices_paid'],
                'total_applied' => $result['total_applied'],
                'prepayments_used' => $result['prepayments_used'],
            ]);
        }
    }

    /***
     * Muestra las metricas al ejecutarse el comando
     * @param array $stats
     * @param bool $isDryRun
     * @return void
     */
    private function displaySummary(array $stats, bool $isDryRun): void
    {
        $mode = $isDryRun ? 'SIMULACIÓN COMPLETADA' : 'PROCESO COMPLETADO';
        $this->info("=================================================================");
        $this->info("===    {$mode}");
        $this->info("=================================================================");
        $this->line("Clientes Procesados: {$stats['clients_processed']}");
        $this->line("Facturas Pagadas: {$stats['total_invoices_paid']}");
        $this->line("Monto Total Aplicado: $" . number_format($stats['total_amount_applied'], 2, '.', ','));

        if ($stats['clients_with_errors'] > 0) $this->error("Clientes con Errores: {$stats['clients_with_errors']}");
        $this->info("=================================================================");
    }
}
