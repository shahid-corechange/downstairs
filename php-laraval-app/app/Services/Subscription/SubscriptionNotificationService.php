<?php

namespace App\Services\Subscription;

use App\Enums\Azure\NotificationHub\NotificationHubEnum;
use App\Enums\Notification\NotificationTypeEnum;
use App\Enums\Subscription\SubscriptionFrequencyEnum;
use App\Helpers\Notification\AppNotificationOptions;
use App\Helpers\Notification\SendNotificationOptions;
use App\Helpers\Notification\SMSNotificationOptions;
use App\Helpers\Util\TextTranslation;
use App\Jobs\SendNotificationJob;
use App\Models\Subscription;
use App\Models\SubscriptionCleaningDetail;
use App\Models\SubscriptionLaundryDetail;
use Carbon\Carbon;

class SubscriptionNotificationService
{
    /**
     * Send notifications when a subscription is created.
     */
    public function sendCreated(Subscription $subscription)
    {
        // send notification to customer
        scoped_localize($subscription->user->info->language, function () use ($subscription) {
            [$startAt, $endAt] = $this->getDateTimes($subscription);
            $text = $subscription->frequency === SubscriptionFrequencyEnum::Once() ?
                'notification body subscription one time created' : 'notification body subscription created';
            $smsText = $subscription->frequency === SubscriptionFrequencyEnum::Once() ?
                'notification body subscription one time created sms' : 'notification body subscription created sms';

            SendNotificationJob::dispatchAfterResponse(
                $subscription->user,
                new SendNotificationOptions(
                    new AppNotificationOptions(
                        NotificationHubEnum::Customer(),
                        NotificationTypeEnum::SubscriptionAdded(),
                    ),
                    new SMSNotificationOptions(
                        body: __($smsText, [
                            'service' => $subscription->service->name,
                            'start_at' => $startAt->format('Y-m-d'),
                            'time' => "{$startAt->format('H:i')} - {$endAt->format('H:i')}",
                        ]),
                    ),
                    title: __('notification title subscription created'),
                    body: __($text, [
                        'customer' => $subscription->user->first_name,
                        'service' => $subscription->service->name,
                        'start_at' => $startAt->format('Y-m-d'),
                        'time' => "{$startAt->format('H:i')} - {$endAt->format('H:i')}",
                    ]),
                    shouldSave: true,
                    shouldInferMethod: true,
                ),
            );
        });

        // send notification to employee
        $subscription->staffs->each(
            function ($staff) {
                scoped_localize($staff->user->info->language, function () use ($staff) {
                    SendNotificationJob::dispatchAfterResponse(
                        $staff->user,
                        new SendNotificationOptions(
                            new AppNotificationOptions(
                                NotificationHubEnum::Employee(),
                                NotificationTypeEnum::ScheduleAdded(),
                                __('notification title schedule added'),
                                __('notification body some schedule added', [
                                    'worker' => $staff->user->first_name,
                                ]),
                            ),
                            shouldSave: true,
                        ),
                    );
                });
            }
        );
    }

    public function sendUpdated(Subscription $subscription)
    {
        [$startAt, $endAt] = $this->getDateTimes($subscription);

        $this->send(
            $subscription,
            NotificationTypeEnum::SubscriptionUpdated(),
            new TextTranslation('notification title subscription updated'),
            new TextTranslation('notification body subscription updated', [
                'customer' => $subscription->user->first_name,
                'service' => $subscription->service->name,
                'start_at' => $startAt->format('Y-m-d'),
                'time' => "{$startAt->format('H:i')} - {$endAt->format('H:i')}",
            ]),
            NotificationTypeEnum::ScheduleUpdated(),
            new TextTranslation('notification title schedule updated'),
            'notification body some schedule updated',
            new TextTranslation(),
        );
    }

    public function sendRemoved(Subscription $subscription)
    {
        $this->send(
            $subscription,
            NotificationTypeEnum::ScheduleUpdated(),
            new TextTranslation('notification title schedule cleaning removed'),
            new TextTranslation('notification body some schedule cleaning removed', [
                'user' => $subscription->user->first_name,
            ]),
            NotificationTypeEnum::ScheduleUpdated(),
            new TextTranslation('notification title schedule cleaning removed'),
            'notification body some schedule cleaning removed',
            new TextTranslation('notification body some schedule cleaning removed sms'),
            true,
        );
    }

    public function sendPaused(Subscription $subscription)
    {
        [$startAt, $endAt] = $this->getDateTimes($subscription);

        $this->send(
            $subscription,
            NotificationTypeEnum::SubscriptionPaused(),
            new TextTranslation('notification title subscription paused'),
            new TextTranslation('notification body subscription paused', [
                'customer' => $subscription->user->first_name,
                'service' => $subscription->service->name,
                'start_at' => $startAt->format('Y-m-d'),
                'time' => "{$startAt->format('H:i')} - {$endAt->format('H:i')}",
            ]),
            NotificationTypeEnum::ScheduleUpdated(),
            new TextTranslation('notification title schedule cleaning removed'),
            'notification body some schedule cleaning removed',
            new TextTranslation(),
        );
    }

    public function sendContinued(Subscription $subscription)
    {
        [$startAt, $endAt] = $this->getDateTimes($subscription);

        $this->send(
            $subscription,
            NotificationTypeEnum::SubscriptionContinued(),
            new TextTranslation('notification title subscription continued'),
            new TextTranslation('notification body subscription continued', [
                'customer' => $subscription->user->first_name,
                'service' => $subscription->service->name,
                'start_at' => $startAt->format('Y-m-d'),
                'time' => "{$startAt->format('H:i')} - {$endAt->format('H:i')}",
            ]),
            NotificationTypeEnum::ScheduleUpdated(),
            new TextTranslation('notification title schedule updated'),
            'notification body some schedule cleaning updated',
            new TextTranslation('notification body subscription continued sms', [
                'service' => $subscription->service->name,
                'start_at' => $startAt->format('Y-m-d'),
                'time' => "{$startAt->format('H:i')} - {$endAt->format('H:i')}",
            ]),
            true,
        );
    }

    private function send(
        Subscription $subscription,
        string $notificationCustomerType,
        TextTranslation $notificationCustomerTitle,
        TextTranslation $notificationCustomerBody,
        string $notificationEmployeeType,
        TextTranslation $notificationEmployeeTitle,
        string $notificationEmployeeBody,
        TextTranslation $notificationCustomerSmsBody,
        bool $shouldInferMethod = false,
    ) {
        // send notification to customer
        scoped_localize($subscription->user->info->language, function () use (
            $subscription,
            $notificationCustomerType,
            $notificationCustomerTitle,
            $notificationCustomerBody,
            $notificationCustomerSmsBody,
            $shouldInferMethod,
        ) {
            SendNotificationJob::dispatchAfterResponse(
                $subscription->user,
                new SendNotificationOptions(
                    new AppNotificationOptions(
                        NotificationHubEnum::Customer(),
                        $notificationCustomerType,
                    ),
                    new SMSNotificationOptions(
                        body: $notificationCustomerSmsBody->get(),
                    ),
                    title: $notificationCustomerTitle->get(),
                    body: $notificationCustomerBody->get(),
                    shouldSave: true,
                    shouldInferMethod: $shouldInferMethod,
                ),
            );
        });

        // send notification to employee
        $subscription->staffs->each(
            function ($staff) use (
                $notificationEmployeeType,
                $notificationEmployeeTitle,
                $notificationEmployeeBody,
            ) {
                scoped_localize($staff->user->info->language, function () use (
                    $staff,
                    $notificationEmployeeType,
                    $notificationEmployeeTitle,
                    $notificationEmployeeBody,
                ) {
                    SendNotificationJob::dispatchAfterResponse(
                        $staff->user,
                        new SendNotificationOptions(
                            new AppNotificationOptions(
                                NotificationHubEnum::Employee(),
                                $notificationEmployeeType,
                                $notificationEmployeeTitle->get(),
                                __($notificationEmployeeBody, [
                                    'user' => $staff->user->first_name,
                                ]),
                            ),
                            shouldSave: true,
                        ),
                    );
                });
            }
        );
    }

    private function getDateTimes(Subscription $subscription)
    {
        /** @var SubscriptionCleaningDetail|SubscriptionLaundryDetail $detail */
        $detail = $subscription->subscribable;
        /** @var Carbon $startTime */
        $startTime = $subscription->isCleaning() ? $detail->start_time : $detail->pickup_time;
        $endTime = $subscription->isCleaning() ? $detail->end_time : $startTime->addMinutes(15);

        $startAt = $subscription->start_at
            ->copy()
            ->setTimeFromTimeString($startTime)
            ->timezone('Europe/Stockholm');
        $endAt = $subscription->start_at
            ->copy()
            ->setTimeFromTimeString($endTime)
            ->timezone('Europe/Stockholm');

        return [$startAt, $endAt];
    }
}
