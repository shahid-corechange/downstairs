<?php

use App\Enums\CacheEnum;
use App\Enums\PermissionsEnum;
use App\Http\Controllers\Property\PropertyController;
use App\Http\Controllers\Property\PropertyWizardController;

Route::prefix('customers/properties')->name('customers.properties.')->group(function () {
    Route::get('', [PropertyController::class, 'index'])
        ->name('index')
        ->middleware(middleware_tags('permission', PermissionsEnum::PropertiesIndex()));
    Route::get('json', [PropertyController::class, 'jsonIndex'])
        ->name('json')
        ->middleware(middleware_tags('permission', PermissionsEnum::PropertiesIndex()))
        ->middleware(middleware_tags('cache', CacheEnum::Properties()))
        ->middleware('etag');
    Route::get('wizard', [PropertyWizardController::class, 'index'])
        ->name('wizard')
        ->middleware(middleware_tags('permission', PermissionsEnum::PropertiesWizard()));

    Route::middleware(middleware_tags(
        'cache',
        CacheEnum::CustomerAddresses(),
        CacheEnum::CustomerProperties(),
        CacheEnum::Addresses(),
        CacheEnum::Properties(),
        CacheEnum::Users(),
        CacheEnum::KeyPlaces(),
    ))->group(function () {
        Route::post('wizard', [PropertyWizardController::class, 'store'])
            ->name('store')
            ->middleware(middleware_tags('permission', PermissionsEnum::PropertiesCreate()))
            ->middleware('throttle:5,1');
        Route::post('{property}/restore', [PropertyController::class, 'restore'])
            ->withTrashed()
            ->name('restore')
            ->middleware(middleware_tags('permission', PermissionsEnum::PropertiesRestore()))
            ->middleware('throttle:5,1');
        Route::patch('{property}', [PropertyController::class, 'update'])
            ->name('update')
            ->middleware(middleware_tags('permission', PermissionsEnum::PropertiesUpdate()))
            ->middleware(middleware_tags('cache', CacheEnum::ScheduleEmployees(), CacheEnum::Schedules()))
            ->middleware('throttle:5,1');
        Route::delete('{property}', [PropertyController::class, 'destroy'])
            ->name('destroy')
            ->middleware(middleware_tags('permission', PermissionsEnum::PropertiesDelete()))
            ->middleware('throttle:5,1');
    });
});
