<?php

use App\Http\Controllers\v1\ArticleController;
use App\Http\Controllers\v1\AuthController;
use App\Http\Controllers\v1\ContactController;
use App\Http\Controllers\v1\NotificationController;
use App\Http\Controllers\v1\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // User routes
    Route::post('registerUser', [UserController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::post('changePassword', [UserController::class, 'changePassword'])->middleware('auth:sanctum');
    Route::post('forgotPassword', [AuthController::class, 'forgotPassword']);

    // Search routes
    Route::get('/search', [ArticleController::class, 'search']);

    //Contact routes
    Route::post('contact', [ContactController::class, 'store']);

    // User authenticated routes
    Route::prefix('user')->middleware('auth:sanctum')->group(function () {
        Route::post('notifications', [NotificationController::class, 'update']);
        Route::get('notifications', [NotificationController::class, 'show']);
    });

    // Articles routes
    Route::prefix('articles')->group(function () {
        Route::get('/', [ArticleController::class, 'index']);
        Route::get('{slug}', [ArticleController::class, 'show']);
        Route::get('{slug}/history', [ArticleController::class, 'history']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/', [ArticleController::class, 'save']);
            Route::put('update/{slug}', [ArticleController::class, 'update']);
        });
    });

});
