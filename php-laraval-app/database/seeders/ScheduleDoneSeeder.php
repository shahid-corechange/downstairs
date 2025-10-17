<?php

namespace Database\Seeders;

use App\Enums\Deviation\DeviationTypeEnum;
use App\Enums\PermissionsEnum;
use App\Enums\ScheduleCleaning\CleaningProductPaymentMethodEnum;
use App\Enums\ScheduleCleaning\ScheduleCleaningStatusEnum;
use App\Enums\ScheduleEmployee\ScheduleEmployeeStatusEnum;
use App\Models\Deviation;
use App\Models\Product;
use App\Models\ScheduleCleaning;
use App\Models\ScheduleCleaningDeviation;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ScheduleDoneSeeder extends Seeder
{
    private int $dayCounter = 0;

    private int $hourCounter = 1;

    private int $counter = 1;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $numberOfInstances = app()->environment() !== 'testing' ? 100 : 20;
        $workers = array_map(function ($user) {
            return [
                'user_id' => $user['id'],
                'status' => ScheduleEmployeeStatusEnum::Pending(),
            ];
        }, User::permission(PermissionsEnum::AccessEmployeeApp())->get()->toArray());

        $subscription = Subscription::first();
        $minute = fake()->randomElement([0, 15, 30, 45]);

        ScheduleCleaning::factory($numberOfInstances)
            ->forSubscription($subscription)
            ->forStatus(ScheduleCleaningStatusEnum::Done())
            ->create()
            ->each(function (ScheduleCleaning $scheduleCleaning) use ($workers, $minute) {
                $this->setSchedule($scheduleCleaning, $workers, $minute);
                $this->setProducts($scheduleCleaning);
                $this->doneSchedule($scheduleCleaning);
                $this->setDeviation($scheduleCleaning);
            });
    }

    private function setSchedule(ScheduleCleaning $scheduleCleaning, array $workers, $minute)
    {
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
    }

    private function setProducts(ScheduleCleaning $scheduleCleaning)
    {
        $ids = [1, 2, 3, 4];
        shuffle($ids);
        $randomValues = array_slice($ids, 0, 2);
        $products = Product::whereIn('id', $randomValues)->get();

        foreach ($products as $product) {
            $isUseCredit = fake()->boolean();
            $scheduleCleaning->products()->create([
                'product_id' => $product->id,
                'price' => $product->price,
                'quantity' => 1,
                'discount_percentage' => $isUseCredit ? 100 : 0,
                'payment_method' => $isUseCredit ?
                    CleaningProductPaymentMethodEnum::Credit() : CleaningProductPaymentMethodEnum::Invoice(),
            ]);
        }
    }

    private function doneSchedule(ScheduleCleaning $scheduleCleaning)
    {
        foreach ($scheduleCleaning->products as $product) {
            $scheduleCleaning->scheduleCleaningTasks()->createMany($product->product->tasks->map(function ($task) {
                return [
                    'custom_task_id' => $task->id,
                    'is_completed' => fake()->boolean(20),
                ];
            })->toArray());
        }

        foreach ($scheduleCleaning->scheduleEmployees as $scheduleEmployee) {
            $isNotAttend = fake()->boolean(65);

            if ($isNotAttend) {
                $scheduleEmployee->update([
                    'status' => ScheduleEmployeeStatusEnum::Cancel(),
                ]);

                continue;
            }

            $scheduleEmployee->update([
                'status' => ScheduleEmployeeStatusEnum::Done(),
                'start_latitude' => fake()->latitude,
                'start_longitude' => fake()->longitude,
                'start_ip' => fake()->ipv4,
                'start_at' => Carbon::parse($scheduleCleaning->start_at)
                    ->addMinutes(fake()->numberBetween(20, 60)),
                'end_latitude' => fake()->latitude,
                'end_longitude' => fake()->longitude,
                'end_ip' => fake()->ipv4,
                'end_at' => Carbon::parse($scheduleCleaning->end_at)
                    ->addMinutes(fake()->numberBetween(20, 60)),
            ]);

            $deviations = [
                [
                    'schedule_cleaning_id' => $scheduleCleaning->id,
                    'user_id' => $scheduleEmployee->user_id,
                    'type' => DeviationTypeEnum::StartWrongTime(),
                    'reason' => fake()->paragraph,
                ],
                [
                    'schedule_cleaning_id' => $scheduleCleaning->id,
                    'user_id' => $scheduleEmployee->user_id,
                    'type' => DeviationTypeEnum::StopWrongTime(),
                    'reason' => fake()->paragraph,
                ],
            ];

            Deviation::insert($deviations);
        }
    }

    private function setDeviation(ScheduleCleaning $scheduleCleaning)
    {
        $types = [
            DeviationTypeEnum::StartWrongTime(),
            DeviationTypeEnum::StopWrongTime(),
        ];
        $totalIncompleteTasks = $scheduleCleaning->scheduleCleaningTasks()
            ->where('is_completed', false)
            ->count();

        if ($totalIncompleteTasks > 0) {
            $types[] = DeviationTypeEnum::IncompleteTask();
        }

        ScheduleCleaningDeviation::create([
            'schedule_cleaning_id' => $scheduleCleaning->id,
            'types' => $types,
            'is_handled' => false,
            'meta' => [],
        ]);
    }
}
