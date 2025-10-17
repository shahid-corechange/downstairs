<?php

use App\Enums\CacheEnum;
use App\Enums\PermissionsEnum;
use App\Http\Controllers\Schedule\RescheduleController;
use App\Http\Controllers\Schedule\ScheduleChangeRequestController;
use App\Http\Controllers\Schedule\ScheduleChangeRequestHistoryController;
use App\Http\Controllers\Schedule\ScheduleController;
use App\Http\Controllers\Schedule\ScheduleHistoryController;
use App\Http\Controllers\Schedule\ScheduleItemController;
use App\Http\Controllers\Schedule\ScheduleTaskController;
use App\Http\Controllers\Schedule\ScheduleWorkerController;

Route::prefix('schedules')->name('schedules.')->group(function () {
    Route::prefix('change-requests')->name('change-requests.')->group(function () {
        Route::get('', [ScheduleChangeRequestController::class, 'index'])
            ->name('index')
            ->middleware(middleware_tags('permission', PermissionsEnum::ScheduleChangeRequestsIndex()));
        Route::prefix('histories')->name('histories.')->group(function () {
            Route::get('', [ScheduleChangeRequestHistoryController::class, 'index'])
                ->name('index')
                ->middleware(middleware_tags('permission', PermissionsEnum::ScheduleChangeRequestsIndex()));
            Route::get('json', [ScheduleChangeRequestHistoryController::class, 'jsonIndex'])
                ->name('json')
                ->middleware(middleware_tags('permission', PermissionsEnum::ScheduleChangeRequestsIndex()))
                ->middleware(middleware_tags('cache', CacheEnum::ChangeRequests()))
                ->middleware('etag');
        });
        Route::get('json', [ScheduleChangeRequestController::class, 'jsonIndex'])
            ->name('json')
            ->middleware(middleware_tags('permission', PermissionsEnum::ScheduleChangeRequestsIndex()))
            ->middleware(middleware_tags('cache', CacheEnum::ChangeRequests()))
            ->middleware('etag');
        Route::post('{changeRequest}/approve', [ScheduleChangeRequestController::class, 'approve'])
            ->name('approve')
            ->middleware(middleware_tags('permission', PermissionsEnum::ScheduleChangeRequestsApprove()))
            ->middleware(middleware_tags('cache', CacheEnum::ScheduleEmployees(), CacheEnum::Schedules()))
            ->middleware('throttle:5,1');
        Route::post('{changeRequest}/reject', [ScheduleChangeRequestController::class, 'reject'])
            ->name('reject')
            ->middleware(middleware_tags('permission', PermissionsEnum::ScheduleChangeRequestsReject()))
            ->middleware(middleware_tags('cache', CacheEnum::ScheduleEmployees(), CacheEnum::Schedules()))
            ->middleware('throttle:5,1');
    });

    Route::prefix('history')->name('history.')
        ->middleware(middleware_tags(
            'cache',
            CacheEnum::ScheduleEmployees(),
            CacheEnum::Schedules(),
            CacheEnum::WorkHours(),
            CacheEnum::Invoices(),
        ))
        ->group(function () {
            Route::post('', [ScheduleHistoryController::class, 'store'])
                ->name('create')
                ->middleware(middleware_tags('permission', PermissionsEnum::SchedulesHistoryCreate()))
                ->middleware('throttle:5,1');
        });

    Route::get('items/json', [ScheduleItemController::class, 'jsonIndex'])
        ->name('items.json')
        ->middleware(middleware_tags('permission', PermissionsEnum::SchedulesIndex()))
        ->middleware(middleware_tags('cache', CacheEnum::Schedules()))
        ->middleware('etag');

    Route::get('', [ScheduleController::class, 'index'])
        ->name('index')
        ->middleware(middleware_tags('permission', PermissionsEnum::SchedulesIndex()));
    Route::get('json', [ScheduleController::class, 'jsonIndex'])
        ->name('json')
        ->middleware(middleware_tags('permission', PermissionsEnum::SchedulesIndex()))
        ->middleware(middleware_tags('cache', CacheEnum::Schedules()))
        ->middleware('etag');
    Route::get('workers/available', [ScheduleWorkerController::class, 'findAvailable'])
        ->name('workers.available');
    Route::post('workers/bulk-change', [ScheduleWorkerController::class, 'bulkChange'])
        ->name('workers.bulk-change')
        ->middleware(middleware_tags('permission', PermissionsEnum::ScheduleWorkersCreate()))
        ->middleware(middleware_tags('cache', CacheEnum::ScheduleEmployees(), CacheEnum::Schedules()))
        ->middleware('throttle:5,1');
    Route::get('{scheduleId}/json', [ScheduleController::class, 'jsonSchedule'])
        ->name('json')
        ->middleware(middleware_tags('permission', PermissionsEnum::SchedulesIndex()))
        ->middleware(middleware_tags('cache', CacheEnum::Schedules()))
        ->middleware('etag');
    Route::post('{schedule}/cancel', [ScheduleController::class, 'cancel'])
        ->name('cancel')
        ->middleware(middleware_tags('permission', PermissionsEnum::SchedulesCancel()))
        ->middleware(middleware_tags(
            'cache',
            CacheEnum::ScheduleEmployees(),
            CacheEnum::Schedules(),
            CacheEnum::Credits(),
            CacheEnum::Invoices(),
        ))
        ->middleware('throttle:5,1');
    Route::patch('{scheduleId}', [ScheduleController::class, 'update'])
        ->name('update')
        ->middleware(middleware_tags('permission', PermissionsEnum::SchedulesUpdate()))
        ->middleware(middleware_tags(
            'cache',
            CacheEnum::ScheduleEmployees(),
            CacheEnum::Schedules(),
            CacheEnum::Credits(),
        ))
        ->middleware('throttle:5,1');

    Route::prefix('{schedule}/workers')->name('workers.')->group(function () {
        Route::get('json', [ScheduleWorkerController::class, 'jsonIndex'])
            ->name('json')
            ->middleware(middleware_tags('permission', PermissionsEnum::ScheduleWorkersIndex()))
            ->middleware(middleware_tags('cache', CacheEnum::ScheduleEmployees()))
            ->middleware('etag');
        Route::post('', [ScheduleWorkerController::class, 'add'])
            ->name('add')
            ->middleware(middleware_tags('permission', PermissionsEnum::ScheduleWorkersCreate()))
            ->middleware(middleware_tags('cache', CacheEnum::ScheduleEmployees(), CacheEnum::Schedules()))
            ->middleware('throttle:5,1');

        Route::prefix('{worker}')->name('worker.')->group(function () {
            Route::get('json', [ScheduleWorkerController::class, 'jsonShow'])
                ->name('json')
                ->middleware(middleware_tags('permission', PermissionsEnum::ScheduleWorkersRead()))
                ->middleware(middleware_tags('cache', CacheEnum::ScheduleEmployees()))
                ->middleware('etag');
            Route::delete('', [ScheduleWorkerController::class, 'remove'])
                ->name('remove')
                ->middleware(middleware_tags('permission', PermissionsEnum::ScheduleWorkersDisable()))
                ->middleware(middleware_tags('cache', CacheEnum::ScheduleEmployees(), CacheEnum::Schedules()))
                ->middleware('throttle:5,1');
            Route::post('enable', [ScheduleWorkerController::class, 'enable'])
                ->name('enable')
                ->middleware(middleware_tags('permission', PermissionsEnum::ScheduleWorkersEnable()))
                ->middleware(middleware_tags('cache', CacheEnum::ScheduleEmployees(), CacheEnum::Schedules()))
                ->middleware('throttle:5,1');
            Route::delete('disable', [ScheduleWorkerController::class, 'disable'])
                ->name('disable')
                ->middleware(middleware_tags('permission', PermissionsEnum::ScheduleWorkersDisable()))
                ->middleware(middleware_tags('cache', CacheEnum::ScheduleEmployees(), CacheEnum::Schedules()))
                ->middleware('throttle:5,1');
            Route::post('revert', [ScheduleWorkerController::class, 'revert'])
                ->name('revert')
                ->middleware(middleware_tags('permission', PermissionsEnum::ScheduleWorkersEnable()))
                ->middleware(
                    middleware_tags(
                        'cache',
                        CacheEnum::ScheduleEmployees(),
                        CacheEnum::Schedules(),
                        CacheEnum::ScheduleDeviations(),
                        CacheEnum::Orders(),
                        CacheEnum::Invoices(),
                        CacheEnum::Credits(),
                    )
                );
            // ->middleware('throttle:5,1');
            Route::patch('attendance', [ScheduleWorkerController::class, 'updateAttendance'])
                ->name('attendance')
                ->middleware(middleware_tags('permission', PermissionsEnum::ScheduleWorkersUpdateAttendance()))
                ->middleware(
                    middleware_tags(
                        'cache',
                        CacheEnum::ScheduleEmployees(),
                        CacheEnum::Schedules(),
                        CacheEnum::ScheduleDeviations(),
                        CacheEnum::TimeAdjustments(),
                    )
                )
                ->middleware('throttle:5,1');
        });
    });

    Route::middleware(middleware_tags(
        'cache',
        CacheEnum::ScheduleEmployees(),
        CacheEnum::Schedules()
    ))->group(function () {
        Route::prefix('{schedule}/tasks')->name('tasks.')->group(function () {
            Route::post('', [ScheduleTaskController::class, 'store'])
                ->name('store')
                ->middleware(middleware_tags('permission', PermissionsEnum::ScheduleTasksCreate()))
                ->middleware('throttle:5,1');
            Route::patch('{taskId}', [ScheduleTaskController::class, 'update'])
                ->name('update')
                ->middleware(middleware_tags('permission', PermissionsEnum::ScheduleTasksUpdate()))
                ->middleware('throttle:5,1');
            Route::delete('{taskId}', [ScheduleTaskController::class, 'destroy'])
                ->name('destroy')
                ->middleware(middleware_tags('permission', PermissionsEnum::ScheduleTasksDelete()))
                ->middleware('throttle:5,1');
        });

        Route::post('{schedule}/reschedule', [RescheduleController::class, 'store'])
            ->name('reschedule')
            ->middleware(middleware_tags('permission', PermissionsEnum::SchedulesReschedule()))
            ->middleware('throttle:5,1');
    });
});
