<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\management\configuration\ConfigurationController;
use App\Http\Controllers\v1\management\configuration\menu\MenuController;
use App\Http\Controllers\v1\management\configuration\geography\CountriesController;
use App\Http\Controllers\v1\management\configuration\geography\StatesController;
use App\Http\Controllers\v1\management\configuration\geography\MunicipalitiesController;

Route::prefix('v1/configuration')->middleware(['auth:sanctum'])->group(function () {
    //      MENU
    Route::group(['prefix' => 'menu'], function () {
        Route::get('/', [ConfigurationController::class, 'getMenu']);
        Route::post('/', [MenuController::class, 'store']);
        Route::post('data', [MenuController::class, 'data']);
        Route::post('edit', [MenuController::class, 'edit']);
        Route::put('{id}', [MenuController::class, 'update']);
        Route::get('parents', [MenuController::class, 'getParents']);
    });

    //      Countries, States, Cities
    Route::group(['prefix' => 'geography'], function () {

        //      Countries
        Route::group(['prefix' => 'countries'], function () {
            Route::post('/', [CountriesController::class, 'create']);
            Route::post('data', [CountriesController::class, 'data']);
            Route::post('edit', [CountriesController::class, 'edit']);
            Route::put('{id}', [CountriesController::class, 'update']);
        });

        //      States
        Route::group(['prefix' => 'states'], function () {
            Route::post('/', [StatesController::class, 'create']);
            Route::post('data', [StatesController::class, 'data']);
            Route::post('edit', [StatesController::class, 'edit']);
            Route::put('{id}', [StatesController::class, 'update']);
        });

        //      Municipalities
        Route::group(['prefix' => 'municipalities'], function () {
            Route::post('/', [MunicipalitiesController::class, 'store']);
            Route::post('data', [MunicipalitiesController::class, 'data']);
            Route::post('edit', [MunicipalitiesController::class, 'edit']);
            Route::put('{id}', [MunicipalitiesController::class, 'update']);
        });
    });
});
