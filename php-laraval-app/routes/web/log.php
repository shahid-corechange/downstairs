<?php

use App\Enums\CacheEnum;
use App\Enums\PermissionsEnum;
use App\Http\Controllers\Logs\ActivityLogsController;
use App\Http\Controllers\Logs\AuthLogsController;
use App\Http\Controllers\Logs\SystemLogsController;

Route::prefix('log')->name('log.')->group(function () {
    Route::prefix('system')->name('system.')->group(function () {
        Route::get('', [SystemLogsController::class, 'index'])
            ->name('index');
        Route::delete('{log}', [SystemLogsController::class, 'destroy'])
            ->name('destroy')
            ->middleware('throttle:5,1');
    });

    Route::prefix('activities')->name('activities.')->group(function () {
        Route::get('', [ActivityLogsController::class, 'index'])
            ->name('index')
            ->middleware(middleware_tags('permission', PermissionsEnum::ActivityLogsIndex()));
        Route::get('json', [ActivityLogsController::class, 'jsonIndex'])
            ->name('json')
            ->middleware(middleware_tags('permission', PermissionsEnum::ActivityLogsIndex()))
            ->middleware(middleware_tags('cache', CacheEnum::ActivityLogs()))
            ->middleware('etag');
    });

    Route::prefix('authentications')->name('authentications.')->group(function () {
        Route::get('', [AuthLogsController::class, 'index'])
            ->name('index')
            ->middleware(middleware_tags('permission', PermissionsEnum::AuthenticationLogsIndex()));
        Route::get('json', [AuthLogsController::class, 'jsonIndex'])
            ->name('json')
            ->middleware(middleware_tags('permission', PermissionsEnum::AuthenticationLogsIndex()))
            ->middleware(middleware_tags('cache', CacheEnum::AuthLogs()))
            ->middleware('etag');
    });
});
