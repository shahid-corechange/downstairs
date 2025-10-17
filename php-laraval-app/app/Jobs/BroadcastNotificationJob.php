<?php

namespace App\Jobs;

use App\Contracts\NotificationService;
use App\Helpers\Notification\AndroidNotificationOptions;
use App\Helpers\Notification\IOSNotificationOptions;
use App\Http\Traits\NotificationTrait;

class BroadcastNotificationJob extends BaseJob
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
        protected string $hub,
        protected string $type,
        protected string $title,
        protected string $body,
        protected array $payload,
        protected ?AndroidNotificationOptions $androidOptions = null,
        protected ?IOSNotificationOptions $iosOptions = null,
    ) {
        $this->queue = 'notifications';
    }

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notification): void
    {
        if (! app()->environment('local')) {
            $this->handleWrapper(function () use ($notification) {
                $notification->hub($this->hub)->broadcast(
                    $this->androidOptions ?: AndroidNotificationOptions::defaultOptions(),
                    $this->iosOptions ?: IOSNotificationOptions::defaultOptions(),
                    [
                        ...$this->payload,
                        'type' => $this->type,
                    ],
                    $this->title,
                    $this->body,
                );
            });
        }
    }
}
