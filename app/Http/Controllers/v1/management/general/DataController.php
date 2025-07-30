<?php

namespace App\Http\Controllers\v1\management\general;

use App\Http\Controllers\Controller;
use App\Models\Billing\Options\ActivityModel;
use App\Models\Billing\Options\DocumentTypeModel as BillingDocumentType;
use App\Models\Configuration\Clients\ContractStateModel;
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
use App\Models\Configuration\Infrastructure\EquipmentStatusModel;
use App\Models\Configuration\MenuModel;
use App\Models\Infrastructure\Equipment\BrandModel;
use App\Models\Infrastructure\Equipment\ModelModel;
use App\Models\Infrastructure\Equipment\TypeModel;
use App\Models\Infrastructure\Network\AuthServerModel;
use App\Models\Infrastructure\Network\NodeModel;
use App\Models\Management\PermissionModel;
use App\Models\Management\RoleModel;
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

    public function countriesWithCode(): JsonResponse
    {
        $query = CountryModel::query()->select('iso_2', 'es_name')->get();
        $result = $query->map(function ($item) {
            return [
                'id' => $item->iso_2,
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

    public function contractStatusList(): JsonResponse
    {
        return response()->json([
            'response' => ContractStateModel::query()
                ->where('status_id', 1)
                ->select('id', 'name')
                ->get()
        ]);
    }

    public function authServersList(): JsonResponse
    {
        return response()->json([
            'response' => AuthServerModel::query()
                ->where('status_id', 1)
                ->select('id', 'name')
                ->orderBy('name', 'ASC')
                ->get()
        ]);
    }

    public function equipmentTypesList(): JsonResponse
    {
        return response()->json([
            'response' => TypeModel::query()
                ->where('status_id', 1)
                ->select('id', 'name')
                ->orderBy('name', 'ASC')
                ->get()
        ]);
    }

    public function equipmentBrandsList(): JsonResponse
    {
        return response()->json([
            'response' => BrandModel::query()
                ->where('status_id', 1)
                ->select('id', 'name')
                ->get()
        ]);
    }

    public function equipmentStatusList(): JsonResponse
    {
        return response()->json([
            'response' => EquipmentStatusModel::query()
                ->where('status_id', 1)
                ->select('id', 'name')
                ->get()
        ]);
    }

    public function nodesList(): JsonResponse
    {
        return response()->json([
            'response' => NodeModel::query()
                ->where('status_id', 1)
                ->select('id', 'name')
                ->get()
        ]);
    }

    public function modelsList(): JsonResponse
    {
        return response()->json([
            'response' => ModelModel::query()
                ->where('status_id', 1)
                ->select('id', 'name')
                ->get()
        ]);
    }

    public function rolesList(): JsonResponse
    {
        return response()->json([
            'response' => RoleModel::query()->select('id', 'name')->get()
        ]);
    }

    public function permissionsList(): JsonResponse
    {
        return response()->json([
            'response' => PermissionModel::query()
                ->select('id as value', 'name as label')
                ->orderBy('name', 'ASC')
                ->get()
        ]);
    }

    public function menuList(): JsonResponse
    {
        return response()->json([
            'response' => MenuModel::query()
                ->select('id', 'slug as name')
                ->where('status_id', 1)
                ->orderBy('name', 'ASC')
                ->get()
        ]);
    }
}
