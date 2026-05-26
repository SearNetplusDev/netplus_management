<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\management\dashboard\DashboardController;

Route::prefix('v1/dashboard')
    ->middleware(['auth:sanctum'])
    ->group(function () {
        Route::get('client-types', [DashboardController::class, 'clientsByType']);
    });
