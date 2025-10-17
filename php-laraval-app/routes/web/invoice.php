<?php

use App\Enums\CacheEnum;
use App\Enums\PermissionsEnum;
use App\Http\Controllers\Invoice\InvoiceActionController;
use App\Http\Controllers\Invoice\InvoiceController;

Route::prefix('invoices')->name('invoices.')->group(function () {
    Route::get('', [InvoiceController::class, 'index'])
        ->name('index')
        ->middleware(middleware_tags('permission', PermissionsEnum::InvoicesIndex()));
    Route::get('json', [InvoiceController::class, 'jsonIndex'])
        ->name('json')
        ->middleware(middleware_tags('permission', PermissionsEnum::InvoicesIndex()))
        ->middleware(middleware_tags('cache', CacheEnum::Invoices()))
        ->middleware('etag');
    Route::get('{invoiceId}/json', [InvoiceController::class, 'jsonShow'])
        ->name('show.json')
        ->middleware(middleware_tags('permission', PermissionsEnum::InvoicesRead()))
        ->middleware(middleware_tags('cache', CacheEnum::Invoices()))
        ->middleware('etag');

    Route::middleware(middleware_tags(
        'cache',
        CacheEnum::Invoices(),
        CacheEnum::Orders(),
    ))->group(function () {
        Route::patch('{invoice}', [InvoiceController::class, 'update'])
            ->name('update')
            ->middleware(middleware_tags('permission', PermissionsEnum::InvoicesUpdate()))
            ->middleware('throttle:5,1');
        Route::post('{invoice}/create', [InvoiceActionController::class, 'create'])
            ->name('create')
            ->middleware(middleware_tags('permission', PermissionsEnum::InvoicesCreateFortnox()))
            ->middleware('throttle:5,1');
        Route::post('{invoice}/cancel', [InvoiceActionController::class, 'cancel'])
            ->name('create')
            ->middleware(middleware_tags('permission', PermissionsEnum::InvoicesCancel()))
            ->middleware('throttle:5,1');
        Route::post('{invoice}/send', [InvoiceActionController::class, 'send'])
            ->name('send')
            ->middleware(middleware_tags('permission', PermissionsEnum::InvoicesSend()))
            ->middleware('throttle:5,1');
    });
});
