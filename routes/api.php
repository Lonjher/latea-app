<?php

use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\MobileProductController;
use App\Http\Controllers\Api\MobileSaleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile API Routes (Cashier App)
|--------------------------------------------------------------------------
*/
Route::prefix('mobile')->group(function () {
    // ── Public ──
    Route::post('/login', [MobileAuthController::class, 'login']);

    // ── Protected (butuh Bearer Token) ──
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me',      [MobileAuthController::class, 'me']);
        Route::post('/logout', [MobileAuthController::class, 'logout']);

        Route::get('/products', [MobileProductController::class, 'index']);

        Route::get('/sales',      [MobileSaleController::class, 'index']);
        Route::get('/sales/product-summary',  [MobileSaleController::class, 'productSummary']);
        Route::post('/sales',     [MobileSaleController::class, 'store']);
        Route::get('/sales/{sale}', [MobileSaleController::class, 'show']);
    });
});
