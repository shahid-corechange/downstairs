<?php

namespace Database\Seeders;

use App\Enums\ScheduleEmployee\ScheduleEmployeeStatusEnum;
use App\Models\ScheduleEmployee;
use App\Models\User;
use App\Models\WorkHour;
use App\Services\WorkHourService;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Seeder;

class WorkHourSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schedules = ScheduleEmployee::where('status', ScheduleEmployeeStatusEnum::Done())->get();

        foreach ($schedules as $schedule) {
            if (! $schedule->start_at || ! $schedule->end_at) {
                continue;
            }

            $startAt = $schedule->start_at->copy()->setTimezone('Europe/Stockholm');
            $endAt = $schedule->end_at->copy()->setTimezone('Europe/Stockholm');

            if (! $startAt->isSameDay($endAt)) {
                $this->apply($schedule, $startAt, $startAt->copy()->endOfDay());
                $this->apply($schedule, $endAt->copy()->startOfDay(), $endAt);

                continue;
            }

            $this->apply($schedule, $startAt, $endAt);
        }
    }

    private function apply(
        ScheduleEmployee $scheduleEmployee,
        Carbon $startAt,
        Carbon $endAt
    ): void {
        /**
         * Get user with trashed in case handle deviation
         * but the user is soft deleted.
         *
         * @var User $user
         */
        $workerId = $scheduleEmployee->user_id;

        /** @var \App\Models\WorkHour|null */
        $workHour = WorkHour::where('user_id', $workerId)
            ->where('date', $startAt->format('Y-m-d'))
            ->first();

        if (! $workHour) {
            DB::transaction(function () use ($scheduleEmployee, $workerId, $startAt, $endAt) {
                $workHour = WorkHour::create([
                    'user_id' => $workerId,
                    'date' => $startAt->format('Y-m-d'),
                    'start_time' => $startAt->format('H:i:s'),
                    'end_time' => $endAt->format('H:i:s'),
                ]);
                $scheduleEmployee->update([
                    'work_hour_id' => $workHour->id,
                ]);
            });

            return;
        }

        [$startTime, $endTime] = WorkHourService::getTimes($scheduleEmployee, $startAt->format('Y-m-d'));

        if ($startTime === $workHour->start_time && $endTime === $workHour->end_time) {
            $scheduleEmployee->update([
                'work_hour_id' => $workHour->id,
            ]);

            return;
        }

        DB::transaction(function () use ($workHour, $scheduleEmployee, $startTime, $endTime) {
            $workHour->update([
                'start_time' => $startTime,
                'end_time' => $endTime,
            ]);
            $scheduleEmployee->update([
                'work_hour_id' => $workHour->id,
            ]);
        });
    }
}
