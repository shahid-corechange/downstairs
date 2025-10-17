<?php

namespace App\Console\Commands;

use App\Enums\CacheEnum;
use App\Enums\Deviation\DeviationTypeEnum;
use App\Enums\GlobalSetting\GlobalSettingEnum;
use App\Enums\Schedule\ScheduleStatusEnum;
use App\Models\Schedule;
use App\Models\ScheduleDeviation;
use Cache;
use Illuminate\Console\Command;
use Log;

class ScheduleNotStartedCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:not-started-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for schedules that have not started yet and set deviation not started';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $now = now()->utc();
        $total = 0;
        $startDayTime = now()->startOfDay()->utc();
        $maxStartMinutes = get_setting(
            GlobalSettingEnum::StartJobLateTime(),
            config('downstairs.schedule.employee.maxStartMinutes')
        );

        $schedules = Schedule::whereBetween('start_at', [$startDayTime, $now])
            ->where('status', '=', ScheduleStatusEnum::Booked())
            ->get();

        foreach ($schedules as $schedule) {
            /**
             * If the schedule has no deviation and
             * the difference in start time is greater than the max start time tolerance,
             * then create a deviation for the schedule
             */
            if (! $schedule->has_deviation &&
                $schedule->start_at->diffInMinutes($now, false) >= $maxStartMinutes) {
                $deviation = ScheduleDeviation::findOrCreate($schedule->id);
                $deviation->update(['types' => [...$deviation->types, DeviationTypeEnum::NotStarted()]]);
                $total++;
            }
        }

        $deviations = ScheduleDeviation::whereJsonContains('types', DeviationTypeEnum::NotStarted())
            ->whereHas('schedule', function ($query) {
                $query->whereNotIn('status', [
                    ScheduleStatusEnum::Booked(),
                ]);
            })
            ->get();

        foreach ($deviations as $deviation) {
            $deviation->update([
                'types' => array_values(
                    array_diff($deviation->types, [DeviationTypeEnum::NotStarted()])
                ),
            ]);
        }

        // Clear cache
        Cache::tags([CacheEnum::ScheduleDeviations()])->flush();

        $info = "Checked for schedules that have not started yet and set deviation not started at {$now}.".
            "Total: {$total}";
        Log::channel('schedule')->info($info);
    }
}
