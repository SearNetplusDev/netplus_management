<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\management\general\DataController;

Route::prefix('v1/general')->middleware('auth:sanctum')->group(function () {
    Route::get('estados', [DataController::class, 'generalStatus']);
    Route::get('states', [DataController::class, 'statesList']);
    Route::get('municipalities', [DataController::class, 'municipalitiesList']);
    Route::get('state/{id}/municipalities', [DataController::class, 'municipalitiesByState']);
    Route::get('districts', [DataController::class, 'districtsList']);
    Route::get('countries', [DataController::class, 'countriesList']);
    Route::get('municipality/{id}/districts', [DataController::class, 'districtsByMunicipality']);
    Route::get('genders', [DataController::class, 'gendersList']);
    Route::get('marital', [DataController::class, 'maritalStatusList']);
    Route::get('branches', [DataController::class, 'branchesList']);
    Route::get('client/types', [DataController::class, 'clientTypesList']);
    Route::get('client/documents', [DataController::class, 'personalDocumentsList']);
    Route::get('client/phones', [DataController::class, 'phoneCategoriesList']);
    Route::get('client/references', [DataController::class, 'referencesList']);
    Route::get('billing/documents', [DataController::class, 'billingDocumentsList']);
    Route::get('billing/activities', [DataController::class, 'billingActivitiesList']);
    Route::get('contract/status', [DataController::class, 'contractStatusList']);
    Route::get('infrastructure/servers', [DataController::class, 'authServersList']);
});
