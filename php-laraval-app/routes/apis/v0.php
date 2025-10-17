<?php

use App\Enums\Auth\TokenAbilityEnum;
use App\Enums\CacheEnum;
use App\Enums\PermissionsEnum;
use App\Http\Controllers\Api\AreaController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AuthOtpController;
use App\Http\Controllers\Api\CartCheckoutController;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\DeviationController;
use App\Http\Controllers\Api\FeedbackUserController;
use App\Http\Controllers\Api\GeocodeController;
use App\Http\Controllers\Api\GlobalSettingController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ScheduleCleaningChangeRequestController;
use App\Http\Controllers\Api\ScheduleCleaningController;
use App\Http\Controllers\Api\ScheduleEmployeeController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\UsersController;
use App\Http\Middleware\Throttle;
use Illuminate\Support\Facades\Route;

Route::middleware(['localization', 'timezone'])->group(function () {
    Route::prefix('v0')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('throttle:5,1');
        Route::post('/forgot-password', [AuthController::class, 'resetPassword'])
            ->middleware('throttle:2,1');

        Route::post('otp/generate', [AuthOtpController::class, 'generate'])
            ->middleware('throttle:5,1');
        Route::post('otp/login', [AuthOtpController::class, 'login'])
            ->middleware('throttle:5,1');

        Route::post('auth/refresh', [AuthController::class, 'refresh'])
            ->middleware(Throttle::class.':1,5')
            ->middleware('auth:sanctum')
            ->middleware('ability:'.TokenAbilityEnum::IssueAccessToken());

        Route::get('settings/global', [GlobalSettingController::class, 'index'])
            ->middleware(middleware_tags('cache', CacheEnum::GlobalSettings()))
            ->middleware('etag');

        Route::prefix('areas')->group(function () {
            Route::get('', [AreaController::class, 'index'])
                ->middleware(middleware_tags('cache', CacheEnum::Areas()))
                ->middleware('etag');
            Route::get('{postalCode}', [AreaController::class, 'findByPostalCode'])
                ->middleware(middleware_tags('cache', CacheEnum::Areas()))
                ->middleware('etag');
        });

        Route::prefix('cities')->group(function () {
            Route::get('', [CityController::class, 'index'])
                ->middleware(middleware_tags('cache', CacheEnum::Cities()))
                ->middleware('etag');
        });

        Route::prefix('countries')->group(function () {
            Route::get('', [CountryController::class, 'index'])
                ->middleware(middleware_tags('cache', CacheEnum::Countries()))
                ->middleware('etag');
        });

        Route::get('geocode', [GeocodeController::class, 'show'])
            ->middleware(middleware_tags('cache', CacheEnum::Geocodes()))
            ->middleware('throttle:10,1')
            ->middleware('etag');

        Route::middleware(['auth:sanctum', 'ability:'.TokenAbilityEnum::APIAccess()])->group(function () {
            Route::group(['middleware' => [
                middleware_tags('permission', PermissionsEnum::AccessCustomerApp()),
            ]], function () {
                Route::prefix('users')->group(function () {
                    Route::get('credits', [UsersController::class, 'credits'])
                        ->middleware(middleware_tags('cache', CacheEnum::Credits()))
                        ->middleware('etag');
                    Route::get('schedule-cleanings', [UsersController::class, 'scheduleCleanings'])
                        ->middleware(middleware_tags('cache', CacheEnum::ScheduleCleanings()))
                        ->middleware('etag');
                    Route::get('schedule-cleanings/frequencies', [UsersController::class, 'scheduleFrequencies'])
                        ->middleware('etag');
                    Route::get('schedule-cleanings/services', [UsersController::class, 'scheduleServices'])
                        ->middleware('etag');
                });

                Route::prefix('carts')->group(function () {
                    Route::post('checkout', [CartCheckoutController::class, 'store'])
                        ->middleware(middleware_tags(
                            'cache',
                            CacheEnum::ScheduleEmployees(),
                            CacheEnum::ScheduleCleanings(),
                            CacheEnum::Credits(),
                            CacheEnum::Notifications(),
                        ))
                        ->middleware('throttle:5,1');
                });

                Route::prefix('schedule-cleanings')->group(function () {
                    Route::get('', [ScheduleCleaningController::class, 'index'])
                        ->middleware(middleware_tags('cache', CacheEnum::ScheduleCleanings()))
                        ->middleware('etag');
                    Route::get('{schedule}', [ScheduleCleaningController::class, 'show'])
                        ->middleware(middleware_tags(
                            'permission',
                            PermissionsEnum::SchedulesRead()
                        ))
                        ->middleware('etag');
                    Route::delete('{scheduleId}/cancel', [ScheduleCleaningController::class, 'cancel'])
                        ->middleware(middleware_tags(
                            'cache',
                            CacheEnum::ScheduleEmployees(),
                            CacheEnum::ScheduleCleanings(),
                            CacheEnum::Credits(),
                            CacheEnum::Notifications(),
                            CacheEnum::Invoices(),
                        ))
                        ->middleware('throttle:5,1');
                    Route::post('{schedule}/change', [ScheduleCleaningChangeRequestController::class, 'store'])
                        ->middleware(middleware_tags(
                            'cache',
                            CacheEnum::ScheduleEmployees(),
                            CacheEnum::ScheduleCleanings(),
                            CacheEnum::ChangeRequests(),
                        ))
                        ->middleware('throttle:5,1');
                    Route::patch('{scheduleId}', [ScheduleCleaningController::class, 'update'])
                        ->middleware(middleware_tags(
                            'cache',
                            CacheEnum::ScheduleEmployees(),
                            CacheEnum::ScheduleCleanings(),
                            CacheEnum::Notifications(),
                        ))
                        ->middleware('throttle:5,1');
                });

                Route::prefix('products')->group(function () {
                    Route::get('', [ProductController::class, 'index'])
                        ->middleware(middleware_tags('cache', CacheEnum::Products()))
                        ->middleware('etag');
                });
            });

            Route::group(['middleware' => [
                middleware_tags('permission', PermissionsEnum::AccessEmployeeApp()),
            ]], function () {
                Route::prefix('schedule-employees')->group(function () {
                    Route::get('', [ScheduleEmployeeController::class, 'index'])
                        ->middleware(middleware_tags('cache', CacheEnum::ScheduleEmployees()))
                        ->middleware('etag');
                    Route::get('{schedule}', [ScheduleEmployeeController::class, 'show'])->middleware('etag');
                    Route::post('{schedule}/start', [ScheduleEmployeeController::class, 'start'])
                        ->middleware(middleware_tags(
                            'cache',
                            CacheEnum::ScheduleCleanings(),
                            CacheEnum::ScheduleEmployees(),
                            CacheEnum::Notifications(),
                            CacheEnum::Deviations(),
                            CacheEnum::ScheduleCleaningDeviations(),
                        ))
                        ->middleware('throttle:1,5');
                    Route::post('{schedule}/end', [ScheduleEmployeeController::class, 'end'])
                        ->middleware(middleware_tags(
                            'cache',
                            CacheEnum::ScheduleCleanings(),
                            CacheEnum::ScheduleEmployees(),
                            CacheEnum::Notifications(),
                            CacheEnum::WorkHours(),
                            CacheEnum::Deviations(),
                            CacheEnum::ScheduleCleaningDeviations(),
                            CacheEnum::Invoices(),
                        ))
                        ->middleware('throttle:1,5');
                    Route::post('{scheduleId}/cancel', [ScheduleEmployeeController::class, 'cancel'])
                        ->middleware(middleware_tags(
                            'cache',
                            CacheEnum::ScheduleCleanings(),
                            CacheEnum::ScheduleEmployees(),
                            CacheEnum::Deviations(),
                            CacheEnum::ScheduleCleaningDeviations(),
                        ))
                        ->middleware('throttle:1,5');
                });
            });

            Route::group(['middleware' => [
                middleware_tags(
                    'permission',
                    PermissionsEnum::AccessCustomerApp(),
                    PermissionsEnum::AccessEmployeeApp()
                ),
            ]], function () {
                Route::post('/logout', [AuthController::class, 'logout']);

                Route::prefix('users')->group(function () {
                    Route::get('info', [UsersController::class, 'info'])
                        ->middleware(middleware_tags('cache', CacheEnum::Users()))
                        ->middleware('etag');
                    Route::patch('info', [UsersController::class, 'updateUserByAuth'])
                        ->middleware(middleware_tags('cache', CacheEnum::Users()))
                        ->middleware('throttle:5,1');
                });

                Route::prefix('settings')->group(function () {
                    Route::get('', [SettingController::class, 'index'])
                        ->middleware(middleware_tags('cache', CacheEnum::Settings()))
                        ->middleware('etag');
                    Route::patch('', [SettingController::class, 'update'])
                        ->middleware(middleware_tags('cache', CacheEnum::Settings()))
                        ->middleware('throttle:5,1');
                });

                Route::prefix('feedbacks')->group(function () {
                    Route::prefix('user')->group(function () {
                        Route::post('', [FeedbackUserController::class, 'store'])
                            ->middleware('throttle:5,1');
                    });
                });

                Route::prefix('notifications')->group(function () {
                    Route::get('', [NotificationController::class, 'index'])
                        ->middleware(middleware_tags('cache', CacheEnum::Notifications()))
                        ->middleware('etag');
                    Route::post('read', [NotificationController::class, 'readAll'])
                        ->middleware(middleware_tags('cache', CacheEnum::Notifications()));
                    Route::post('{notification}/read', [NotificationController::class, 'read'])
                        ->middleware(middleware_tags('cache', CacheEnum::Notifications()));
                });

                Route::prefix('deviations')->group(function () {
                    Route::post('', [DeviationController::class, 'store'])
                        ->middleware('throttle:5,1');
                });
            });
        });
    });
});
