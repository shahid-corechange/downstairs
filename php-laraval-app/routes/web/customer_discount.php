<?php

use App\Enums\CacheEnum;
use App\Enums\PermissionsEnum;
use App\Http\Controllers\CustomerDiscount\CustomerDiscountController;

Route::prefix('customers/discounts')->name('customers.discounts.')->group(function () {
    Route::get('', [CustomerDiscountController::class, 'index'])
        ->name('index')
        ->middleware(middleware_tags('permission', PermissionsEnum::CustomerDiscountsIndex()));
    Route::get('json', [CustomerDiscountController::class, 'jsonIndex'])
        ->name('json')
        ->middleware(middleware_tags('permission', PermissionsEnum::CustomerDiscountsIndex()))
        ->middleware(middleware_tags('cache', CacheEnum::CustomerDiscounts()))
        ->middleware('etag');

    Route::middleware(middleware_tags(
        'cache',
        CacheEnum::CustomerDiscounts(),
        CacheEnum::Products(),
    ))->group(function () {
        Route::post('', [CustomerDiscountController::class, 'store'])
            ->name('store')
            ->middleware(middleware_tags('permission', PermissionsEnum::CustomerDiscountsCreate()))
            ->middleware('throttle:5,1');
        Route::post('{customerDiscount}/restore', [CustomerDiscountController::class, 'restore'])
            ->withTrashed()
            ->name('restore')
            ->middleware(middleware_tags('permission', PermissionsEnum::CustomerDiscountsRestore()))
            ->middleware('throttle:5,1');
        Route::patch('{customerDiscount}', [CustomerDiscountController::class, 'update'])
            ->name('update')
            ->middleware(middleware_tags('permission', PermissionsEnum::CustomerDiscountsUpdate()))
            ->middleware('throttle:5,1');
        Route::delete('{customerDiscount}', [CustomerDiscountController::class, 'destroy'])
            ->name('destroy')
            ->middleware(middleware_tags('permission', PermissionsEnum::CustomerDiscountsDelete()))
            ->middleware('throttle:5,1');
    });
});
