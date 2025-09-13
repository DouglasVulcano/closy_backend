<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

/**
 * @Autoload-V1
 *
 * Automatic load for api v1 routes,
 * do not need to use imports
 */
Route::prefix('v1')->name('api.v1.')->group(function () {
    collect(File::allFiles(__DIR__ . '/V1'))->each(
        fn(SplFileInfo $file) => Route::namespace("\\V1")->group($file->getPathname())
    );
});
