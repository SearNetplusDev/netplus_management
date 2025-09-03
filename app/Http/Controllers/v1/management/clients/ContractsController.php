<?php

namespace App\Http\Controllers\v1\management\clients;

use App\Http\Controllers\Controller;
use App\Models\Configuration\BranchModel;
use Carbon\Carbon;
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
                'client.passport',
                'client.residence',
                'client.address.state',
                'client.address.municipality',
                'client.address.district',
                'client.country',
                'client.mobile',
            ])
            ->where('id', $id)
            ->get();

        $branch = BranchModel::query()
            ->with(['state:id,name', 'municipality:id,name', 'district:id,name', 'country:id,es_name'])
            ->find(1);
        $branchAddress = $branch->address . ', ';
        $branchAddress .= "distrito de {$branch->district?->name}, ";
        $branchAddress .= "municipio de {$branch->municipality?->name}, ";
        $branchAddress .= "departamento de {$branch->state?->name}, ";
        $branchAddress .= "{$branch->country?->es_name}";

        $mappedContract = $contract->map(function ($contract) use ($branchAddress) {
            $address = $contract->client?->address?->neighborhood . ', ';
            $address .= $contract->client?->address?->address . ', ';
            $address .= $contract->client?->address?->district?->name . ', ';
            $address .= $contract->client?->address?->municipality?->name . ', ';
            $address .= $contract->client?->address?->state?->name;

            return [
                'name' => "{$contract->client?->name} {$contract->client?->surname}",
                'document_type' => $contract->client?->primary_document
                    ? "{$contract->client?->primary_document->type}"
                    : '',
                'document_number' => $contract->client?->primary_document
                    ? "{$contract->client?->primary_document->number}"
                    : "",
                'phone' => $contract->client?->mobile?->number ?? '',
                'address' => $address,
                'contract_date' => Carbon::parse($contract->contract_date)->isoFormat('D [de] MMMM [del] YYYY'),
                'office_address' => $branchAddress ?? '',
            ];
        });

        $pdf = Pdf::loadView('v1.management.pdf.clients.residential_contract', ['data' => $mappedContract->first()])
            ->setPaper('A4', 'portrait');
        return $pdf->stream();
    }
}
