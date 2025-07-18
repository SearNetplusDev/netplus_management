<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\management\infrastructure\network\AuthServersController;
use App\Http\Controllers\v1\management\infrastructure\network\NodesController;

Route::prefix('v1/infrastructure')
    ->middleware(['auth:sanctum'])
    ->group(function () {

        //      Network
        Route::group(['prefix' => 'network'], function () {
            //      Auth Server
            Route::group(['prefix' => 'servers'], function () {
                Route::post('/', [AuthServersController::class, 'store']);
                Route::post('data', [AuthServersController::class, 'data']);
                Route::post('edit', [AuthServersController::class, 'edit']);
                Route::put('{id}', [AuthServersController::class, 'update']);
            });

            //      Nodes
            Route::group(['prefix' => 'nodes'], function () {
                Route::post('/', [NodesController::class, 'store']);
                Route::post('data', [NodesController::class, 'data']);
                Route::post('edit', [NodesController::class, 'edit']);
                Route::put('{id}', [NodesController::class, 'update']);
            });
        });
    });
