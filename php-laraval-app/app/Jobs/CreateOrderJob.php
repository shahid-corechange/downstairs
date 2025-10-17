<?php

namespace App\Jobs;

use App\Enums\Order\OrderStatusEnum;
use App\Models\ScheduleCleaning;
use App\Services\OrderService;

/**
 * @deprecated Use CreateOrderFromScheduleJob instead.
 */
class CreateOrderJob extends BaseJob
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
        protected ScheduleCleaning $cleaning,
    ) {
        $this->queue = 'default';
    }

    /**
     * Execute the job.
     */
    public function handle(
        OrderService $orderService,
    ): void {
        $this->handleWrapper(function () use ($orderService) {
            scoped_localize('sv_SE', function () use ($orderService) {
                $order = $this->cleaning->order()
                    ->where('user_id', $this->cleaning->subscription->user_id)
                    ->where('customer_id', $this->cleaning->subscription->customer_id)
                    ->where('service_id', $this->cleaning->subscription->service_id)
                    ->where('subscription_id', $this->cleaning->subscription_id)
                    ->where('status', OrderStatusEnum::Draft())
                    ->where('ordered_at', $this->cleaning->start_at)
                    ->first();

                if (! $order) {
                    [$order, $invoice] = $orderService->createOrder($this->cleaning);
                    $orderService->createOrderRows($order, $this->cleaning);
                    UpdateInvoiceSummationJob::dispatchSync($invoice);
                }
            });
        });
    }
}
