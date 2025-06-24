<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Route publik, tidak perlu login
Route::post('/login', [AuthController::class, 'login']);

// Grup route yang WAJIB login dan statusnya harus aktif
Route::middleware(['auth:sanctum', 'check.status'])->group(function () {
    
    // Route untuk mendapatkan data user yang sedang login
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Satu baris ini akan otomatis membuat semua route untuk CRUD Task:
    // GET    /tasks          -> TaskController@index
    // POST   /tasks          -> TaskController@store
    // GET    /tasks/{task}   -> TaskController@show
    // PUT    /tasks/{task}   -> TaskController@update
    // DELETE /tasks/{task}   -> TaskController@destroy
    Route::apiResource('tasks', TaskController::class);

    // Nanti route untuk User & Log Management juga bisa ditaruh di sini
});
