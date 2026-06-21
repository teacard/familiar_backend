<?php

use App\Http\Controllers\AdminApi\RoleController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:admin')->controller(RoleController::class)->prefix('roles')->group(function () {
    Route::get('/', 'index');
    Route::post('/', 'store');
    Route::get('/select', 'select'); // 須宣告於 /{id} 之前，避免 select 被當成 id
    Route::get('/{id}', 'show');
    Route::put('/{id}', 'update');
    Route::delete('/{id}', 'destroy');
});
