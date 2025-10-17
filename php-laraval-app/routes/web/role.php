<?php

use App\Enums\CacheEnum;
use App\Enums\PermissionsEnum;
use App\Http\Controllers\Role\RoleController;

Route::prefix('roles')->name('roles.')->group(function () {
    Route::get('', [RoleController::class, 'index'])
        ->name('index')
        ->middleware(middleware_tags('permission', PermissionsEnum::RolesIndex()));

    Route::middleware(
        middleware_tags('cache', CacheEnum::Roles())
    )->group(function () {
        Route::post('', [RoleController::class, 'store'])
            ->name('store')
            ->middleware(middleware_tags('permission', PermissionsEnum::RolesCreate()));

        Route::patch('{role}', [RoleController::class, 'update'])
            ->name('update')
            ->middleware(middleware_tags('permission', PermissionsEnum::RolesUpdate()));

        Route::delete('{role}', [RoleController::class, 'destroy'])
            ->name('destroy')
            ->middleware(middleware_tags('permission', PermissionsEnum::RolesDelete()));
    });
});
