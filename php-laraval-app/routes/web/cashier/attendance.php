<?php

use App\Enums\CacheEnum;
use App\Http\Controllers\CashierAttendance\CashierAttendanceController;

Route::prefix('attendances')->name('attendances.')->group(function () {
    Route::get('json', [CashierAttendanceController::class, 'jsonIndex'])
        ->name('json')
        ->middleware(middleware_tags('cache', CacheEnum::CashierAttendances()))
        ->middleware('etag');

    Route::middleware(middleware_tags(
        'cache',
        CacheEnum::CashierAttendances(),
        CacheEnum::WorkHours(),
    ))->group(function () {
        Route::post('check-in', [CashierAttendanceController::class, 'checkIn'])
            ->name('check-in')
            ->middleware('throttle:5,1');
        Route::post('check-out', [CashierAttendanceController::class, 'checkOut'])
            ->name('check-out')
            ->middleware('throttle:5,1');
    });
});
