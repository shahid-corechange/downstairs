<?php

namespace App\Services\Schedule;

use App\DTOs\Notification\NotificationSchedulePayloadDTO;
use App\Enums\Azure\NotificationHub\NotificationHubEnum;
use App\Enums\Notification\NotificationTypeEnum;
use App\Enums\Schedule\ScheduleStatusEnum;
use App\Helpers\Notification\AppNotificationOptions;
use App\Helpers\Notification\SendNotificationOptions;
use App\Helpers\Notification\SMSNotificationOptions;
use App\Jobs\SendNotificationJob;
use App\Models\Schedule;
use App\Models\ScheduleEmployee;
use App\Models\Store;
use App\Models\User;
use App\Services\CreditService;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class ScheduleService
{
    public function __construct(
        public OrderService $orderService,
        public CreditService $creditService,
    ) {
    }

    /**
     * Normalize time for schedule so it's not affected by DST.
     */
    public static function normalizeTime(
        Carbon|string $subscriptionStart,
        Carbon|string $startAt,
        Carbon|string $endAt,
        string $timezone = 'Europe/Stockholm'
    ) {
        $scheduleStart = $startAt instanceof Carbon
            ? $startAt->copy()->setTimezone($timezone)
            : Carbon::parse($startAt)->setTimezone($timezone);
        $scheduleEnd = $endAt instanceof Carbon
            ? $endAt->copy()->setTimezone($timezone)
            : Carbon::parse($endAt)->setTimezone($timezone);

        $subscriptionOffset = $subscriptionStart->format('O') / 100;
        $scheduleStartOffset = $scheduleStart->format('O') / 100;
        $scheduleEndOffset = $scheduleEnd->format('O') / 100;

        if ($scheduleStartOffset !== $subscriptionOffset) {
            $scheduleStart->addHours($subscriptionOffset - $scheduleStartOffset);
        }

        if ($scheduleEndOffset !== $subscriptionOffset) {
            $scheduleEnd->addHours($subscriptionOffset - $scheduleEndOffset);
        }

        return [$scheduleStart->utc(), $scheduleEnd->utc()];
    }

    /**
     * Send notification to customer and employee.
     */
    public static function sendNotif(
        Schedule $schedule,
        string $notificationType,
        string $title,
        string $employeeBody,
        string $customerBody,
        bool $shouldInferMethod = false,
    ): void {
        // send notification to employee
        $schedule->scheduleEmployees->each(
            function (ScheduleEmployee $scheduleEmployee) use (
                $schedule,
                $notificationType,
                $title,
                $employeeBody,
            ) {
                scoped_localize($scheduleEmployee->user->info->language, function () use (
                    $schedule,
                    $scheduleEmployee,
                    $notificationType,
                    $title,
                    $employeeBody,
                ) {
                    $startAt = $schedule->start_at
                        ->copy()
                        ->timezone('Europe/Stockholm');

                    SendNotificationJob::dispatchAfterResponse(
                        $scheduleEmployee->user,
                        new SendNotificationOptions(
                            new AppNotificationOptions(
                                NotificationHubEnum::Employee(),
                                $notificationType,
                                __($title),
                                __($employeeBody, [
                                    'worker' => $scheduleEmployee->user->first_name,
                                    'date' => $startAt->format('Y-m-d'),
                                    'time' => $startAt->format('H:i'),
                                ]),
                                NotificationSchedulePayloadDTO::from([
                                    'id' => $scheduleEmployee->id,
                                    'start_at' => $schedule->start_at,
                                ])->toArray(),
                            ),
                            shouldSave: true,
                        ),
                    );
                });
            }
        );

        // send notification to customer
        scoped_localize($schedule->user->info->language, function () use (
            $schedule,
            $notificationType,
            $title,
            $customerBody,
            $shouldInferMethod,
        ) {
            $startAt = $schedule->start_at->copy()->timezone('Europe/Stockholm');

            SendNotificationJob::dispatchAfterResponse(
                $schedule->user,
                new SendNotificationOptions(
                    new AppNotificationOptions(
                        NotificationHubEnum::Customer(),
                        $notificationType,
                        payload: NotificationSchedulePayloadDTO::from([
                            'id' => $schedule->id,
                            'start_at' => $schedule->start_at,
                        ])->toArray(),
                    ),
                    new SMSNotificationOptions(
                        body: __("$customerBody sms", [
                            'date' => $startAt->format('Y-m-d'),
                            'time' => $startAt->format('H:i'),
                        ]),
                    ),
                    title: __($title),
                    body: __($customerBody, [
                        'customer' => $schedule->user->first_name,
                        'date' => $startAt->format('Y-m-d'),
                        'time' => $startAt->format('H:i'),
                    ]),
                    shouldSave: true,
                    shouldInferMethod: $shouldInferMethod,
                ),
            );
        });
    }

    /**
     * Send added worker notification to employee.
     *
     * @param  Schedule  $schedule
     * @param  array<int, int>  $userIds
     */
    public function sendAddedWorkerNotif($schedule, $userIds)
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\ScheduleEmployee> $scheduleEmployees */
        $scheduleEmployees = $schedule->scheduleEmployees()->whereIn('user_id', $userIds)->get();

        foreach ($scheduleEmployees as $scheduleEmployee) {
            $user = $scheduleEmployee->user;

            scoped_localize($user->info->language, function () use ($user, $schedule, $scheduleEmployee) {
                $startAt = $schedule->start_at->copy()->timezone('Europe/Stockholm');

                SendNotificationJob::dispatchAfterResponse(
                    $user,
                    new SendNotificationOptions(
                        new AppNotificationOptions(
                            NotificationHubEnum::Employee(),
                            NotificationTypeEnum::ScheduleAdded(),
                            __('notification title schedule added'),
                            __('notification body schedule added', [
                                'worker' => $user->first_name,
                                'date' => $startAt->format('Y-m-d'),
                                'time' => $startAt->format('H:i'),
                            ]),
                            NotificationSchedulePayloadDTO::from([
                                'id' => $scheduleEmployee->id,
                                'start_at' => $schedule->start_at,
                            ])->toArray(),
                        ),
                        shouldSave: true,
                    ),
                );
            });
        }
    }

    /**
     * Send enabled or disabled worker notification to employee.
     *
     * @param  Schedule  $schedule
     * @param  ScheduleEmployee  $scheduleEmployee
     * @param  int  $userId
     * @param  string  $title
     * @param  string  $body
     * @param  string  $type
     */
    public function sendEnabledDisableWorkerNotif(
        $schedule,
        $scheduleEmployee,
        $userId,
        $title,
        $body,
        $type,
    ) {
        $user = User::find($userId);

        scoped_localize(
            $user->info->language,
            function () use ($user, $schedule, $scheduleEmployee, $title, $body, $type) {
                $startAt = $schedule->start_at->copy()->timezone('Europe/Stockholm');

                SendNotificationJob::dispatchAfterResponse(
                    $user,
                    new SendNotificationOptions(
                        new AppNotificationOptions(
                            NotificationHubEnum::Employee(),
                            $type,
                            __($title),
                            __($body, [
                                'worker' => $user->first_name,
                                'date' => $startAt->format('Y-m-d'),
                                'time' => $startAt->format('H:i'),
                            ]),
                            NotificationSchedulePayloadDTO::from([
                                'id' => $scheduleEmployee->id,
                                'start_at' => $schedule->start_at,
                            ])->toArray(),
                        ),
                        shouldSave: true,
                    ),
                );
            }
        );
    }

    /**
     * Send revert notification to customer.
     */
    public function sendRevertNotifToCustomer(
        Schedule $schedule,
        bool $sendNotifCredit = false,
    ) {
        $user = $schedule->user;
        $text = $sendNotifCredit ? ' with credit' : '';

        scoped_localize(
            $user->info->language,
            function () use ($user, $schedule, $text) {
                $displayDateTime = $schedule->start_at->copy()->timezone(
                    'Europe/Stockholm'
                );

                SendNotificationJob::dispatchAfterResponse(
                    $user,
                    new SendNotificationOptions(
                        new AppNotificationOptions(
                            NotificationHubEnum::Customer(),
                            NotificationTypeEnum::ScheduleUpdated(),
                            __('notification title schedule updated'),
                            __('notification body schedule updated to customer'.$text, [
                                'customer' => $user->first_name,
                                'date' => $displayDateTime->format('Y-m-d'),
                                'time' => $displayDateTime->format('H:i'),
                            ]),
                            NotificationSchedulePayloadDTO::from([
                                'id' => $schedule->id,
                                'start_at' => $schedule->start_at,
                            ])->toArray(),
                        ),
                        shouldSave: true,
                    ),
                );
            }
        );
    }

    /**
     * Check if there is conflict with other schedule.
     *
     * @param  Schedule  $schedule
     * @param  int  $teamId
     * @param  Carbon|string  $startTime
     * @param  Carbon|string  $endTime
     * @return Schedule|null
     */
    public static function getConflictSchedule($schedule, $teamId, $startTime, $endTime)
    {
        $carbonStartTime = $startTime instanceof Carbon
            ? $startTime
            : Carbon::parse($startTime);
        $carbonEndTime = $endTime instanceof Carbon
            ? $endTime
            : Carbon::parse($endTime);

        return Schedule::whereIn('status', [
            ScheduleStatusEnum::Booked(),
            ScheduleStatusEnum::Progress(),
        ])
            ->where('id', '!=', $schedule->id)
            ->where('team_id', '=', $teamId)
            ->whereNot(function (Builder $query) use ($carbonStartTime, $carbonEndTime) {
                $query->where('start_at', '>=', $carbonEndTime)
                    ->orWhere('end_at', '<=', $carbonStartTime);
            })
            ->first();
    }

    /**
     * Send remove laundry notification to customer.
     *
     * @param  Schedule  $schedule
     * @param  Store  $store
     * @param  string  $laundryType
     */
    public static function sendRemoveLaundryNotif($schedule, $store, $laundryType)
    {
        $user = $schedule->user;

        scoped_localize(
            $user->info->language,
            function () use ($user, $schedule, $store, $laundryType) {
                $displayDateTime = $schedule->start_at->copy()->timezone(
                    'Europe/Stockholm'
                );
                $customerBody = "notification body laundry removed $laundryType";

                SendNotificationJob::dispatchAfterResponse(
                    $user,
                    new SendNotificationOptions(
                        new AppNotificationOptions(
                            NotificationHubEnum::Customer(),
                            NotificationTypeEnum::ScheduleCancel(),
                            payload: NotificationSchedulePayloadDTO::from([
                                'id' => $schedule->id,
                                'start_at' => $schedule->start_at,
                            ])->toArray(),
                        ),
                        new SMSNotificationOptions(
                            body: __("$customerBody sms", [
                                'date' => $displayDateTime->format('Y-m-d'),
                                'time' => $displayDateTime->format('H:i'),
                                'store' => $store ? $store->name : null,
                            ]),
                        ),
                        title: __('notification title laundry removed'),
                        body: __("notification body laundry removed $laundryType", [
                            'customer' => $user->first_name,
                            'date' => $displayDateTime->format('Y-m-d'),
                            'time' => $displayDateTime->format('H:i'),
                            'store' => $store ? $store->name : null,
                        ]),
                        shouldSave: true,
                    ),
                );
            }
        );
    }
}
