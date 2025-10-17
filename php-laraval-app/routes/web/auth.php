<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\StoreSelectionController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware(['guest'])->group(function () {
    // Login
    Route::get('/login', [AuthenticatedSessionController::class, 'index'])
        ->name('login');

    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware(['throttle:3,3']);

    // Two Factor
    Route::prefix('two-factor')->name('two-factor.')->group(function () {
        Route::post('/login', [TwoFactorController::class, 'login'])
            ->middleware(['throttle:3,3']);
        Route::post('/otp/resend', [TwoFactorController::class, 'resendOtp'])
            ->middleware(['throttle:1,1']);
    });

    // Store Selection
    Route::prefix('store-selection')->name('store-selection.')->group(function () {
        Route::post('/login', [StoreSelectionController::class, 'login'])
            ->middleware(['throttle:3,3']);
    });

    // Forgot Password
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware(['throttle:5,1'])
        ->name('password.email');

    // Create Password
    Route::get('/create-password/{hash}', [NewPasswordController::class, 'create'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('password.create');

    Route::post('/create-password', [NewPasswordController::class, 'store'])
        ->name('password.store');

    Route::post('/create-password/resend', [NewPasswordController::class, 'resend'])
        ->middleware(['throttle:6,1'])
        ->name('password.resend');

    // Reset Password
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('/reset-password', [ResetPasswordController::class, 'store'])
        ->middleware(['throttle:5,1'])
        ->name('password.update');
});

Route::middleware(['auth'])->group(function () {
    // Verify Email
    Route::get('/verify-email', [EmailVerificationPromptController::class, '__invoke'])
        ->name('verification.notice');

    Route::get('/verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // Logout
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    // Change from portal to cashier
    Route::post('change-store', [StoreSelectionController::class, 'change'])
        ->name('change-store.change');
});
