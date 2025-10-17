<?php

use App\Enums\CacheEnum;
use App\Enums\PermissionsEnum;
use App\Http\Controllers\Feedback\FeedbackController;

Route::prefix('feedbacks')->name('feedbacks.')->group(function () {
    Route::get('', [FeedbackController::class, 'index'])
        ->name('index')
        ->middleware(middleware_tags('permission', PermissionsEnum::FeedbacksIndex()));
    Route::get('json', [FeedbackController::class, 'jsonIndex'])
        ->name('json')
        ->middleware(middleware_tags('permission', PermissionsEnum::FeedbacksIndex()))
        ->middleware(middleware_tags('cache', CacheEnum::Feedbacks()))
        ->middleware('etag');

    Route::middleware(middleware_tags(
        'cache',
        CacheEnum::Feedbacks(),
    ))->group(function () {
        Route::post('{feedback}/restore', [FeedbackController::class, 'restore'])
            ->withTrashed()
            ->name('restore')
            ->middleware(middleware_tags('permission', PermissionsEnum::FeedbacksRestore()))
            ->middleware('throttle:5,1');
        Route::delete('{feedback}', [FeedbackController::class, 'destroy'])
            ->name('destroy')
            ->middleware(middleware_tags('permission', PermissionsEnum::FeedbacksDelete()))
            ->middleware('throttle:5,1');
    });
});
