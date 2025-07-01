<?php

namespace App\Services\v1\management;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

class DataViewerService
{
    public function handle(Request $request, Builder $query, array $filterColumns = []): JsonResponse
    {
        if ($request->has('e')) {
            foreach ($request->e as $filter) {
                if (!isset($filter['column'], $filter['data'])) continue;

                $data = json_decode($filter['data'], true);

                if (!is_array($data)) continue;

                if (isset($filterColumns[$filter['column']])) {
                    $filterColumns[$filter['column']] ($query, $data);
                }
            }
        }
        $query = $query->orderByDesc('status_id')->advancedFilter();

        return response()->json(['collection' => $query]);
    }
}
