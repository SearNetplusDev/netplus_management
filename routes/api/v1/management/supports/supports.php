<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\management\supports\PriceController;
use App\Http\Controllers\v1\management\supports\StatusController;
use App\Http\Controllers\v1\management\supports\SupportsController;
use App\Http\Controllers\v1\management\supports\SurveyController;
use App\Http\Controllers\v1\management\supports\TypesController;

Route::prefix('v1/supports')
    ->middleware(['auth:sanctum'])
    ->group(function () {
        Route::post('data', [SupportsController::class, 'data']);
        Route::post('/', [SupportsController::class, 'store']);
        Route::post('edit', [SupportsController::class, 'read']);
        Route::put('{id}', [SupportsController::class, 'update']);
        Route::get('print/{id}', [SupportsController::class, 'print']);
        Route::post('logs', [SupportsController::class, 'logs']);

        //  Surveys
        Route::group(['prefix' => 'survey'], function () {
            Route::post('/', [SurveyController::class, 'store']);
            Route::post('show', [SurveyController::class, 'read']);
            Route::put('{id}', [SurveyController::class, 'update']);
        });


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

        //  Prices
        Route::group(['prefix' => 'prices'], function () {
            Route::post('/', [PriceController::class, 'store']);
            Route::post('data', [PriceController::class, 'data']);
            Route::post('edit', [PriceController::class, 'read']);
            Route::put('{id}', [PriceController::class, 'update']);
            Route::get('catalog', [PriceController::class, 'catalog']);
        });
    });
