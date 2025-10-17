<?php

use App\Enums\CacheEnum;
use App\Enums\PermissionsEnum;
use App\Http\Controllers\WorkHour\MonthlyWorkHourController;
use App\Http\Controllers\WorkHour\WorkHourController;

Route::prefix('time-reports')->name('time-reports.')->group(function () {
    Route::get('', [MonthlyWorkHourController::class, 'index'])
        ->name('index')
        ->middleware(middleware_tags('permission', PermissionsEnum::TimeReportsIndex()));
    Route::get('json', [MonthlyWorkHourController::class, 'jsonIndex'])
        ->name('json')
        ->middleware(middleware_tags('permission', PermissionsEnum::TimeReportsIndex()))
        ->middleware(middleware_tags('cache', CacheEnum::WorkHours()))
        ->middleware('etag');

    Route::prefix('daily')->name('daily.')->group(function () {
        Route::get('', [WorkHourController::class, 'index'])
            ->name('index')
            ->middleware(middleware_tags('permission', PermissionsEnum::TimeReportsIndex()));
        Route::get('json', [WorkHourController::class, 'jsonIndex'])
            ->name('json')
            ->middleware(middleware_tags('permission', PermissionsEnum::TimeReportsIndex()))
            ->middleware(middleware_tags('cache', CacheEnum::WorkHours()))
            ->middleware('etag');
    });
});
