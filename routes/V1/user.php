<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('user')->group(function () {
    Route::get('/', [UserController::class, 'profile']);
    Route::put('/', [UserController::class, 'updateProfile']);
    Route::delete('/', [UserController::class, 'deleteAccount']);
});
