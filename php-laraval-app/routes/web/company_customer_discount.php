<?php

use App\Enums\CacheEnum;
use App\Enums\PermissionsEnum;
use App\Http\Controllers\CompanyCustomerDiscount\CompanyCustomerDiscountController;

Route::prefix('companies/discounts')->name('companies.discounts.')->group(function () {
    Route::get('', [CompanyCustomerDiscountController::class, 'index'])
        ->name('index')
        ->middleware(middleware_tags('permission', PermissionsEnum::CompanyDiscountsIndex()));
    Route::get('json', [CompanyCustomerDiscountController::class, 'jsonIndex'])
        ->name('json')
        ->middleware(middleware_tags('permission', PermissionsEnum::CompanyDiscountsIndex()))
        ->middleware(middleware_tags('cache', CacheEnum::CompanyDiscounts()))
        ->middleware('etag');

    Route::middleware(middleware_tags(
        'cache',
        CacheEnum::CompanyDiscounts(),
        CacheEnum::Products(),
    ))->group(function () {
        Route::post('', [CompanyCustomerDiscountController::class, 'store'])
            ->name('store')
            ->middleware(middleware_tags('permission', PermissionsEnum::CompanyDiscountsCreate()))
            ->middleware('throttle:5,1');
        Route::post('{customerDiscount}/restore', [CompanyCustomerDiscountController::class, 'restore'])
            ->withTrashed()
            ->name('restore')
            ->middleware(middleware_tags('permission', PermissionsEnum::CompanyDiscountsRestore()))
            ->middleware('throttle:5,1');
        Route::patch('{customerDiscount}', [CompanyCustomerDiscountController::class, 'update'])
            ->name('update')
            ->middleware(middleware_tags('permission', PermissionsEnum::CompanyDiscountsUpdate()))
            ->middleware('throttle:5,1');
        Route::delete('{customerDiscount}', [CompanyCustomerDiscountController::class, 'destroy'])
            ->name('destroy')
            ->middleware(middleware_tags('permission', PermissionsEnum::CompanyDiscountsDelete()))
            ->middleware('throttle:5,1');
    });
});
