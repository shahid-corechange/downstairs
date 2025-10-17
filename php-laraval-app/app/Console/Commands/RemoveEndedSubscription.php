<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Command;
use Log;

class RemoveEndedSubscription extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscription:remove-ended';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove ended subscriptions';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $query = Subscription::inactive();
        $total = $query->count();
        $query->delete();

        $info = 'Ended subscriptions have been removed.'.
            " Total: {$total}";
        Log::channel('daily')->info($info);
    }
}
