<?php

use App\Http\Controllers\CheckController;

Route::prefix('check')->name('check.')->group(function () {
    Route::get('olddb', [CheckController::class, 'olddb'])
        ->name('olddb');
});
