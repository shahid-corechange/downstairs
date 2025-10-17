<?php

namespace App\Console\Commands;

use App\DTOs\Notification\NotificationSchedulePayloadDTO;
use App\Enums\Azure\NotificationHub\NotificationHubEnum;
use App\Enums\Notification\NotificationTypeEnum;
use App\Enums\Schedule\ScheduleStatusEnum;
use App\Helpers\Notification\AppNotificationOptions;
use App\Helpers\Notification\SendNotificationOptions;
use App\Helpers\Notification\SMSNotificationOptions;
use App\Jobs\SendNotificationJob;
use App\Models\Schedule;
use App\Models\ScheduleCleaning;
use App\Models\ScheduleEmployee;
use Illuminate\Console\Command;
use Log;

class UpcomingReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminder:upcoming';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notification to employee and customer about upcoming schedule cleaning';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get all schedules that are not yet started and within 1 hour
        $schedules = Schedule::where('start_at', '>=', now())
            ->where('start_at', '<=', now()->addHour())
            ->where('status', '=', ScheduleStatusEnum::Booked())
            ->with('scheduleable.laundryOrder.store')
            ->get();

        Log::channel('reminder_upcoming')
            ->info('Send notification to employee and customer about upcoming schedule. Total: '
            .$schedules->count().' Schedules');

        $schedules->each(function (Schedule $schedule) {
            $cleaningHasLaundry = false;
            $displayDateTime = $schedule->start_at->copy()->timezone(
                'Europe/Stockholm'
            );

            $type = $schedule->scheduleable_type === ScheduleCleaning::class ?
                'cleaning notification' : 'laundry notification';

            // Send notification to customer
            scoped_localize(
                $schedule->user->info->language,
                function () use ($schedule, $displayDateTime, $type) {
                    SendNotificationJob::dispatchSync(
                        $schedule->user,
                        new SendNotificationOptions(
                            new AppNotificationOptions(
                                NotificationHubEnum::Customer(),
                                NotificationTypeEnum::UpcomingScheduleStart(),
                                payload: NotificationSchedulePayloadDTO::from([
                                    'id' => $schedule->id,
                                    'start_at' => $schedule->start_at,
                                ])->toArray(),
                            ),
                            new SMSNotificationOptions(
                                body: __('notification body reminder customer upcoming schedule sms', [
                                    'date' => $displayDateTime->format('Y-m-d'),
                                    'time' => $displayDateTime->format('H:i'),
                                    'type' => __($type),
                                ]),
                            ),
                            title: __('notification title reminder customer upcoming schedule', [
                                'type' => ucfirst(__($type)),
                            ]),
                            body: __('notification body reminder customer upcoming schedule', [
                                'customer' => $schedule->user->first_name,
                                'date' => $displayDateTime->format('Y-m-d'),
                                'time' => $displayDateTime->format('H:i'),
                                'type' => __($type),
                            ]),
                            shouldSave: true,
                            shouldInferMethod: true,
                        )
                    );
                }
            );

            if ($schedule->scheduleable_type === ScheduleCleaning::class &&
                $schedule->scheduleable->laundry_order_id
            ) {
                $cleaningHasLaundry = true;
            }

            // Send notification to employee
            $schedule->scheduleEmployees->each(
                function (ScheduleEmployee $scheduleEmployee) use ($schedule, $type, $cleaningHasLaundry) {
                    $displayDateTime = $schedule->start_at->copy()->timezone(
                        'Europe/Stockholm'
                    );

                    if ($cleaningHasLaundry) {
                        $this->cleaningHasLaundryNotification(
                            $schedule,
                            $scheduleEmployee,
                            $displayDateTime,
                            $type
                        );
                    } else {
                        $this->cleaningNotification($schedule, $scheduleEmployee, $displayDateTime, $type);
                    }
                }
            );
        });
    }

    /**
     * Send notification to employee about upcoming schedule cleaning
     *
     * @param  Schedule  $schedule
     * @param  ScheduleEmployee  $scheduleEmployee
     * @param  Carbon  $displayDateTime
     * @param  string  $type
     */
    private function cleaningNotification($schedule, $scheduleEmployee, $displayDateTime, $type)
    {
        scoped_localize(
            $scheduleEmployee->user->info->language,
            function () use ($scheduleEmployee, $displayDateTime, $schedule, $type) {
                SendNotificationJob::dispatchSync(
                    $scheduleEmployee->user,
                    new SendNotificationOptions(
                        new AppNotificationOptions(
                            NotificationHubEnum::Employee(),
                            NotificationTypeEnum::UpcomingScheduleStart(),
                            __('notification title reminder worker upcoming schedule', [
                                'type' => ucfirst(__($type)),
                            ]),
                            __('notification body reminder worker upcoming schedule', [
                                'worker' => $scheduleEmployee->user->first_name,
                                'date' => $displayDateTime->format('Y-m-d'),
                                'time' => $displayDateTime->format('H:i'),
                                'type' => __($type),
                            ]),
                            NotificationSchedulePayloadDTO::from([
                                'id' => $scheduleEmployee->id,
                                'start_at' => $schedule->start_at,
                            ])->toArray(),
                        ),
                        shouldSave: true,
                    )
                );
            }
        );
    }

    /**
     * Send notification to employee about upcoming schedule cleaning with delivery laundry
     *
     * @param  Schedule  $schedule
     * @param  ScheduleEmployee  $scheduleEmployee
     * @param  Carbon  $displayDateTime
     * @param  string  $type
     */
    private function cleaningHasLaundryNotification($schedule, $scheduleEmployee, $displayDateTime, $type)
    {
        $laundryType = $schedule->scheduleable->laundry_type;

        scoped_localize(
            $scheduleEmployee->user->info->language,
            function () use ($scheduleEmployee, $displayDateTime, $schedule, $type, $laundryType) {
                SendNotificationJob::dispatchSync(
                    $scheduleEmployee->user,
                    new SendNotificationOptions(
                        new AppNotificationOptions(
                            NotificationHubEnum::Employee(),
                            NotificationTypeEnum::UpcomingScheduleStart(),
                            __('notification title reminder worker upcoming schedule'),
                            __("notification body reminder worker upcoming schedule include laundry $laundryType", [
                                'worker' => $scheduleEmployee->user->first_name,
                                'time' => $displayDateTime->format('H:i'),
                                'type' => __($type),
                                'store' => $schedule->scheduleable->laundryOrder->store->name,
                            ]),
                            NotificationSchedulePayloadDTO::from([
                                'id' => $scheduleEmployee->id,
                                'start_at' => $schedule->start_at,
                            ])->toArray(),
                        ),
                        shouldSave: true,
                    )
                );
            }
        );
    }
}
