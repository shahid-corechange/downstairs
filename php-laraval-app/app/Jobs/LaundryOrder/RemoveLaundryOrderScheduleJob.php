<?php

namespace App\Jobs\LaundryOrder;

use App\DTOs\Notification\NotificationSchedulePayloadDTO;
use App\Enums\Azure\NotificationHub\NotificationHubEnum;
use App\Enums\Notification\NotificationTypeEnum;
use App\Helpers\Notification\AppNotificationOptions;
use App\Helpers\Notification\SendNotificationOptions;
use App\Jobs\BaseJob;
use App\Jobs\SendNotificationJob;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class RemoveLaundryOrderScheduleJob extends BaseJob
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
        protected Schedule $schedule,
    ) {
        $this->queue = 'default';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->handleWrapper(function () {
            /** @var Collection<int, User> $employees */
            $employees = $this->schedule->scheduleEmployees->map(fn ($scheduleEmployee) => $scheduleEmployee->user);
            $startAt = $this->schedule->start_at;

            $this->schedule->forceDelete();

            // send notification to employees in the team
            if ($this->schedule->team_id) {
                foreach ($employees as $employee) {
                    $this->sendNotification($employee, $startAt);
                }
            }
        });
    }

    /**
     * Send notification to employee
     *
     * @param  User  $employee
     * @param  Carbon  $startAt
     */
    private function sendNotification($employee, $startAt)
    {
        scoped_localize($employee->info->language, function () use ($employee, $startAt) {
            SendNotificationJob::dispatch(
                $employee,
                new SendNotificationOptions(
                    new AppNotificationOptions(
                        NotificationHubEnum::Employee(),
                        NotificationTypeEnum::ScheduleDeleted(),
                        __('notification title schedule deleted'),
                        __('notification body schedule deleted', [
                            'worker' => $employee->first_name,
                            'date' => $startAt->format('Y-m-d'),
                            'time' => $startAt->format('H:i'),
                        ]),
                        NotificationSchedulePayloadDTO::from([])->toArray(),
                    ),
                    shouldSave: true,
                ),
            );
        });
    }
}
