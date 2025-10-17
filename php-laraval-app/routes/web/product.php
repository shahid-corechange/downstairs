<?php

use App\Enums\CacheEnum;
use App\Enums\PermissionsEnum;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Product\ProductTranslationController;

Route::prefix('products')->name('products.')->group(function () {
    Route::get('', [ProductController::class, 'index'])
        ->name('index')
        ->middleware(middleware_tags('permission', PermissionsEnum::ProductsIndex()));
    Route::get('json', [ProductController::class, 'jsonIndex'])
        ->name('json')
        ->middleware(middleware_tags('permission', PermissionsEnum::ProductsIndex()))
        ->middleware(middleware_tags('cache', CacheEnum::Products()))
        ->middleware('etag');

    Route::middleware(middleware_tags(
        'cache',
        CacheEnum::Products(),
    ))->group(function () {
        Route::post('', [ProductController::class, 'store'])
            ->name('store')
            ->middleware(middleware_tags('permission', PermissionsEnum::ProductsCreate()))
            ->middleware(middleware_tags(
                'cache',
                CacheEnum::Addons(),
                CacheEnum::Services(),
                CacheEnum::Categories(),
            ))
            ->middleware('throttle:5,1');
        Route::post('{product}/restore', [ProductController::class, 'restore'])
            ->withTrashed()
            ->name('restore')
            ->middleware(middleware_tags('permission', PermissionsEnum::ProductsRestore()))
            ->middleware('throttle:5,1');
        Route::patch('{product}', [ProductController::class, 'update'])
            ->name('update')
            ->middleware(middleware_tags('permission', PermissionsEnum::ProductsUpdate()))
            ->middleware(middleware_tags(
                'cache',
                CacheEnum::PriceAdjustments(),
                CacheEnum::Addons(),
                CacheEnum::Services(),
                CacheEnum::Categories(),
                CacheEnum::Schedules(),
                CacheEnum::ScheduleEmployees(),
            ))
            ->middleware('throttle:5,1');
        Route::delete('{product}', [ProductController::class, 'destroy'])
            ->name('destroy')
            ->middleware(middleware_tags('permission', PermissionsEnum::ProductsDelete()))
            ->middleware(middleware_tags('cache', CacheEnum::PriceAdjustments()))
            ->middleware('throttle:5,1');
        Route::patch('{product}/translations', [ProductTranslationController::class, 'update'])
            ->name('translations.update')
            ->middleware(middleware_tags('permission', PermissionsEnum::ProductTranslationsUpdate()))
            ->middleware('throttle:5,1');
    });
});
