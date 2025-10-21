<?php

namespace App\Strategies\v1\Operations\Technical;

use App\Enums\v1\Supports\SupportStatus;
use App\Models\Services\ServiceModel;
use App\Models\Supports\SupportModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class ChangeAddressStrategy extends BaseSupportStrategy
{
    /*****
     * @throws ValidationException
     *****/
    public function handle(SupportModel $model, array $params): SupportModel
    {
        $status = SupportStatus::tryFrom((int)$params['status']);

        if (!$status || !$status->isFinalized()) return $model;

        try {
            DB::transaction(function () use ($model, $params) {
                $this->ensureExistingService($model);
                $this->ensureServiceStatus($model);
                $this->updateServiceAddress($model, $params);
            });
        } catch (Throwable $e) {
            throw ValidationException::withMessages([
                'support' => "Error al procesar cambio de domicilio. {$e->getMessage()}",
            ]);
        }

        return $model;
    }

    private function updateServiceAddress(SupportModel $model, array $params): void
    {
        $service = ServiceModel::query()->findOrFail($model->service_id);
        $updateData = collect([
            'node_id' => (int)$params['node'],
            'equipment_id' => (int)$params['equipment'],
            'latitude' => $params['latitude'],
            'longitude' => $params['longitude'],
            'state_id' => (int)$params['state'],
            'municipality_id' => (int)$params['municipality'],
            'district_id' => (int)$params['district'],
            'address' => $params['address'],
        ])->filter(fn($val) => !is_null($val));

        $service->update($updateData->toArray());
//        return $service->refresh();
    }
}
