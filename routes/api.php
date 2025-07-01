<?php

use App\Http\Controllers\Api\V1\AuthController;
use Illuminate\Support\Facades\Route;

// Public routes (require API key but no user authentication)
Route::namespace('Api\V1')->group(function () {
    Route::group(['middleware' => 'api.token'], function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('register', [AuthController::class, 'register']);
    });
});

// Protected routes (require API key + user authentication)
Route::namespace('Api\V1')->group(function () {
    Route::group(['middleware' => 'api.token'], function () {
        Route::group(['middleware' => 'auth:sanctum'], function () {
            // Authentication routes
            Route::get('me', [AuthController::class, 'me']);
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('logout-all', [AuthController::class, 'logoutAll']);
            Route::post('refresh', [AuthController::class, 'refresh']);
            
            // User routes
            // Route::get('user', 'UserController@index');
            
            // Add more protected routes here
            // Route::apiResource('bugs', 'BugController');
            // Route::apiResource('projects', 'ProjectController');
        });
    });
});