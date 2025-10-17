<?php

use App\Enums\CacheEnum;
use App\Enums\PermissionsEnum;
use App\Http\Controllers\LeaveRegistration\LeaveRegistrationActionController;
use App\Http\Controllers\LeaveRegistration\LeaveRegistrationController;
use App\Http\Controllers\LeaveRegistration\LeaveRegistrationScheduleController;

Route::prefix('leave-registrations')->name('leave.registrations.')->group(function () {
    Route::get('', [LeaveRegistrationController::class, 'index'])
        ->name('index')
        ->middleware(middleware_tags('permission', PermissionsEnum::LeaveRegistrationsIndex()));
    Route::get('json', [LeaveRegistrationController::class, 'jsonIndex'])
        ->name('json')
        ->middleware(middleware_tags('permission', PermissionsEnum::LeaveRegistrationsIndex()))
        ->middleware(middleware_tags('cache', CacheEnum::LeaveRegistrations()))
        ->middleware('etag');
    Route::get(
        '{leaveRegistration}/schedules/json',
        [LeaveRegistrationScheduleController::class, 'jsonIndex']
    )
        ->name('schedules.json')
        ->middleware(middleware_tags('permission', PermissionsEnum::LeaveRegistrationsIndex()))
        ->middleware('etag');

    Route::middleware(middleware_tags(
        'cache',
        CacheEnum::LeaveRegistrations(),
    ))->group(function () {
        Route::post('', [LeaveRegistrationController::class, 'store'])
            ->name('store')
            ->middleware(middleware_tags('permission', PermissionsEnum::LeaveRegistrationsCreate()))
            ->middleware('throttle:5,1');
        Route::post('{leaveRegistration}/stop', [LeaveRegistrationActionController::class, 'stop'])
            ->name('stop')
            ->middleware(middleware_tags('permission', PermissionsEnum::LeaveRegistrationsCreate()))
            ->middleware('throttle:5,1');
        Route::patch('{leaveRegistration}', [LeaveRegistrationController::class, 'update'])
            ->name('update')
            ->middleware(middleware_tags('permission', PermissionsEnum::LeaveRegistrationsUpdate()))
            ->middleware('throttle:5,1');
        Route::delete('{leaveRegistration}', [LeaveRegistrationController::class, 'destroy'])
            ->name('destroy')
            ->middleware(middleware_tags('permission', PermissionsEnum::LeaveRegistrationsDelete()))
            ->middleware('throttle:5,1');
    });
});
