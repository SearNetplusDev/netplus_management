<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\management\Accounting\DTE\DTEController;

Route::prefix('v1/accounting')
    ->middleware(['auth:sanctum'])
    ->group(function () {

        //  DTEs
        Route::group(['prefix' => 'dte'], function () {
            Route::post('create/{documentId}/{paymentId?}', [DTEController::class, 'store']);
        });
    });
