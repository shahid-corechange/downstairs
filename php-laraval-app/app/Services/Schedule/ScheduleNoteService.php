<?php

namespace App\Services\Schedule;

use App\Enums\ScheduleLaundry\ScheduleLaundryTypeEnum;
use App\Models\LaundryOrder;
use App\Models\Schedule;
use App\Models\ScheduleCleaning;

class ScheduleNoteService
{
    /**
     * Add note to schedule.
     *
     * @param  Schedule  $schedule
     * @param  LaundryOrder  $laundryOrder
     * @param  string  $type
     * @return void
     */
    public static function addLaundryNote($schedule, $laundryOrder, $type)
    {
        if ($type === ScheduleLaundryTypeEnum::Pickup()) {
            $schedule->update([
                'note->note' => __(
                    'pickup laundry to store',
                    ['store' => $laundryOrder->store->name],
                    locale: 'sv_SE',
                ),
            ]);
        } else {
            $schedule->update([
                'note->note' => __(
                    'delivery laundry from store',
                    ['store' => $laundryOrder->store->name],
                    locale: 'sv_SE',
                ),
            ]);
        }
    }

    /**
     * Remove laundry task from schedule.
     *
     * @param  Schedule  $schedule
     * @return void
     */
    public static function removeLaundryNote($schedule)
    {
        $note = $schedule->note['note'];
        /** @var ScheduleCleaning $cleaning */
        $cleaning = $schedule->scheduleable;

        if ($note) {
            if ($cleaning->laundry_type === ScheduleLaundryTypeEnum::Pickup()) {
                $noteToRemove = __(
                    'pickup laundry to store',
                    ['store' => $cleaning->laundryOrder?->store?->name],
                    locale: 'sv_SE',
                );
                // remove laundry note from given string
                $note = str_replace($noteToRemove, '', $note);

                $schedule->update([
                    'note->note' => $note,
                ]);
            } elseif ($cleaning->laundry_type === ScheduleLaundryTypeEnum::Delivery()) {
                $noteToRemove = __(
                    'delivery laundry from store',
                    ['store' => $cleaning->laundryOrder?->store?->name],
                    locale: 'sv_SE',
                );
                // remove laundry note from given string
                $note = str_replace($noteToRemove, '', $note);

                $schedule->update([
                    'note->note' => $note,
                ]);
            }
        }
    }
}
