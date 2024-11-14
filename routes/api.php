<?php

use App\Http\Controllers\v1\AuthController;
use App\Http\Controllers\v1\ContactController;
use App\Http\Controllers\v1\NotificationController;
use App\Http\Controllers\v1\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // User routes
    Route::post('register', [UserController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::post('changePassword', [UserController::class, 'changePassword'])->middleware('auth:sanctum');
    Route::post('forgotPassword', [AuthController::class, 'forgotPassword']);

    //Contact routes
    Route::post('contact', [ContactController::class, 'store']);

    // Authentication routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('user')->group(function () {
            Route::post('notificationSave', [NotificationController::class, 'store']);
            Route::post('notificationUpdate', [NotificationController::class, 'update']);
            Route::get('notificationShow', [NotificationController::class, 'show']);
        });
    });

});