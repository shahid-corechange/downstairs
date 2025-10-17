<?php

namespace App\Jobs;

use App\Contracts\NotificationService;
use App\Enums\Notification\NotificationRegistrationActionEnum;
use App\Http\Traits\NotificationTrait;
use App\Models\User;

class NotificationRegistrationManagerJob extends BaseJob
{
    use NotificationTrait;

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
        protected string $action,
        protected string $devicePlatform,
        protected string $deviceToken
    ) {
        $this->queue = 'notifications';
    }

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notification): void
    {
        $this->handleWrapper(function () use ($notification) {
            if ($this->action == NotificationRegistrationActionEnum::Register()) {
                $this->register($this->user, $notification, $this->devicePlatform, $this->deviceToken);
            } elseif ($this->action == NotificationRegistrationActionEnum::Unregister()) {
                $this->unregister($this->user, $notification, $this->devicePlatform, $this->deviceToken);
            }
        });
    }
}
