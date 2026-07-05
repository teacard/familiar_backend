<?php

use App\Http\Controllers\AdminApi\ItemController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:admin')->controller(ItemController::class)->prefix('items')->group(function () {
    Route::get('/', 'index');
    Route::get('/select', 'select'); // 須宣告於 /{id} 之前，避免 select 被當成 id
    Route::get('/{id}', 'show');
    Route::post('/', 'store');
    Route::put('/{id}', 'update');
    Route::delete('/{id}', 'destroy');
});
