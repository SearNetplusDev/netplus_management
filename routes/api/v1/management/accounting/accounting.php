<?php

use App\Http\Controllers\v1\management\Accounting\AccountingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\management\Accounting\DTE\DTEController;

Route::prefix('v1/accounting')
    ->middleware(['auth:sanctum'])
    ->group(function () {

        Route::post('/client/invoices', [AccountingController::class, 'clientInvoices']);

        //  DTEs
        Route::group(['prefix' => 'dte'], function () {
            Route::post('create/{documentId}', [DTEController::class, 'store']);
        });

    });
