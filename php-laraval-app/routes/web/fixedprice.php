<?php

use App\Enums\CacheEnum;
use App\Enums\PermissionsEnum;
use App\Http\Controllers\FixedPrice\FixedPriceController;
use App\Http\Controllers\FixedPrice\FixedPriceRowController;

Route::prefix('customers/fixedprices')->name('fixedprices.')->group(function () {
    Route::get('', [FixedPriceController::class, 'index'])
        ->name('index')
        ->middleware(middleware_tags('permission', PermissionsEnum::FixedPricesIndex()));
    Route::get('json', [FixedPriceController::class, 'jsonIndex'])
        ->name('json')
        ->middleware(middleware_tags('permission', PermissionsEnum::FixedPricesIndex()))
        ->middleware(middleware_tags('cache', CacheEnum::FixedPrices()))
        ->middleware('etag');
    Route::get('all/json', [FixedPriceController::class, 'allJsonIndex'])
        ->name('all.json')
        ->middleware(middleware_tags('permission', PermissionsEnum::FixedPricesIndex()))
        ->middleware(middleware_tags('cache', CacheEnum::FixedPrices(), CacheEnum::CompanyFixedPrices()))
        ->middleware('etag');
    Route::get('{fixedPriceId}/json', [FixedPriceController::class, 'jsonShow'])
        ->name('show.json')
        ->middleware(middleware_tags('permission', PermissionsEnum::FixedPricesRead()))
        ->middleware(middleware_tags('cache', CacheEnum::FixedPrices()))
        ->middleware('etag');

    Route::middleware(middleware_tags(
        'cache',
        CacheEnum::FixedPrices(),
        CacheEnum::Subscriptions(),
        CacheEnum::Orders(),
        CacheEnum::Schedules(),
        CacheEnum::Invoices(),
    ))->group(function () {
        Route::post('', [FixedPriceController::class, 'store'])
            ->name('store')
            ->middleware(middleware_tags('permission', PermissionsEnum::FixedPricesCreate()))
            ->middleware('throttle:5,1');
        Route::post('{fixedPrice}/restore', [FixedPriceController::class, 'restore'])
            ->withTrashed()
            ->name('restore')
            ->middleware(middleware_tags('permission', PermissionsEnum::FixedPricesRestore()))
            ->middleware('throttle:5,1');
        Route::patch('{fixedPrice}', [FixedPriceController::class, 'update'])
            ->name('update')
            ->middleware(middleware_tags('permission', PermissionsEnum::FixedPricesUpdate()))
            ->middleware('throttle:5,1');
        Route::delete('{fixedPrice}', [FixedPriceController::class, 'destroy'])
            ->name('destroy')
            ->middleware(middleware_tags('permission', PermissionsEnum::FixedPricesDelete()))
            ->middleware('throttle:5,1');

        Route::prefix('{fixedPrice}/rows')->name('rows.')->group(function () {
            Route::post('', [FixedPriceRowController::class, 'store'])
                ->name('store')
                ->middleware(middleware_tags('permission', PermissionsEnum::FixedPriceRowsCreate()));
            Route::patch('{rowId}', [FixedPriceRowController::class, 'update'])
                ->name('update')
                ->middleware(middleware_tags('permission', PermissionsEnum::FixedPriceRowsUpdate()))
                ->middleware(
                    middleware_tags(
                        'cache',
                        CacheEnum::PriceAdjustments(),
                    )
                )
                ->middleware('throttle:5,1');
            Route::delete('{rowId}', [FixedPriceRowController::class, 'destroy'])
                ->name('destroy')
                ->middleware(middleware_tags('permission', PermissionsEnum::FixedPriceRowsDelete()))
                ->middleware(
                    middleware_tags(
                        'cache',
                        CacheEnum::PriceAdjustments(),
                    )
                )
                ->middleware('throttle:5,1');
        });
    });
});
