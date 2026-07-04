<?php

use App\Http\Controllers\AdminApi\ProductTypeController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:admin')->controller(ProductTypeController::class)->prefix('product-types')->group(function () {
    Route::get('/', 'index');
    Route::get('/select', 'select'); // 須宣告於 /{id} 之前，避免 select 被當成 id
    Route::post('/', 'store');
    Route::put('/{id}', 'update');
    Route::delete('/{id}', 'destroy');
});
