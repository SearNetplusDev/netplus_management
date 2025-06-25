<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Configuration\StateModel;
use Illuminate\Support\Facades\File;

class StatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $states = json_decode(File::get(database_path("seeders/data/states.json")), true);
        foreach ($states as $state) {
            StateModel::create([
                'name' => $state['nombre'],
                'code' => $state['codigo'],
                'iso_code' => $state['ISO3166-2'],
                'status_id' => 1,
            ]);
        }
    }
}
