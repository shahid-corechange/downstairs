<?php

use App\Enums\Auth\TokenAbilityEnum;
use App\Enums\CacheEnum;
use App\Enums\PermissionsEnum;
use App\Http\Controllers\Api\WorkHourController;

Route::middleware(['localization', 'timezone'])->group(function () {
    Route::prefix('v1')->group(function () {
        Route::middleware(['auth:sanctum', 'ability:'.TokenAbilityEnum::APIAccess()])->group(function () {
            Route::group(['middleware' => [
                middleware_tags('permission', PermissionsEnum::AccessEmployeeApp()),
            ]], function () {
                Route::prefix('work-hours')->group(function () {
                    Route::get('', [WorkHourController::class, 'index'])
                        ->middleware(middleware_tags('cache', CacheEnum::WorkHours()))
                        ->middleware('etag');
                });
            });
        });
    });
});
