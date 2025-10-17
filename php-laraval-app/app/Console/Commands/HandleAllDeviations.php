<?php

namespace App\Console\Commands;

use App\Enums\Deviation\DeviationTypeEnum;
use App\Enums\ScheduleEmployee\ScheduleEmployeeStatusEnum;
use App\Jobs\SentWorkingHoursJob;
use App\Models\Deviation;
use App\Models\ScheduleDeviation;
use App\Models\ScheduleEmployee;
use App\Services\ScheduleDeviationService;
use DB;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class HandleAllDeviations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deviation:handle-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Handle all kinds of deviations';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(
        ScheduleDeviationService $scheduleDeviationService
    ) {
        /**
         * @var Collection<array-key,ScheduleDeviation>
         */
        $scheduleDeviations = ScheduleDeviation::with([
            'schedule.scheduleEmployees',
            'schedule.items',
        ])
            ->where('is_handled', false)
            ->get();
        $total = $scheduleDeviations->count();

        for ($i = 0; $i < $scheduleDeviations->count(); $i++) {
            $pos = $i + 1;
            echo "Handling schedule deviation $pos of $total\n";

            $deviation = $scheduleDeviations[$i];
            $schedule = $deviation->schedule;

            foreach ($schedule->scheduleEmployees as $scheduleEmployee) {
                DB::transaction(function () use ($scheduleEmployee, $schedule) {
                    $scheduleEmployee->update([
                        'start_at' => $schedule->start_at,
                        'end_at' => $schedule->end_at,
                        'status' => ScheduleEmployeeStatusEnum::Done(),
                    ]);

                    Deviation::where('schedule_id', $schedule->id)
                        ->where('user_id', $scheduleEmployee->user_id)
                        ->whereIn('type', [
                            DeviationTypeEnum::StartWrongTime(),
                            DeviationTypeEnum::StopWrongTime(),
                            DeviationTypeEnum::NotStarted(),
                            DeviationTypeEnum::FinishedEarly(),
                        ])
                        ->where('is_handled', false)
                        ->update(['is_handled' => true]);
                });
            }

            $scheduleDeviationService->handle(
                $deviation,
                $schedule->items,
                $schedule->quarters,
                ['actual_quarters' => $schedule->quarters],
            );

            echo "Handled schedule deviation $pos of $total\n";
        }

        /**
         * @var Collection<array-key,Deviation>
         */
        $employeeDeviations = Deviation::with('schedule')
            ->where('is_handled', false)
            ->get();
        $total = $employeeDeviations->count();

        for ($i = 0; $i < $employeeDeviations->count(); $i++) {
            $pos = $i + 1;
            echo "Handling employee deviation $pos of $total\n";

            $deviation = $employeeDeviations[$i];
            $schedule = $deviation->schedule;

            DB::transaction(function () use ($deviation, $schedule) {
                if (in_array($deviation->type, [
                    DeviationTypeEnum::StartWrongTime(),
                    DeviationTypeEnum::StopWrongTime(),
                    DeviationTypeEnum::NotStarted(),
                    DeviationTypeEnum::FinishedEarly(),
                ])) {
                    $scheduleEmployee = ScheduleEmployee::where('schedule_id', $deviation->schedule_id)
                        ->where('user_id', $deviation->user_id)
                        ->first();

                    Deviation::where('schedule_id', $deviation->schedule_id)
                        ->where('user_id', $deviation->user_id)
                        ->whereIn('type', [
                            DeviationTypeEnum::StartWrongTime(),
                            DeviationTypeEnum::StopWrongTime(),
                            DeviationTypeEnum::NotStarted(),
                            DeviationTypeEnum::FinishedEarly(),
                        ])
                        ->where('is_handled', false)
                        ->update(['is_handled' => true]);

                    if ($scheduleEmployee) {
                        $scheduleEmployee->update([
                            'start_at' => $schedule->start_at,
                            'end_at' => $schedule->end_at,
                            'status' => ScheduleEmployeeStatusEnum::Done(),
                        ]);
                        SentWorkingHoursJob::dispatchSync($scheduleEmployee);
                    }
                } else {
                    $deviation->update(['is_handled' => true]);
                }
            });

            echo "Handled employee deviation $pos of $total\n";
        }
    }
}
