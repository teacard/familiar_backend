<?php

use App\Http\Controllers\AdminApi\PlayerController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:admin')->controller(PlayerController::class)->prefix('players')->group(function () {
    Route::get('/', 'index');
    Route::post('/', 'store');
    Route::get('/{id}', 'show');
    Route::put('/{id}', 'update');
    Route::delete('/{id}', 'destroy');
});
