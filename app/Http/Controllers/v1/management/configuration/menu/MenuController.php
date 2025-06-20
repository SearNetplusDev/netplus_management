<?php

namespace App\Http\Controllers\v1\management\configuration\menu;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Configuration\MenuModel;

class MenuController extends Controller
{
    public function data(Request $request): JsonResponse
    {
        $query = MenuModel::query();
        $statusFilter = collect($request->e ?? [])->firstWhere('column', 'status');
        if ($statusFilter) {
            $query->whereIn('status_id', json_decode($statusFilter['data']));
        }
        $query = $query->orderBy('status_id', 'desc')->advancedFilter();

        return response()->json(['collection' => $query]);
    }
}
