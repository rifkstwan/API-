<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EconomicIndicatorController;
use App\Http\Controllers\InterestRateController;
use App\Http\Controllers\MarketIndicatorController;
use App\Http\Controllers\CustomReportController;

// Public route - Health check (tidak perlu auth)
Route::get('/health', function () {
    return response()->json([
        'status' => 'OK',
        'service' => 'Economic Data API',
        'version' => '1.0.0',
        'timestamp' => now()->toIso8601String(),
        'authentication' => 'OAuth2 Client Credentials (Passport)',
    ]);
});

// Protected routes dengan Laravel Passport Client Credentials
// Menggunakan middleware 'client' bawaan Passport
Route::middleware('client')->group(function () {

    // Endpoint 1: Economic Indicators
    Route::get('/economic-indicators', [EconomicIndicatorController::class, 'index']);

    // Endpoint 2: Interest Rates
    Route::get('/interest-rates', [InterestRateController::class, 'index']);

    // Endpoint 3: Market Indicators
    Route::get('/market-indicators', [MarketIndicatorController::class, 'index']);

    // Endpoint 4: Custom Report
    Route::post('/custom-report', [CustomReportController::class, 'generate']);
    Route::get('/custom-report/available-indicators', [CustomReportController::class, 'availableIndicators']);
});
