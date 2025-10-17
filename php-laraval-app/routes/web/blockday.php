<?php

use App\Enums\CacheEnum;
use App\Enums\PermissionsEnum;
use App\Http\Controllers\Blockday\BlockDayController;

Route::prefix('blockdays')->name('blockdays.')->group(function () {
    Route::get('', [BlockDayController::class, 'index'])
        ->name('index')
        ->middleware(middleware_tags('permission', PermissionsEnum::BlockdaysIndex()));

    Route::middleware(middleware_tags(
        'cache',
        CacheEnum::Blockdays(),
    ))->group(function () {
        Route::post('', [BlockDayController::class, 'store'])
            ->name('store')
            ->middleware(middleware_tags('permission', PermissionsEnum::BlockdaysCreate()))
            ->middleware('throttle:5,1');
        Route::patch('{blockday}', [BlockDayController::class, 'update'])
            ->name('update')
            ->middleware(middleware_tags('permission', PermissionsEnum::BlockdaysUpdate()))
            ->middleware('throttle:5,1');
        Route::delete('{blockday}', [BlockDayController::class, 'destroy'])
            ->name('destroy')
            ->middleware(middleware_tags('permission', PermissionsEnum::BlockdaysDelete()))
            ->middleware('throttle:5,1');
    });
});
