<?php

use App\Enums\CacheEnum;
use App\Enums\PermissionsEnum;
use App\Http\Controllers\Setting\SystemSettingController;

Route::prefix('system-settings')->name('system-settings.')->group(function () {
    Route::get('', [SystemSettingController::class, 'index'])
        ->name('index')
        ->middleware(middleware_tags('permission', PermissionsEnum::SystemSettingsIndex()));
    Route::patch('{setting}', [SystemSettingController::class, 'update'])
        ->name('update')
        ->middleware(middleware_tags('permission', PermissionsEnum::SystemSettingsUpdate()))
        ->middleware(middleware_tags('cache', CacheEnum::GlobalSettings()))
        ->middleware('throttle:5,1');
});
