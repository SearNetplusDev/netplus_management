<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\management\general\DataController;

Route::prefix('v1/general')->middleware('auth:sanctum')->group(function () {
    Route::get('estados', [DataController::class, 'generalStatus']);
    Route::get('states', [DataController::class, 'statesList']);
    Route::get('municipalities', [DataController::class, 'municipalitiesList']);
    Route::get('state/{id}/municipalities', [DataController::class, 'municipalitiesByState']);
});
