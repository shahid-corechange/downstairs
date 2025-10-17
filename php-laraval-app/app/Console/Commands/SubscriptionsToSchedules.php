<?php

namespace App\Console\Commands;

use App\Enums\CacheEnum;
use App\Models\Subscription;
use App\Models\SubscriptionCleaningDetail;
use App\Services\Subscription\SubscriptionCleaningService;
use App\Services\Subscription\SubscriptionLaundryService;
use Cache;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Log;

class SubscriptionsToSchedules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedules:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate schedules from subscriptions';

    /**
     * Execute the console command.
     */
    public function handle(
        SubscriptionCleaningService $cleaningService,
        SubscriptionLaundryService $laundryService
    ) {
        /**
         * Get all subscriptions that are not paused, not ended, and not yet started
         * and have not yet reached the refill sequence.
         */
        $subscriptions = Subscription::where('is_paused', 0)
            ->where(function (Builder $query) {
                $query->whereNull('end_at')->orWhere('end_at', '>=', now());
            })
            // TODO: Remove this when implementing laundry subscription
            ->where('subscribable_type', SubscriptionCleaningDetail::class)
            ->where('start_at', '<', now())
            ->get();

        if ($subscriptions->count() > 0) {
            foreach ($subscriptions as $subscription) {
                $service = $subscription->isCleaning() ? $cleaningService : $laundryService;
                $service->createNextSchedules($subscription, 0);
            }

            Cache::tags([
                CacheEnum::Schedules(),
                CacheEnum::ScheduleEmployees(),
                CacheEnum::LaundryOrders(),
            ])->flush();
        }

        Log::channel('schedules_generate')
            ->info('Generating schedules from subscriptions. Total subscriptions: '.$subscriptions->count());
    }
}
