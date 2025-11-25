<?php

namespace App\Console\Commands;

use App\Models\Billing\PeriodModel;
use App\Services\v1\management\billing\BillingService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:generate-invoices {period? : Period code (YYYYMM)} {--all-clients : Generar para todos los clientes} {--stats : Mostrar estadísticas}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generar facturas para un período determinado';

    /***
     * Execute the console command.
     * @param BillingService $billingService
     * @return void
     * @throws \Throwable
     */
    public function handle(BillingService $billingService): void
    {
        $periodCode = $this->argument('period') ?? Carbon::now()->format('Ym');
        $period = PeriodModel::query()->where('code', $periodCode)->first();

        if (!$period) {
            $this->error("Período {$periodCode} no encontrado.");
            return;
        }

        if ($period->is_closed) {
            $this->error("El período {$periodCode} está cerrado.");
            return;
        }

        $this->info("Generando facturas para {$period->name}");
        $results = $billingService->generateInvoicesForPeriod($period, $this->option('all-clients'));
        $this->showResults($results, $period);

        if ($this->option('stats')) {
            $this->showStatistics($billingService, $period);
        }
    }

    private function showResults(array $results, PeriodModel $period): void
    {
        $this->info("Procesados: {$results['total_clients']} clientes");
        $this->info("Facturas generadas: {$results['generated']}");

        if (!empty($results['errors'])) {
            $this->error("Errores encontrados:");
            foreach ($results['errors'] as $error) {
                $this->error(" - {$error}");
            }
        }

        if ($results['generated'] > 0) {
            $this->info("Facturas generadas exitosamente para {$period->name}");
        } else {
            $this->info("No se generaron nuevas facturas para {$period->name}");
        }
    }

    private function showStatistics(BillingService $billingService, PeriodModel $period): void
    {
        $stats = $billingService->getBillingStatistics($period);
        $this->info("\nEstadísticas de Facturación:");
        $this->line("Facturas totales: {$stats['total_invoices']}");
        $this->line("Monto total: $" . number_format($stats['total_amount'], 8));
        $this->line("Monto pendiente: $" . number_format($stats['pending_amount'], 8));
        $this->line("Monto pagado: $" . number_format($stats['paid_amount'], 8));
    }
}
