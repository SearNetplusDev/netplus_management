<?php

namespace App\Http\Controllers\v1\management\clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\v1\Management\Clients\DocumentRequest;
use App\Models\Clients\DocumentModel;
use App\Services\v1\management\client\DocumentService;
use App\Http\Resources\v1\management\clients\DocumentResource;

class DocumentsController extends Controller
{
    public function data(Request $request): JsonResponse
    {
        return response()->json(['response' => DocumentModel::query()->where('client_id', $request->clientID)->get()]);
    }

    public function store(DocumentRequest $request, DocumentService $documentService): JsonResponse
    {
        $document = $documentService->createDocument($request->toDTO());

        return response()->json([
            'saved' => (bool)$document,
            'document' => new DocumentResource($document)
        ]);
    }

    public function edit(Request $request): JsonResponse
    {
        return response()->json([
            'document' => DocumentModel::query()
                ->with('document_type')
                ->where([
                    ['id', $request->documentID],
                    ['client_id', $request->clientID],
                ])
                ->get()
        ]);
    }

    public function update(DocumentRequest $request, DocumentModel $id, DocumentService $documentService): JsonResponse
    {
        $document = $documentService->updateDocument($id, $request->toDTO());

        return response()->json([
            'saved' => (bool)$document,
            'document' => new DocumentResource($document)
        ]);
    }
}
