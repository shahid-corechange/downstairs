<?php

namespace App\Console\Commands;

use App\Enums\Deviation\DeviationTypeEnum;
use App\Enums\Schedule\ScheduleStatusEnum;
use App\Enums\ScheduleEmployee\ScheduleEmployeeStatusEnum;
use App\Jobs\SentWorkingHoursJob;
use App\Models\Deviation;
use App\Models\ScheduleEmployee;
use DB;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class HandleNotStartedDeviation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deviation:handle-not-started';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Handle employee deviations that have not started yet'.
     ' and send working hours to Fortnox.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        /**
         * @var Collection<array-key,Deviation>
         */
        $employeeDeviations = Deviation::with('schedule')
            ->where('type', DeviationTypeEnum::NotStarted())
            ->where('is_handled', false)
            ->whereHas('schedule', function ($query) {
                $query->where('status', ScheduleStatusEnum::Done());
            })
            ->get();

        foreach ($employeeDeviations as $deviation) {
            $scheduleEmployee = ScheduleEmployee::where('schedule_id', $deviation->schedule_id)
                ->where('user_id', $deviation->user_id)
                ->first();

            DB::transaction(function () use ($deviation, $scheduleEmployee) {
                $deviation->update(['is_handled' => true]);
                $scheduleEmployee->update([
                    'status' => ScheduleEmployeeStatusEnum::Done(),
                    'start_at' => $deviation->schedule->actual_start_at ??
                        $deviation->schedule->start_at,
                    'end_at' => $deviation->schedule->actual_end_at ??
                        $deviation->schedule->end_at,
                ]);
            });

            SentWorkingHoursJob::dispatchSync($scheduleEmployee);
        }
    }
}
