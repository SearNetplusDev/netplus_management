<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\management\Monitoring\InternetController;

Route::prefix('v1/monitoring')
    ->middleware(['auth:sanctum'])
    ->group(function () {

        Route::group(['prefix' => 'internet'], function () {
            Route::post('data', [InternetController::class, 'data']);
            Route::post('pppoe', [InternetController::class, 'pppoeInfo']);
        });
    });
