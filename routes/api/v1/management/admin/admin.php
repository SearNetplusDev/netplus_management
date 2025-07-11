<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\management\Admin\UsersController;

Route::prefix('v1/administracion')->middleware(['auth:sanctum'])->group(function () {
    Route::group(['prefix' => 'usuarios'], function () {
        Route::post('/', [UsersController::class, 'store']);
        Route::post('data', [UsersController::class, 'data']);
        Route::post('edit', [UsersController::class, 'edit']);
        Route::put('{id}', [UsersController::class, 'update']);
    });
});
