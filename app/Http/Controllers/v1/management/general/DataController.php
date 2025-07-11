<?php

namespace App\Http\Controllers\v1\management\general;

use App\Http\Controllers\Controller;
use App\Models\Billing\Options\ActivityModel;
use App\Models\Billing\Options\DocumentTypeModel as BillingDocumentType;
use App\Models\Configuration\Clients\DocumentTypeModel as ClientDocumentType;
use App\Models\Configuration\BranchModel;
use App\Models\Configuration\Clients\ClientTypeModel;
use App\Models\Configuration\Clients\GenderModel;
use App\Models\Configuration\Clients\KinshipModel;
use App\Models\Configuration\Clients\MaritalStatusModel;
use App\Models\Configuration\Clients\PhoneTypeModel;
use App\Models\Configuration\Geography\CountryModel;
use App\Models\Configuration\Geography\DistrictModel;
use App\Models\Configuration\Geography\MunicipalityModel;
use App\Models\Configuration\Geography\StateModel;
use Illuminate\Http\JsonResponse;

class DataController extends Controller
{
    public function generalStatus(): JsonResponse
    {
        $status = [
            [
                'id' => 0,
                'name' => 'Inactivo'
            ],
            [
                'id' => 1,
                'name' => 'Activo'
            ],
        ];

        return response()->json(['response' => $status]);
    }

    public function statesList(): JsonResponse
    {
        return response()->json([
            'response' => StateModel::query()
                ->select('id', 'name')
                ->orderBy('name', 'ASC')
                ->get()
        ]);
    }

    public function municipalitiesList(): JsonResponse
    {
        return response()->json([
            'response' => MunicipalityModel::query()
                ->select('id', 'name')
                ->orderBy('name', 'ASC')
                ->get()
        ]);
    }

    public function municipalitiesByState(int $stateID): JsonResponse
    {
        return response()->json([
            'response' => MunicipalityModel::query()
                ->select('id', 'name')
                ->where('state_id', $stateID)
                ->orderBy('name', 'ASC')
                ->get()
        ]);
    }

    public function districtsList(): JsonResponse
    {
        return response()->json(['response' => DistrictModel::query()->select('id', 'name')->get()]);
    }

    public function countriesList(): JsonResponse
    {
        $query = CountryModel::query()->select('id', 'es_name')->get();
        $result = $query->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->es_name,
            ];
        });

        return response()->json(['response' => $result]);
    }

    public function districtsByMunicipality(int $municipalityID): JsonResponse
    {
        $query = DistrictModel::query()
            ->select('id', 'name')
            ->where('municipality_id', $municipalityID)
            ->get();

        return response()->json(['response' => $query]);
    }

    public function gendersList(): JsonResponse
    {
        return response()->json([
            'response' => GenderModel::query()
                ->where('status_id', 1)
                ->select('id', 'name')
                ->get()
        ]);
    }

    public function maritalStatusList(): JsonResponse
    {
        return response()->json([
            'response' => MaritalStatusModel::query()
                ->where('status_id', 1)
                ->select('id', 'name')
                ->get()
        ]);
    }

    public function branchesList(): JsonResponse
    {
        return response()->json([
            'response' => BranchModel::query()
                ->where('status_id', 1)
                ->select('id', 'name')
                ->get()
        ]);
    }

    public function clientTypesList(): JsonResponse
    {
        return response()->json([
            'response' => ClientTypeModel::query()
                ->where('status_id', 1)
                ->select('id', 'name')
                ->get()
        ]);
    }

    public function billingDocumentsList(): JsonResponse
    {
        return response()->json([
            'response' => BillingDocumentType::query()
                ->where('status_id', 1)
                ->select('id', 'name')
                ->get()
        ]);
    }

    public function personalDocumentsList(): JsonResponse
    {
        return response()->json([
            'response' => ClientDocumentType::query()
                ->where('status_id', 1)
                ->select('id', 'name', 'status_id')
                ->get()
        ]);
    }

    public function phoneCategoriesList(): JsonResponse
    {
        return response()->json([
            'response' => PhoneTypeModel::query()
                ->where('status_id', 1)
                ->select(['id', 'name'])
                ->get()
        ]);
    }

    public function referencesList(): JsonResponse
    {
        return response()->json([
            'response' => KinshipModel::query()
                ->where('status_id', 1)
                ->select('id', 'name')
                ->orderBy('name', 'ASC')
                ->get()
        ]);
    }

    public function billingActivitiesList(): JsonResponse
    {
        $query = ActivityModel::query()
            ->where('status_id', 1)
            ->select('id', 'name')
            ->orderBy('name', 'ASC')
            ->get();

        return response()->json([
            'response' => $query->makeHidden(['status'])
        ]);
    }
}
