<?php

namespace App\Console\Commands;

use App\Services\v1\management\billing\background\OverdueServiceCutService;
use Illuminate\Console\Command;

class CutOverdueServices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:cut-overdue-invoices {--dry-run : Solo mostrar, servicios, no cortar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corta los servicios de los clientes con facturas vencidas sin extensiones';

    /**
     * Execute the console command.
     */
    public function handle(OverdueServiceCutService $service): int
    {
        $this->info('Iniciando corte de servicios por morosidad.');
        $this->newLine();

        if ($this->option('dry-run')) {
            $this->warn('Modo simulación (dry-run)');
        }

        $result = $service->cutOverdueClients();

        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Servicios Evaluados', $result['total']],
                ['Servicios Cortados', $result['cut']],
                ['Errores', count($result['errors'])],
            ]
        );

        if (!empty($result['errors'])) {
            $this->newLine();
            $this->error('Errores encontrados');
            foreach ($result['errors'] as $error) {
                $this->error(": {$error}");
            }
        }

        return Command::SUCCESS;
    }
}
