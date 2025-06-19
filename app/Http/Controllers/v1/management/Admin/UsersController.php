<?php

namespace App\Http\Controllers\v1\management\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;

class UsersController extends Controller
{
    public function data(Request $request): JsonResponse
    {
        $query = User::query();
        $statusFilter = collect($request->e ?? [])
            ->firstWhere('column', 'status');
        if ($statusFilter) {
            $query->whereIn('status_id', json_decode($statusFilter['data']));
        }
        $query = $query->orderBy('status_id', 'desc')
            ->advancedFilter();

        return response()->json(['collection' => $query]);
    }

    public function edit(Request $request): JsonResponse
    {
        return response()->json(['user' => User::query()->find($request->id)]);
    }
}
