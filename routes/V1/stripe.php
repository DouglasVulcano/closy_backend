<?php

use App\Http\Controllers\StripeController;
use Illuminate\Support\Facades\Route;

/**
 * Stripe routes
 */
Route::prefix('stripe')->name('stripe.')->group(function () {
    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/checkout', [StripeController::class, 'checkout'])->name('checkout');
        Route::get('/portal', [StripeController::class, 'portal'])->name('portal');
    });
    
    // Webhook route (no auth required)
    Route::post('/webhook', [StripeController::class, 'webhook'])->name('webhook');
});