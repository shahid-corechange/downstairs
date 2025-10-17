<?php

namespace App\Services;

use App\DTOs\Notification\NotificationSchedulePayloadDTO;
use App\Enums\Azure\NotificationHub\NotificationHubEnum;
use App\Enums\Notification\NotificationTypeEnum;
use App\Helpers\Notification\AppNotificationOptions;
use App\Helpers\Notification\SendNotificationOptions;
use App\Jobs\SendNotificationJob;
use App\Models\Schedule;
use Carbon\Carbon;

class ChangeRequestService
{
    public function sendApprovedNotif(
        Schedule $schedule,
        Carbon $originalStartAt,
        Carbon $originalEndAt,
        int $originalTeamId,
    ) {
        scoped_localize(
            $schedule->user->info->language,
            function () use ($schedule, $originalStartAt, $originalEndAt, $originalTeamId) {
                $localizedOriginalStartAt = $originalStartAt->copy()->timezone(
                    'Europe/Stockholm'
                );
                $localizedOriginalEndAt = $originalEndAt->copy()->timezone('Europe/Stockholm');
                $localizedNewStartAt = $schedule->start_at->copy()->timezone('Europe/Stockholm');
                $localizedNewEndAt = $schedule->end_at->copy()->timezone('Europe/Stockholm');
                $teamText = $originalTeamId !== $schedule->team_id ? ' with a different team' : '';

                SendNotificationJob::dispatchAfterResponse(
                    $schedule->user,
                    new SendNotificationOptions(
                        new AppNotificationOptions(
                            NotificationHubEnum::Customer(),
                            NotificationTypeEnum::ChangeRequestApproved(),
                            __('notification title change request approved'),
                            __('notification body change request approved'.$teamText, [
                                'customer' => $schedule->user->first_name,
                                'original_date' => $localizedOriginalStartAt->format('Y-m-d'),
                                'original_time' => $localizedOriginalStartAt->format('H:i').' - '.
                                    $localizedOriginalEndAt->format('H:i'),
                                'new_date' => $localizedNewStartAt->format('Y-m-d'),
                                'new_time' => $localizedNewStartAt->format('H:i').' - '.
                                    $localizedNewEndAt->format('H:i'),
                                'team' => $schedule->team->name,
                            ]),
                            NotificationSchedulePayloadDTO::from([
                                'id' => $schedule->id,
                                'start_at' => $schedule->start_at,
                                'end_at' => $schedule->end_at,
                            ])->toArray(),
                        ),
                        shouldSave: true,
                    ),
                );
            }
        );

        foreach ($schedule->scheduleEmployees as $scheduleEmployee) {
            scoped_localize(
                $scheduleEmployee->user->info->language,
                function () use ($scheduleEmployee, $schedule) {
                    $localizedNewStartAt = $schedule->start_at->copy()->timezone(
                        'Europe/Stockholm'
                    );

                    SendNotificationJob::dispatchAfterResponse(
                        $scheduleEmployee->user,
                        new SendNotificationOptions(
                            new AppNotificationOptions(
                                NotificationHubEnum::Employee(),
                                NotificationTypeEnum::ScheduleUpdated(),
                                __('notification title schedule updated'),
                                __('notification body schedule updated', [
                                    'worker' => $scheduleEmployee->user->first_name,
                                    'date' => $localizedNewStartAt->format('Y-m-d'),
                                    'time' => $localizedNewStartAt->format('H:i'),
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
    }
}
