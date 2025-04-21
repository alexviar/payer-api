<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PlantController;
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

Route::controller(PlantController::class)->prefix('plants')->group(function () {
    Route::get('/', 'index')->middleware('auth:sanctum');
    Route::get('/{plant}', 'show')->middleware('auth:sanctum');
    Route::post('/', 'store')->middleware('auth:sanctum');
    Route::put('/{plant}', 'update')->middleware('auth:sanctum');
    Route::delete('/{plant}', 'destroy')->middleware('auth:sanctum');
});
