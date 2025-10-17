<?php

use App\Enums\CacheEnum;
use App\Enums\PermissionsEnum;
use App\Http\Controllers\Customer\CustomerAccountController;
use App\Http\Controllers\Customer\CustomerAddressController;
use App\Http\Controllers\Customer\CustomerRutCoApplicantController;
use App\Http\Controllers\Customer\CustomerWizardController;

Route::prefix('customers')->name('customers.')->group(function () {
    Route::get('', [CustomerAccountController::class, 'index'])
        ->name('index')
        ->middleware(middleware_tags('permission', PermissionsEnum::CustomersIndex()));
    Route::get('json', [CustomerAccountController::class, 'jsonIndex'])
        ->name('json')
        ->middleware(middleware_tags('permission', PermissionsEnum::CustomersIndex()))
        ->middleware(middleware_tags('cache', CacheEnum::Customers()))
        ->middleware('etag');
    Route::get('wizard', [CustomerWizardController::class, 'index'])
        ->name('wizard')
        ->middleware(middleware_tags('permission', PermissionsEnum::CustomersWizard()));
    Route::get('addresses', [CustomerAccountController::class, 'privateAddresses'])
        ->name('addresses')
        ->middleware(middleware_tags('permission', PermissionsEnum::CustomersIndex()))
        ->middleware(middleware_tags('cache', CacheEnum::CustomerAddresses()))
        ->middleware('etag');

    Route::prefix('{user}')->name('show.')->group(function () {
        Route::get('properties', [CustomerAccountController::class, 'properties'])
            ->name('properties')
            ->middleware(middleware_tags('cache', CacheEnum::CustomerProperties()))
            ->middleware('etag');
        Route::get('addresses', [CustomerAccountController::class, 'addresses'])
            ->name('addresses')
            ->middleware(middleware_tags('cache', CacheEnum::CustomerAddresses()))
            ->middleware('etag');
        Route::get('credits', [CustomerAccountController::class, 'credits'])
            ->name('credits')
            ->middleware(middleware_tags('cache', CacheEnum::Credits()))
            ->middleware('etag');
    });

    Route::middleware(
        middleware_tags(
            'cache',
            CacheEnum::Customers(),
            CacheEnum::CustomerAddresses(),
            CacheEnum::ScheduleEmployees(),
            CacheEnum::Schedules(),
            CacheEnum::KeyPlaces(),
        )
    )->group(function () {
        Route::post('wizard', [CustomerWizardController::class, 'store'])
            ->name('store')
            ->middleware(middleware_tags('permission', PermissionsEnum::CustomersCreate()))
            ->middleware('throttle:5,1');
        Route::post('{user}/restore', [CustomerAccountController::class, 'restore'])
            ->withTrashed()
            ->name('restore')
            ->middleware(middleware_tags('permission', PermissionsEnum::CustomersRestore()))
            ->middleware('throttle:5,1');
        Route::patch('{user}', [CustomerAccountController::class, 'update'])
            ->name('update')
            ->middleware(middleware_tags('permission', PermissionsEnum::CustomersUpdate()))
            ->middleware('throttle:5,1');
        Route::delete('{user}', [CustomerAccountController::class, 'destroy'])
            ->name('destroy')
            ->middleware(middleware_tags('permission', PermissionsEnum::CustomersDelete()))
            ->middleware('throttle:5,1');

        Route::prefix('{user}/addresses')->name('addresses.')->group(function () {
            Route::post('', [CustomerAddressController::class, 'store'])
                ->name('store')
                ->middleware(
                    middleware_tags(
                        'permission',
                        PermissionsEnum::CustomerInvoiceAddressesCreate()
                    )
                )
                ->middleware('throttle:5,1');
            Route::post('{customer}/restore', [CustomerAddressController::class, 'restore'])
                ->withTrashed()
                ->name('restore')
                ->middleware(
                    middleware_tags(
                        'permission',
                        PermissionsEnum::CustomerInvoiceAddressesRestore()
                    )
                )
                ->middleware('throttle:5,1');
            Route::patch('{customer}', [CustomerAddressController::class, 'update'])
                ->name('update')
                ->middleware(
                    middleware_tags(
                        'permission',
                        PermissionsEnum::CustomersPrimaryAddressUpdate(),
                        PermissionsEnum::CustomerInvoiceAddressesUpdate()
                    )
                )
                ->middleware('throttle:5,1');
            Route::delete('{customer}', [CustomerAddressController::class, 'destroy'])
                ->name('delete')
                ->middleware(
                    middleware_tags(
                        'permission',
                        PermissionsEnum::CustomerInvoiceAddressesDelete()
                    )
                )
                ->middleware('throttle:5,1');
        });

        Route::prefix('{user}/rut-co-applicants')
            ->middleware(
                middleware_tags(
                    'cache',
                    CacheEnum::RutCoApplicants(),
                )
            )
            ->name('rut-co-applicants.')
            ->group(function () {
                Route::get('', [CustomerRutCoApplicantController::class, 'jsonIndex'])
                    ->name('json')
                    ->middleware(
                        middleware_tags(
                            'permission',
                            PermissionsEnum::CustomerRutCoApplicantIndex()
                        )
                    )
                    ->middleware('etag');
                Route::post('', [CustomerRutCoApplicantController::class, 'store'])
                    ->name('store')
                    ->middleware(
                        middleware_tags(
                            'permission',
                            PermissionsEnum::CustomerRutCoApplicantCreate()
                        )
                    )
                    ->middleware('throttle:5,1');
                Route::post(
                    '{rutCoApplicant}/enable',
                    [CustomerRutCoApplicantController::class, 'enable']
                )
                    ->name('enable')
                    ->middleware(
                        middleware_tags(
                            'permission',
                            PermissionsEnum::CustomerRutCoApplicantEnable()
                        )
                    )
                    ->middleware('throttle:5,1');
                Route::post(
                    '{rutCoApplicant}/disable',
                    [CustomerRutCoApplicantController::class, 'disable']
                )
                    ->name('disable')
                    ->middleware(
                        middleware_tags(
                            'permission',
                            PermissionsEnum::CustomerRutCoApplicantDisable()
                        )
                    )
                    ->middleware('throttle:5,1');
                Route::post(
                    '{rutCoApplicant}/pause',
                    [CustomerRutCoApplicantController::class, 'pause']
                )
                    ->name('pause')
                    ->middleware(
                        middleware_tags(
                            'permission',
                            PermissionsEnum::CustomerRutCoApplicantPause()
                        )
                    )
                    ->middleware('throttle:5,1');
                Route::post(
                    '{rutCoApplicant}/continue',
                    [CustomerRutCoApplicantController::class, 'continue']
                )
                    ->name('continue')
                    ->middleware(
                        middleware_tags(
                            'permission',
                            PermissionsEnum::CustomerRutCoApplicantContinue()
                        )
                    )
                    ->middleware('throttle:5,1');
                Route::patch(
                    '{rutCoApplicant}',
                    [CustomerRutCoApplicantController::class, 'update']
                )
                    ->name('update')
                    ->middleware(
                        middleware_tags(
                            'permission',
                            PermissionsEnum::CustomerRutCoApplicantUpdate()
                        )
                    )
                    ->middleware('throttle:5,1');
                Route::delete(
                    '{rutCoApplicant}',
                    [CustomerRutCoApplicantController::class, 'destroy']
                )
                    ->name('delete')
                    ->middleware(
                        middleware_tags(
                            'permission',
                            PermissionsEnum::CustomerRutCoApplicantDelete()
                        )
                    )
                    ->middleware('throttle:5,1');
            });
    });
});
