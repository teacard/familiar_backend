<?php

use App\Http\Controllers\Api\RegistrationController;
use App\Http\Middleware\ResolveRegistrationDraft;
use Illuminate\Support\Facades\Route;

Route::controller(RegistrationController::class)->prefix('registrations')->group(function () {
    Route::post('/', 'store');
    Route::get('/', 'show')->middleware(ResolveRegistrationDraft::class);
    Route::post('verification-code', 'sendVerificationCode')->middleware([ResolveRegistrationDraft::class, 'throttle:6,1']);
    Route::post('verification-code/verify', 'verifyCode')->middleware(ResolveRegistrationDraft::class);
    Route::post('profile/avatar', 'uploadAvatar')->middleware(ResolveRegistrationDraft::class);
    Route::post('profile', 'updateProfile')->middleware(ResolveRegistrationDraft::class);
    Route::post('password', 'complete')->middleware(ResolveRegistrationDraft::class);
});
