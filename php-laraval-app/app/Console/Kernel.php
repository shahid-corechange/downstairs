<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\SubscriptionsToSchedules::class,
        Commands\Reminder::class,
        Commands\UpcomingReminder::class,
        Commands\ScheduleNotStartedCheck::class,
        Commands\BackupLogs::class,
        Commands\RemoveEndedSubscription::class,
        Commands\RemoveEndedDiscount::class,
        Commands\Fortnox\PollingFortnoxInvoiceStatus::class,
        Commands\Fortnox\CreateFortnoxInvoice::class,
        Commands\Fortnox\RecreateFortnoxCustomer::class,
        Commands\Fortnox\SentFortnoxInvoice::class,
        Commands\Fortnox\RenewFortnoxToken::class,
        Commands\Fortnox\Sync::class,
        Commands\Fortnox\SendLeaveRegistration::class,
        Commands\CacheTableSchema::class,
        Commands\RunPriceAdjustment::class,
        Commands\UpdateInvoiceSummation::class,
        Commands\MigrateSubscriptionCleaningDetails::class,
        Commands\MigrateScheduleCleanings::class,
        Commands\MigrateScheduleCleaningsByDate::class,
        Commands\AddCashierRole::class,
        Commands\AddStores::class,
        Commands\AddProducts::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        if (config('downstairs.schedule.flag.healthCheck')) {
            $schedule->command('health:check')->everyFifteenMinutes();
        }

        if (config('downstairs.schedule.flag.generateSchedules')) {
            $schedule->command('schedules:generate')->cron(config('downstairs.schedule.run.subscription'));
        }

        if (config('downstairs.schedule.flag.notifyReminder')) {
            $schedule->command('reminder:notify')->everyFifteenMinutes();
        }

        if (config('downstairs.schedule.flag.upcomingReminder')) {
            $schedule->command('reminder:upcoming')->hourly();
        }

        if (config('downstairs.schedule.flag.notStartedCheck')) {
            $schedule->command('schedule:not-started-check')->everyFiveMinutes();
        }

        if (config('downstairs.schedule.flag.renewFortnoxToken')) {
            $schedule->command('fortnox:renew-token')->hourly();
        }

        if (config('downstairs.schedule.flag.createFortnoxInvoice')) {
            $schedule->command('fortnox:create-invoice')->daily();
        }

        if (config('downstairs.schedule.flag.sentFortnoxInvoice')) {
            $schedule->command('fortnox:sent-invoice')->everySixHours();
        }

        if (config('downstairs.schedule.flag.syncFortnox')) {
            $schedule->command('fortnox:sync')->everySixHours();
        }

        if (config('downstairs.schedule.run.sendLeaveRegistration')) {
            $schedule->command('fortnox:send-leave-registration')
                ->cron(config('downstairs.schedule.run.sendLeaveRegistration'));
        }

        if (config('downstairs.schedule.flag.removeEndedSubscription')) {
            $schedule->command('subscription:remove-ended')->daily();
        }

        if (config('downstairs.schedule.flag.removeEndedDiscount')) {
            $schedule->command('discount:remove-ended')->daily();
        }

        if (config('downstairs.schedule.flag.backupLogs')) {
            $schedule->command('backup:logs')->cron(config('downstairs.schedule.run.logs'));
        }

        if (config('downstairs.schedule.flag.pollingFortnoxInvoiceStatus')) {
            $schedule->command('fortnox:polling-invoice-status')->hourly()->withoutOverlapping();
        }

        if (config('downstairs.schedule.flag.cleanActivityLog')) {
            /*
            *Clean up activity logs
            *Documentation: https://spatie.be/docs/laravel-activitylog/v4/basic-usage/cleaning-up-the-log
            */
            $schedule->command('activitylog:clean')->daily();
        }
        if (config('downstairs.schedule.flag.runPriceAdjustment')) {
            $schedule->command('price-adjustment:run')->cron(config('downstairs.schedule.run.priceAdjustment'));
        }
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
