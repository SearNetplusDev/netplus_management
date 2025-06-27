<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use App\Models\Configuration\StateModel;
use App\Models\Configuration\MunicipalityModel;
use App\Models\Configuration\DistrictModel;

class NationalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $content = json_decode(File::get(database_path("seeders/data/political-division.json")), true);

        foreach ($content as $data) {
            foreach ($data as $department) {
                $state = StateModel::create([
                    'name' => $department['name'],
                    'code' => $department['code'],
                    'iso_code' => $department['iso'],
                    'status_id' => 1
                ]);

                foreach ($department['municipalities'] as $municipality) {
                    $mun = MunicipalityModel::create([
                        'name' => $municipality['name'],
                        'code' => $municipality['code'],
                        'state_id' => $state->id,
                        'status_id' => 1
                    ]);

                    foreach ($municipality['districts'] as $district) {
                        DistrictModel::create([
                            'name' => $district['name'],
                            'state_id' => $state->id,
                            'municipality_id' => $mun->id,
                            'status_id' => 1
                        ]);
                    }
                }

            }
        }
    }
}
