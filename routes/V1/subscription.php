<?php

use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

/**
 * Subscriptions routes
 */
Route::middleware('auth:sanctum', 'role:STARTER,PRO')->prefix('subscriptions')->name('subscriptions.')->group(function () {
    Route::get('/current', [SubscriptionController::class, 'current'])->name('current');
    Route::post('/cancel', [SubscriptionController::class, 'cancel'])->name('cancel');
    Route::post('/resume', [SubscriptionController::class, 'resume'])->name('resume');
});
