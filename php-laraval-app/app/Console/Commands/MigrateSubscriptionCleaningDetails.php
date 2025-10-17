<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\SubscriptionCleaningDetail;
use DB;
use Illuminate\Console\Command;

class MigrateSubscriptionCleaningDetails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscription-cleaning-details:migrate {start_id} {end_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate subscription cleaning details from subscriptions';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $startId = $this->argument('start_id');
        $endId = $this->argument('end_id');

        $subscriptions = Subscription::withTrashed()->whereBetween('id', [$startId, $endId])->get();

        foreach ($subscriptions as $subscription) {
            if (! $subscription->subscribable_id) {
                DB::transaction(function () use ($subscription) {
                    $detail = SubscriptionCleaningDetail::create([
                        'property_id' => $subscription->property_id,
                        'team_id' => $subscription->team_id,
                        'quarters' => $subscription->quarters,
                        'start_time' => $subscription->start_time_at,
                        'end_time' => $subscription->end_time_at,
                    ]);

                    $subscription->update([
                        'subscribable_type' => SubscriptionCleaningDetail::class,
                        'subscribable_id' => $detail->id,
                    ]);
                });
            }
        }
    }
}
