<?php

namespace App\Services\v1\management\supports;

use App\DTOs\v1\management\supports\SupportDTO;
use App\Enums\v1\Supports\SupportStatus;
use App\Enums\v1\Supports\SupportType;
use App\Models\Supports\SupportModel;
use Illuminate\Validation\ValidationException;

class SupportService
{
    public function create(SupportDTO $DTO): SupportModel
    {
        $type = SupportType::from((int)$DTO->type_id);

        //  Si el tipo de soportes no permite duplicados, validar
        if (!$type->allowsDuplicates()) {
            $exists = SupportModel::query()
                ->where('client_id', $DTO->client_id)
                ->whereIn('status_id', SupportStatus::getActiveStatuses())
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'client' => 'Este cliente ya tiene un soporte activo.'
                ]);
            }
        }

        $ticket = $this->createTicket();

        //  Buscando estrategia correcta
        $strategy = SupportFactory::make((int)$DTO->type_id);
        return $strategy->handle($DTO->toArray(), $ticket);
    }

    public function read(int $id): SupportModel
    {
        return SupportModel::query()
            ->with(['client', 'service', 'details', 'contract'])
            ->findOrFail($id);
    }

    public function update(SupportModel $model, SupportDTO $DTO)/*: SupportModel*/
    {
        $strategy = SupportFactory::make((int)$DTO->type_id);

        return $strategy->update($model, $DTO->toArray());
    }

    private function createTicket(): string
    {
        $prefix = 'NETPLUS_SPT-';
        $total = SupportModel::query()->withTrashed()->count();
        $totalLength = 10;
        $zeroFill = max(0, $totalLength - strlen($total));
        $filling = str_repeat('0', $zeroFill);
        $prefix .= $filling . ($total + 1);
        return $prefix;
    }
}
