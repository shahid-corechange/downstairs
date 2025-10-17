<?php

namespace App\Jobs;

use App\Models\LaundryOrder;
use App\Services\Order\OrderStoreLaundryService;

class UpdateLaundryOrderInvoiceJob extends BaseJob
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
        protected LaundryOrder $laundryOrder,
    ) {
        $this->queue = 'default';
    }

    /**
     * Execute the job.
     */
    public function handle(
        OrderStoreLaundryService $orderStoreLaundryService,
    ): void {
        $this->handleWrapper(function () use ($orderStoreLaundryService) {
            $orderStoreLaundryService->syncOrderRows($this->laundryOrder);
        });
    }
}
