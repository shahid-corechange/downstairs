<?php

namespace Database\Seeders;

use App\Enums\ScheduleCleaning\ScheduleCleaningChangeStatusEnum;
use App\Enums\ScheduleCleaning\ScheduleCleaningStatusEnum;
use App\Models\ScheduleCleaning;
use App\Models\User;
use Illuminate\Database\Seeder;

class ScheduleChangeRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->environment() !== 'testing') {
            $schedules = ScheduleCleaning::where('status', ScheduleCleaningStatusEnum::Booked())->get();

            foreach ($schedules as $key => $schedule) {
                // status pending if div 2 == 0, else status approved or rejected
                $status = $key % 2 === 0 ? ScheduleCleaningChangeStatusEnum::Pending() :
                    fake()->randomElement([
                        ScheduleCleaningChangeStatusEnum::Approved(),
                        ScheduleCleaningChangeStatusEnum::Rejected(),
                    ]);

                $schedule->changeRequest()->create([
                    'causer_id' => User::role('Employee')->get()->random()->id,
                    'original_start_at' => $schedule->start_at,
                    'start_at_changed' => $schedule->start_at->addHours(1),
                    'original_end_at' => $schedule->end_at,
                    'end_at_changed' => $schedule->end_at->addHours(1),
                    'status' => $status,
                ]);
            }
        }
    }
}
