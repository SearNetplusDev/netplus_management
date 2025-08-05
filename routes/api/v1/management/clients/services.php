<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\management\services\ServicesController;

Route::prefix('v1/services')
    ->middleware(['auth:sanctum'])
    ->group(function () {
        //  General Data
        Route::post('data', [ServicesController::class, 'data']);
    });
