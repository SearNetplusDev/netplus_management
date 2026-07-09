<?php

namespace App\Http\Controllers\v1\management\Monitoring;

use App\Http\Controllers\Controller;
use App\Models\Monitoring\ActiveConnectionModel;
use App\Services\v1\management\DataViewerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InternetController extends Controller
{
    public function data(Request $request, DataViewerService $service): JsonResponse
    {
        $query = ActiveConnectionModel::query()
            ->with([
                'internet_service.service.client.dui',
                'internet_service.service.client.branch',
                'internet_service.service.client.mobile',
            ]);

        return $service->handle($request, $query, [

        ]);
    }
}
