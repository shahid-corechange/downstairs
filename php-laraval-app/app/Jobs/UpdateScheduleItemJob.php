<?php

namespace App\Jobs;

use App\Models\Addon;
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

            $schedules = $this->getSchedules($this->subscription);
            // get add ons if not empty
            $addonData = [];
            if (! empty($this->addonIds)) {
                $addons = Addon::whereIn('id', $this->addonIds)->get(['id', 'price']);
                // build sync payload keyed by addon id with pivot attributes
                $addonData = $addons->mapWithKeys(function ($addon) {
                    return [
                        $addon->id => [
                            'price' => $addon->price,
                            // quantity, discount_percentage and payment_method will use defaults or remain unchanged
                        ],
                    ];
                })->toArray();
            }

            DB::transaction(function () use ($schedules, $addonData) {
                foreach ($schedules as $schedule) {
                    $schedule->products()->sync($this->products);
                    $schedule->addons()->sync($addonData);
                }
            });
        });
    }

    /**
     * Get the schedule cleanings that related to the subscription.
     *
     * @return Collection<int, \App\Models\ScheduleCleaning>
     */
    private function getSchedules(Subscription $subscription)
    {
        return $subscription
            ->schedules()
            ->future()
            ->with('items')
            ->get();
    }
}
