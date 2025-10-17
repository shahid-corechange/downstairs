<?php

use App\Enums\CacheEnum;
use App\Enums\PermissionsEnum;
use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\Order\OrderRowController;

Route::prefix('orders')->name('orders.')->group(function () {
    Route::get('', [OrderController::class, 'index'])
        ->name('index')
        ->middleware(middleware_tags('permission', PermissionsEnum::OrdersIndex()));
    Route::get('json', [OrderController::class, 'jsonIndex'])
        ->name('json')
        ->middleware(middleware_tags('permission', PermissionsEnum::OrdersIndex()))
        ->middleware(middleware_tags('cache', CacheEnum::Orders()))
        ->middleware('etag');
    Route::get('{orderId}/json', [OrderController::class, 'jsonShow'])
        ->name('show.json')
        ->middleware(middleware_tags('permission', PermissionsEnum::OrdersRead()))
        ->middleware(middleware_tags('cache', CacheEnum::Orders()))
        ->middleware('etag');

    Route::middleware(middleware_tags(
        'cache',
        CacheEnum::Orders(),
        CacheEnum::Invoices(),
    ))->group(function () {
        Route::prefix('{order}/rows')->name('rows.')->group(function () {
            Route::post('', [OrderRowController::class, 'store'])
                ->name('store')
                ->middleware(middleware_tags('permission', PermissionsEnum::OrderRowsCreate()));
            Route::patch('{rowId}', [OrderRowController::class, 'update'])
                ->name('update')
                ->middleware(middleware_tags('permission', PermissionsEnum::OrderRowsUpdate()))
                ->middleware('throttle:5,1');
            Route::delete('{rowId}', [OrderRowController::class, 'destroy'])
                ->name('destroy')
                ->middleware(middleware_tags('permission', PermissionsEnum::OrderRowsDelete()))
                ->middleware('throttle:5,1');
        });
    });
});
