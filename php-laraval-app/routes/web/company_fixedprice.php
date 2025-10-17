<?php

use App\Enums\CacheEnum;
use App\Enums\PermissionsEnum;
use App\Http\Controllers\CompanyFixedPrice\CompanyFixedPriceController;
use App\Http\Controllers\CompanyFixedPrice\CompanyFixedPriceRowController;

Route::prefix('companies/fixedprices')->name('companies.fixedprices.')->group(function () {
    Route::get('', [CompanyFixedPriceController::class, 'index'])
        ->name('index')
        ->middleware(middleware_tags('permission', PermissionsEnum::CompanyFixedPricesIndex()));
    Route::get('json', [CompanyFixedPriceController::class, 'jsonIndex'])
        ->name('json')
        ->middleware(middleware_tags('permission', PermissionsEnum::CompanyFixedPricesIndex()))
        ->middleware(middleware_tags('cache', CacheEnum::CompanyFixedPrices()))
        ->middleware('etag');
    Route::get('{fixedPriceId}/json', [CompanyFixedPriceController::class, 'jsonShow'])
        ->name('show.json')
        ->middleware(middleware_tags('permission', PermissionsEnum::CompanyFixedPricesRead()))
        ->middleware(middleware_tags('cache', CacheEnum::CompanyFixedPrices()))
        ->middleware('etag');

    Route::middleware(middleware_tags(
        'cache',
        CacheEnum::CompanyFixedPrices(),
        CacheEnum::CompanySubscriptions(),
        CacheEnum::Orders(),
        CacheEnum::Schedules(),
        CacheEnum::Invoices(),
    ))->group(function () {
        Route::post('', [CompanyFixedPriceController::class, 'store'])
            ->name('store')
            ->middleware(middleware_tags('permission', PermissionsEnum::CompanyFixedPricesCreate()))
            ->middleware('throttle:5,1');
        Route::post('{fixedPrice}/restore', [CompanyFixedPriceController::class, 'restore'])
            ->withTrashed()
            ->name('restore')
            ->middleware(middleware_tags('permission', PermissionsEnum::CompanyFixedPricesRestore()))
            ->middleware('throttle:5,1');
        Route::patch('{fixedPrice}', [CompanyFixedPriceController::class, 'update'])
            ->name('update')
            ->middleware(middleware_tags('permission', PermissionsEnum::CompanyFixedPricesUpdate()))
            ->middleware('throttle:5,1');
        Route::delete('{fixedPrice}', [CompanyFixedPriceController::class, 'destroy'])
            ->name('destroy')
            ->middleware(middleware_tags('permission', PermissionsEnum::CompanyFixedPricesDelete()))
            ->middleware('throttle:5,1');

        Route::prefix('{fixedPrice}/rows')->name('rows.')->group(function () {
            Route::post('', [CompanyFixedPriceRowController::class, 'store'])
                ->name('store')
                ->middleware(middleware_tags('permission', PermissionsEnum::CompanyFixedPriceRowsCreate()));
            Route::patch('{rowId}', [CompanyFixedPriceRowController::class, 'update'])
                ->name('update')
                ->middleware(middleware_tags('permission', PermissionsEnum::CompanyFixedPriceRowsUpdate()))
                ->middleware(
                    middleware_tags(
                        'cache',
                        CacheEnum::PriceAdjustments(),
                    )
                )
                ->middleware('throttle:5,1');
            Route::delete('{rowId}', [CompanyFixedPriceRowController::class, 'destroy'])
                ->name('destroy')
                ->middleware(middleware_tags('permission', PermissionsEnum::CompanyFixedPriceRowsDelete()))
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
