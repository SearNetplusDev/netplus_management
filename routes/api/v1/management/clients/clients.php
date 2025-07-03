<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\management\clients\ClientsController;
use App\Http\Controllers\v1\management\clients\DocumentsController;

Route::prefix('v1/clients')->middleware(['auth:sanctum'])->group(function () {

    Route::post('/', [ClientsController::class, 'store']);
    Route::post('data', [ClientsController::class, 'data']);
    Route::post('edit', [ClientsController::class, 'edit']);
    Route::put('{id}', [ClientsController::class, 'update']);

    //      PERSONAL DOCUMENTS
    Route::group(['prefix' => 'documents'], function () {
        Route::post('/', [DocumentsController::class, 'store']);
        Route::post('data', [DocumentsController::class, 'data']);
        Route::post('edit', [DocumentsController::class, 'edit']);
        Route::put('{id}', [DocumentsController::class, 'update']);
    });
});
