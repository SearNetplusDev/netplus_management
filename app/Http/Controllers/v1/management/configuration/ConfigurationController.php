<?php

namespace App\Http\Controllers\v1\management\configuration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Configuration\MenuModel;
use Illuminate\Support\Facades\Auth;

class ConfigurationController extends Controller
{
    public function getMenu(): JsonResponse
    {
        $query = MenuModel::query()
            ->whereNull('parent_id')
            ->with('children.children')
            ->get();
        return response()->json(['data' => $query, 'user' => Auth::user()]);
    }
}
