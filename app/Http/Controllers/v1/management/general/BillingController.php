<?php

namespace App\Http\Controllers\v1\management\general;

use App\Http\Controllers\Controller;
use App\Models\Billing\Options\ActivityModel;
use App\Models\Billing\Options\DocumentTypeModel;
use App\Models\Billing\Options\StatusModel;
use Illuminate\Http\JsonResponse;

class BillingController extends Controller
{
    public function billingDocumentsList(): JsonResponse
    {
        return response()->json([
            'response' => DocumentTypeModel::query()
                ->where('status_id', 1)
                ->select('id', 'name')
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

    public function statusesList(): JsonResponse
    {
        return response()->json([
            'response' => StatusModel::query()
                ->select(['id', 'name'])
                ->orderBy('name')
                ->get()
                ->makeHidden(['status'])
        ]);
    }
}
