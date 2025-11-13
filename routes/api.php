<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\StatsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Authentication routes (public)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    // Logout requires authentication
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('jwt.auth');
});

// Protected routes (require JWT authentication)
Route::middleware('jwt.auth')->group(function () {
    // User management routes
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);

    // Statistics routes
    Route::prefix('stats')->group(function () {
        Route::get('/daily', [StatsController::class, 'daily']);
        Route::get('/weekly', [StatsController::class, 'weekly']);
        Route::get('/monthly', [StatsController::class, 'monthly']);
    });
});
