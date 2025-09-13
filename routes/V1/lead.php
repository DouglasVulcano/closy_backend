<?php

use App\Http\Controllers\LeadController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('lead')->group(function () {
    Route::get('/', [LeadController::class, 'index']);
    Route::get('/{id}', [LeadController::class, 'show']);
    Route::put('/{id}/status', [LeadController::class, 'updateStatus']);
    Route::delete('/{id}', [LeadController::class, 'destroy']);
});

Route::prefix('public')->group(function () {
    Route::post('/lead/{campaign_slug}', [LeadController::class, 'storePublic']);
});
