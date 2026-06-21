<?php

use App\Http\Controllers\AdminApi\AuthController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->prefix('auth')->group(function () {
    Route::post('/login', 'login'); // 後台登入
});

Route::middleware('auth:admin')->controller(AuthController::class)->group(function () {
    Route::get('/profile', 'profile'); // 個人資料-取得
});
