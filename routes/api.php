<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CheckHistoryController;
use App\Http\Controllers\DomainController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn(Request $request) => $request->user());
    Route::put('/user', [AuthController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('domains', DomainController::class);
    Route::get('domains/{domain}/history', [CheckHistoryController::class, 'index']);
});
