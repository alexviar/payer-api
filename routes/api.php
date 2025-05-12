<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CustomAttributeController;
use App\Http\Controllers\DefectController;
use App\Http\Controllers\DefectInstanceController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\InspectionLotController;
use App\Http\Controllers\PlantController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReworkController;
use App\Http\Controllers\SalesAgentController;
use App\Http\Controllers\ReworkInstanceController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::controller(AuthController::class)->prefix('auth')->group(function () {
    Route::post('login', 'login');
    Route::post('logout', 'logout')->middleware('auth:sanctum');
    Route::post('change-password', 'changePassword')->middleware('auth:sanctum');
    Route::post('reset-password', 'resetPassword')->name('password.reset');
    Route::post('forgot-password', 'forgotPassword');
});

Route::controller(UserController::class)->prefix('users')->group(function () {
    Route::get('/', 'index')->middleware('auth:sanctum');
    Route::get('/{user}', 'show')->middleware('auth:sanctum');
    Route::post('/', 'store')->middleware('auth:sanctum');
    Route::patch('/{user}', 'update')->middleware('auth:sanctum');
    Route::delete('/{user}', 'destroy')->middleware('auth:sanctum');
});

Route::controller(PlantController::class)->prefix('plants')->group(function () {
    Route::get('/', 'index')->middleware('auth:sanctum');
    Route::get('/{plant}', 'show')->middleware('auth:sanctum');
    Route::post('/', 'store')->middleware('auth:sanctum');
    Route::patch('/{plant}', 'update')->middleware('auth:sanctum');
    Route::delete('/{plant}', 'destroy')->middleware('auth:sanctum');
});

Route::controller(ClientController::class)->prefix('clients')->group(function () {
    Route::get('/', 'index')->middleware('auth:sanctum');
    Route::get('/{client}', 'show')->middleware('auth:sanctum');
    Route::post('/', 'store')->middleware('auth:sanctum');
    Route::patch('/{client}', 'update')->middleware('auth:sanctum');
    Route::delete('/{client}', 'destroy')->middleware('auth:sanctum');
});

Route::controller(CustomAttributeController::class)->prefix('custom-attributes')->group(function () {
    Route::get('/', 'index')->middleware('auth:sanctum');
    Route::get('/{customAttribute}', 'show')->middleware('auth:sanctum');
    Route::post('/', 'store')->middleware('auth:sanctum');
    Route::patch('/{customAttribute}', 'update')->middleware('auth:sanctum');
    Route::delete('/{customAttribute}', 'destroy')->middleware('auth:sanctum');
});

Route::controller(ProductController::class)->prefix('products')->group(function () {
    Route::get('/', 'index')->middleware('auth:sanctum');
    Route::get('/{product}', 'show')->middleware('auth:sanctum');
    Route::post('/', 'store')->middleware('auth:sanctum');
    Route::patch('/{product}', 'update')->middleware('auth:sanctum');
    Route::delete('/{product}', 'destroy')->middleware('auth:sanctum');
});

Route::controller(DefectController::class)->prefix('defects')->group(function () {
    Route::get('/', 'index')->middleware('auth:sanctum');
    Route::get('/{defect}', 'show')->middleware('auth:sanctum');
    Route::post('/', 'store')->middleware('auth:sanctum');
    Route::patch('/{defect}', 'update')->middleware('auth:sanctum');
    Route::delete('/{defect}', 'destroy')->middleware('auth:sanctum');
});

Route::controller(ReworkController::class)->prefix('reworks')->group(function () {
    Route::get('/', 'index')->middleware('auth:sanctum');
    Route::get('/{rework}', 'show')->middleware('auth:sanctum');
    Route::post('/', 'store')->middleware('auth:sanctum');
    Route::patch('/{rework}', 'update')->middleware('auth:sanctum');
    Route::delete('/{rework}', 'destroy')->middleware('auth:sanctum');
});

Route::controller(SalesAgentController::class)->prefix('sales-agents')->group(function () {
    Route::get('/', 'index')->middleware('auth:sanctum');
    Route::get('/{salesAgent}', 'show')->middleware('auth:sanctum');
    Route::post('/', 'store')->middleware('auth:sanctum');
    Route::patch('/{salesAgent}', 'update')->middleware('auth:sanctum');
    Route::delete('/{salesAgent}', 'destroy')->middleware('auth:sanctum');
});

Route::controller(InspectionController::class)->prefix('inspections')->group(function () {
    Route::get('/', 'index')->middleware('auth:sanctum');
    Route::get('/{inspection}', 'show')->middleware('auth:sanctum');
    Route::post('/', 'store')->middleware('auth:sanctum');
    Route::patch('/{inspection}', 'update')->middleware('auth:sanctum');
    Route::delete('/{inspection}', 'destroy')->middleware('auth:sanctum');

    Route::controller(InspectionLotController::class)->prefix('{inspection}/lots')->group(function () {
        Route::get('/', 'index')->middleware('auth:sanctum');
        Route::get('/{inspectionLot}', 'show')->middleware('auth:sanctum');
        Route::post('/', 'store')->middleware('auth:sanctum');
        Route::patch('/{inspectionLot}', 'update')->middleware('auth:sanctum');
        Route::delete('/{inspectionLot}', 'destroy')->middleware('auth:sanctum');

        Route::controller(DefectInstanceController::class)->prefix('{inspectionLot}/defects')->group(function () {
            Route::get('/', 'index')->middleware('auth:sanctum');
            Route::get('/{defect}', 'show')->middleware('auth:sanctum');
            Route::post('/', 'store')->middleware('auth:sanctum');
            Route::patch('/{defect}', 'update')->middleware('auth:sanctum');
            Route::delete('/{defect}', 'destroy')->middleware('auth:sanctum');
        });

        Route::controller(ReworkInstanceController::class)->prefix('{inspectionLot}/reworks')->group(function () {
            Route::get('/', 'index')->middleware('auth:sanctum');
            Route::get('/{rework}', 'show')->middleware('auth:sanctum');
            Route::post('/', 'store')->middleware('auth:sanctum');
            Route::patch('/{rework}', 'update')->middleware('auth:sanctum');
            Route::delete('/{rework}', 'destroy')->middleware('auth:sanctum');
        });
    });
});
