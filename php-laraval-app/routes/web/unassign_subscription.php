<?php

use App\Enums\CacheEnum;
use App\Enums\PermissionsEnum;
use App\Http\Controllers\UnassignSubscription\UnassignSubscriptionActionController;
use App\Http\Controllers\UnassignSubscription\UnassignSubscriptionController;

Route::prefix('unassign-subscriptions')->name('unassign.subscriptions.')->group(function () {
    Route::get('', [UnassignSubscriptionController::class, 'index'])
        ->name('index')
        ->middleware(middleware_tags('permission', PermissionsEnum::UnassignSubscriptionsIndex()));
    Route::get('json', [UnassignSubscriptionController::class, 'jsonIndex'])
        ->name('json')
        ->middleware(middleware_tags('permission', PermissionsEnum::UnassignSubscriptionsIndex()))
        ->middleware(middleware_tags('cache', CacheEnum::UnassignSubscriptions()))
        ->middleware('etag');

    Route::middleware(middleware_tags(
        'cache',
        CacheEnum::UnassignSubscriptions(),
    ))->group(function () {
        Route::post('', [UnassignSubscriptionController::class, 'store'])
            ->name('store')
            ->middleware(middleware_tags('permission', PermissionsEnum::UnassignSubscriptionsCreate()))
            ->middleware('throttle:5,1');
        Route::post('{unassignSubscription}/generate', [UnassignSubscriptionActionController::class, 'generate'])
            ->name('generate')
            ->middleware(middleware_tags('permission', PermissionsEnum::UnassignSubscriptionsCreate()))
            ->middleware(middleware_tags(
                'cache',
                CacheEnum::FixedPrices(),
                CacheEnum::ScheduleEmployees(),
                CacheEnum::Schedules(),
                CacheEnum::Subscriptions(),
            ))
            ->middleware('throttle:5,1');
        Route::patch('{subscription}', [UnassignSubscriptionController::class, 'update'])
            ->name('update')
            ->middleware(middleware_tags('permission', PermissionsEnum::UnassignSubscriptionsUpdate()))
            ->middleware('throttle:5,1');
        Route::delete('{subscription}', [UnassignSubscriptionController::class, 'destroy'])
            ->name('destroy')
            ->middleware(middleware_tags('permission', PermissionsEnum::UnassignSubscriptionsDelete()))
            ->middleware('throttle:5,1');
    });
});
