<?php

use App\Http\Controllers\PlanController;
use Illuminate\Support\Facades\Route;

/**
 * Plans routes
 */
Route::prefix('plans')->name('plans.')->group(function () {
    Route::get('/', [PlanController::class, 'index'])->name('index');
    Route::get('/{id}', [PlanController::class, 'show'])->name('show');
});