<?php

use App\Enums\CacheEnum;
use App\Http\Controllers\CashierCustomer\CashierCompanyCustomerController;
use App\Http\Controllers\CashierCustomer\CashierCustomerController;
use App\Http\Controllers\CashierCustomer\CashierPrivateCustomerController;
use App\Http\Controllers\CashierOrder\CashierOrderCartController;
use App\Http\Controllers\CashierOrder\CashierOrderController;
use App\Http\Controllers\Customer\CustomerAccountController;

Route::prefix('customers')->name('customers.')->group(function () {
    Route::get('wizard', [CashierCustomerController::class, 'wizard'])->name('wizard');
    Route::get('json', [CashierCustomerController::class, 'jsonIndex'])
        ->name('json.index')
        ->middleware(middleware_tags('cache', CacheEnum::Customers()))
        ->middleware('etag');

    Route::prefix('{user}')->name('show.')->group(function () {
        Route::get('addresses', [CustomerAccountController::class, 'addresses'])
            ->name('addresses')
            ->middleware(middleware_tags('cache', CacheEnum::CustomerAddresses()))
            ->middleware('etag');
    });

    Route::middleware(middleware_tags(
        'cache',
        CacheEnum::Customers(),
        CacheEnum::CustomerDiscounts(),
        CacheEnum::CustomerAddresses(),
        CacheEnum::Users(),
    ))->group(function () {
        Route::post('private', [CashierPrivateCustomerController::class, 'store'])
            ->name('private.store')
            ->middleware('throttle:5,1');
        Route::patch('{user}/private', [CashierPrivateCustomerController::class, 'update'])
            ->name('private.update')
            ->middleware(middleware_tags(
                'cache',
                CacheEnum::Schedules(),
            ))
            ->middleware('throttle:5,1');
        Route::post('company', [CashierCompanyCustomerController::class, 'store'])
            ->name('company.store')
            ->middleware('throttle:5,1');
        Route::patch('{user}/company', [CashierCompanyCustomerController::class, 'update'])
            ->name('company.update')
            ->middleware(middleware_tags(
                'cache',
                CacheEnum::Companies(),
                CacheEnum::CompanyUsers(),
                CacheEnum::CompanyAddresses(),
                CacheEnum::Schedules(),
            ))
            ->middleware('throttle:5,1');
    });

    Route::prefix('{user}')->group(function () {
        Route::get('cart/checkout', [CashierOrderCartController::class, 'indexCheckout'])->name('checkout');
        Route::get('cart', [CashierOrderCartController::class, 'index'])->name('cart');
        Route::get('orders', [CashierOrderController::class, 'indexCustomer'])->name('orders');
        Route::get('orders/{laundryOrder}', [CashierOrderController::class, 'showCustomerOrder'])->name('orders.show');
        Route::get('orders/{laundryOrder}/card-payment', [CashierOrderController::class, 'cardPayment'])
            ->name('orders.card-payment');
        Route::get('orders/{laundryOrder}/invoice-payment', [CashierOrderController::class, 'invoicePayment'])
            ->name('orders.invoice-payment');
    });
});
