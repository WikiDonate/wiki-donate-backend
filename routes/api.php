<?php

use App\Http\Controllers\v1\ArticleController;
use App\Http\Controllers\v1\AuthController;
use App\Http\Controllers\v1\ContactController;
use App\Http\Controllers\v1\NotificationController;
use App\Http\Controllers\v1\SectionController;
use App\Http\Controllers\v1\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // User routes
    Route::post('register', [UserController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::post('changePassword', [UserController::class, 'changePassword'])->middleware('auth:sanctum');
    Route::post('forgotPassword', [AuthController::class, 'forgotPassword']);

    // Search routes
    Route::get('/search', [ArticleController::class, 'search']);

    //Contact routes
    Route::post('contact', [ContactController::class, 'store']);

    Route::middleware('auth:sanctum')->group(function () {
        // User routes
        Route::prefix('user')->group(function () {
            Route::post('notifications', [NotificationController::class, 'update']);
            Route::get('notifications', [NotificationController::class, 'show']);
        });

        // Articles routes
        Route::prefix('articles')->group(function () {
            Route::get('/', [ArticleController::class, 'index']);
            Route::get('article/{slug}', [ArticleController::class, 'show']);
            Route::post('article', [ArticleController::class, 'store']);
            Route::get('section', [ArticleController::class, 'getSection']);
            Route::get('versions', [ArticleController::class, 'getVersions']);

            // Section routes
            Route::prefix('section')->group(function () {
                Route::get('{uuid}', [SectionController::class, 'show']);
                Route::put('{uuid}', [SectionController::class, 'update']);
            });
        });

    });

});
