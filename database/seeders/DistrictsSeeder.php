<?php

namespace Database\Seeders;

use App\Models\Configuration\Geography\DistrictModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class DistrictsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $districts = json_decode(File::get(database_path("seeders/data/districts.json")), true);
        foreach ($districts as $district) {
            DistrictModel::create([
                'name' => $district['nombre'],
                'municipality_id' => $district['municipio'],
                'state_id' => $district['departamento'],
                'status_id' => 1,
            ]);
        }
    }
}
