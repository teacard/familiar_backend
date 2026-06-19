<?php

use App\Http\Controllers\TelescopeAuth\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Telescope 後台 web 登入（session）。路徑刻意不在 /telescope 之下，避免被 Telescope catch-all 路由攔截。
Route::get('telescope-login', [LoginController::class, 'showLoginForm'])->name('telescope.login');
Route::post('telescope-login', [LoginController::class, 'login'])->name('telescope.login.attempt');
