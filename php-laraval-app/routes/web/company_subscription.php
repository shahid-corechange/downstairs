<?php

use App\Enums\CacheEnum;
use App\Enums\PermissionsEnum;
use App\Http\Controllers\CompanySubscription\CompanySubscriptionController;
use App\Http\Controllers\CompanySubscription\CompanySubscriptionStaffController;
use App\Http\Controllers\CompanySubscription\CompanySubscriptionTaskController;
use App\Http\Controllers\CompanySubscription\CompanySubscriptionWizardController;

Route::prefix('companies/subscriptions')->name('companies.subscriptions.')->group(function () {
    Route::get('', [CompanySubscriptionController::class, 'index'])
        ->name('index')
        ->middleware(middleware_tags('permission', PermissionsEnum::CompanySubscriptionsIndex()));
    Route::get('json', [CompanySubscriptionController::class, 'jsonIndex'])
        ->name('json')
        ->middleware(middleware_tags('permission', PermissionsEnum::CompanySubscriptionsIndex()))
        ->middleware(middleware_tags('cache', CacheEnum::CompanySubscriptions()))
        ->middleware('etag');
    Route::get('wizard', [CompanySubscriptionWizardController::class, 'index'])
        ->name('wizard')
        ->middleware(middleware_tags('permission', PermissionsEnum::CompanySubscriptionsWizard()));

    Route::prefix('{subscription}')->name('show.')->group(function () {
        Route::get('json', [CompanySubscriptionController::class, 'jsonShow'])
            ->name('json')
            ->middleware(middleware_tags('permission', PermissionsEnum::CompanySubscriptionsRead()))
            ->middleware(middleware_tags('cache', CacheEnum::CompanySubscriptions()))
            ->middleware('etag');
    });

    Route::middleware(middleware_tags(
        'cache',
        CacheEnum::ScheduleEmployees(),
        CacheEnum::Schedules(),
        CacheEnum::CompanySubscriptions()
    ))->group(function () {
        Route::post('{subscription}/staff/{staff}', [CompanySubscriptionStaffController::class, 'store'])
            ->name('staff.store')
            ->middleware('throttle:5,1');
        Route::delete('{subscription}/staff/{staff}', [CompanySubscriptionStaffController::class, 'destroy'])
            ->name('staff.destroy')
            ->middleware('throttle:5,1');

        Route::prefix('{subscription}/tasks')->name('tasks.')->group(function () {
            Route::post('', [CompanySubscriptionTaskController::class, 'store'])
                ->name('store')
                ->middleware(middleware_tags('permission', PermissionsEnum::CompanySubscriptionTasksCreate()))
                ->middleware('throttle:5,1');
            Route::patch('{taskId}', [CompanySubscriptionTaskController::class, 'update'])
                ->name('update')
                ->middleware(middleware_tags('permission', PermissionsEnum::CompanySubscriptionTasksUpdate()))
                ->middleware('throttle:5,1');
            Route::delete('{taskId}', [CompanySubscriptionTaskController::class, 'destroy'])
                ->name('destroy')
                ->middleware(middleware_tags('permission', PermissionsEnum::CompanySubscriptionTasksDelete()))
                ->middleware('throttle:5,1');
        });

        Route::post('wizard', [CompanySubscriptionWizardController::class, 'store'])
            ->name('store')
            ->middleware(middleware_tags('permission', PermissionsEnum::CompanySubscriptionsCreate()))
            ->middleware(middleware_tags('cache', CacheEnum::CompanyFixedPrices()))
            ->middleware('throttle:5,1');
        Route::post('{subscription}/restore', [CompanySubscriptionController::class, 'restore'])
            ->withTrashed()
            ->name('restore')
            ->middleware(middleware_tags('permission', PermissionsEnum::CompanySubscriptionsRestore()))
            ->middleware('throttle:5,1');
        Route::post('{subscription}/pause', [CompanySubscriptionController::class, 'pause'])
            ->name('pause')
            ->middleware(middleware_tags('permission', PermissionsEnum::CompanySubscriptionsPause()))
            ->middleware('throttle:5,1');
        Route::post('{subscription}/continue', [CompanySubscriptionController::class, 'continue'])
            ->name('continue')
            ->middleware(middleware_tags('permission', PermissionsEnum::CompanySubscriptionsContinue()))
            ->middleware('throttle:5,1');
        Route::patch('{subscription}', [CompanySubscriptionController::class, 'update'])
            ->name('update')
            ->middleware(middleware_tags('permission', PermissionsEnum::CompanySubscriptionsUpdate()))
            ->middleware(middleware_tags('cache', CacheEnum::CompanyFixedPrices()))
            ->middleware('throttle:5,1');
        Route::delete('{subscription}', [CompanySubscriptionController::class, 'destroy'])
            ->name('destroy')
            ->middleware(middleware_tags('permission', PermissionsEnum::CompanySubscriptionsDelete()))
            ->middleware('throttle:5,1');
    });
});
