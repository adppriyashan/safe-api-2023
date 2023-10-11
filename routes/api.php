<?php

use App\Http\Controllers\API\DistrictController;
use App\Http\Controllers\API\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('/raw')->group(function () {
    Route::post('/districts', [DistrictController::class, 'getDistricts']);
});

Route::prefix('/auth')->group(function () {
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'login']);
    Route::post('/sent-otp', [UserController::class, 'sendOtp']);
    Route::post('/reset', [UserController::class, 'resetPassword']);
});

Route::prefix('/authenticated')->middleware('auth:sanctum')->group(function () {
    Route::prefix('/user')->group(function () {
        Route::post('/get-data', [UserController::class, 'getData']);
    });
});
