<?php

use App\Http\Controllers\AdminApi\AuthController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->prefix('auth')->group(function () {
    Route::post('/login', 'login'); // 後台登入
});
