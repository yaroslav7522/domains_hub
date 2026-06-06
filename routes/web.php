<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/login', function () {
    return Inertia::render('Auth/Login');
})->name('login');

Route::get('/dashboard', function () {
     return Inertia::render('Dashboard');
})->middleware('auth:sanctum')->name('dashboard');

Route::get('{any?}', function() {
    return view('app');
})->where('any', '.*');