<?php

namespace Database\Seeders;

use App\Enums\ScheduleEmployee\ScheduleEmployeeStatusEnum;
use App\Models\ScheduleEmployee;
use App\Models\User;
use Illuminate\Database\Seeder;

class TimeAdjustmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->environment() !== 'testing') {
            $scheduleEmployees = ScheduleEmployee::where('status', ScheduleEmployeeStatusEnum::Done())
                ->inRandomOrder()
                ->limit(50)
                ->get();

            foreach ($scheduleEmployees as $scheduleEmployee) {
                $scheduleEmployee->timeAdjustment()->create([
                    'causer_id' => User::role('Superadmin')->inRandomOrder()->first()->id,
                    'quarters' => fake()->numberBetween(-1, 4),
                    'reason' => fake()->sentence(),
                ]);
            }
        }
    }
}
