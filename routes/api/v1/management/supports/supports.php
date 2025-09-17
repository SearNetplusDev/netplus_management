<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\management\supports\StatusController;
use App\Http\Controllers\v1\management\supports\TypesController;
use App\Http\Controllers\v1\management\supports\SupportsController;

Route::prefix('v1/supports')
    ->middleware(['auth:sanctum'])
    ->group(function () {
        Route::post('data', [SupportsController::class, 'data']);
        Route::post('/', [SupportsController::class, 'store']);
        Route::post('edit', [SupportsController::class, 'read']);

        //  Types
        Route::group(['prefix' => 'types'], function () {
            Route::post('/', [TypesController::class, 'store']);
            Route::post('data', [TypesController::class, 'data']);
            Route::post('edit', [TypesController::class, 'read']);
            Route::put('{id}', [TypesController::class, 'update']);
        });

        //  Status
        Route::group(['prefix' => 'status'], function () {
            Route::post('/', [StatusController::class, 'store']);
            Route::post('data', [StatusController::class, 'data']);
            Route::post('edit', [StatusController::class, 'read']);
            Route::put('{id}', [StatusController::class, 'update']);
        });
    });
