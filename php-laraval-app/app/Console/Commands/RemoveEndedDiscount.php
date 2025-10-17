<?php

namespace App\Console\Commands;

use App\Models\CustomerDiscount;
use Illuminate\Console\Command;
use Log;

class RemoveEndedDiscount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discount:remove-ended';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove ended discounts';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        /**
         * Soft delete ended discounts, because it needs to be kept for create invoice.
         */
        $querySoftDeleted = CustomerDiscount::whereNotNull('end_date')
            ->where('end_date', '<', now());
        $totalSoftDeleted = $querySoftDeleted->count();
        $querySoftDeleted->delete();

        /**
         * Force delete if usage limit is 0.
         * Force delete discounts if end date after 1 month from end date.
         */
        $queryForceDeleted = CustomerDiscount::withTrashed()
            ->where('usage_limit', 0)
            ->orWhere(function ($query) {
                $query->whereNotNull('end_date')
                    ->where('end_date', '<', now()->subMonth());
            });
        $totalForceDeleted = $queryForceDeleted->count();
        $queryForceDeleted->forceDelete();

        $info = 'Ended discounts have been removed.'.
            " Soft deleted: {$totalSoftDeleted},".
            " Force deleted: {$totalForceDeleted}";
        Log::channel('daily')->info($info);
    }
}
