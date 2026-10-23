<?php

use App\Http\Controllers\PolicyController;
use Illuminate\Support\Facades\Route;

// PolicyFlow Basic REST API Endpoints
Route::post('/policy-requests', [PolicyController::class, 'store']);
Route::get('/policy-requests', [PolicyController::class, 'index']);
Route::get('/policy-requests/{id}', [PolicyController::class, 'show']);
Route::post('/policy-requests/{id}/submit', [PolicyController::class, 'submit']);
Route::post('/mock-insurer/callback', [PolicyController::class, 'callback']);
