<?php

use App\Enums\CacheEnum;
use App\Enums\PermissionsEnum;
use App\Http\Controllers\CompanyProperty\CompanyPropertyController;
use App\Http\Controllers\CompanyProperty\CompanyPropertyWizardController;

Route::prefix('companies/properties')->name('companies.properties.')->group(function () {
    Route::get('', [CompanyPropertyController::class, 'index'])
        ->name('index')
        ->middleware(middleware_tags('permission', PermissionsEnum::CompanyPropertiesIndex()));
    Route::get('json', [CompanyPropertyController::class, 'jsonIndex'])
        ->name('json')
        ->middleware(middleware_tags('permission', PermissionsEnum::CompanyPropertiesIndex()))
        ->middleware(middleware_tags('cache', CacheEnum::CompanyProperties()))
        ->middleware('etag');
    Route::get('wizard', [CompanyPropertyWizardController::class, 'index'])
        ->name('wizard')
        ->middleware(middleware_tags('permission', PermissionsEnum::CompanyPropertiesWizard()));

    Route::middleware(middleware_tags(
        'cache',
        CacheEnum::CompanyAddresses(),
        CacheEnum::CompanyProperties(),
        CacheEnum::CustomerAddresses(),
        CacheEnum::CustomerProperties(),
        CacheEnum::Addresses(),
        CacheEnum::Properties(),
        CacheEnum::KeyPlaces(),
    ))->group(function () {
        Route::post('wizard', [CompanyPropertyWizardController::class, 'store'])
            ->name('store')
            ->middleware(middleware_tags('permission', PermissionsEnum::CompanyPropertiesCreate()))
            ->middleware('throttle:5,1');
        Route::post('{property}/restore', [CompanyPropertyController::class, 'restore'])
            ->withTrashed()
            ->name('restore')
            ->middleware(middleware_tags('permission', PermissionsEnum::CompanyPropertiesRestore()))
            ->middleware('throttle:5,1');
        Route::patch('{property}', [CompanyPropertyController::class, 'update'])
            ->name('update')
            ->middleware(middleware_tags('permission', PermissionsEnum::CompanyPropertiesUpdate()))
            ->middleware('throttle:5,1');
        Route::delete('{property}', [CompanyPropertyController::class, 'destroy'])
            ->name('destroy')
            ->middleware(middleware_tags('permission', PermissionsEnum::CompanyPropertiesDelete()))
            ->middleware('throttle:5,1');
    });
});
