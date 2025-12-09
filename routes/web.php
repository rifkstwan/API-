<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Dummy login route untuk API (tidak digunakan, tapi menghindari error)
Route::get('/login', function () {
    return response()->json([
        'message' => 'This is an API-only application. Please use /oauth/token to get access token.',
        'documentation' => 'See README for API usage'
    ], 401);
})->name('login');
