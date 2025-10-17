<?php

use App\Enums\CacheEnum;
use App\Enums\PermissionsEnum;
use App\Http\Controllers\Credit\CreditController;

Route::prefix('credits')->name('credits.')->group(function () {
    Route::middleware(middleware_tags(
        'cache',
        CacheEnum::Credits(),
    ))->group(function () {
        Route::post('', [CreditController::class, 'store'])
            ->name('store')
            ->middleware(middleware_tags('permission', PermissionsEnum::CreditsCreate()))
            ->middleware('throttle:5,1');
        Route::patch('{credit}', [CreditController::class, 'update'])
            ->name('update')
            ->middleware(middleware_tags('permission', PermissionsEnum::CreditsUpdate()))
            ->middleware('throttle:5,1');
        Route::delete('{credit}', [CreditController::class, 'destroy'])
            ->name('destroy')
            ->middleware(middleware_tags('permission', PermissionsEnum::CreditsDelete()))
            ->middleware('throttle:5,1');
    });
});
