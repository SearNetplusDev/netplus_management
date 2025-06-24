<?php

namespace App\Http\Controllers\v1\management\configuration\geography;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Configuration\CountryModel;

class CountriesController extends Controller
{
    public function data(Request $request): JsonResponse
    {
        $query = CountryModel::query();
        $statusFilter = collect($request->e ?? [])->firstWhere('column', 'status');
        if ($statusFilter) {
            $query->whereIn('status_id', json_decode($statusFilter['data']));
        }
        $query = $query->orderBy('status_id', 'desc')->advancedFilter();

        return response()->json(['collection' => $query]);
    }
}
