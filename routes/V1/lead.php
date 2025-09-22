<?php

use App\Http\Controllers\LeadController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum', 'role:STARTER,PRO')->prefix('leads')->group(function () {
    Route::get('/statistics', [LeadController::class, 'statistics']);
    Route::get('/', [LeadController::class, 'index']);
    Route::get('/{id}', [LeadController::class, 'show']);
    Route::put('/{id}/status', [LeadController::class, 'updateStatus']);
    Route::delete('/{id}', [LeadController::class, 'destroy']);
});

Route::prefix('public')->middleware('lead.limit')->group(function () {
    Route::post('/lead/{campaign_slug}', [LeadController::class, 'storePublic']);
});
