<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\management\billing\options\DocumentController;

Route::prefix('v1/billing')->middleware(['auth:sanctum'])->group(function () {

    //      Options
    Route::group(['prefix' => 'options'], function () {

        //      Document Types
        Route::group(['prefix' => 'documents'], function () {
            Route::post('/', [DocumentController::class, 'store']);
            Route::post('/data', [DocumentController::class, 'data']);
            Route::post('/edit', [DocumentController::class, 'edit']);
            Route::put('{id}', [DocumentController::class, 'update']);
        });
    });
});
