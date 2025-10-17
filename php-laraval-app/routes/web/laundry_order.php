<?php

use App\Enums\CacheEnum;
use App\Enums\PermissionsEnum;
use App\Http\Controllers\LaundryOrder\LaundryOrderController;

Route::prefix('laundry-orders')->name('laundry.orders.')->group(function () {
    Route::get('', [LaundryOrderController::class, 'index'])
        ->name('index')
        ->middleware(middleware_tags('permission', PermissionsEnum::LaundryOrdersIndex()));
    Route::get('json', [LaundryOrderController::class, 'jsonIndex'])
        ->name('index.json')
        ->middleware(middleware_tags('permission', PermissionsEnum::LaundryOrdersIndex()))
        ->middleware(middleware_tags('cache', CacheEnum::LaundryOrders()))
        ->middleware('etag');
    Route::get('{laundryOrderId}/json', [LaundryOrderController::class, 'jsonShow'])
        ->name('show.json')
        ->middleware(middleware_tags('permission', PermissionsEnum::LaundryOrdersRead()))
        ->middleware(middleware_tags('cache', CacheEnum::LaundryOrders()))
        ->middleware('etag');
});
