<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\VoucherController;
use Illuminate\Support\Facades\Route;

// Auth routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');

// Order routes
Route::middleware('auth')->group(function () {
    Route::post('/orders', [OrderController::class, 'create']);
    Route::post('/orders/{id}/pay', [OrderController::class, 'pay']);
    Route::post('/orders/{id}/ship', [OrderController::class, 'ship']);
    Route::post('/orders/{id}/deliver', [OrderController::class, 'deliver']);
    Route::post('/orders/{id}/request-refund', [OrderController::class, 'requestRefund']);
    Route::post('/orders/{id}/approve-refund', [OrderController::class, 'approveRefund']);
    Route::put('/orders/{id}/amount', [OrderController::class, 'updateAmount']);
});

// Wallet routes
Route::middleware('auth')->group(function () {
    Route::post('/wallets/deposit', [WalletController::class, 'deposit']);
    Route::post('/wallets/withdraw', [WalletController::class, 'withdraw']);
    Route::post('/wallets/transfer', [WalletController::class, 'transfer']);
    Route::get('/wallets/{id}/balance', [WalletController::class, 'getBalance']);
});

// Voucher routes
Route::middleware('auth')->group(function () {
    Route::post('/vouchers/redeem', [VoucherController::class, 'redeem']);
    Route::post('/vouchers/apply', [VoucherController::class, 'apply']);
    Route::post('/vouchers', [VoucherController::class, 'create']);
});

// Token routes
Route::middleware('auth')->group(function () {
    Route::post('/token/refresh', [AuthController::class, 'refreshToken']);
    Route::post('/token/revoke', [AuthController::class, 'revokeToken']);
    Route::get('/tokens', [AuthController::class, 'listTokens']);
});
