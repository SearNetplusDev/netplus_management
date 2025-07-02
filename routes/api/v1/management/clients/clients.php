<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\management\clients\ClientsController;

Route::prefix('v1/clients')->middleware(['auth:sanctum'])->group(function () {

    Route::post('/', [ClientsController::class, 'store']);
    Route::post('data', [ClientsController::class, 'data']);
    Route::post('edit', [ClientsController::class, 'edit']);
    Route::put('{id}', [ClientsController::class, 'update']);
});
