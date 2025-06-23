<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\management\configuration\ConfigurationController;
use App\Http\Controllers\v1\management\configuration\menu\MenuController;

Route::prefix('v1/configuration')->middleware(['auth:sanctum'])->group(function () {
    Route::group(['prefix' => 'menu'], function () {
        Route::get('/', [ConfigurationController::class, 'getMenu']);
        Route::post('/', [MenuController::class, 'store']);
        Route::post('data', [MenuController::class, 'data']);
        Route::post('edit', [MenuController::class, 'edit']);
        Route::put('{id}', [MenuController::class, 'update']);
        Route::get('parents', [MenuController::class, 'getParents']);
    });
});
