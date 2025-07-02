<?php

namespace App\Http\Controllers\v1\management\clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Clients\ClientModel;
use App\Services\v1\management\DataViewerService;

class ClientsController extends Controller
{
    public function data(Request $request): JsonResponse
    {

    }

    public function store(Request $request): JsonResponse
    {
        return response()->json([
            'saved' => true,
            'client' => ['id' => 1, 'name' => 'Sear']
        ]);
    }

    public function edit(Request $request): JsonResponse
    {

    }

    public function update(Request $request, $id): JsonResponse
    {

    }
}
