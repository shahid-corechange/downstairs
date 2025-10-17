<?php

use App\Enums\PermissionsEnum;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/**
 * For check the health of the application
 */
Route::get('/health', \Spatie\Health\Http\Controllers\HealthCheckResultsController::class);

Route::middleware(['localization'])->group(function () {
    Route::get('/', function () {
        if (session()->has('store_id')) {
            return redirect()->route('cashier.search.index');
        }

        return redirect()->route('dashboard.index');
    })->name('home');

    require __DIR__.'/web/auth.php';
});

/**
 * Routes for portal
 */
Route::middleware([
    'auth',
    'verified',
    'localization',
    'timezone',
    'prevent.portal.access',
    middleware_tags('permission', PermissionsEnum::AccessPortal()),
])
    ->group(function () {
        require __DIR__.'/web/dashboard.php';
        require __DIR__.'/web/profile.php';
        require __DIR__.'/web/team.php';
        require __DIR__.'/web/subscription.php';
        require __DIR__.'/web/property.php';
        require __DIR__.'/web/schedule.php';
        require __DIR__.'/web/log.php';
        require __DIR__.'/web/customer_discount.php';
        require __DIR__.'/web/fixedprice.php';
        require __DIR__.'/web/customer.php';
        require __DIR__.'/web/employee.php';
        require __DIR__.'/web/feedback.php';
        require __DIR__.'/web/blockday.php';
        require __DIR__.'/web/service_quarter.php';
        require __DIR__.'/web/service.php';
        require __DIR__.'/web/addon.php';
        require __DIR__.'/web/role.php';
        require __DIR__.'/web/order.php';
        require __DIR__.'/web/invoice.php';
        require __DIR__.'/web/setting.php';
        require __DIR__.'/web/keyplace.php';
        require __DIR__.'/web/fortnox.php';
        require __DIR__.'/web/deviation.php';
        require __DIR__.'/web/role.php';
        require __DIR__.'/web/check.php';
        require __DIR__.'/web/company_property.php';
        require __DIR__.'/web/company_subscription.php';
        require __DIR__.'/web/company_fixedprice.php';
        require __DIR__.'/web/company_customer_discount.php';
        require __DIR__.'/web/company.php';
        require __DIR__.'/web/credit.php';
        require __DIR__.'/web/time_report.php';
        require __DIR__.'/web/unassign_subscription.php';
        require __DIR__.'/web/leave_registration.php';
        require __DIR__.'/web/time_adjustment.php';
        require __DIR__.'/web/price_adjustment.php';
        require __DIR__.'/web/store.php';
        require __DIR__.'/web/category.php';
        require __DIR__.'/web/product.php';
        require __DIR__.'/web/laundry_order.php';
        require __DIR__.'/web/cashier_attendance.php';
    });

/**
 * Routes for cashier
 */
Route::prefix('cashier')
    ->name('cashier.')
    ->middleware([
        'auth',
        'verified',
        'localization',
        'timezone',
        'cashier',
    ])
    ->group(function () {
        require __DIR__.'/web/cashier/misc.php';
        require __DIR__.'/web/cashier/customer.php';
        require __DIR__.'/web/cashier/store.php';
        require __DIR__.'/web/cashier/order.php';
        require __DIR__.'/web/cashier/sale.php';
        require __DIR__.'/web/cashier/product.php';
        require __DIR__.'/web/cashier/attendance.php';
        require __DIR__.'/web/cashier/profile.php';
    });
