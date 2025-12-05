<?php

namespace App\Console\Commands;

use App\Services\v1\management\billing\InvoiceStatusService;
use Illuminate\Console\Command;

class UpdateInvoiceStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:update-status {--period=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza el estado de las facturas según fechas de período';

    /**
     * Execute the console command.
     */
    public function handle(InvoiceStatusService $service): int
    {
        $this->info('Inicializando actualización de estados de facturas');

        $periodId = $this->option('period');
        $result = $service->updateInvoiceStatuses($periodId);

        $this->info("Facturas actualizadas: {$result['updated']}");
        $this->info("Total procesadas: {$result['total']}");

        if (!empty($result['errors'])) {
            $this->error("Errores encontrados:");
            foreach ($result['errors'] as $error) {
                $this->error($error);
            }
        }

        return Command::SUCCESS;
    }
}
