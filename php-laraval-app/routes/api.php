<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

require __DIR__.'/apis/v0.php';
require __DIR__.'/apis/v1.php';
require __DIR__.'/apis/v2.php';

Route::get('/health', \Spatie\Health\Http\Controllers\SimpleHealthCheckController::class)
    ->middleware('throttle:2,1');
