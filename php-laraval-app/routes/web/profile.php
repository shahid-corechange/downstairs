<?php

use App\Enums\CacheEnum;
use App\Http\Controllers\Profile\ProfileController;
use App\Http\Controllers\Profile\ProfileEmailController;
use App\Http\Controllers\Profile\ProfilePasswordController;
use App\Http\Controllers\Profile\ProfileSettingsController;

Route::prefix('profile')->name('profile.')->group(function () {
    // Route::prefix('settings')->group(function () {
    //     Route::put('email', [ProfileEmailController::class, 'update'])->name('email.update');
    //     Route::put('password', [ProfilePasswordController::class, 'update'])->name('password.update');
    // });
    // Route::resource('settings', ProfileSettingsController::class)->only('index', 'update');

    // Route::resource('security', ProfileSettingsController::class)->only('index', 'update');

    Route::get('', [ProfileController::class, 'index'])->name('index');
    Route::patch('', [ProfileController::class, 'update'])
        ->name('index')
        ->middleware(middleware_tags(
            'cache',
            CacheEnum::ScheduleEmployees(),
            CacheEnum::Schedules(),
            CacheEnum::Users(),
        ));
});
