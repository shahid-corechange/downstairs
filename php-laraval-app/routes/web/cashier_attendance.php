<?php

use App\Enums\CacheEnum;
use App\Http\Controllers\CashierAttendance\CashierAttendanceController;

Route::prefix('cashier-attendances')->name('cashier.attendances.')->group(function () {
    Route::get('json', [CashierAttendanceController::class, 'jsonIndex'])
        ->name('json')
        ->middleware(middleware_tags('cache', CacheEnum::CashierAttendances()))
        ->middleware('etag');
});
