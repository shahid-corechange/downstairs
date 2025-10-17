<?php

use App\Enums\CacheEnum;
use App\Enums\PermissionsEnum;
use App\Http\Controllers\Service\ServiceController;
use App\Http\Controllers\Service\ServiceTaskController;
use App\Http\Controllers\Service\ServiceTranslationController;

Route::prefix('services')->name('services.')->group(function () {
    Route::get('', [ServiceController::class, 'index'])
        ->name('index')
        ->middleware(middleware_tags('permission', PermissionsEnum::ServicesIndex()));
    Route::get('json', [ServiceController::class, 'jsonIndex'])
        ->name('json')
        ->middleware(middleware_tags('permission', PermissionsEnum::ServicesIndex()))
        ->middleware(middleware_tags('cache', CacheEnum::Services()))
        ->middleware('etag');

    Route::middleware(middleware_tags(
        'cache',
        CacheEnum::Services(),
    ))->group(function () {
        Route::post('', [ServiceController::class, 'store'])
            ->name('store')
            ->middleware(middleware_tags('permission', PermissionsEnum::ServicesCreate()))
            ->middleware(middleware_tags(
                'cache',
                CacheEnum::Addons(),
                CacheEnum::Products(),
                CacheEnum::Categories(),
            ))
            ->middleware('throttle:5,1');
        Route::post('{service}/restore', [ServiceController::class, 'restore'])
            ->withTrashed()
            ->name('restore')
            ->middleware(middleware_tags('permission', PermissionsEnum::ServicesRestore()))
            ->middleware('throttle:5,1');
        Route::patch('{service}', [ServiceController::class, 'update'])
            ->name('update')
            ->middleware(middleware_tags('permission', PermissionsEnum::ServicesUpdate()))
            ->middleware(middleware_tags(
                'cache',
                CacheEnum::PriceAdjustments(),
                CacheEnum::Addons(),
                CacheEnum::Products(),
                CacheEnum::Categories(),
                CacheEnum::Schedules(),
                CacheEnum::ScheduleEmployees(),
            ))
            ->middleware('throttle:5,1');
        Route::delete('{service}', [ServiceController::class, 'destroy'])
            ->name('destroy')
            ->middleware(middleware_tags('permission', PermissionsEnum::ServicesDelete()))
            ->middleware(middleware_tags('cache', CacheEnum::PriceAdjustments()))
            ->middleware('throttle:5,1');

        Route::middleware(middleware_tags(
            'cache',
            CacheEnum::Schedules(),
            CacheEnum::ScheduleEmployees()
        ))->group(function () {
            Route::prefix('{service}/tasks')->name('tasks.')->group(function () {
                Route::post('', [ServiceTaskController::class, 'store'])
                    ->name('store')
                    ->middleware(middleware_tags('permission', PermissionsEnum::ServiceTasksCreate()))
                    ->middleware('throttle:5,1');
                Route::patch('{taskId}', [ServiceTaskController::class, 'update'])
                    ->name('update')
                    ->middleware(middleware_tags('permission', PermissionsEnum::ServiceTasksUpdate()))
                    ->middleware('throttle:5,1');
                Route::delete('{taskId}', [ServiceTaskController::class, 'destroy'])
                    ->name('destroy')
                    ->middleware(middleware_tags('permission', PermissionsEnum::ServiceTasksDelete()))
                    ->middleware('throttle:5,1');
            });
        });

        Route::patch('{service}/translations', [ServiceTranslationController::class, 'update'])
            ->name('update')
            ->middleware(middleware_tags('permission', PermissionsEnum::ServiceTranslationsUpdate()))
            ->middleware('throttle:5,1');
    });
});
