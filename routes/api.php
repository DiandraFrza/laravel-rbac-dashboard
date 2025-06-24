<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Route publik, tidak perlu login
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'check.status'])->group(function () {
    
    // Route untuk mendapatkan data user yang sedang login
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::apiResource('tasks', TaskController::class);

    Route::apiResource('users', UserController::class)->only(['index', 'store']);
});
