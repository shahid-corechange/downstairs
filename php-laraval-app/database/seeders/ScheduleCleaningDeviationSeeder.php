<?php

namespace Database\Seeders;

use App\Enums\Deviation\DeviationTypeEnum;
use App\Enums\ScheduleCleaning\ScheduleCleaningStatusEnum;
use App\Models\Deviation;
use App\Models\ScheduleCleaning;
use App\Models\ScheduleCleaningDeviation;
use Illuminate\Database\Seeder;

class ScheduleCleaningDeviationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $numberOfInstances = app()->environment() !== 'testing' ? 5 : 2;

        /** @var \Illuminate\Support\Collection<array-key,\App\Models\ScheduleCleaning> */
        $schedules = ScheduleCleaning::where('status', ScheduleCleaningStatusEnum::Done())
            ->inRandomOrder()
            ->limit($numberOfInstances)
            ->get();

        foreach ($schedules as $schedule) {
            $totalWorker = random_int(1, $schedule->scheduleEmployees->count());
            $scheduleEmployees = $schedule->scheduleEmployees->shuffle()->slice(0, $totalWorker);

            foreach ($scheduleEmployees as $scheduleEmployee) {
                $deviationTypes = DeviationTypeEnum::values();

                shuffle($deviationTypes);

                $deviationTypes = array_slice(
                    $deviationTypes,
                    0,
                    random_int(1, count(DeviationTypeEnum::values()))
                );
                $factory = Deviation::factory(state: [
                    'schedule_cleaning_id' => $schedule->id,
                    'user_id' => $scheduleEmployee->user_id,
                ]);

                foreach ($deviationTypes as $deviationType) {
                    $factory->forType($deviationType)->create();
                }
            }

            $deviationTypes = Deviation::select('type')
                ->where('schedule_cleaning_id', $schedule->id)
                ->groupBy('type')
                ->get()
                ->pluck('type')
                ->toArray();

            ScheduleCleaningDeviation::create([
                'schedule_cleaning_id' => $schedule->id,
                'types' => $deviationTypes,
            ]);
        }
    }
}
