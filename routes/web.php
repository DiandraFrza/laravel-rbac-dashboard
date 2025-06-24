<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', function () {
    return File::get(public_path('frontend/index.html'));
})->name('login');

Route::get('/dashboard', function () {
    return File::get(public_path('frontend/dashboard.html'));
});

Route::get('/dashboard/admin', function () {
    return File::get(public_path('frontend/admin.html'));
});