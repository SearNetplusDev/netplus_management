<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Configuration\CountryModel;
use Illuminate\Support\Facades\File;

class CountriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = File::get(database_path("seeders/data/countries.json"));
        $countries = json_decode($json, true);

        foreach ($countries as $country) {
            CountryModel::create([
                'es_name' => $country['nameES'],
                'en_name' => $country['nameEN'],
                'iso_2' => $country['iso2'],
                'iso_3' => $country['iso3'],
                'phone_prefix' => $country['phoneCode'],
                'status_id' => 1,
            ]);
        }
    }
}
