<?php

namespace App\Strategies\v1\Operations\Technical;

use App\Enums\v1\Supports\SupportStatus;
use App\Models\Services\ServiceInternetModel;
use App\Models\Services\ServiceModel;
use App\Models\Supports\SupportModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class RenovationStrategy extends BaseSupportStrategy
{
    /**
     * @throws ValidationException
     * */
    public function handle(SupportModel $model, array $params): SupportModel
    {
        $status = SupportStatus::tryFrom((int)$params['status']);

        if (!$status || !$status->isFinalized()) return $model;

        try {
            DB::transaction(function () use ($model, $params) {
                $this->validateExistingService($model);
                $this->ensureServiceStatus($model);
                $this->updateInternetProfile($model, $params);
            });
        } catch (Throwable $e) {
//            report($e);
            throw ValidationException::withMessages([
                'support' => "Error al procesar la renovación del servicio: {$e->getMessage()}"
            ]);
        }

        return $model;
    }

    /*****
     * Verifica la existencia del servicio
     * @throws ValidationException
     *****/
    private function validateExistingService(SupportModel $model): void
    {
        if (!$model->service_id) {
            throw ValidationException::withMessages([
                'support' => "No se puede renovar sin un servicio"
            ]);
        }

        $service = ServiceModel::with('internet')->findOrFail($model->service_id);

        if (!$service->internet) {
            throw ValidationException::withMessages([
                'support' => "Este servicio no tiene credenciales de internet asociadas."
            ]);
        }
    }

    private function updateInternetProfile(SupportModel $model, array $params): void
    {
        $currentPlan = ServiceInternetModel::query()
            ->where('service_id', $model->service_id)
            ->first();
        $currentPlan->update(['internet_profile_id' => (int)$params['profile']]);
    }
}
