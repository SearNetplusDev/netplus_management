<?php

namespace App\Http\Controllers\v1\management\general;

use App\Http\Controllers\Controller;
use App\Models\Supports\StatusModel;
use App\Models\Supports\TypeModel as SupportType;
use Illuminate\Http\JsonResponse;

class SupportsController extends Controller
{
    public function supports_status(): JsonResponse
    {
        return response()->json([
            'response' => StatusModel::query()
                ->where('status_id', 1)
                ->select('id', 'name')
                ->orderBy('id', 'ASC')
                ->get()
                ->makeHidden('status'),
        ]);
    }

    public function supports_types(): JsonResponse
    {
        return response()->json([
            'response' => SupportType::query()
                ->where('status_id', 1)
                ->select('id', 'name')
                ->orderBy('name', 'ASC')
                ->get()
                ->makeHidden('status'),
        ]);
    }
}
