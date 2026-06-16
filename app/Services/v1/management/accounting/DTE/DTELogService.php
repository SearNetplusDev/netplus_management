<?php

namespace App\Services\v1\management\accounting\DTE;

use App\Models\Accounting\DTEEventModel;
use App\Models\Accounting\DTELogModel;
use App\Models\Accounting\DTEModel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

readonly class DTELogService
{
    public function logHaciendaResponse(
        DTEModel|DTEEventModel $model,
        object                 $haciendaResponse,
    ): void
    {
        try {
            $isEvent = $model instanceof DTEEventModel;

            DTELogModel::query()
                ->create([
                    'dte_id' => $isEvent ? null : $model->id,
                    'event_id' => $isEvent ? $model->id : null,
                    'json_response' => $haciendaResponse,
                    'transaction_date' => Carbon::now(),
                ]);

        } catch (Throwable $e) {
            Log::channel('dte_log')->error("[DTE] Error al almacenar la respuesta de hacienda: ", [
                'model_id' => $model->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
