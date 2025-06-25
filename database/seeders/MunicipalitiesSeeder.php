<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use App\Models\Configuration\MunicipalityModel;

class MunicipalitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $municipalities = json_decode(File::get(database_path("seeders/data/municipalities.json")), true);
        foreach ($municipalities as $municipality) {
            MunicipalityModel::create([
                'name' => $municipality['name'],
                'code' => $municipality['code'],
                'state_id' => $municipality['state'],
                'status_id' => 1,
            ]);
        }
    }
}
