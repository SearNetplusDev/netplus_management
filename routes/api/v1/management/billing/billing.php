<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\management\billing\options\DocumentController;
use App\Http\Controllers\v1\management\billing\options\ActivitiesController;

Route::prefix('v1/billing')
    ->middleware(['auth:sanctum'])
    ->group(function () {

        //      Options
        Route::group(['prefix' => 'options'], function () {

            //      Document Types
            Route::group(['prefix' => 'documents'], function () {
                Route::post('/', [DocumentController::class, 'store']);
                Route::post('/data', [DocumentController::class, 'data']);
                Route::post('/edit', [DocumentController::class, 'edit']);
                Route::put('{id}', [DocumentController::class, 'update']);
            });

            //      Activities
            Route::group(['prefix' => 'activities'], function () {
                Route::post('/', [ActivitiesController::class, 'store']);
                Route::post('/data', [ActivitiesController::class, 'data']);
                Route::post('/edit', [ActivitiesController::class, 'edit']);
                Route::put('{id}', [ActivitiesController::class, 'update']);
            });
        });
    });
