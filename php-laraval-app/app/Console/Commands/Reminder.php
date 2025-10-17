<?php

namespace App\Console\Commands;

use App\DTOs\Notification\NotificationSchedulePayloadDTO;
use App\Enums\Azure\NotificationHub\NotificationHubEnum;
use App\Enums\GlobalSetting\GlobalSettingEnum;
use App\Enums\Notification\NotificationTypeEnum;
use App\Enums\Schedule\ScheduleStatusEnum;
use App\Enums\ScheduleEmployee\ScheduleEmployeeStatusEnum;
use App\Helpers\Notification\AppNotificationOptions;
use App\Helpers\Notification\SendNotificationOptions;
use App\Jobs\SendNotificationJob;
use App\Models\ScheduleCleaning;
use App\Models\ScheduleEmployee;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Log;

class Reminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminder:notify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reminder employee schedules';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $info = 'Reminder employee schedules';

        $now = now();

        $schedules = $this->getNearEndSchedules($now);
        $info = "{$info} Total near end: {$schedules->count()},";

        $schedules->each(function (ScheduleEmployee $scheduleEmployee) {
            $this->sendNearEndNotification($scheduleEmployee);
        });

        $schedules = $this->getNotStartedSchedules($now);
        $info = "{$info} Total not yet started and late: {$schedules->count()},";

        $schedules->each(function (ScheduleEmployee $scheduleEmployee) {
            $this->sendNotStartedNotification($scheduleEmployee);
        });

        $schedules = $this->getSupposeToEndSchedules($now);
        $info = "{$info} Total suppose to end: {$schedules->count()}.";

        $schedules->each(function (ScheduleEmployee $scheduleEmployee) {
            $this->sendSupposeToEndNotification($scheduleEmployee);
        });

        Log::channel('reminder_notify')->info($info);
    }

    /**
     * Get all schedule employees that already started and near end (within 15 minutes)
     *
     * @param  Carbon  $dateTime
     * @return Collection
     */
    private function getNearEndSchedules($dateTime)
    {
        return ScheduleEmployee::whereHas('schedule', function (Builder $query) use ($dateTime) {
            $query->where('end_at', '>=', $dateTime)
                ->where('end_at', '<=', $dateTime->copy()->addMinutes(15))
                ->where('status', '=', ScheduleStatusEnum::Progress())
                ->where('scheduleable_type', '=', ScheduleCleaning::class);
        })
            ->where('status', '=', ScheduleEmployeeStatusEnum::Progress())
            ->get();
    }

    /**
     * Send notification to worker that are near end
     *
     * @param  ScheduleEmployee  $scheduleEmployee
     */
    private function sendNearEndNotification($scheduleEmployee)
    {
        scoped_localize(
            $scheduleEmployee->user->info->language,
            function () use ($scheduleEmployee) {
                SendNotificationJob::dispatchSync(
                    $scheduleEmployee->user,
                    new SendNotificationOptions(
                        new AppNotificationOptions(
                            NotificationHubEnum::Employee(),
                            NotificationTypeEnum::UpcomingScheduleEnd(),
                            __('notification title reminder worker schedule cleaning near end'),
                            __('notification body reminder worker schedule cleaning near end', [
                                'worker' => $scheduleEmployee->user->first_name,
                            ]),
                            NotificationSchedulePayloadDTO::from([
                                'id' => $scheduleEmployee->id,
                                'end_at' => $scheduleEmployee->schedule->end_at,
                            ])->toArray()
                        )
                    ),
                );
            }
        );
    }

    /**
     * Get all schedule employees that are not yet started and late
     *
     * @param  Carbon  $dateTime
     * @return Collection
     */
    private function getNotStartedSchedules($dateTime)
    {
        return ScheduleEmployee::whereHas('schedule', function (Builder $query) use ($dateTime) {
            $minutes = get_setting(GlobalSettingEnum::ScheduleStartReminderMinutes(), 60);
            $dateStart = $dateTime->copy()->subMinutes($minutes);
            $query->whereBetween('start_at', [$dateStart, $dateTime])
                ->whereIn('status', [
                    ScheduleStatusEnum::Booked(),
                    ScheduleStatusEnum::Progress(),
                ]);
        })
            ->where('status', '=', ScheduleEmployeeStatusEnum::Pending())
            ->get();
    }

    /**
     * Send notification to worker that are not yet started and late
     *
     * @param  ScheduleEmployee  $scheduleEmployee
     */
    private function sendNotStartedNotification($scheduleEmployee)
    {
        $displayDateTime = $scheduleEmployee->schedule->start_at->copy()->timezone(
            'Europe/Stockholm'
        );
        $language = $scheduleEmployee->user->info->language;

        if ($scheduleEmployee->schedule->scheduleable_type === ScheduleCleaning::class &&
            $scheduleEmployee->schedule->scheduleable->laundry_order_id
        ) {
            $laundryType = $scheduleEmployee->schedule->scheduleable->laundry_type;
            $store = $scheduleEmployee->schedule->scheduleable->laundryOrder->store->name;

            // send notification to employee with pickup or delivery laundry
            scoped_localize(
                $language,
                function () use ($scheduleEmployee, $displayDateTime, $laundryType, $store) {
                    SendNotificationJob::dispatchSync(
                        $scheduleEmployee->user,
                        new SendNotificationOptions(
                            new AppNotificationOptions(
                                NotificationHubEnum::Employee(),
                                NotificationTypeEnum::StartScheduleLate(),
                                __('notification title reminder start schedule late'),
                                __("notification body reminder start schedule late include laundry $laundryType", [
                                    'worker' => $scheduleEmployee->user->first_name,
                                    'time' => $displayDateTime->format('H:i'),
                                    'store' => $store,
                                ]),
                                NotificationSchedulePayloadDTO::from([
                                    'id' => $scheduleEmployee->id,
                                    'start_at' => $scheduleEmployee->schedule->start_at,
                                ])->toArray()
                            )
                        ),
                    );
                }
            );
        } else {
            scoped_localize(
                $language,
                function () use ($scheduleEmployee, $displayDateTime) {
                    SendNotificationJob::dispatchSync(
                        $scheduleEmployee->user,
                        new SendNotificationOptions(
                            new AppNotificationOptions(
                                NotificationHubEnum::Employee(),
                                NotificationTypeEnum::StartScheduleLate(),
                                __('notification title reminder start schedule late'),
                                __('notification body reminder start schedule late', [
                                    'worker' => $scheduleEmployee->user->first_name,
                                    'date' => $displayDateTime->format('Y-m-d'),
                                    'time' => $displayDateTime->format('H:i'),
                                ]),
                                NotificationSchedulePayloadDTO::from([
                                    'id' => $scheduleEmployee->id,
                                    'start_at' => $scheduleEmployee->schedule->start_at,
                                ])->toArray()
                            )
                        ),
                    );
                }
            );
        }
    }

    /**
     * Get all schedule employees that are suppose to end
     *
     * @param  Carbon  $dateTime
     * @return Collection
     */
    private function getSupposeToEndSchedules($dateTime)
    {
        $endReminder = get_setting(GlobalSettingEnum::ScheduleEndReminderMinutes(), 600);

        return ScheduleEmployee::whereHas('schedule', function (Builder $query) use ($dateTime) {
            $query->where('end_at', '<', $dateTime)
                ->whereIn('status', [ScheduleStatusEnum::Progress(), ScheduleStatusEnum::Done()]);
        })
            ->where('start_at', '>', $dateTime->copy()->subMinutes($endReminder))
            ->where('status', '=', ScheduleEmployeeStatusEnum::Progress())
            ->get();
    }

    /**
     * Send notification to worker that are suppose to end
     *
     * @param  ScheduleEmployee  $scheduleEmployee
     */
    private function sendSupposeToEndNotification($scheduleEmployee)
    {
        scoped_localize($scheduleEmployee->user->info->language, function () use ($scheduleEmployee) {
            SendNotificationJob::dispatchSync(
                $scheduleEmployee->user,
                new SendNotificationOptions(
                    new AppNotificationOptions(
                        NotificationHubEnum::Employee(),
                        NotificationTypeEnum::EndScheduleLate(),
                        __('notification title reminder end schedule late'),
                        __('notification body reminder end schedule late', [
                            'worker' => $scheduleEmployee->user->first_name,
                        ]),
                        NotificationSchedulePayloadDTO::from([
                            'id' => $scheduleEmployee->id,
                            'end_at' => $scheduleEmployee->schedule->end_at,
                        ])->toArray()
                    )
                ),
            );
        });
    }
}
