<?php

namespace App\Services\Schedule;

use App\DTOs\Subscription\SubscriptionScheduleDTO;
use App\Enums\Credit\CreditTransactionTypeEnum;
use App\Enums\Notification\NotificationTypeEnum;
use App\Enums\Schedule\ScheduleChangeStatusEnum;
use App\Enums\Schedule\ScheduleItemPaymentMethodEnum;
use App\Enums\Schedule\ScheduleStatusEnum;
use App\Enums\ScheduleEmployee\ScheduleEmployeeStatusEnum;
use App\Enums\ScheduleLaundry\ScheduleLaundryTypeEnum;
use App\Jobs\UpdateInvoiceSummationJob;
use App\Models\Addon;
use App\Models\LaundryOrder;
use App\Models\Product;
use App\Models\Schedule;
use App\Models\ScheduleEmployee;
use App\Models\ScheduleLaundry;
use App\Models\Team;
use App\Models\User;
use App\Services\ChangeRequestService;
use App\Services\CreditService;
use App\Services\Order\OrderLaundryService;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Builder;

class ScheduleLaundryService
{
    public function __construct(
        public OrderLaundryService $orderService,
        public CreditService $creditService,
        public ScheduleService $scheduleService,
    ) {
    }

    /**
     * Update schedule from admin.
     * TODO: Need to adjust for schedule laundry
     *
     * @param  Schedule  $schedule
     * @param  ScheduleBackOfficeUpdateRequestDTO  $request
     * @param  array  $cart
     * @param  \Carbon\Carbon  $startAt
     * @param  \Carbon\Carbon  $endAt
     * @return void
     */
    public function update($schedule, $request, $cart, $startAt, $endAt)
    {
        $sendChangeRequestNotif = false;
        $originalStartAt = $schedule->start_at;
        $originalEndAt = $schedule->end_at;
        $originalTeamId = $schedule->team_id;

        DB::transaction(function () use ($schedule, $request, $cart, $startAt, $endAt, &$sendChangeRequestNotif) {
            // Update change request status
            $changeRequest = $schedule->changeRequest;
            if ($changeRequest && $changeRequest->status === ScheduleChangeStatusEnum::Pending()) {
                $timesMatch = $changeRequest->isTimeMatch($request->start_at, $request->end_at);
                $changeRequestStatus = $timesMatch ? ScheduleChangeStatusEnum::Approved() :
                    ScheduleChangeStatusEnum::Handled();

                $changeRequest->update([
                    'status' => $changeRequestStatus,
                    'causer_id' => Auth::user()->id,
                    'original_start_at' => $schedule->start_at,
                    'original_end_at' => $schedule->end_at,
                ]);

                $sendChangeRequestNotif = $timesMatch;
            }

            foreach ($cart['items'] as $item) {
                $data = $schedule->items->where('itemable_id', $item['itemable_id'])
                    ->where('itemable_type', $item['itemable_type'])
                    ->first();

                if ($data) {
                    $data->update([
                        'price' => $item['price'],
                        'quantity' => $item['quantity'],
                        'discount_percentage' => $item['discount_percentage'],
                        'payment_method' => $item['payment_method'],
                    ]);
                } else {
                    $schedule->items()->create($item);
                }
            }

            foreach ($cart['transactions'] as $transaction) {
                $this->creditService->createTransaction(
                    $schedule->user_id,
                    $transaction['type'],
                    $transaction['amount'],
                    $transaction['description'],
                    $schedule->id,
                );
            }

            // For removing items and refund credit
            if (! is_null($request->remove_add_ons) || ! is_null($request->remove_products)) {
                $items = $schedule->items->filter(function ($item) use ($request) {
                    // Only refund credit
                    if ($item->payment_method !== ScheduleItemPaymentMethodEnum::Credit()) {
                        return false;
                    }

                    // Remove product if exist
                    if ($item->itemable_type === Product::class) {
                        return in_array($item->itemable_id, $request->remove_products);
                    }

                    // Remove add on if exist
                    return in_array($item->itemable_id, $request->remove_add_ons);
                });

                foreach ($items as $item) {
                    // refund credit
                    $this->creditService->createTransaction(
                        $schedule->subscription->user_id,
                        CreditTransactionTypeEnum::Refund(),
                        $item->itemable->credit_price,
                        $item->itemable->name,
                        $schedule->id,
                        Auth::user()->id,
                    );
                }

                // delete product and add on
                $schedule->items()
                    ->where(function (Builder $query) use ($request) {
                        $query->where('itemable_type', Product::class)
                            ->whereIn('itemable_id', $request->remove_products);
                    })
                    ->orWhere(function (Builder $query) use ($request) {
                        $query->where('itemable_type', Addon::class)
                            ->whereIn('itemable_id', $request->remove_add_ons);
                    })
                    ->delete();
            }

            $totalActiveWorkers = $schedule->scheduleEmployees->count();
            $calendarQuarters = (int) ceil($startAt->diffInMinutes($endAt) / 15);
            $quarters = $calendarQuarters * $totalActiveWorkers;
            $note = array_merge(
                $schedule->note ?? [],
                $request->isNotOptional('note') ? ['note' => $request->note] : []
            );

            // if team is changed, delete all schedule employees and create new ones
            if ($schedule->team_id !== $request->team_id) {
                $schedule->scheduleEmployees()->forceDelete();
                $team = Team::find($request->team_id);
                $schedule->scheduleEmployees()->createMany(
                    $team->users->map(function (User $worker) {
                        return [
                            'user_id' => $worker->id,
                            'status' => ScheduleEmployeeStatusEnum::Pending(),
                        ];
                    })
                );
            }

            // update schedule
            $schedule->update([
                'team_id' => $request->team_id,
                'start_at' => $startAt,
                'end_at' => $endAt,
                'quarters' => $quarters,
                'note' => empty($note) ? ['note' => ''] : $note,
            ]);
        });

        /**
         * Notifications
         * 1.If change request is approved
         * 2.If change request is Rescheduled
         *
         * Notification will be sent if schedule has team / assigned schedule
         */
        if ($schedule->team_id) {
            if ($sendChangeRequestNotif) {
                $changeRequestService = new ChangeRequestService();
                $changeRequestService->sendApprovedNotif($schedule, $originalStartAt, $originalEndAt, $originalTeamId);
            } else {
                ScheduleService::sendNotif(
                    $schedule,
                    NotificationTypeEnum::ScheduleUpdated(),
                    'notification title schedule updated',
                    'notification body schedule updated by admin',
                    'notification body schedule updated to customer',
                );
            }
        }
    }

    /**
     * Cancel schedule from admin.
     *
     * @param  \App\Models\Schedule  $pickupSchedule
     * @param  bool  $refund
     * @return void
     */
    public function cancel($pickupSchedule, $refund)
    {
        $deliverySchedule = Schedule::where('scheduleable_type', ScheduleLaundry::class)
            ->whereHas('scheduleable', function (Builder $query) use ($pickupSchedule) {
                $query->where('type', ScheduleLaundryTypeEnum::Pickup())
                    ->where('laundry_order_id', $pickupSchedule->scheduleable->laundry_order_id);
            })
            ->first();

        $scheduleIds = $deliverySchedule ? [$pickupSchedule->id, $deliverySchedule->id] : [$pickupSchedule->id];

        DB::transaction(function () use (&$pickupSchedule, &$deliverySchedule, $scheduleIds, $refund) {
            $adminId = request()->user()->id;

            // Update change request status
            $pickupChangeRequest = $pickupSchedule->changeRequest;
            if ($pickupChangeRequest && $pickupChangeRequest->status === ScheduleChangeStatusEnum::Pending()) {
                $pickupChangeRequest->update([
                    'status' => ScheduleChangeStatusEnum::Canceled(),
                    'causer_id' => $adminId,
                    'original_start_at' => $pickupSchedule->start_at,
                    'original_end_at' => $pickupSchedule->end_at,
                ]);
            }

            if ($deliverySchedule) {
                $deliveryChangeRequest = $deliverySchedule->changeRequest;
                if ($deliveryChangeRequest && $deliveryChangeRequest->status === ScheduleChangeStatusEnum::Pending()) {
                    $deliveryChangeRequest->update([
                        'status' => ScheduleChangeStatusEnum::Canceled(),
                        'causer_id' => $adminId,
                        'original_start_at' => $deliverySchedule->start_at,
                        'original_end_at' => $deliverySchedule->end_at,
                    ]);
                }
            }

            Schedule::whereIn('id', $scheduleIds)->update([
                'status' => ScheduleStatusEnum::Cancel(),
                'cancelable_type' => User::class,
                'cancelable_id' => $adminId,
                'canceled_at' => now(),
            ]);

            ScheduleEmployee::whereIn('schedule_id', $scheduleIds)->update([
                'status' => ScheduleEmployeeStatusEnum::Cancel(),
                'description' => __('schedule canceled by admin'),
            ]);

            // Must refund items that are paid with credit
            $this->creditService->refundItems($pickupSchedule, $adminId);
            if ($deliverySchedule) {
                $this->creditService->refundItems($deliverySchedule, $adminId);
            }

            if ($refund) {
                // TODO: Refund credit laundry order
                // $this->creditService->refund(
                //     $schedule,
                //     issuerId: $adminId,
                // );

                scoped_localize('sv_SE', function () use ($pickupSchedule) {
                    [$order, $invoice] = $this->orderService->createOrder($pickupSchedule, true);
                    $this->orderService->cancelByAdmin($order, $pickupSchedule);
                    UpdateInvoiceSummationJob::dispatchAfterResponse($invoice);
                });
            }
        });

        ScheduleService::sendNotif(
            $pickupSchedule,
            NotificationTypeEnum::ScheduleCancel(),
            'notification title schedule canceled',
            'notification body schedule canceled by admin',
            'notification body schedule canceled to customer',
        );
    }

    /**
     * Get employees for a schedule
     */
    public function getEmployees(int $teamId)
    {
        return User::whereHas(
            'teams',
            function ($query) use ($teamId) {
                $query->where('id', $teamId);
            }
        )
            ->get()
            ->map(fn ($user) => ['user_id' => $user->id])
            ->toArray();
    }

    /**
     * Store schedule to database
     *
     * @param  SubscriptionScheduleDTO  $data
     * @param  LaundryOrder  $laundryOrder
     * @param  Carbon  $startAt
     * @param  Carbon  $endAt
     * @param  array  $employees
     * @param  array  $items
     */
    public function storeSchedule(
        $data,
        $laundryOrder,
        $startAt,
        $endAt,
        $employees,
        $items,
    ) {
        $scheduleLaundry = ScheduleLaundry::create([
            'laundry_order_id' => $laundryOrder->id,
            'type' => ScheduleLaundryTypeEnum::Pickup(),
        ]);

        $schedule = Schedule::create([
            ...$data->toArray(),
            'user_id' => $laundryOrder->user_id,
            'service_id' => $laundryOrder->subscription->service_id,
            'start_at' => $startAt,
            'end_at' => $endAt,
            'original_start_at' => $startAt,
            'scheduleable_id' => $scheduleLaundry->id,
            'scheduleable_type' => ScheduleLaundry::class,
        ]);

        $schedule->scheduleEmployees()->createMany($employees);
        $schedule->items()->createMany($items);
    }
}
