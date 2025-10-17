<?php

namespace App\Jobs\Order;

use App\Enums\Order\OrderStatusEnum;
use App\Jobs\BaseJob;
use App\Jobs\UpdateInvoiceSummationJob;
use App\Models\Schedule;
use App\Services\Order\OrderCleaningService;

class CreateOrderFromScheduleJob extends BaseJob
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
        protected Schedule $schedule,
    ) {
        $this->queue = 'default';
    }

    /**
     * Execute the job.
     */
    public function handle(
        OrderCleaningService $orderService,
    ): void {
        $this->handleWrapper(function () use ($orderService) {
            scoped_localize('sv_SE', function () use ($orderService) {
                $order = $this->schedule->order()
                    ->where('user_id', $this->schedule->user_id)
                    ->where('customer_id', $this->schedule->customer_id)
                    ->where('service_id', $this->schedule->service_id)
                    ->where('subscription_id', $this->schedule->subscription_id)
                    ->where('status', OrderStatusEnum::Draft())
                    ->where('ordered_at', $this->schedule->start_at)
                    ->first();

                if (! $order) {
                    [$order, $invoice] = $orderService->createOrder($this->schedule);
                    $orderService->createOrderRows($order, $this->schedule);
                    UpdateInvoiceSummationJob::dispatchSync($invoice);
                }
            });
        });
    }
}
