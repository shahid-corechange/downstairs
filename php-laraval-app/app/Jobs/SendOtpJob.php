<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\TwoFactorService;

class SendOtpJob extends BaseJob
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
        protected string $otp,
    ) {
        $this->queue = 'default';
    }

    /**
     * Execute the job.
     */
    public function handle(
        TwoFactorService $twoFactorService,
    ): void {
        $this->handleWrapper(function () use ($twoFactorService) {
            $twoFactorService->sendOtp($this->user, $this->otp);
        });
    }
}
