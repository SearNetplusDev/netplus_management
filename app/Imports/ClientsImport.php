<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ClientsImport implements ToCollection, WithHeadingRow
{
    /***
     * @param Collection $collection
     * @return Collection
     */
    public function collection(Collection $collection): Collection
    {
        foreach ($collection as $row) {
            collect([
                'name' => $row['client_name'],
                'surname' => $row['client_surname'],
                'genre' => $row['genre'],
                'client_type' => $row['client_type'],
                'civil_status' => $row['civil_status'],
                'client_status' => $row['client_status'],
                'dui' => $row['dui'],
                'nit' => $row['nit'],
                'phone' => $row['phone'],
                'address' => $row['address'],
                'state' => $row['state_id'],
                'municipality' => $row['municipality_id'],
                'district' => $row['district_id'],
                'reference_name' => $row['reference_name'],
                'reference_dui' => $row['reference_dui'],
                'reference_phone' => $row['reference_phone'],
                'node' => $row['node'],
                'panel' => $row['panel'],
                'installation_date' => $row['installation_date'],
                'service_status' => $row['service_status'],
                'internet_profile' => $row['internet_plan'],
                'pppoe_user' => $row['secret_user'],
                'pppoe_password' => $row['secret_password'],
                'iptv' => $row['iptv'],
                'iptv_mail' => $row['iptv_mail'],
                'iptv_password' => $row['iptv_password'],
                'iptv_activation_password' => $row['iptv_activation_password'],
                'contract_begins' => $row['contract_begins'],
                'contract_ends' => $row['contract_ends'],
                'observation' => $row['observation'],
            ]);
        }
    }
}
