<?php

use App\Http\Controllers\MessageTemplateController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('message-template')->group(function () {
    Route::get('/', [MessageTemplateController::class, 'index']);
    Route::post('/', [MessageTemplateController::class, 'store']);
    Route::get('/{id}', [MessageTemplateController::class, 'show']);
    Route::put('/{id}', [MessageTemplateController::class, 'update']);
    Route::delete('/{id}', [MessageTemplateController::class, 'destroy']);
});
