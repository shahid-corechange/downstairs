<?php

use App\Enums\CacheEnum;
use App\Enums\PermissionsEnum;
use App\Http\Controllers\Subscription\SubscriptionController;
use App\Http\Controllers\Subscription\SubscriptionStaffController;
use App\Http\Controllers\Subscription\SubscriptionTaskController;
use App\Http\Controllers\Subscription\SubscriptionWizardController;

Route::prefix('customers/subscriptions')->name('customers.subscriptions.')->group(function () {
    Route::get('', [SubscriptionController::class, 'index'])
        ->name('index')
        ->middleware(middleware_tags('permission', PermissionsEnum::SubscriptionsIndex()));
    Route::get('json', [SubscriptionController::class, 'jsonIndex'])
        ->name('json')
        ->middleware(middleware_tags('permission', PermissionsEnum::SubscriptionsIndex()))
        ->middleware(middleware_tags('cache', CacheEnum::Subscriptions()))
        ->middleware('etag');
    Route::get('wizard', [SubscriptionWizardController::class, 'index'])
        ->name('wizard')
        ->middleware(middleware_tags('permission', PermissionsEnum::SubscriptionsWizard()));

    Route::prefix('{subscription}')->name('show.')->group(function () {
        Route::get('json', [SubscriptionController::class, 'jsonShow'])
            ->name('json')
            ->middleware(middleware_tags('permission', PermissionsEnum::SubscriptionsRead()))
            ->middleware(middleware_tags('cache', CacheEnum::Subscriptions()))
            ->middleware('etag');
    });

    Route::middleware(middleware_tags(
        'cache',
        CacheEnum::ScheduleEmployees(),
        CacheEnum::Schedules(),
        CacheEnum::Subscriptions()
    ))->group(function () {
        Route::post('{subscription}/staff/{staff}', [SubscriptionStaffController::class, 'store'])
            ->name('staff.store')
            ->middleware('throttle:5,1');
        Route::delete('{subscription}/staff/{staff}', [SubscriptionStaffController::class, 'destroy'])
            ->name('staff.destroy')
            ->middleware('throttle:5,1');

        Route::prefix('{subscription}/tasks')->name('tasks.')->group(function () {
            Route::post('', [SubscriptionTaskController::class, 'store'])
                ->name('store')
                ->middleware(middleware_tags('permission', PermissionsEnum::SubscriptionTasksCreate()))
                ->middleware('throttle:5,1');
            Route::patch('{taskId}', [SubscriptionTaskController::class, 'update'])
                ->name('update')
                ->middleware(middleware_tags('permission', PermissionsEnum::SubscriptionTasksUpdate()))
                ->middleware('throttle:5,1');
            Route::delete('{taskId}', [SubscriptionTaskController::class, 'destroy'])
                ->name('destroy')
                ->middleware(middleware_tags('permission', PermissionsEnum::SubscriptionTasksDelete()))
                ->middleware('throttle:5,1');
        });

        Route::post('wizard', [SubscriptionWizardController::class, 'store'])
            ->name('store')
            ->middleware(middleware_tags('permission', PermissionsEnum::SubscriptionsCreate()))
            ->middleware(middleware_tags('cache', CacheEnum::FixedPrices()))
            ->middleware('throttle:5,1');
        Route::post('{subscription}/restore', [SubscriptionController::class, 'restore'])
            ->withTrashed()
            ->name('restore')
            ->middleware(middleware_tags('permission', PermissionsEnum::SubscriptionsRestore()))
            ->middleware('throttle:5,1');
        Route::post('{subscription}/pause', [SubscriptionController::class, 'pause'])
            ->name('pause')
            ->middleware(middleware_tags('permission', PermissionsEnum::SubscriptionsPause()))
            ->middleware('throttle:5,1');
        Route::post('{subscription}/continue', [SubscriptionController::class, 'continue'])
            ->name('continue')
            ->middleware(middleware_tags('permission', PermissionsEnum::SubscriptionsContinue()))
            ->middleware('throttle:5,1');
        Route::patch('{subscription}', [SubscriptionController::class, 'update'])
            ->name('update')
            ->middleware(middleware_tags('permission', PermissionsEnum::SubscriptionsUpdate()))
            ->middleware(middleware_tags('cache', CacheEnum::FixedPrices()))
            ->middleware('throttle:5,1');
        Route::delete('{subscription}', [SubscriptionController::class, 'destroy'])
            ->name('destroy')
            ->middleware(middleware_tags('permission', PermissionsEnum::SubscriptionsDelete()))
            ->middleware('throttle:5,1');
    });
});
