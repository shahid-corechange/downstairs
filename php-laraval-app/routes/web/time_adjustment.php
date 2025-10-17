<?php

use App\Enums\CacheEnum;
use App\Enums\PermissionsEnum;
use App\Http\Controllers\TimeAdjustment\TimeAdjustmentController;

Route::prefix('time-adjustments')->name('time-adjustments.')->group(function () {
    Route::get('json', [TimeAdjustmentController::class, 'jsonIndex'])
        ->name('json')
        ->middleware(middleware_tags('permission', PermissionsEnum::TimeAdjustmentsIndex()))
        ->middleware(middleware_tags('cache', CacheEnum::TimeAdjustments()))
        ->middleware('etag');

    Route::middleware(middleware_tags(
        'cache',
        CacheEnum::TimeAdjustments(),
    ))->group(function () {
        Route::post('', [TimeAdjustmentController::class, 'store'])
            ->name('store')
            ->middleware(middleware_tags('permission', PermissionsEnum::TimeAdjustmentsCreate()));
        Route::put('{timeAdjustment}', [TimeAdjustmentController::class, 'update'])
            ->name('update')
            ->middleware(middleware_tags('permission', PermissionsEnum::TimeAdjustmentsUpdate()));
        Route::delete('{timeAdjustment}', [TimeAdjustmentController::class, 'destroy'])
            ->name('destroy')
            ->middleware(middleware_tags('permission', PermissionsEnum::TimeAdjustmentsDelete()));
    });
});
