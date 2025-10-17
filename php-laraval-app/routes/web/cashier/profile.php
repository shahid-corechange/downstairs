<?php

use App\Enums\CacheEnum;
use App\Http\Controllers\Profile\ProfileController;

Route::prefix('profile')->name('profile.')->group(function () {
    Route::get('', [ProfileController::class, 'index'])->name('index');
    Route::patch('', [ProfileController::class, 'update'])
        ->name('update')
        ->middleware(middleware_tags(
            'cache',
            CacheEnum::ScheduleEmployees(),
            CacheEnum::Schedules(),
            CacheEnum::Users(),
        ));
});
