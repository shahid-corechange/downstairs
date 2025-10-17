<?php

use App\Enums\CacheEnum;
use App\Http\Controllers\CashierProduct\CashierProductController;

Route::prefix('products')->name('products.')->group(function () {
    Route::get('json', [CashierProductController::class, 'jsonIndex'])
        ->name('json')
        ->middleware(middleware_tags('cache', CacheEnum::Products()))
        ->middleware('etag');

    Route::middleware(middleware_tags(
        'cache',
        CacheEnum::Products(),
    ))->group(function () {
        Route::post('', [CashierProductController::class, 'store'])
            ->name('store')
            ->middleware('throttle:5,1');
        Route::delete('', [CashierProductController::class, 'destroy'])
            ->name('destroy')
            ->middleware('throttle:5,1');
    });
});
