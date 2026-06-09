<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();``
})->middleware('auth:sanctum');

// routes/api.php
Route::post('/location', [LocationController::class, 'store']);
Route::get('/devices/latest', [LocationController::class, 'latest']);
Route::get('/dashboard', [DashboardController::class, 'index']);
