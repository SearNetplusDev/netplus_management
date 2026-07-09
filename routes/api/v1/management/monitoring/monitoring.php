<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\management\Monitoring\InternetController;

Route::prefix('v1/monitoring')
    ->middleware(['auth:sanctum'])
    ->group(function () {
        Route::post('internet/data', [InternetController::class, 'data']);
    });
