<?php

namespace App\Providers;

use App\Models\Notification;
use App\Models\Schedule;
use App\Models\ScheduleCleaning;
use App\Models\ScheduleEmployee;
use App\Policies\NotificationPolicy;
use App\Policies\ScheduleCleaningPolicy;
use App\Policies\ScheduleEmployeePolicy;
use App\Policies\SchedulePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        ScheduleCleaning::class => ScheduleCleaningPolicy::class,
        ScheduleEmployee::class => ScheduleEmployeePolicy::class,
        Notification::class => NotificationPolicy::class,
        Schedule::class => SchedulePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Implicitly grant "Super Admin" role all permissions
        // This works in the app by using gate-related functions like auth()->user->can() and @can()
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Superadmin') ? true : null;
        });
    }
}
