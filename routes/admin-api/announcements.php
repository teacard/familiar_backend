<?php

use App\Http\Controllers\AdminApi\AnnouncementController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:admin')->controller(AnnouncementController::class)->prefix('announcements')->group(function () {
    Route::get('/', 'index');
    Route::post('/', 'store');
    Route::get('/{id}', 'show');
    Route::put('/{id}', 'update');
    Route::delete('/{id}', 'destroy');
});
