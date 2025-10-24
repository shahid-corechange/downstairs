<?php

use App\Enums\Auth\TokenAbilityEnum;
use App\Enums\CacheEnum;
use App\Enums\PermissionsEnum;
use App\Http\Controllers\Api\V2\AddOnController;
use App\Http\Controllers\Api\V2\CartCheckoutController;
use App\Http\Controllers\Api\V2\DeviationController;
use App\Http\Controllers\Api\V2\ScheduleChangeRequestController;
use App\Http\Controllers\Api\V2\ScheduleController;
use App\Http\Controllers\Api\V2\ScheduleEmployeeController;
use App\Http\Controllers\Api\V2\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['localization', 'timezone'])->group(function () {
    Route::prefix('v2')->group(function () {
        Route::middleware(['auth:sanctum', 'ability:'.TokenAbilityEnum::APIAccess()])->group(function () {
            Route::group(['middleware' => [
                middleware_tags('permission', PermissionsEnum::AccessCustomerApp()),
            ]], function () {
                Route::prefix('users/schedules')->group(function () {
                    Route::get('', [UserController::class, 'schedules'])
                        ->middleware(middleware_tags('cache', CacheEnum::Schedules()))
                        ->middleware('etag');
                    Route::get('frequencies', [UserController::class, 'scheduleFrequencies'])
                        ->middleware('etag');
                    Route::get('services', [UserController::class, 'scheduleServices'])
                        ->middleware('etag');
                });

                Route::prefix('carts')->group(function () {
                    Route::post('checkout', [CartCheckoutController::class, 'store'])
                        ->middleware(middleware_tags(
                            'cache',
                            CacheEnum::ScheduleEmployees(),
                            CacheEnum::Schedules(),
                            CacheEnum::Credits(),
                            CacheEnum::Notifications(),
                        ))
                        ->middleware('throttle:5,1');
                });

                Route::prefix('schedules')->group(function () {
                    Route::get('', [ScheduleController::class, 'index'])
                        ->middleware(middleware_tags('cache', CacheEnum::Schedules()))
                        ->middleware('etag');
                    Route::get('{schedule}', [ScheduleController::class, 'show'])
                        ->middleware(middleware_tags(
                            'permission',
                            PermissionsEnum::SchedulesRead()
                        ))
                        ->middleware('etag');
                    Route::delete('{scheduleId}/cancel', [ScheduleController::class, 'cancel'])
                        ->middleware(middleware_tags(
                            'cache',
                            CacheEnum::ScheduleEmployees(),
                            CacheEnum::Schedules(),
                            CacheEnum::Credits(),
                            CacheEnum::Notifications(),
                            CacheEnum::Invoices(),
                        ))
                        ->middleware('throttle:5,1');
                    Route::post('{schedule}/change', [ScheduleChangeRequestController::class, 'store'])
                        ->middleware(middleware_tags(
                            'cache',
                            CacheEnum::ScheduleEmployees(),
                            CacheEnum::Schedules(),
                            CacheEnum::ChangeRequests(),
                        ))
                        ->middleware('throttle:5,1');
                    Route::patch('{scheduleId}', [ScheduleController::class, 'update'])
                        ->middleware(middleware_tags(
                            'cache',
                            CacheEnum::ScheduleEmployees(),
                            CacheEnum::Schedules(),
                            CacheEnum::Notifications(),
                        ))
                        ->middleware('throttle:5,1');
                });

                Route::get('addons', [AddOnController::class, 'index'])
                    ->middleware(middleware_tags('cache', CacheEnum::Addons()))
                    ->middleware('etag');
            });

            Route::group(['middleware' => [
                middleware_tags('permission', PermissionsEnum::AccessEmployeeApp()),
            ]], function () {
                Route::prefix('schedule-employees')->group(function () {
                    Route::get('', [ScheduleEmployeeController::class, 'index'])
                        ->middleware(middleware_tags('cache', CacheEnum::ScheduleEmployees()))
                        ->middleware('etag');
                    Route::get('{scheduleId}', [ScheduleEmployeeController::class, 'show'])
                        ->middleware(middleware_tags(
                            'cache',
                            CacheEnum::ScheduleEmployees(),
                        ))
                        ->middleware('etag');

                    Route::group(['middleware' => [
                        middleware_tags('permission', PermissionsEnum::AccessEmployeeApp()),
                        middleware_tags(
                            'cache',
                            CacheEnum::Schedules(),
                            CacheEnum::ScheduleEmployees(),
                            CacheEnum::Deviations(),
                            CacheEnum::ScheduleDeviations(),
                        ),
                    ]], function () {
                        Route::post('{schedule}/start', [ScheduleEmployeeController::class, 'start'])
                            ->middleware(middleware_tags(
                                'cache',
                                CacheEnum::Notifications(),
                            ))
                            ->middleware('throttle:5,1');
                        Route::post('{schedule}/end', [ScheduleEmployeeController::class, 'end'])
                            ->middleware(middleware_tags(
                                'cache',
                                CacheEnum::Notifications(),
                                CacheEnum::WorkHours(),
                                CacheEnum::Invoices(),
                            ))
                            ->middleware('throttle:5,1');
                        Route::post('{scheduleId}/cancel', [ScheduleEmployeeController::class, 'cancel'])
                            ->middleware('throttle:5,1');
                    });
                });
            });

            Route::group(['middleware' => [
                middleware_tags(
                    'permission',
                    PermissionsEnum::AccessCustomerApp(),
                    PermissionsEnum::AccessEmployeeApp()
                ),
            ]], function () {
                Route::prefix('deviations')->group(function () {
                    Route::post('', [DeviationController::class, 'store'])
                        ->middleware('throttle:5,1');
                });
            });
        });
    });
});
