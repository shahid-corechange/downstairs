<?php

use App\Enums\CacheEnum;
use App\Enums\PermissionsEnum;
use App\Http\Controllers\PriceAdjustment\PriceAdjustmentController;

Route::prefix('price-adjustments')->name('price-adjustments.')->group(function () {
    Route::get('', [PriceAdjustmentController::class, 'index'])
        ->name('index')
        ->middleware(middleware_tags('permission', PermissionsEnum::PriceAdjustmentIndex()));
    Route::get('json', [PriceAdjustmentController::class, 'jsonIndex'])
        ->name('json')
        ->middleware(middleware_tags('permission', PermissionsEnum::PriceAdjustmentIndex()))
        ->middleware(middleware_tags('cache', CacheEnum::PriceAdjustments()))
        ->middleware('etag');

    Route::middleware(middleware_tags(
        'cache',
        CacheEnum::PriceAdjustments(),
    ))->group(function () {
        Route::post('', [PriceAdjustmentController::class, 'store'])
            ->name('store')
            ->middleware(middleware_tags('permission', PermissionsEnum::PriceAdjustmentCreate()));
        Route::put('{priceAdjustment}', [PriceAdjustmentController::class, 'update'])
            ->name('update')
            ->middleware(middleware_tags('permission', PermissionsEnum::PriceAdjustmentUpdate()));
        Route::delete('{priceAdjustment}', [PriceAdjustmentController::class, 'destroy'])
            ->name('destroy')
            ->middleware(middleware_tags('permission', PermissionsEnum::PriceAdjustmentDelete()));
    });
});
