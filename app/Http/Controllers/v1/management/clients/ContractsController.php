<?php

namespace App\Http\Controllers\v1\management\clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\v1\Management\Clients\ContractRequest;
use App\Http\Resources\v1\management\clients\ContractResource;
use App\Models\Clients\ContractModel;
use App\Services\v1\management\client\ContractService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class ContractsController extends Controller
{
    public function data(Request $request): JsonResponse
    {
        return response()->json([
            'response' => ContractModel::query()
                ->with('contract_status')
                ->where('client_id', $request->input('clientID'))
                ->get()
        ]);
    }

    public function store(ContractRequest $request, ContractService $service): JsonResponse
    {
        $contract = $service->create($request->toDTO());

        return response()->json([
            'saved' => (bool)$contract,
            'contract' => new ContractResource($contract)
        ]);
    }

    public function edit(Request $request): JsonResponse
    {
        return response()->json([
            'contract' => ContractModel::query()->find($request->input('id')),
        ]);
    }

    public function update(ContractRequest $request, ContractModel $id, ContractService $service): JsonResponse
    {
        $contract = $service->update($id, $request->toDTO());

        return response()->json([
            'saved' => (bool)$contract,
            'contract' => new ContractResource($contract)
        ]);
    }

    public function print(int $id): Response
    {
        $contract = ContractModel::query()
            ->with([
                'client.dui',
                'client.nit',
                'client.address.state',
                'client.address.municipality',
                'client.address.district',
                'client.country',
                'client.mobile',
                'client.branch.country',
                'client.branch.municipality',
                'client.branch.district',
                'client.branch.state',
            ])
            ->find($id);
        $pdf = Pdf::loadView('v1.management.pdf.clients.residential_contract', ['data' => $contract])
            ->setPaper('A4', 'portrait');
        return $pdf->stream();
    }
}
