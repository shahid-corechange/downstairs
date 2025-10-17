<?php

namespace App\Policies;

use App\Enums\Schedule\ScheduleStatusEnum;
use App\Enums\ScheduleEmployee\ScheduleEmployeeStatusEnum;
use App\Models\ScheduleEmployee;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Illuminate\Http\Response as HttpResponse;

class ScheduleEmployeePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view model.
     */
    public function view(User $user, ScheduleEmployee $schedule): Response
    {
        return $user->id !== $schedule->user_id ?
            $this->denyAsNotFound(__('schedule not found')) :
            $this->allow();
    }

    /**
     * Start schedule employee
     */
    public function start(
        User $user,
        ScheduleEmployee $scheduleEmployee,
    ): Response {
        $now = now();

        if ($user->id !== $scheduleEmployee->user_id) {
            return $this->denyAsNotFound(__('schedule not found'));
        }

        /** @var \App\Models\Schedule */
        $schedule = $scheduleEmployee->schedule;

        if ($schedule->status === ScheduleStatusEnum::Done()) {
            return $this->denyWithStatus(HttpResponse::HTTP_BAD_REQUEST, __('schedule already done'));
        } elseif ($schedule->status === ScheduleStatusEnum::Cancel()) {
            return $this->denyWithStatus(HttpResponse::HTTP_BAD_REQUEST, __('schedule already canceled'));
        } elseif ($schedule->end_at < $now) {
            return $this->denyWithStatus(HttpResponse::HTTP_BAD_REQUEST, __('can not start past schedule'));
        }

        $inProgress = ScheduleEmployee::ofUser($user->id)
            ->where('status', ScheduleEmployeeStatusEnum::Progress())
            ->first();

        $scheduleStartAt = $schedule->start_at->copy()->setTimezone($user->timezone);
        $startAt = now()->setTimezone($user->timezone);

        if (! $startAt->isSameDay($scheduleStartAt)) {
            return $this->denyWithStatus(HttpResponse::HTTP_BAD_REQUEST, __('can not start schedule in different day'));
        } elseif ($now->diffInMinutes($schedule->start_at, false)
            > config('downstairs.schedule.employee.minStartMinutes')) {
            return $this->denyWithStatus(HttpResponse::HTTP_BAD_REQUEST, __('too soon to start'));
        } elseif ($inProgress) {
            return $this->denyWithStatus(HttpResponse::HTTP_BAD_REQUEST, __('you have in progress schedule'));
        }

        return $this->allow();
    }

    /**
     * End schedule employee
     */
    public function end(
        User $user,
        ScheduleEmployee $scheduleEmployee
    ): Response {
        if ($user->id !== $scheduleEmployee->user_id) {
            return $this->denyAsNotFound(__('schedule not found'));
        } elseif ($scheduleEmployee->status !== ScheduleEmployeeStatusEnum::Progress()) {
            return $this->denyWithStatus(HttpResponse::HTTP_BAD_REQUEST, __('schedule employee is not in progress'));
        }

        return $this->allow();
    }

    /**
     * Cancel schedule employee
     */
    public function cancel(
        User $user,
        ScheduleEmployee $scheduleEmployee
    ): Response {
        if ($user->id !== $scheduleEmployee->user_id) {
            return $this->denyAsNotFound(__('schedule not found'));
        }

        if ($scheduleEmployee->schedule->status == ScheduleStatusEnum::Done()) {
            return $this->denyWithStatus(HttpResponse::HTTP_BAD_REQUEST, __('schedule already done'));
        }

        return $this->allow();
    }
}
