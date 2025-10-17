<?php

namespace App\Jobs;

use App\Contracts\NotificationService;
use App\Contracts\SMSService;
use App\Enums\User\UserNotificationMethodEnum;
use App\Helpers\Notification\AndroidNotificationOptions;
use App\Helpers\Notification\IOSNotificationOptions;
use App\Helpers\Notification\SendNotificationOptions;
use App\Helpers\SMS\SMSTemplate;
use App\Http\Traits\NotificationTrait;
use App\Models\Notification;
use App\Models\User;
use App\Notifications\GenericNotification;

class SendNotificationJob extends BaseJob
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
        protected SendNotificationOptions $options,
    ) {
        $this->queue = 'notifications';
    }

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService, SMSService $smsService): void
    {
        if (! app()->environment('local')) {
            $this->handleWrapper(function () use ($notificationService, $smsService) {
                if (! $this->options->shouldInferMethod) {
                    return $this->sendViaApp($notificationService);
                }

                $notificationMethod = $this->user->info->notification_method;

                if ($notificationMethod === UserNotificationMethodEnum::Email()) {
                    return $this->sendViaEmail();
                } elseif ($notificationMethod === UserNotificationMethodEnum::SMS()) {
                    return $this->sendViaSMS($smsService);
                }

                return $this->sendViaApp($notificationService);
            });
        }
    }

    private function sendViaApp(NotificationService $notificationService): void
    {
        $options = $this->options->appOptions;
        $title = $options->title ?? $this->options->title;
        $body = $options->body ?? $this->options->body;

        $notificationService->hub($options->hub)->send(
            $this->userTag($this->user->id),
            $options->androidOptions ?: AndroidNotificationOptions::defaultOptions(),
            $options->iosOptions ?: IOSNotificationOptions::defaultOptions(),
            [
                ...$options->payload,
                'type' => $options->type,
            ],
            $title,
            $body,
        );

        if ($this->options->shouldSave) {
            Notification::create([
                'user_id' => $this->user->id,
                'hub' => $options->hub,
                'type' => $options->type,
                'title' => $title,
                'description' => $body,
            ]);
        }
    }

    private function sendViaEmail(): void
    {
        scoped_localize($this->user->info->language, function () {
            $options = $this->options->emailOptions;
            $title = $options?->title ?? $this->options->title;
            $body = $options?->body ?? $this->options->body;

            $this->user->notify(new GenericNotification($title, $body));
        });
    }

    private function sendViaSMS(SMSService $smsService): void
    {
        $options = $this->options->smsOptions;
        $title = $options?->title ?? $this->options->title;
        $body = $options?->body ?? $this->options->body;

        $smsService->personalize(SMSTemplate::NOTIFICATION_TEMPLATE, $title, $body)
            ->send($this->user->cellphone);
    }
}
