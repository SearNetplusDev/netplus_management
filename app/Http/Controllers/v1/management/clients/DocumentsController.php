<?php

namespace App\Http\Controllers\v1\management\clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Clients\DocumentModel;

class DocumentsController extends Controller
{
    public function data(Request $request): JsonResponse
    {
        return response()->json(['response' => DocumentModel::query()->where('client_id', $request->clientID)->get()]);
    }

    public function store(Request $request): JsonResponse
    {

    }

    public function edit(Request $request): JsonResponse
    {

    }

    public function update(Request $request, $id): JsonResponse
    {

    }
}
