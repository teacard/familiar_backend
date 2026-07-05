<?php

use App\Http\Controllers\AdminApi\EnumController;
use Illuminate\Support\Facades\Route;

Route::controller(EnumController::class)->prefix('enums')->group(function () {
    Route::get('permission', 'permission');
    Route::get('admin-status', 'adminStatus');
    Route::get('announcement-status', 'announcementStatus');
    Route::get('announcement-target-audience', 'announcementTargetAudience');
    Route::get('player-status', 'playerStatus');
    Route::get('product-status', 'productStatus');
    Route::get('item-status', 'itemStatus');
});
