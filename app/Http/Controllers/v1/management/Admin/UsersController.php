<?php

namespace App\Http\Controllers\v1\management\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\v1\Management\Admin\UsersRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

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

    public function store(UsersRequest $request): JsonResponse
    {
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->status_id = $request->status;
        $user->save() ? $saved = true : $saved = false;
        return response()->json(['saved' => $saved, 'user' => $user]);
    }

    public function edit(Request $request): JsonResponse
    {
        return response()->json(['user' => User::query()->find($request->id)]);
    }

    public function update(UsersRequest $request, $id): JsonResponse
    {
        $user = User::query()->find($id);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->status_id = $request->status;
        if ($request->has('password')) {
            $user->password = bcrypt($request->password);
        }
        $user->save() ? $saved = true : $saved = false;
        return response()->json(['saved' => $saved, 'user' => $user]);
    }
}
