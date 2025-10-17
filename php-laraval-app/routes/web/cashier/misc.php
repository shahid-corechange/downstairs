<?php

use App\Enums\CacheEnum;
use App\Http\Controllers\Blockday\BlockDayController;
use App\Http\Controllers\CashierMisc\CashierDiscountController;
use App\Http\Controllers\CashierMisc\CashierEmployeeController;
use App\Http\Controllers\CashierMisc\CashierFixedPriceController;
use App\Http\Controllers\CashierMisc\CashierScheduleController;
use App\Http\Controllers\CashierMisc\CashierTeamController;
use App\Http\Controllers\Search\SearchController;

Route::get('search', [SearchController::class, 'index'])->name('search.index');
Route::get('teams/json', [CashierTeamController::class, 'jsonIndex'])
    ->name('teams.json')
    ->middleware(middleware_tags('cache', CacheEnum::Teams()))
    ->middleware('etag');
Route::get('blockdays/json', [BlockDayController::class, 'jsonIndex'])
    ->name('blockdays.json')
    ->middleware(middleware_tags('cache', CacheEnum::Blockdays()))
    ->middleware('etag');
Route::get('employees/json', [CashierEmployeeController::class, 'jsonIndex'])
    ->name('employees.json')
    ->middleware('etag');
Route::get('schedules/json', [CashierScheduleController::class, 'jsonIndex'])
    ->name('schedules.json')
    ->middleware(middleware_tags('cache', CacheEnum::Schedules()))
    ->middleware('etag');
Route::get('discounts/json', [CashierDiscountController::class, 'jsonIndex'])
    ->name('discounts.json')
    ->middleware(middleware_tags('cache', CacheEnum::CustomerDiscounts()))
    ->middleware('etag');
Route::get('users/{userId}/fixed-price/json', [CashierFixedPriceController::class, 'findFixedPriceByUser'])
    ->name('fixed-price.json')
    ->middleware(middleware_tags('cache', CacheEnum::FixedPrices()))
    ->middleware('etag');
