<?php

use App\Enums\CacheEnum;
use App\Http\Controllers\CashierSale\CashierSaleController;
use App\Http\Controllers\CashierSale\CashierSaleHistoryController;

Route::prefix('direct-sales')->name('direct-sales.')->group(function () {
    Route::get('cart', [CashierSaleController::class, 'index'])
        ->name('index');
    Route::get('cart/checkout', [CashierSaleController::class, 'checkout'])
        ->name('checkout');
    Route::get('cart/checkout/card-payment', [CashierSaleController::class, 'cardPayment'])
        ->name('checkout.card-payment');

    Route::prefix('histories')->name('histories.')->group(function () {
        Route::get('', [CashierSaleHistoryController::class, 'index'])
            ->name('index');
        Route::get('json', [CashierSaleHistoryController::class, 'jsonIndex'])
            ->middleware(middleware_tags('cache', CacheEnum::StoreSales()))
            ->name('json');
        Route::get('{storeSaleId}/json', [CashierSaleHistoryController::class, 'jsonShow'])
            ->middleware(middleware_tags('cache', CacheEnum::StoreSales()))
            ->name('show.json');
    });

    Route::middleware(middleware_tags(
        'cache',
        CacheEnum::StoreSales(),
    ))->group(function () {
        Route::post('cart/checkout', [CashierSaleController::class, 'store'])
            ->name('store')
            ->middleware('throttle:5,1')
            ->middleware(middleware_tags('cache', CacheEnum::Invoices()));
    });
});
