<?php

use App\Enums\CacheEnum;
use App\Enums\PermissionsEnum;
use App\Http\Controllers\Category\CategoryController;
use App\Http\Controllers\Category\CategoryTranslationController;

Route::prefix('categories')->name('categories.')->group(function () {
    Route::get('', [CategoryController::class, 'index'])
        ->name('index')
        ->middleware(middleware_tags('permission', PermissionsEnum::CategoriesIndex()));
    Route::get('json', [CategoryController::class, 'jsonIndex'])
        ->name('json')
        ->middleware(middleware_tags('permission', PermissionsEnum::CategoriesIndex()))
        ->middleware(middleware_tags('cache', CacheEnum::Categories()))
        ->middleware('etag');

    Route::middleware(middleware_tags(
        'cache',
        CacheEnum::Categories(),
        CacheEnum::Services(),
        CacheEnum::Products(),
        CacheEnum::Addons(),
    ))->group(function () {
        Route::post('', [CategoryController::class, 'store'])
            ->name('store')
            ->middleware(middleware_tags('permission', PermissionsEnum::CategoriesCreate()));
        Route::patch('{category}', [CategoryController::class, 'update'])
            ->name('update')
            ->middleware(middleware_tags('permission', PermissionsEnum::CategoriesUpdate()));
        Route::delete('{category}', [CategoryController::class, 'destroy'])
            ->name('destroy')
            ->middleware(middleware_tags('permission', PermissionsEnum::CategoriesDelete()));
        Route::post('{category}/restore', [CategoryController::class, 'restore'])
            ->withTrashed()
            ->name('restore')
            ->middleware(middleware_tags('permission', PermissionsEnum::CategoriesRestore()));
        Route::patch('{category}/translations', [CategoryTranslationController::class, 'update'])
            ->name('translations.update')
            ->middleware(middleware_tags('permission', PermissionsEnum::CategoriesTranslationsUpdate()))
            ->middleware('throttle:5,1');
    });
});
