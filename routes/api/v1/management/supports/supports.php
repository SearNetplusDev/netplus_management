<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\management\supports\StatusController;

Route::prefix('v1/supports')
    ->middleware(['auth:sanctum'])
    ->group(function () {

        //  Status
        Route::group(['prefix' => 'status'], function () {
            Route::post('/', [StatusController::class, 'store']);
            Route::post('data', [StatusController::class, 'data']);
            Route::post('edit', [StatusController::class, 'read']);
            Route::put('{id}', [StatusController::class, 'update']);
        });
    });
