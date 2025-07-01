<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Add a login route for web authentication (if needed)
Route::get('/login', function () {
    return redirect('/');
})->name('login');
