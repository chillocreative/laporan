<?php

use Illuminate\Support\Facades\Route;

// Named login route for authentication redirects
Route::get('/login', function () {
    return view('app');
})->name('login');

// SPA catch-all — serve the Vue app for all non-API routes
Route::get('/{any?}', function () {
    return view('app');
})->where('any', '.*');
