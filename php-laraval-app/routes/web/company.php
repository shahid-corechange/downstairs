<?php

use App\Enums\CacheEnum;
use App\Enums\PermissionsEnum;
use App\Http\Controllers\Company\CompanyAccountController;
use App\Http\Controllers\Company\CompanyAddressController;
use App\Http\Controllers\Company\CompanyWizardController;

Route::prefix('companies')->name('companies.')->group(function () {
    Route::get('', [CompanyAccountController::class, 'index'])
        ->name('index')
        ->middleware(middleware_tags('permission', PermissionsEnum::CompaniesIndex()));
    Route::get('wizard', [CompanyWizardController::class, 'index'])
        ->name('wizard')
        ->middleware(middleware_tags('permission', PermissionsEnum::CompaniesWizard()));
    Route::get('users', [CompanyAccountController::class, 'companyUsers'])
        ->name('users')
        ->middleware(middleware_tags('permission', PermissionsEnum::CompaniesIndex()))
        ->middleware(middleware_tags('cache', CacheEnum::CompanyUsers()))
        ->middleware('etag');
    Route::get('addresses', [CompanyAccountController::class, 'addresses'])
        ->name('addresses')
        ->middleware(middleware_tags('permission', PermissionsEnum::CompaniesIndex()))
        ->middleware(middleware_tags('cache', CacheEnum::CompanyAddresses()))
        ->middleware('etag');

    Route::prefix('{company}')->name('show.')->group(function () {
        Route::prefix('addresses')->name('addresses.')->group(function () {
            Route::get('', [CompanyAddressController::class, 'jsonIndex'])
                ->name('json')
                ->middleware(middleware_tags('permission', PermissionsEnum::CompaniesIndex()))
                ->middleware(middleware_tags('cache', CacheEnum::CompanyAddresses()))
                ->middleware('etag');
        });
    });

    Route::middleware(
        middleware_tags(
            'cache',
            CacheEnum::Companies(),
            CacheEnum::CompanyUsers(),
            CacheEnum::CompanyAddresses(),
            CacheEnum::ScheduleEmployees(),
            CacheEnum::Schedules(),
            CacheEnum::KeyPlaces(),
        )
    )->group(function () {
        Route::post('wizard', [CompanyWizardController::class, 'store'])
            ->name('store')
            ->middleware(middleware_tags('permission', PermissionsEnum::CompaniesCreate()))
            ->middleware('throttle:5,1');
        Route::post('{company}/restore', [CompanyAccountController::class, 'restore'])
            ->withTrashed()
            ->name('restore')
            ->middleware(middleware_tags('permission', PermissionsEnum::CompaniesRestore()))
            ->middleware('throttle:5,1');
        Route::patch('{company}', [CompanyAccountController::class, 'update'])
            ->name('update')
            ->middleware(middleware_tags('permission', PermissionsEnum::CompaniesUpdate()))
            ->middleware('throttle:5,1');
        Route::delete('{company}', [CompanyAccountController::class, 'destroy'])
            ->name('destroy')
            ->middleware(middleware_tags('permission', PermissionsEnum::CompaniesDelete()))
            ->middleware('throttle:5,1');

        Route::prefix('{company}/addresses')->name('addresses.')->group(function () {
            Route::post('', [CompanyAddressController::class, 'store'])
                ->name('store')
                ->middleware(
                    middleware_tags(
                        'permission',
                        PermissionsEnum::CompanyInvoiceAddressesCreate()
                    )
                )
                ->middleware('throttle:5,1');
            Route::post('{customer}/restore', [CompanyAddressController::class, 'restore'])
                ->withTrashed()
                ->name('restore')
                ->middleware(
                    middleware_tags(
                        'permission',
                        PermissionsEnum::CompanyInvoiceAddressesRestore()
                    )
                )
                ->middleware('throttle:5,1');
            Route::patch('{customer}', [CompanyAddressController::class, 'update'])
                ->name('update')
                ->middleware(
                    middleware_tags(
                        'permission',
                        PermissionsEnum::CompaniesPrimaryAddressUpdate(),
                        PermissionsEnum::CompanyInvoiceAddressesUpdate()
                    )
                )
                ->middleware('throttle:5,1');
            Route::delete('{customer}', [CompanyAddressController::class, 'destroy'])
                ->name('delete')
                ->middleware(
                    middleware_tags(
                        'permission',
                        PermissionsEnum::CompanyInvoiceAddressesDelete()
                    )
                )
                ->middleware('throttle:5,1');
        });
    });
});
