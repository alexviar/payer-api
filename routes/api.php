<?php

use App\Http\Controllers\AuthController;
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
