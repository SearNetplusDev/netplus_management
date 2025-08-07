<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\management\services\ServicesController;

Route::prefix('v1/services')
    ->middleware(['auth:sanctum'])
    ->group(function () {
        //  General Data
        Route::post('/', [ServicesController::class, 'store']);
        Route::post('data', [ServicesController::class, 'data']);
        Route::post('/edit', [ServicesController::class, 'read']);
        Route::put('{id}', [ServicesController::class, 'update']);
        Route::post('/client', [ServicesController::class, 'clientServices']);
    });
