<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\management\general\DataController;

Route::prefix('v1/general')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::get('estados', [DataController::class, 'generalStatus']);
        Route::get('states', [DataController::class, 'statesList']);
        Route::get('municipalities', [DataController::class, 'municipalitiesList']);
        Route::get('state/{id}/municipalities', [DataController::class, 'municipalitiesByState']);
        Route::get('districts', [DataController::class, 'districtsList']);
        Route::get('countries', [DataController::class, 'countriesList']);
        Route::get('countries/phones', [DataController::class, 'countriesWithCode']);
        Route::get('municipality/{id}/districts', [DataController::class, 'districtsByMunicipality']);
        Route::get('genders', [DataController::class, 'gendersList']);
        Route::get('marital', [DataController::class, 'maritalStatusList']);
        Route::get('branches', [DataController::class, 'branchesList']);
        Route::get('contract/status', [DataController::class, 'contractStatusList']);

        Route::group(['prefix' => 'management'], function () {

            Route::group(['prefix' => 'roles'], function () {
                Route::get('/', [DataController::class, 'rolesList']);
                Route::get('{id}/permissions', [DataController::class, 'permissionsByRoleId']);
            });

            Route::group(['prefix' => 'users'], function () {
                Route::get('list', [DataController::class, 'usersList']);
                Route::get('technicians', [DataController::class, 'technicianList']);
            });
            Route::get('permissions', [DataController::class, 'permissionsList']);
        });

        Route::group(['prefix' => 'billing'], function () {
            Route::get('documents', [DataController::class, 'billingDocumentsList']);
            Route::get('activities', [DataController::class, 'billingActivitiesList']);
        });

        Route::group(['prefix' => 'client'], function () {
            Route::get('types', [DataController::class, 'clientTypesList']);
            Route::get('documents', [DataController::class, 'personalDocumentsList']);
            Route::get('phones', [DataController::class, 'phoneCategoriesList']);
            Route::get('references', [DataController::class, 'referencesList']);
        });

        Route::group(['prefix' => 'infrastructure'], function () {
            Route::get('servers', [DataController::class, 'authServersList']);
            Route::get('types', [DataController::class, 'equipmentTypesList']);
            Route::get('brands', [DataController::class, 'equipmentBrandsList']);
            Route::get('status', [DataController::class, 'equipmentStatusList']);
            Route::get('nodes', [DataController::class, 'nodesList']);
            Route::get('models', [DataController::class, 'modelsList']);
            Route::get('node/{id}/equipment', [DataController::class, 'equipmentByNode']);
        });

        Route::group(['prefix' => 'configuration'], function () {
            Route::get('menu', [DataController::class, 'menuList']);
        });

        Route::group(['prefix' => 'profiles'], function () {
            Route::get('internet', [DataController::class, 'internetProfilesList']);
        });
    });
