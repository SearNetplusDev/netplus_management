<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Configuration\Clients\SexTypeModel;

class SexTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $array = ['Masculino', 'Femenino'];
        foreach ($array as $value) {
            SexTypeModel::create([
                'name' => $value,
                'status_id' => 1,
            ]);
        }
    }
}
