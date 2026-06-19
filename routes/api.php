<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route publik (tidak perlu auth)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Route yang perlu auth + validasi IP
Route::middleware(['auth:sanctum', \App\Http\Middleware\ValidateTokenIp::class])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Modul 2 Routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/orders', [\App\Http\Controllers\OrderController::class, 'create']);
    Route::post('/orders/{id}/pay', [\App\Http\Controllers\OrderController::class, 'pay']);
    Route::post('/orders/{id}/ship', [\App\Http\Controllers\OrderController::class, 'ship']);
    Route::post('/orders/{id}/deliver', [\App\Http\Controllers\OrderController::class, 'deliver']);
    Route::post('/orders/{id}/request-refund', [\App\Http\Controllers\OrderController::class, 'requestRefund']);
    Route::post('/orders/{id}/approve-refund', [\App\Http\Controllers\OrderController::class, 'approveRefund']);
    Route::put('/orders/{id}/amount', [\App\Http\Controllers\OrderController::class, 'updateAmount']);
});