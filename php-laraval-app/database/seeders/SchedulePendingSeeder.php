<?php

namespace Database\Seeders;

use App\Enums\ScheduleCleaning\ScheduleCleaningStatusEnum;
use App\Enums\ScheduleEmployee\ScheduleEmployeeStatusEnum;
use App\Enums\TranslationEnum;
use App\Models\ScheduleCleaning;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SchedulePendingSeeder extends Seeder
{
    private int $dayCounter = 0;

    private int $hourCounter = 1;

    private int $counter = 1;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $numberOfInstances = app()->environment() !== 'testing' ? 90 : 18;
        $workers = array_map(function ($user) {
            return [
                'user_id' => $user['id'],
                'status' => ScheduleEmployeeStatusEnum::Pending(),
            ];
        }, $this->getWorkers());

        $subscription = Subscription::first();
        $minute = fake()->randomElement([0, 15, 30, 45]);
        ScheduleCleaning::factory($numberOfInstances)
            ->forSubscription($subscription)
            ->forStatus(ScheduleCleaningStatusEnum::Booked())
            ->create()
            ->each(function (ScheduleCleaning $scheduleCleaning) use ($workers, $minute) {
                $startAt = Carbon::now()
                    ->addDays($this->dayCounter)
                    ->addHours($this->hourCounter)
                    ->setMinutes($minute)
                    ->startOfMinute();
                $scheduleCleaning->fill([
                    'start_at' => $startAt,
                    'end_at' => Carbon::instance($startAt)->addHours(1),
                ])->save();
                $scheduleCleaning->scheduleEmployees()->createMany($workers);

                if ($this->counter % 3 == 0) {
                    $this->dayCounter++;
                    $this->hourCounter = 1;
                    $this->counter = 1;
                } else {
                    $this->counter++;
                    $this->hourCounter = $this->hourCounter + 2;
                }

                $task = fake()->randomElement($this->getTasks());
                $customTask = $scheduleCleaning->tasks()->create();

                $customTask->setName($task['name_sv_se'], TranslationEnum::Swedish());
                $customTask->setDescription($task['description_sv_se'], TranslationEnum::Swedish());
                $customTask->setName($task['name_en_us'], TranslationEnum::English());
                $customTask->setDescription($task['description_en_us'], TranslationEnum::English());
            });
    }

    private function getTasks()
    {
        return [
            [
                'name_en_us' => 'Tidy up sofa',
                'description_en_us' => 'Tidy up and clean sofa',
                'name_sv_se' => 'Städa soffan',
                'description_sv_se' => 'Städa och rengör soffan',
            ],
            [
                'name_en_us' => 'Remove carpet dirt',
                'description_en_us' => 'Remove dirt from carpet',
                'name_sv_se' => 'Ta bort smuts från mattan',
                'description_sv_se' => 'Ta bort smuts från mattan',
            ],
        ];
    }

    /**
     * @return User[]
     */
    private function getUsers()
    {
        return User::whereIn('email', [
            'customer1@email.com',
            'customer2@email.com',
        ])->get();
    }

    private function getWorkers(): array
    {
        return User::whereIn('email', [
            'worker1@email.com',
            'worker2@email.com',
            'worker3@email.com',
        ])->get()->toArray();
    }
}
