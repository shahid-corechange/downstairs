<?php

use App\Enums\CacheEnum;
use App\Enums\PermissionsEnum;
use App\Http\Controllers\Deviation\DeviationController;
use App\Http\Controllers\Deviation\ScheduleDeviationController;

Route::prefix('deviations')->name('deviations.')->group(function () {
    Route::get('', [ScheduleDeviationController::class, 'index'])
        ->name('index')
        ->middleware(middleware_tags('permission', PermissionsEnum::DeviationsIndex()));
    Route::get('json', [ScheduleDeviationController::class, 'jsonIndex'])
        ->name('json')
        ->middleware(middleware_tags('permission', PermissionsEnum::DeviationsIndex()))
        ->middleware(middleware_tags('cache', CacheEnum::ScheduleDeviations()))
        ->middleware('etag');

    Route::prefix('employee')->name('employee.')->group(function () {
        Route::get('', [DeviationController::class, 'index'])
            ->name('index')
            ->middleware(middleware_tags('permission', PermissionsEnum::DeviationsIndex()));
        Route::get('json', [DeviationController::class, 'jsonIndex'])
            ->name('json')
            ->middleware(middleware_tags('permission', PermissionsEnum::DeviationsIndex()))
            ->middleware(middleware_tags('cache', CacheEnum::Deviations()))
            ->middleware('etag');
        Route::post('{deviation}/handle', [DeviationController::class, 'handle'])
            ->name('handle')
            ->middleware(middleware_tags('permission', PermissionsEnum::DeviationsHandle()))
            ->middleware(middleware_tags('cache', CacheEnum::Deviations(), CacheEnum::WorkHours()))
            ->middleware('throttle:5,1');
        Route::patch('{deviation}/attendance', [DeviationController::class, 'updateWorkerAttendance'])
            ->name('attendance')
            ->middleware(middleware_tags('permission', PermissionsEnum::DeviationsHandle()))
            ->middleware(
                middleware_tags(
                    'cache',
                    CacheEnum::ScheduleEmployees(),
                    CacheEnum::Schedules(),
                    CacheEnum::Deviations(),
                    CacheEnum::TimeAdjustments(),
                )
            )
            ->middleware('throttle:5,1');
    });

    Route::get('{deviationId}/json', [ScheduleDeviationController::class, 'jsonShow'])
        ->name('show.json')
        ->middleware(middleware_tags('permission', PermissionsEnum::DeviationsRead()))
        ->middleware(middleware_tags('cache', CacheEnum::ScheduleDeviations()))
        ->middleware('etag');
    Route::post('{deviation}/handle', [ScheduleDeviationController::class, 'handle'])
        ->name('handle')
        ->middleware(middleware_tags('permission', PermissionsEnum::DeviationsHandle()))
        ->middleware(
            middleware_tags(
                'cache',
                CacheEnum::ScheduleDeviations(),
                CacheEnum::Deviations(),
                CacheEnum::WorkHours(),
                CacheEnum::Invoices(),
            )
        )
        ->middleware('throttle:5,1');
});
