<?php

use App\Enums\CacheEnum;
use App\Enums\PermissionsEnum;
use App\Http\Controllers\Addon\AddOnController;
use App\Http\Controllers\Addon\AddOnTaskController;
use App\Http\Controllers\Addon\AddOnTranslationController;

Route::prefix('addons')->name('addons.')->group(function () {
    Route::get('', [AddOnController::class, 'index'])
        ->name('index')
        ->middleware(middleware_tags('permission', PermissionsEnum::AddonsIndex()));
    Route::get('json', [AddOnController::class, 'jsonIndex'])
        ->name('json')
        ->middleware(middleware_tags('permission', PermissionsEnum::AddonsIndex()))
        ->middleware(middleware_tags('cache', CacheEnum::Addons()))
        ->middleware('etag');

    Route::middleware(middleware_tags(
        'cache',
        CacheEnum::Addons(),
    ))->group(function () {
        Route::post('', [AddOnController::class, 'store'])
            ->name('store')
            ->middleware(middleware_tags('permission', PermissionsEnum::AddonsCreate()))
            ->middleware(middleware_tags(
                'cache',
                CacheEnum::Products(),
                CacheEnum::Services(),
                CacheEnum::Categories(),
            ))
            ->middleware('throttle:5,1');
        Route::post('{addon}/restore', [AddOnController::class, 'restore'])
            ->withTrashed()
            ->name('restore')
            ->middleware(middleware_tags('permission', PermissionsEnum::AddonsRestore()))
            ->middleware('throttle:5,1');
        Route::patch('{addon}', [AddOnController::class, 'update'])
            ->name('update')
            ->middleware(middleware_tags('permission', PermissionsEnum::AddonsUpdate()))
            ->middleware(middleware_tags(
                'cache',
                CacheEnum::PriceAdjustments(),
                CacheEnum::Products(),
                CacheEnum::Services(),
                CacheEnum::Categories(),
                CacheEnum::Schedules(),
                CacheEnum::ScheduleEmployees(),
            ))
            ->middleware('throttle:5,1');
        Route::delete('{addon}', [AddOnController::class, 'destroy'])
            ->name('destroy')
            ->middleware(middleware_tags('permission', PermissionsEnum::AddonsDelete()))
            ->middleware(middleware_tags('cache', CacheEnum::PriceAdjustments()))
            ->middleware('throttle:5,1');

        Route::middleware(middleware_tags(
            'cache',
            CacheEnum::Schedules(),
            CacheEnum::ScheduleEmployees()
        ))->group(function () {
            Route::prefix('{addon}/tasks')->name('tasks.')->group(function () {
                Route::post('', [AddOnTaskController::class, 'store'])
                    ->name('store')
                    ->middleware(middleware_tags('permission', PermissionsEnum::AddonTasksCreate()))
                    ->middleware('throttle:5,1');
                Route::patch('{taskId}', [AddOnTaskController::class, 'update'])
                    ->name('update')
                    ->middleware(middleware_tags('permission', PermissionsEnum::AddonTasksUpdate()))
                    ->middleware('throttle:5,1');
                Route::delete('{taskId}', [AddOnTaskController::class, 'destroy'])
                    ->name('destroy')
                    ->middleware(middleware_tags('permission', PermissionsEnum::AddonTasksDelete()))
                    ->middleware('throttle:5,1');
            });
        });

        Route::patch('{addon}/translations', [AddOnTranslationController::class, 'update'])
            ->name('translations.update')
            ->middleware(middleware_tags('permission', PermissionsEnum::AddonTranslationsUpdate()))
            ->middleware('throttle:5,1');
    });
});
