<?php

namespace Database\Seeders;

use App\Enums\Credit\CreditTransactionTypeEnum;
use App\Enums\Credit\CreditTypeEnum;
use App\Enums\Invoice\InvoiceTypeEnum;
use App\Enums\ScheduleCleaning\ScheduleCleaningStatusEnum;
use App\Jobs\UpdateInvoiceSummationJob;
use App\Models\Credit;
use App\Models\CreditCreditTransaction;
use App\Models\CreditTransaction;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\ScheduleCleaning;
use App\Models\Subscription;
use App\Models\Team;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(OrderService $orderService): void
    {
        $faker = fake();
        $subscriptions = Subscription::all();
        $statuses = [
            ScheduleCleaningStatusEnum::Cancel(),
            ScheduleCleaningStatusEnum::Cancel(),
            ScheduleCleaningStatusEnum::Cancel(),
            ScheduleCleaningStatusEnum::Done(),
        ];

        foreach ($subscriptions as $subscription) {
            foreach ($statuses as $status) {
                $scheduleCleaning = ScheduleCleaning::factory()
                    ->forSubscription($subscription)
                    ->forStatus($status)
                    ->create();

                if ($status == ScheduleCleaningStatusEnum::Cancel()) {
                    $this->setCancel($scheduleCleaning);

                    $credit = Credit::create([
                        'user_id' => $subscription->user->id,
                        'initial_amount' => $subscription->quarters,
                        'remaining_amount' => $subscription->quarters,
                        'type' => CreditTypeEnum::Refund(),
                        'valid_until' => now()->addYear(),
                        'description' => 'Refund for cancelation',
                    ]);

                    $transaction = CreditTransaction::create([
                        'user_id' => $credit->user_id,
                        'schedule_cleaning_id' => $scheduleCleaning->id,
                        'type' => CreditTransactionTypeEnum::Refund(),
                        'total_amount' => $subscription->quarters,
                        'description' => 'Refund for cancelation',
                    ]);

                    CreditCreditTransaction::create([
                        'credit_id' => $credit->id,
                        'credit_transaction_id' => $transaction->id,
                        'amount' => $subscription->quarters,
                    ]);
                }

                foreach ($subscription->products as $product) {
                    $productData = Product::find($product->product_id);
                    $scheduleCleaning->products()->create([
                        'schedule_cleaning_id' => $scheduleCleaning->id,
                        'product_id' => $product->product_id,
                        'price' => $productData->price,
                        'quantity' => $product->quantity,
                        'discount_percentage' => 0,
                    ]);
                }

                $data = [
                    'user_id' => $subscription->team->users->random()->id,
                    'status' => $status,
                ];

                $type = Invoice::getUserType(
                    $scheduleCleaning->subscription->user_id,
                    $scheduleCleaning->subscription->customer->membership_type,
                    InvoiceTypeEnum::Cleaning()
                );

                $invoice = Invoice::findOrCreate(
                    $scheduleCleaning->subscription->user_id,
                    $scheduleCleaning->subscription->customer_id,
                    $scheduleCleaning->start_at->month,
                    $scheduleCleaning->start_at->year,
                    $type,
                );

                if ($status === ScheduleCleaningStatusEnum::Done()) {
                    foreach ($scheduleCleaning->products as $product) {
                        $scheduleCleaning->scheduleCleaningTasks()
                            ->createMany($product->product->tasks->map(function ($task) {
                                return [
                                    'custom_task_id' => $task->id,
                                    'is_completed' => true,
                                ];
                            })->toArray());
                    }

                    $scheduleCleaning->scheduleEmployees()->create([
                        ...$data,
                        'start_latitude' => $faker->latitude,
                        'start_longitude' => $faker->longitude,
                        'start_ip' => $faker->ipv4,
                        'start_at' => $scheduleCleaning->start_at,
                        'end_latitude' => $faker->latitude,
                        'end_longitude' => $faker->longitude,
                        'end_ip' => $faker->ipv4,
                        'end_at' => $scheduleCleaning->end_at,
                    ]);

                    scoped_localize('sv_SE', function () use ($scheduleCleaning, $orderService) {
                        [$order] = $orderService->createOrder($scheduleCleaning);
                        $orderService->createOrderRows($order, $scheduleCleaning);
                    });
                } else {
                    $scheduleCleaning->scheduleEmployees()->create([
                        ...$data,
                        'description' => 'Sick or Unwell',
                    ]);

                    scoped_localize('sv_SE', function () use ($scheduleCleaning, $orderService) {
                        [$order] = $orderService->createOrder($scheduleCleaning, true);
                        $orderService->cancelByCustomer($order, $scheduleCleaning);
                    });
                }

                UpdateInvoiceSummationJob::dispatchSync($invoice);
            }
        }
    }

    private function setCancel(ScheduleCleaning $scheduleCleaning): void
    {
        $cancelableType = fake()->randomElement([
            Customer::class,
            User::class,
            Team::class,
        ]);

        $cancelableId = $cancelableType === Customer::class;

        if ($cancelableType === Customer::class) {
            $cancelableId = $scheduleCleaning->customer_id;
        } elseif ($cancelableType === User::class) {
            if (app()->environment() !== 'testing') {
                $cancelableId = User::role('Superadmin')->first()->id;
            } else {
                $cancelableId = User::first()->id;
            }
        } elseif ($cancelableType === Team::class) {
            $cancelableId = $scheduleCleaning->team_id;
        }

        $scheduleCleaning->update([
            'canceled_at' => $scheduleCleaning->updated_at,
            'cancelable_type' => $cancelableType,
            'cancelable_id' => $cancelableId,
        ]);
    }
}
