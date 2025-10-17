<?php

namespace App\Policies;

use App\Enums\PermissionsEnum;
use App\Enums\Schedule\ScheduleStatusEnum;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Illuminate\Http\Response as HttpResponse;
use Str;

class SchedulePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the schedule cleaning.
     */
    public function view(User $user, Schedule $schedule): Response
    {
        return $user->can(PermissionsEnum::SchedulesRead()) && $user->id == $schedule->user_id ?
            $this->allow() :
            $this->denyAsNotFound(__('schedule not found'));
    }

    /**
     * Determine whether the user can update the schedule cleaning.
     */
    public function update(User $user, Schedule $schedule): Response
    {
        if (in_array($schedule->status, [
            ScheduleStatusEnum::Cancel(),
            ScheduleStatusEnum::Done(),
        ])) {
            return $this->denyWithStatus(
                HttpResponse::HTTP_BAD_REQUEST,
                __('invalid schedule status', ['status' => $schedule->status])
            );
        }

        return $this->allow();
    }

    /**
     * Determine whether the user can request to change the schedule cleaning.
     */
    public function change(User $user, Schedule $schedule): Response
    {
        $denyStatuses = [
            ScheduleStatusEnum::Progress() => 'in progress',
            ScheduleStatusEnum::Cancel() => 'canceled',
            ScheduleStatusEnum::Done() => 'done',
        ];

        if (in_array($schedule->status, array_keys($denyStatuses))) {
            return $this->denyWithStatus(
                HttpResponse::HTTP_BAD_REQUEST,
                __('invalid schedule status', [
                    'status' => Str::lower(__($denyStatuses[$schedule->status])),
                ])
            );
        }

        return $this->allow();
    }

    /**
     * Determine whether the user can cancel the schedule cleaning.
     */
    public function cancel(User $user, Schedule $schedule): Response
    {
        $denyStatuses = [
            ScheduleStatusEnum::Cancel() => 'canceled',
            ScheduleStatusEnum::Done() => 'done',
        ];

        if (in_array($schedule->status, array_keys($denyStatuses))) {
            return $this->denyWithStatus(
                HttpResponse::HTTP_BAD_REQUEST,
                __('invalid schedule status', [
                    'status' => Str::lower(__($denyStatuses[$schedule->status])),
                ])
            );
        }

        return $this->allow();
    }
}
