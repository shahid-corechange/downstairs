<?php

use App\Http\Controllers\Auth\StoreSelectionController;

Route::prefix('stores')->name('stores.')->group(function () {
    Route::get('json', [StoreSelectionController::class, 'jsonIndex'])
        ->name('json.index')
        ->middleware('etag');
    Route::post('change/{storeId}', [StoreSelectionController::class, 'change'])->name('change');
});
