<?php

namespace App\Jobs;

use App\Models\Subscription;
use DB;
use Illuminate\Database\Eloquent\Collection;

class UpdateScheduleItemJob extends BaseJob
{
    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected Subscription $subscription,
        protected array $products,
        protected array $addonIds,
    ) {
        $this->queue = 'default';
    }

    /**
     * Job for update all schedule cleaning products that related to selected subscription.
     * This job will triger when updating the subscripton.
     */
    public function handle(): void
    {
        $this->handleWrapper(function () {
            if (empty($this->products) && empty($this->addonIds)) {
                return;
            }

            $scheduleCleanings = $this->getScheduleCleanings($this->subscription);

            DB::transaction(function () use ($scheduleCleanings) {
                foreach ($scheduleCleanings as $scheduleCleaning) {
                    $scheduleCleaning->products()->sync($this->products);
                    $scheduleCleaning->addons()->sync($this->addonIds);
                }
            });
        });
    }

    /**
     * Get the schedule cleanings that related to the subscription.
     *
     * @return Collection<int, \App\Models\ScheduleCleaning>
     */
    private function getScheduleCleanings(Subscription $subscription)
    {
        return $subscription
            ->scheduleCleanings()
            ->future()
            ->with('items')
            ->get();
    }
}
