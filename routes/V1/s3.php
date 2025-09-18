<?php

use App\Http\Controllers\S3Controller;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum', 'role:STARTER,PRO')->prefix('s3')->group(function () {
    Route::post('/presigned-url', [S3Controller::class, 'generatePresignedUrl']);
});
