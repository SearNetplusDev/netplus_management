<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\management\services\ServicesController;
use App\Http\Controllers\v1\management\services\ServiceInternetsController;
use App\Http\Controllers\v1\management\services\ServiceEquipmentController;

Route::prefix('v1/services')
    ->middleware(['auth:sanctum'])
    ->group(function () {
        //  General Data
        Route::post('/', [ServicesController::class, 'store']);
        Route::post('data', [ServicesController::class, 'data']);
        Route::post('/edit', [ServicesController::class, 'read']);
        Route::put('{id}', [ServicesController::class, 'update']);
        Route::post('/client', [ServicesController::class, 'clientServices']);

        //      Services Internet
        Route::group(['prefix' => 'internet'], function () {
            Route::post('/', [ServiceInternetsController::class, 'store']);
            Route::post('read', [ServiceInternetsController::class, 'read']);
            Route::put('{id}', [ServiceInternetsController::class, 'update']);
        });

        //      Service Equipment
        Route::group(['prefix' => 'equipment'], function () {
            Route::post('/', [ServiceEquipmentController::class, 'store']);
            Route::post('list', [ServiceEquipmentController::class, 'list']);
        });
    });
