<?php

use App\Http\Controllers\CampaignsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('campaign')->group(function () {
    Route::get('/', [CampaignsController::class, 'index']);
    Route::post('/', [CampaignsController::class, 'store']);
    Route::get('/{id}', [CampaignsController::class, 'show']);
    Route::put('/{id}', [CampaignsController::class, 'update']);
    Route::delete('/{id}', [CampaignsController::class, 'destroy']);
});
