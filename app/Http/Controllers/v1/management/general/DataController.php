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
use App\Models\Infrastructure\Network\EquipmentModel;
use App\Models\Infrastructure\Network\NodeModel;
use App\Models\Management\PermissionModel;
use App\Models\Management\RoleModel;
use App\Models\Management\TechnicianModel;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

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
        $user = Auth::user()->load('roles');
        $role = $user->roles->first()?->id;
        $query = RoleModel::query()->select('id', 'name');

        if ($role !== 1) {
            $query->where('id', '!=', 1);
        }

        return response()->json([
            'response' => $query->get()
        ]);
    }

    public function permissionsList(): JsonResponse
    {
        $permissions = PermissionModel::query()
            ->selectRaw("split_part(name, '.', 1) as category, id as value, name as label")
            ->orderBy('category', 'ASC')
            ->orderBy('label', 'ASC')
            ->get()
            ->groupBy('category')
            ->map(function ($items, $category) {
                return [
                    'category' => ucfirst($category),
                    'permissions' => $items->map(function ($item) {
                        return [
                            'value' => $item->value,
                            'label' => $item->label,
                        ];
                    })
                ];
            })
            ->values();

        return response()->json([
            'response' => $permissions
        ]);
    }

    public function permissionsByRoleId(int $roleID): JsonResponse
    {
        $role = RoleModel::query()->with('permissions')->find($roleID);
        return response()->json([
            'response' => $role->permissions()->pluck('id')->toArray()
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

    public function usersList(): JsonResponse
    {
        return response()->json([
            'response' => User::query()
                ->where('status_id', 1)
                ->select('id', 'name')
                ->orderBy('name', 'ASC')
                ->get()
        ]);
    }

    public function equipmentByNode(int $id): JsonResponse
    {
        $query = EquipmentModel::query()
            ->where([
                ['node_id', $id],
                ['status_id', 10]
            ])
            ->select('id', 'name')
            ->orderBy('name', 'ASC')
            ->get();

        return response()->json(['response' => $query]);
    }

    public function technicianList(): JsonResponse
    {
        $query = TechnicianModel::query()
            ->with('user:id,name')
            ->where('status_id', 1)
            ->get();
        $data = [];

        foreach ($query as $item) {
            $el = [
                'id' => $item->id,
                'name' => $item->user?->name,
            ];
            $data[] = $el;
        }

        return response()->json(['response' => $data]);
    }
}
