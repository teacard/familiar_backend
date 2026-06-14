<?php

use Illuminate\Support\Facades\Route;

Route::middleware('api')->prefix('api')->group(function () {
    foreach (glob(base_path('routes/api/*.php')) as $file) {
        require $file;
    }
});

Route::middleware('api')->prefix('admin-api')->group(function () {
    foreach (glob(base_path('routes/admin-api/*.php')) as $file) {
        require $file;
    }
});
