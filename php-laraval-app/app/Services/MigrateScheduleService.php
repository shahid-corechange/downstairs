<?php

namespace App\Services;

use App\Models\Addon;
use App\Models\Credit;
use App\Models\CreditTransaction;
use App\Models\CustomTask;
use App\Models\Deviation;
use App\Models\Order;
use App\Models\Schedule;
use App\Models\ScheduleCleaning;
use App\Models\ScheduleCleaningProduct;
use App\Models\ScheduleEmployee;
use App\Models\ScheduleItem;
use DB;
use Kolossal\Multiplex\Meta;

class MigrateScheduleService
{
    /**
     * Migrate schedule cleaning.
     */
    public function migrate(ScheduleCleaning $cleaning)
    {
        DB::transaction(function () use ($cleaning) {
            $schedule = Schedule::create([
                'user_id' => $cleaning->subscription->user_id,
                'service_id' => $cleaning->subscription->service_id,
                'team_id' => $cleaning->team_id,
                'customer_id' => $cleaning->customer_id,
                'property_id' => $cleaning->property_id,
                'subscription_id' => $cleaning->subscription_id,
                'status' => $cleaning->status,
                'start_at' => $cleaning->start_at,
                'end_at' => $cleaning->end_at,
                'original_start_at' => $cleaning->original_start_at,
                'quarters' => $cleaning->quarters,
                'is_fixed' => $cleaning->is_fixed,
                'key_information' => $cleaning->key_information,
                'note' => $cleaning->note,
                'cancelable_type' => $cleaning->cancelable_type,
                'cancelable_id' => $cleaning->cancelable_id,
                'canceled_at' => $cleaning->canceled_at,
                'scheduleable_id' => $cleaning->id,
                'scheduleable_type' => ScheduleCleaning::class,
            ]);

            // Update schedule employees
            ScheduleEmployee::withTrashed()
                ->where('scheduleable_id', $cleaning->id)
                ->update(['schedule_id' => $schedule->id]);

            // Migrate products to items
            $products = ScheduleCleaningProduct::with('product')
                ->where('schedule_cleaning_id', $cleaning->id)
                ->get();

            /** @var \App\Models\ScheduleCleaningProduct $product */
            foreach ($products as $product) {
                ScheduleItem::create([
                    'schedule_id' => $schedule->id,
                    'itemable_id' => $product->product->addon_id,
                    'itemable_type' => Addon::class,
                    'price' => $product->product->price,
                    'quantity' => $product->quantity,
                    'discount_percentage' => $product->discount_percentage,
                    'payment_method' => $product->payment_method,
                ]);
            }

            // Migrate tasks
            CustomTask::where('taskable_id', $cleaning->id)
                ->where('taskable_type', ScheduleCleaning::class)
                ->update([
                    'taskable_id' => $schedule->id,
                    'taskable_type' => Schedule::class,
                ]);

            // Migrate schedule tasks
            foreach ($cleaning->scheduleCleaningTasks as $task) {
                $schedule->scheduleTasks()->create([
                    'custom_task_id' => $task->custom_task_id,
                    'is_completed' => $task->is_completed,
                ]);
            }

            // Migrate deviation
            if ($cleaning->deviation) {
                $schedule->deviation()->create([
                    'types' => $cleaning->deviation->types,
                    'is_handled' => $cleaning->deviation->is_handled,
                    'meta' => $cleaning->deviation->meta,
                ]);
            }

            // Migrate employee deviations
            Deviation::where('schedule_cleaning_id', $cleaning->id)
                ->update([
                    'schedule_id' => $schedule->id,
                ]);

            // Migrate change request
            $changeRequest = $cleaning->changeRequest;
            if ($changeRequest) {
                $schedule->changeRequest()->create([
                    'causer_id' => $changeRequest->causer_id,
                    'original_start_at' => $changeRequest->original_start_at,
                    'start_at_changed' => $changeRequest->start_at_changed,
                    'original_end_at' => $changeRequest->original_end_at,
                    'end_at_changed' => $changeRequest->end_at_changed,
                    'status' => $changeRequest->status,
                ]);
            }

            // Migrate credits
            Credit::where('schedule_cleaning_id', $cleaning->id)
                ->update([
                    'schedule_id' => $schedule->id,
                ]);

            // Migrate Credit transactions
            CreditTransaction::where('schedule_cleaning_id', $cleaning->id)
                ->update([
                    'schedule_id' => $schedule->id,
                ]);

            // Migrate Meta
            Meta::where('metable_type', ScheduleCleaning::class)
                ->where('metable_id', $cleaning->id)
                ->update([
                    'metable_id' => $schedule->id,
                    'metable_type' => Schedule::class,
                ]);

            // Migrate order
            Order::withTrashed()
                ->where('orderable_id', $cleaning->id)
                ->where('orderable_type', ScheduleCleaning::class)
                ->update([
                    'orderable_id' => $schedule->id,
                    'orderable_type' => Schedule::class,
                ]);

            // Soft delete schedule if schedulecleaning is soft deleted
            if ($cleaning->trashed()) {
                $schedule->delete();
            }
        });
    }
}
