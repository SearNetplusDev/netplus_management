<?php

namespace App\Http\Controllers\v1\management\clients;

use App\Http\Controllers\Controller;
use App\Services\v1\management\client\ReferenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\v1\Management\Clients\ReferencesRequest;
use App\Models\Clients\ReferenceModel;
use App\Http\Resources\v1\management\clients\ReferencesResource;

class ReferencesController extends Controller
{
    public function data(Request $request): JsonResponse
    {

    }

    public function store(ReferencesRequest $request, ReferenceService $service): JsonResponse
    {

    }

    public function edit(Request $request): JsonResponse
    {

    }

    public function update(ReferencesRequest $request, ReferenceModel $id, ReferenceService $service): JsonResponse
    {

    }
}
