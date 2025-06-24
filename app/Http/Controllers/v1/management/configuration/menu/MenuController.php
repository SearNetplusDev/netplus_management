<?php

namespace App\Http\Controllers\v1\management\configuration\menu;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Configuration\MenuModel;
use App\Http\Requests\v1\Management\Configuration\MenuRequest;

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

    public function edit(Request $request): JsonResponse
    {
        return response()->json(['response' => MenuModel::find($request->id)->load('parent.parent')]);
    }

    public function store(MenuRequest $request): JsonResponse
    {
        $menu = MenuModel::create([
            'name' => $request->name,
            'url' => $request->url,
            'icon' => $request->icon,
            'parent_id' => $request->parent === 0 || $request->parent === null ? null : $request->parent,
            'order' => $request->order,
            'status_id' => $request->status,
        ]);
        return response()->json(['saved' => (bool)$menu, 'item' => $menu]);
    }

    public function update(MenuRequest $request, $id): JsonResponse
    {
        $menu = MenuModel::query()->findOrFail($id);
        $update = $menu->update([
            'name' => $request->name,
            'url' => $request->url,
            'icon' => $request->icon,
            'parent_id' => $request->parent === 0 || $request->parent === null ? null : $request->parent,
            'order' => $request->order,
            'status_id' => $request->status,
        ]);
        return response()->json(['saved' => (bool)$update, 'item' => $menu]);
    }

    public function getParents(): JsonResponse
    {
        $menuTree = MenuModel::query()
            ->with(['children', 'parent'])
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();
        $parents = $this->extractMenusWithChildren($menuTree);
        $parents[] = ['id' => 0, 'name' => 'Sin Padre'];
        $data = collect($parents)->sortBy('id')->values();

        return response()->json(['response' => $data]);
    }

    protected function extractMenusWithChildren($menu): array
    {
        $result = [];

        foreach ($menu as $menuItem) {
            if ($menuItem->children->isNotEmpty()) {
                $name = ($menuItem->parent->name) ?? null ? $menuItem->parent->name . ' - ' . $menuItem->name : $menuItem->name;
                $result[] = [
                    'id' => $menuItem->id,
                    'name' => $name
                ];
                $result = [...$result, ...$this->extractMenusWithChildren($menuItem->children)];
            }
        }
        return $result;
    }
}
