<?php

use App\Enums\CacheEnum;
use App\Enums\PermissionsEnum;
use App\Http\Controllers\Employee\EmployeeAccountController;
use App\Http\Controllers\Employee\EmployeeRoleController;
use App\Http\Controllers\Employee\EmployeeWizardController;

Route::prefix('employees')->name('employees.')->group(function () {
    Route::get('', [EmployeeAccountController::class, 'index'])
        ->name('index')
        ->middleware(middleware_tags('permission', PermissionsEnum::EmployeesIndex()));
    Route::get('json', [EmployeeAccountController::class, 'jsonIndex'])
        ->name('json')
        ->middleware(middleware_tags('permission', PermissionsEnum::EmployeesIndex()))
        ->middleware(middleware_tags('cache', CacheEnum::Employees()))
        ->middleware('etag');
    Route::get('wizard', [EmployeeWizardController::class, 'index'])
        ->name('wizard')
        ->middleware(middleware_tags('permission', PermissionsEnum::EmployeesWizard()));

    Route::middleware(middleware_tags(
        'cache',
        CacheEnum::Employees(),
    ))->group(function () {
        Route::post('wizard', [EmployeeWizardController::class, 'store'])
            ->name('store')
            ->middleware(middleware_tags('permission', PermissionsEnum::EmployeesCreate()))
            ->middleware('throttle:5,1');
        Route::post('{user}/restore', [EmployeeAccountController::class, 'restore'])
            ->withTrashed()
            ->name('restore')
            ->middleware(middleware_tags('permission', PermissionsEnum::EmployeesRestore()))
            ->middleware('throttle:5,1');
        Route::patch('{user}', [EmployeeAccountController::class, 'update'])
            ->name('update')
            ->middleware(middleware_tags('permission', PermissionsEnum::EmployeesUpdate()))
            ->middleware('throttle:5,1');
        Route::delete('{user}', [EmployeeAccountController::class, 'destroy'])
            ->name('destroy')
            ->middleware(middleware_tags('permission', PermissionsEnum::EmployeesDelete()))
            ->middleware('throttle:5,1');

        Route::put('{user}/roles', [EmployeeRoleController::class, 'update'])
            ->name('roles.update')
            ->middleware(middleware_tags('permission', PermissionsEnum::EmployeeRolesUpdate()))
            ->middleware('throttle:5,1');
    });
});
