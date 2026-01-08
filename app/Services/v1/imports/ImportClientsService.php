<?php

namespace App\Services\v1\imports;

use App\Imports\ClientsImport;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use function Termwind\parse;

class ImportClientsService
{
    public function importClients(): array
    {
        $path = storage_path('app/imports/clients.xlsx');
        $clients = Excel::toCollection(new ClientsImport, $path)[0];
        $data = [];
        $counter = [];

        foreach ($clients as $client) {
            if (empty($client['name'])) continue;
            $documents = [
                'dui' => [
                    'type' => 3,
                    'number' => $client['dui'],
                    'expiration' => '2050-12-31',
                ],
                'nit' => [
                    'type' => 4,
                    'number' => $client['nit'],
                    'expiration' => '2050-12-31',
                ]
            ];

            $phone = [
                'phone_type' => 2,
                'number' => $client['phone'],
                'country' => 'SV',
            ];

            $municipality = match ((int)$client['district']) {
                234, 243, 230, 241 => 42,
                201 => 38,
                208, 209, 211 => 39,
                14 => 4,
                216 => 40,
                195 => 37,
                188 => 36,
            };

            $address = [
                'address' => $this->parsedName($client['address']),
                'district' => $client['district'],
                'municipality' => $municipality,
                'state' => $client['state'],
                'country' => 59,
            ];

            $reference = [
                'name' => $this->parsedName($client['reference_name'] ?? ''),
                'dui' => $client['reference_dui'],
                'phone' => $client['reference_phone'],
                'kinship' => rand(1, 24),
            ];

            $key = $this->getClientKey($client->toArray());

            if (!isset($counter[$key])) {
                $counter[$key] = 1;
            } else {
                $counter[$key]++;
            }

            $split = $this->splitFullName($client['name']);

            $data[] = [
                'first_name' => $this->parsedName($split['first_names']),
                'last_name' => $this->parsedName($split['last_names']),
                'gender' => 1,
                'birthday' => $this->getBirthDate($client['nit']),
                'civil_status' => $client['civil_status'],
                'branch' => 1,
                'client_type' => 1,
                'profession' => 'Otro',
                'country' => 59,
                'document_type' => 1,
                'legal_entity' => false,
                'times_presented' => $counter[$key],
                'documents' => $documents,
                'phone' => $phone,
                'address' => $address,
                'reference' => $reference,
            ];
        }

        return $data;
    }

    /***
     * Convierte a inicial mayuscula
     * @param string $name
     * @return string
     */
    private function parsedName(string $name): string
    {
        $lower = strtolower($name);
        return (ucwords($lower));
    }

    /***
     * Divide los nombres completos en nombres y apellidos
     * @param string $fullName
     * @return array
     */
    private function splitFullName(string $fullName): array
    {
        $fullName = trim(preg_replace('/\s+/', ' ', $fullName));
        $particles = ['de la', 'de los', 'de las', 'del', 'de', 'vda. de', 'vda de'];
        $words = explode(' ', $fullName);
        $lastName = [];
        $firstName = [];

        while (!empty($words)) {
            $word = array_pop($words);
            $wordLower = strtolower($word);
            $isParticle = false;

            foreach ($particles as $particle) {
                $particleParts = explode(' ', $particle);
                $numParts = count($particleParts);

                if ($numParts > 1) {
                    $context = array_slice($lastName, 0, $numParts - 1);
                    $potentialPhrase = strtolower(implode(' ', array_merge([$word], $context)));

                    if ($potentialPhrase === $particle) {
                        array_unshift($lastName, $word);
                        $isParticle = true;
                        break;
                    }
                } else {
                    if ($wordLower === $particle) {
                        array_unshift($lastName, $word);
                        $isParticle = true;
                        break;
                    }
                }
            }

            if ($isParticle) continue;

            if (count($lastName) >= 2) {
                array_unshift($firstName, $word);

                while (!empty($words)) {
                    array_unshift($firstName, array_pop($words));
                }
                break;
            }

            array_unshift($lastName, $word);
        }

        return [
            'first_names' => trim(implode(' ', $firstName)),
            'last_names' => trim(implode(' ', $lastName)),
        ];
    }

    /***
     * Extrae la fecha de nacimiento según el NIT
     * @param string|null $number
     * @return string
     */
    private function getBirthDate(?string $number): string
    {
        $defaultDate = '1945-08-06';
        $document = strtoupper(trim($number ?? ''));

        if (empty($document) || $document === '0000-000000-000-0' || $document === 'HOMOLOGADO')
            $document = '1217-060845-106-7';
        if (!preg_match('/^\d{4}-\d{6}-\d{3}-\d{1}$/', $number)) return $defaultDate;

        $dateStr = explode('-', $document)[1];
        $day = (int)substr($dateStr, 0, 2);
        $month = (int)substr($dateStr, 2, 2);
        $year = (int)substr($dateStr, 4, 2);
        if (!checkdate($month, $day, 2000)) return $defaultDate;
        $fullYear = $year <= (int)date('y') ? 2000 + $year : 1900 + $year;
        return Carbon::createFromDate($fullYear, $month, $day)->toDateString();
    }

    /***
     * Para identificar si un cliente se encuentra duplicado
     * @param array $client
     * @return string
     */
    private function getClientKey(array $client): string
    {
        if (!empty($client['dui'])) return trim($client['dui']);

        return trim($client['phone']);
    }
}
