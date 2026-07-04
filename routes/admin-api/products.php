<?php

use App\Http\Controllers\AdminApi\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:admin')->controller(ProductController::class)->prefix('products')->group(function () {
    Route::get('/', 'index');
    Route::post('/', 'store');
    Route::get('/{id}', 'show');
    Route::put('/{id}', 'update');
    Route::delete('/{id}', 'destroy');
});
