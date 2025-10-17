<?php

use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\WidgetAddOnController;

Route::prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('', [DashboardController::class, 'index'])->name('index');

    Route::prefix('widget')->name('widget.')->group(function () {
        Route::prefix('addons')->name('addons.')->group(function () {
            Route::get('statistic', [WidgetAddOnController::class, 'statistic'])
                ->name('statistic')
                ->middleware('etag');
        });
    });
});
