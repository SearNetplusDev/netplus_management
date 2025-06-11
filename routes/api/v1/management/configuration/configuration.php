<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\management\configuration\ConfigurationController;

Route::group(['prefix' => 'v1/configuration'], function () {
    Route::get('menu', [ConfigurationController::class, 'getMenu']);
})->middleware(['auth:sanctum']);
