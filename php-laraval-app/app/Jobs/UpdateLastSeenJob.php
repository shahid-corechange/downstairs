<?php

namespace App\Jobs;

use App\Models\User;

class UpdateLastSeenJob extends BaseJob
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
        protected User $user,
    ) {
        $this->queue = 'default';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->handleWrapper(function () {
            $this->user
                ->disableLogging()
                ->update(['last_seen' => now()]);
        });
    }
}
