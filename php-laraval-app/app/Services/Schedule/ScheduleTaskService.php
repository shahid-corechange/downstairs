<?php

namespace App\Services\Schedule;

use App\Enums\ScheduleLaundry\ScheduleLaundryTypeEnum;
use App\Models\CustomTask;
use App\Models\LaundryOrder;
use App\Models\Schedule;

class ScheduleTaskService
{
    /**
     * Add laundry task to schedule.
     *
     * @param  Schedule  $schedule
     * @param  LaundryOrder  $laundryOrder
     * @param  string  $type
     * @return void
     */
    public static function addLaundryTask($schedule, $laundryOrder, $type)
    {
        $task = $schedule->tasks()->create([]);

        if ($type === ScheduleLaundryTypeEnum::Pickup()) {
            $task->translations()->createMany([
                to_translation('name', [
                    'en_US' => __('pickup laundry', locale: 'en_US'),
                    'sv_SE' => __('pickup laundry', locale: 'sv_SE'),
                ]),
                to_translation('description', [
                    'en_US' => __(
                        'pickup laundry to store',
                        ['store' => $laundryOrder->store->name],
                        locale: 'en_US',
                    ),
                    'sv_SE' => __(
                        'pickup laundry to store',
                        ['store' => $laundryOrder->store->name],
                        locale: 'sv_SE'
                    ),
                ]),
            ]);
        } else {
            $task->translations()->createMany([
                to_translation('name', [
                    'en_US' => __('delivery laundry', locale: 'en_US'),
                    'sv_SE' => __('delivery laundry', locale: 'sv_SE'),
                ]),
                to_translation('description', [
                    'en_US' => __(
                        'delivery laundry from store',
                        ['store' => $laundryOrder->store->name],
                        locale: 'en_US',
                    ),
                    'sv_SE' => __(
                        'delivery laundry from store',
                        ['store' => $laundryOrder->store->name],
                        locale: 'sv_SE',
                    ),
                ]),
            ]);
        }

        // save task id to schedule meta in order to remove task when schedule is removed
        $schedule->saveMeta(['laundry_task_id' => $task->id]);
    }

    /**
     * Remove laundry task from schedule.
     *
     * @param  Schedule  $schedule
     * @return void
     */
    public static function removeLaundryTask($schedule)
    {
        $taskId = $schedule->laundry_task_id;
        /** @var CustomTask|null $task */
        $task = $schedule->tasks()->where('id', $taskId)->first();

        if ($task) {
            $task->translations()->forceDelete();
            $task->delete();
        }

        $schedule->deleteMeta('laundry_task_id');
    }
}
