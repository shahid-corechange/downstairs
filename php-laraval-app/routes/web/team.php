<?php

use App\Enums\CacheEnum;
use App\Enums\PermissionsEnum;
use App\Http\Controllers\Team\TeamController;

Route::prefix('teams')->name('teams.')->group(function () {
    Route::get('', [TeamController::class, 'index'])
        ->name('index')
        ->middleware(middleware_tags('permission', PermissionsEnum::TeamsIndex()));

    Route::middleware(middleware_tags(
        'cache',
        CacheEnum::Teams(),
        CacheEnum::ScheduleEmployees(),
        CacheEnum::Schedules()
    ))->group(function () {
        Route::post('', [TeamController::class, 'store'])
            ->name('store')
            ->middleware(middleware_tags('permission', PermissionsEnum::TeamsCreate()))
            ->middleware('throttle:5,1');
        Route::post('{team}/restore', [TeamController::class, 'restore'])
            ->withTrashed()
            ->name('restore')
            ->middleware(middleware_tags('permission', PermissionsEnum::TeamsRestore()))
            ->middleware('throttle:5,1');
        Route::patch('{team}', [TeamController::class, 'update'])
            ->name('update')
            ->middleware(middleware_tags('permission', PermissionsEnum::TeamsUpdate()))
            ->middleware('throttle:5,1');
        Route::delete('{team}', [TeamController::class, 'destroy'])
            ->name('destroy')
            ->middleware(middleware_tags('permission', PermissionsEnum::TeamsDelete()))
            ->middleware('throttle:5,1');
    });
});
