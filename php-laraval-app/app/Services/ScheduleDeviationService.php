<?php

namespace App\Services;

use App\Enums\Deviation\DeviationTypeEnum;
use App\Enums\Schedule\ScheduleStatusEnum;
use App\Enums\ScheduleEmployee\ScheduleEmployeeStatusEnum;
use App\Jobs\SentWorkingHoursJob;
use App\Jobs\UpdateInvoiceSummationJob;
use App\Models\Deviation;
use App\Models\ScheduleDeviation;
use App\Models\ScheduleItem;
use App\Models\User;
use App\Services\Order\OrderCleaningService;
use DB;

class ScheduleDeviationService
{
    public function __construct(
        private OrderCleaningService $orderService,
    ) {
    }

    /**
     * Handle the deviation.
     * For now, only cleaning is supported.
     *
     * @param  ScheduleDeviation  $deviation
     * @param  \Illuminate\Database\Eloquent\Collection<array-key, ScheduleItem>  $items
     * @param  int  $quarters
     * @param  array  $meta
     * @param  bool  $isHttp
     **/
    public function handle(
        $deviation,
        $items,
        $quarters,
        $meta = [],
        $isHttp = false,
    ) {
        $schedule = $deviation->schedule;

        DB::transaction(function () use (
            $deviation,
            $schedule,
            $items,
            $quarters,
            $meta,
            $isHttp,
        ) {
            /**
             * Also update quarters in schedule cleaning
             * It will be used to create work hours
             */
            $isDone = $schedule->actual_start_at && $schedule->actual_end_at;

            if ($isDone) {
                $schedule->update([
                    'quarters' => $quarters,
                    'status' => ScheduleStatusEnum::Done(),
                ]);
            } else {
                $schedule->update([
                    'status' => ScheduleStatusEnum::Cancel(),
                    'cancelable_type' => $isHttp ? User::class : null,
                    'cancelable_id' => $isHttp ? request()->user()->id : null,
                    'canceled_at' => now(),
                ]);
            }

            $deviation->update([
                'is_handled' => true,
                'meta' => $meta,
            ]);

            scoped_localize('sv_SE', function () use ($schedule, $items, $isHttp) {
                [$order, $invoice] = $this->orderService->createOrder($schedule);
                $this->orderService->createOrderRows($order, $schedule, $items);

                if ($isHttp) {
                    UpdateInvoiceSummationJob::dispatchAfterResponse($invoice);
                } else {
                    UpdateInvoiceSummationJob::dispatchSync($invoice);
                }
            });

            foreach ($schedule->scheduleEmployees as $scheduleEmployee) {
                if ($scheduleEmployee->status === ScheduleEmployeeStatusEnum::Done()) {
                    if ($isHttp) {
                        SentWorkingHoursJob::dispatchAfterResponse($scheduleEmployee);
                    } else {
                        SentWorkingHoursJob::dispatchSync($scheduleEmployee);
                    }

                    continue;
                }

                if (is_null($scheduleEmployee->start_at)) {
                    Deviation::create([
                        'schedule_id' => $schedule->id,
                        'user_id' => $scheduleEmployee->user_id,
                        'type' => DeviationTypeEnum::NotStarted(),
                        'reason' => null,
                    ]);
                }
            }
        });
    }
}
