<?php

namespace App\Providers;

use App\Checks\Api46ElksCheck;
use App\Checks\ApiFortnoxCustomerCheck;
use App\Checks\ApiFortnoxEmployeeCheck;
use App\Checks\MailCheck;
use Illuminate\Support\ServiceProvider;
use Spatie\CpuLoadHealthCheck\CpuLoadCheck;
use Spatie\Health\Checks\Checks\CacheCheck;
use Spatie\Health\Checks\Checks\DatabaseCheck;
use Spatie\Health\Checks\Checks\DatabaseConnectionCountCheck;
use Spatie\Health\Checks\Checks\DatabaseSizeCheck;
use Spatie\Health\Checks\Checks\EnvironmentCheck;
use Spatie\Health\Checks\Checks\RedisCheck;
use Spatie\Health\Checks\Checks\RedisMemoryUsageCheck;
use Spatie\Health\Checks\Checks\UsedDiskSpaceCheck;
use Spatie\Health\Facades\Health;

class HealthServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Health::checks([
            EnvironmentCheck::new()->expectEnvironment(config('app.env')),
            CpuLoadCheck::new(),
            UsedDiskSpaceCheck::new(),
            DatabaseCheck::new(),
            DatabaseConnectionCountCheck::new(),
            DatabaseSizeCheck::new(),
            RedisCheck::new(),
            RedisMemoryUsageCheck::new()->failWhenAboveMb(config('health.checks.redis.usage')),
            CacheCheck::new(),
            ApiFortnoxCustomerCheck::new()->name('FortnoxCustomerApi')->label('Fortnox Customer'),
            ApiFortnoxEmployeeCheck::new()->name('FortnoxEmployeeApi')->label('Fortnox Employee'),
            Api46ElksCheck::new()->name('46elksApi')->label('SMS'),
            MailCheck::new()->name('Mail')->label('Mail'),
        ]);
    }
}
