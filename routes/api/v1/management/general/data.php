<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\management\general\DataController;

Route::prefix('v1/general')->middleware('auth:sanctum')->group(function () {
    Route::get('estados', [DataController::class, 'generalStatus']);
});
