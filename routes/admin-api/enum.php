<?php

use App\Http\Controllers\AdminApi\EnumController;
use Illuminate\Support\Facades\Route;

Route::controller(EnumController::class)->prefix('enums')->group(function () {
    Route::get('permission', 'permission');
});
