<?php

use App\Enums\CacheEnum;
use App\Http\Controllers\CashierOrder\CashierOrderActionController;
use App\Http\Controllers\CashierOrder\CashierOrderController;
use App\Http\Controllers\CashierOrder\CashierScheduleOrderController;
use App\Http\Controllers\CashierOrder\LaundryOrderPreferenceController;

Route::prefix('orders')->name('orders.')->group(function () {
    Route::get('', [CashierOrderController::class, 'index'])->name('index');
    Route::get('json', [CashierOrderController::class, 'jsonIndex'])
        ->name('json')
        ->middleware(middleware_tags('cache', CacheEnum::LaundryOrders()))
        ->middleware('etag');
    Route::get('preferences/json', [LaundryOrderPreferenceController::class, 'jsonIndex'])
        ->name('preferences.json')
        ->middleware(middleware_tags('cache', CacheEnum::LaundryPreferences()));

    Route::middleware(middleware_tags(
        'cache',
        CacheEnum::LaundryOrders(),
        CacheEnum::Schedules(),
        CacheEnum::ScheduleEmployees(),
        CacheEnum::Subscriptions(),
        CacheEnum::CompanySubscriptions(),
        CacheEnum::FixedPrices(),
        CacheEnum::CompanyFixedPrices(),
    ))->group(function () {
        Route::post('', [CashierScheduleOrderController::class, 'store'])
            ->name('store')
            ->middleware('throttle:5,1');

        Route::patch('{laundryOrder}', [CashierScheduleOrderController::class, 'update'])
            ->name('update')
            ->middleware('throttle:5,1');
    });

    Route::middleware(middleware_tags(
        'cache',
        CacheEnum::LaundryOrders(),
    ))->group(function () {
        Route::post('{laundryOrder}/change-status', [CashierOrderActionController::class, 'changeStatus'])
            ->name('change-status')
            ->middleware('throttle:5,1');

        Route::post('{laundryOrder}/pay', [CashierOrderActionController::class, 'pay'])
            ->name('pay')
            ->middleware('throttle:5,1')
            ->middleware(middleware_tags('cache', CacheEnum::Invoices()));
    });
});
