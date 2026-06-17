<?php

use App\Http\Controllers\AdminApi\RoleController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:admin')->controller(RoleController::class)->prefix('roles')->group(function () {
    Route::get('/', 'index');
    Route::post('/', 'store');
    Route::get('/{id}', 'show');
    Route::put('/{id}', 'update');
    Route::delete('/{id}', 'destroy');
});
