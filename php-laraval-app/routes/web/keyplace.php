<?php

use App\Enums\CacheEnum;
use App\Enums\PermissionsEnum;
use App\Http\Controllers\KeyPlace\KeyPlaceController;

Route::prefix('keyplaces')->name('keyplaces.')->group(function () {
    Route::get('', [KeyPlaceController::class, 'index'])
        ->name('index')
        ->middleware(middleware_tags('permission', PermissionsEnum::KeyPlacesIndex()));
    Route::get('json', [KeyPlaceController::class, 'jsonIndex'])
        ->name('json')
        ->middleware(middleware_tags('permission', PermissionsEnum::KeyPlacesIndex()))
        ->middleware(middleware_tags('cache', CacheEnum::KeyPlaces()))
        ->middleware('etag');
});
