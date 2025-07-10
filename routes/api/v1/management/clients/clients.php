<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\management\clients\ClientsController;
use App\Http\Controllers\v1\management\clients\DocumentsController;
use App\Http\Controllers\v1\management\clients\PhonesController;
use App\Http\Controllers\v1\management\clients\EmailsController;
use App\Http\Controllers\v1\management\clients\AddressesController;
use App\Http\Controllers\v1\management\clients\ReferencesController;
use App\Http\Controllers\v1\management\clients\FinancialInformationController;

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

    //      PHONES
    Route::group(['prefix' => 'phones'], function () {
        Route::post('/', [PhonesController::class, 'store']);
        Route::post('data', [PhonesController::class, 'data']);
        Route::post('edit', [PhonesController::class, 'edit']);
        Route::put('{id}', [PhonesController::class, 'update']);
    });

    //      EMAILS
    Route::group(['prefix' => 'emails'], function () {
        Route::post('/', [EmailsController::class, 'store']);
        Route::post('data', [EmailsController::class, 'data']);
        Route::post('edit', [EmailsController::class, 'edit']);
        Route::put('{id}', [EmailsController::class, 'update']);
    });

    //      ADDRESSES
    Route::group(['prefix' => 'addresses'], function () {
        Route::post('/', [AddressesController::class, 'store']);
        Route::post('data', [AddressesController::class, 'data']);
        Route::post('edit', [AddressesController::class, 'edit']);
        Route::put('{id}', [AddressesController::class, 'update']);
    });

    //      REFERENCES
    Route::group(['prefix' => 'references'], function () {
        Route::post('/', [ReferencesController::class, 'store']);
        Route::post('data', [ReferencesController::class, 'data']);
        Route::post('edit', [ReferencesController::class, 'edit']);
        Route::put('{id}', [ReferencesController::class, 'update']);
    });

    //      Financial Information
    Route::group(['prefix' => 'financial/information'], function () {
        Route::post('/', [FinancialInformationController::class, 'store']);
        Route::post('data', [FinancialInformationController::class, 'data']);
        Route::post('edit', [FinancialInformationController::class, 'edit']);
        Route::put('{id}', [FinancialInformationController::class, 'update']);
    });
});
