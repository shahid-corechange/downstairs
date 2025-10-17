<?php

use App\Enums\CacheEnum;
use App\Enums\PermissionsEnum;
use App\Http\Controllers\Store\StoreController;
use App\Http\Controllers\Store\StoreProductController;

Route::prefix('stores')->name('stores.')->group(function () {
    Route::get('', [StoreController::class, 'index'])
        ->name('index')
        ->middleware(middleware_tags('permission', PermissionsEnum::StoresIndex()));
    Route::get('json', [StoreController::class, 'jsonIndex'])
        ->name('json')
        ->middleware(middleware_tags('permission', PermissionsEnum::StoresIndex()))
        ->middleware(middleware_tags('cache', CacheEnum::Stores()))
        ->middleware('etag');
    Route::get('{store}/json', [StoreController::class, 'jsonShow'])
        ->name('show.json')
        ->middleware(middleware_tags('permission', PermissionsEnum::StoresRead()))
        ->middleware(middleware_tags('cache', CacheEnum::Stores()))
        ->middleware('etag');

    Route::middleware(middleware_tags(
        'cache',
        CacheEnum::Stores(),
    ))->group(function () {
        Route::post('', [StoreController::class, 'store'])
            ->name('store')
            ->middleware(middleware_tags('permission', PermissionsEnum::StoresCreate()))
            ->middleware('throttle:5,1');
        Route::post('{store}/restore', [StoreController::class, 'restore'])
            ->withTrashed()
            ->name('restore')
            ->middleware(middleware_tags('permission', PermissionsEnum::StoresRestore()))
            ->middleware('throttle:5,1');
        Route::patch('{store}', [StoreController::class, 'update'])
            ->name('update')
            ->middleware(middleware_tags('permission', PermissionsEnum::StoresUpdate()))
            ->middleware('throttle:5,1');
        Route::delete('{store}', [StoreController::class, 'destroy'])
            ->name('destroy')
            ->middleware(middleware_tags('permission', PermissionsEnum::StoresDelete()))
            ->middleware('throttle:5,1');

        Route::patch('{store}/products', [StoreProductController::class, 'update'])
            ->name('products.update')
            ->middleware(middleware_tags('permission', PermissionsEnum::StoresUpdate()))
            ->middleware('cache', CacheEnum::Products())
            ->middleware('throttle:5,1');
    });
});
