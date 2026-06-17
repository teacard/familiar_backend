<?php

use App\Http\Controllers\AdminApi\AdminController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:admin')->controller(AdminController::class)->prefix('admin')->group(function () {
    Route::get('/', 'index');
    Route::post('/', 'store');
    Route::get('/{id}', 'show');
    Route::put('/{id}', 'update');
    Route::patch('/{id}/status', 'updateStatus');
    Route::delete('/{id}', 'destroy');
});
