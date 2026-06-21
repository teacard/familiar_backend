<?php

use App\Http\Controllers\AdminApi\MediaController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:admin')->controller(MediaController::class)->prefix('media')->group(function () {
    Route::post('/', 'store');
});
