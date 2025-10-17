<?php

use App\Http\Controllers\Fortnox\FortnoxController;

Route::prefix('fortnox')->name('fortnox.')->group(function () {
    Route::get('customer/activation', [FortnoxController::class, 'customer'])
        ->name('customer.activation');
    Route::get('employee/activation', [FortnoxController::class, 'employee'])
        ->name('employee.activation');
    Route::post('sync', [FortnoxController::class, 'sync'])
        ->name('sync');
});
