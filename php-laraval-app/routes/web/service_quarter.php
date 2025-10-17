<?php

use App\Enums\CacheEnum;
use App\Enums\PermissionsEnum;
use App\Http\Controllers\Service\ServiceQuarterController;

Route::prefix('services/quarters')->name('services.quarters.')->group(function () {
    Route::get('', [ServiceQuarterController::class, 'index'])
        ->name('index')
        ->middleware(middleware_tags('permission', PermissionsEnum::ServiceQuartersIndex()));
    Route::get('json', [ServiceQuarterController::class, 'jsonIndex'])
        ->name('json')
        ->middleware(middleware_tags('permission', PermissionsEnum::ServiceQuartersIndex()))
        ->middleware(middleware_tags('cache', CacheEnum::ServiceQuarters()))
        ->middleware('etag');

    Route::middleware(middleware_tags(
        'cache',
        CacheEnum::ServiceQuarters(),
    ))->group(function () {
        Route::post('', [ServiceQuarterController::class, 'store'])
            ->name('store')
            ->middleware(middleware_tags('permission', PermissionsEnum::ServiceQuartersCreate()))
            ->middleware('throttle:5,1');
        Route::patch('{serviceQuarter}', [ServiceQuarterController::class, 'update'])
            ->name('update')
            ->middleware(middleware_tags('permission', PermissionsEnum::ServiceQuartersUpdate()))
            ->middleware('throttle:5,1');
        Route::delete('{serviceQuarter}', [ServiceQuarterController::class, 'destroy'])
            ->name('destroy')
            ->middleware(middleware_tags('permission', PermissionsEnum::ServiceQuartersDelete()))
            ->middleware('throttle:5,1');
    });
});
