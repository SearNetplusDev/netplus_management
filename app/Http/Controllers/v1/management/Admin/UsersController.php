<?php

namespace App\Http\Controllers\v1\management\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\v1\Management\Admin\UsersRequest;
use App\Models\User;
use App\Services\v1\management\DataViewerService;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class UsersController extends Controller
{
    public function data(Request $request, DataViewerService $service): JsonResponse
    {
        $query = User::query()->with('roles');

        return $service->handle($request, $query, [
            'status' => fn($q, $data) => $q->whereIn('status_id', $data),
        ]);
    }

    public function store(UsersRequest $request): JsonResponse
    {
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->status_id = $request->status;
        $user->save() ? $saved = true : $saved = false;

        $roleID = $request->input('role');
        $role = Role::query()->find($roleID);
        if ($role) {
            $user->syncRoles([$role->name]);
        }
        return response()->json(['saved' => $saved, 'user' => $user]);
    }

    public function edit(Request $request): JsonResponse
    {
        return response()->json([
            'user' => User::query()
                ->find($request->input('id'))
                ->load('roles')
        ]);
    }

    public function update(UsersRequest $request, $id): JsonResponse
    {
        $user = User::query()->find($id);
        $isLogged = Auth::id() === $user->id;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->status_id = $request->status;
        $password = $request->input('password');

        if (!is_null($password) && trim($password) != '') {
            $user->password = bcrypt($password);
            if ($isLogged) {
                session()->put('password_hash_' . Auth::getDefaultDriver(), $user->getAuthPassword());
            }
        }
        $saved = $user->save();
        $roleID = $request->input('role');
        $role = Role::query()->find($roleID);

        if ($role) {
            $user->syncRoles([$role->name]);
        }


        return response()->json(['saved' => $saved, 'user' => $user]);
    }
}
