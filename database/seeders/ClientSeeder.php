<?php

namespace Database\Seeders;

use App\Services\v1\imports\ImportClientsService;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     * @throws \Throwable
     */
    public function run(): void
    {
        $service = app(ImportClientsService::class);
        $service->importClients();
    }
}
