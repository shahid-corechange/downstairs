<?php

namespace App\Services\Schedule;

use App\DTOs\Schedule\ScheduleBackOfficeUpdateRequestDTO;
use App\DTOs\Subscription\SubscriptionScheduleDTO;
use App\Enums\Credit\CreditTransactionTypeEnum;
use App\Enums\Notification\NotificationTypeEnum;
use App\Enums\Schedule\ScheduleChangeStatusEnum;
use App\Enums\Schedule\ScheduleItemPaymentMethodEnum;
use App\Enums\Schedule\ScheduleStatusEnum;
use App\Enums\ScheduleEmployee\ScheduleEmployeeStatusEnum;
use App\Jobs\LaundryOrder\CreateLaundryOrderFromScheduleJob;
use App\Jobs\UpdateInvoiceSummationJob;
use App\Models\Addon;
use App\Models\Product;
use App\Models\Schedule;
use App\Models\ScheduleCleaning;
use App\Models\Subscription;
use App\Models\SubscriptionItem;
use App\Models\Team;
use App\Models\User;
use App\Services\ChangeRequestService;
use App\Services\CreditService;
use App\Services\Order\OrderCleaningService;
use Auth;
use DB;
use Illuminate\Database\Eloquent\Builder;

class ScheduleCleaningService
{
    public function __construct(
        public OrderCleaningService $orderService,
        public CreditService $creditService,
    ) {
    }

    /**
     * Store all data to database.
     */
    public function store(SubscriptionScheduleDTO $data)
    {
        $subscription = Subscription::where('id', $data->subscription_id)->first();
        /** @var \App\Models\SubscriptionCleaningDetail $detail */
        $detail = $subscription->subscribable;
        $timezone = 'Europe/Stockholm';
        $subscriptionStart = $subscription->start_at
            ->copy()
            ->setTimeFromTimeString($detail->start_time)
            ->setTimezone($timezone);
        [$normalizedStart, $normalizedEnd] = ScheduleService::normalizeTime(
            $subscriptionStart,
            $data->start_at,
            $data->end_at,
        );

        $isExists = Schedule::where('subscription_id', $subscription->id)
            ->where('team_id', $data->team_id)
            ->where('start_at', $normalizedStart)
            ->where('end_at', $normalizedEnd)
            ->where('status', $data->status)
            ->exists();

        if (! $isExists) {
            $employees = User::whereHas('teams', function ($query) use ($data) {
                $query->where('id', $data->team_id);
            })
                ->get()
                ->map(fn ($user) => ['user_id' => $user->id])
                ->toArray();

            $items = $subscription->items
                ->map(fn (SubscriptionItem $item) => [
                    'itemable_id' => $item->itemable_id,
                    'itemable_type' => $item->itemable_type,
                    'price' => $item->itemable->price,
                    'quantity' => $item->quantity,
                    'discount_percentage' => 0,
                ]);

            DB::transaction(function () use (
                $data,
                $normalizedStart,
                $normalizedEnd,
                $employees,
                $items,
                $subscription,
            ) {
                $scheduleCleaning = ScheduleCleaning::create([]);

                $schedule = Schedule::create([
                    ...$data->toArray(),
                    'user_id' => $subscription->user_id,
                    'service_id' => $subscription->service_id,
                    'start_at' => $normalizedStart,
                    'end_at' => $normalizedEnd,
                    'original_start_at' => $normalizedStart,
                    'scheduleable_id' => $scheduleCleaning->id,
                    'scheduleable_type' => ScheduleCleaning::class,
                ]);

                $schedule->scheduleEmployees()->createMany($employees);
                $schedule->items()->createMany($items);
            });
        }
    }

    /**
     * Update schedule from admin.
     *
     * @param  Schedule  $schedule
     * @param  ScheduleBackOfficeUpdateRequestDTO  $request
     * @param  array  $cart
     * @param  \Carbon\Carbon  $startAt
     * @param  \Carbon\Carbon  $endAt
     * @param  bool  $isContainLaundryAddon
     * @return void
     */
    public function update($schedule, $request, $cart, $startAt, $endAt, $isContainLaundryAddon)
    {
        $causer = Auth::user();
        $sendChangeRequestNotif = false;
        $originalStartAt = $schedule->start_at;
        $originalEndAt = $schedule->end_at;
        $originalTeamId = $schedule->team_id;
        $isRemoveLaundry = in_array(
            config('downstairs.addons.laundry.id'),
            $request->remove_add_ons
        );
        $store = $schedule->scheduleable->laundryOrder?->store;
        $laundryType = $schedule->scheduleable->laundry_type;

        DB::transaction(function () use (
            $schedule,
            $request,
            $cart,
            $startAt,
            $endAt,
            &$sendChangeRequestNotif,
            $isRemoveLaundry,
            $causer,
        ) {
            // Update change request status
            $changeRequest = $schedule->changeRequest;
            if ($changeRequest && $changeRequest->status === ScheduleChangeStatusEnum::Pending()) {
                $timesMatch = $changeRequest->isTimeMatch($request->start_at, $request->end_at);
                $changeRequestStatus = $timesMatch ? ScheduleChangeStatusEnum::Approved() :
                    ScheduleChangeStatusEnum::Handled();

                $changeRequest->update([
                    'status' => $changeRequestStatus,
                    'causer_id' => $causer->id,
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
            if (($request->isNotOptional('remove_add_ons') && ! is_null($request->remove_add_ons)) ||
                ($request->isNotOptional('remove_products') && ! is_null($request->remove_products))) {
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
                        $schedule->user_id,
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

            // remove laundry from schedule cleaning
            if ($isRemoveLaundry) {
                $schedule->scheduleable->update([
                    'laundry_order_id' => null,
                    'laundry_type' => null,
                ]);

                ScheduleTaskService::removeLaundryTask($schedule);
                ScheduleNoteService::removeLaundryNote($schedule);
            }
        });

        if ($schedule->isCleaning() && $isContainLaundryAddon) {
            CreateLaundryOrderFromScheduleJob::dispatchAfterResponse($schedule, $causer);
        }

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

        if ($isRemoveLaundry) {
            ScheduleService::sendRemoveLaundryNotif($schedule, $store, $laundryType);
        }
    }

    /**
     * Cancel schedule from admin.
     *
     * @param  \App\Models\Schedule  $schedule
     * @param  bool  $refund
     * @return void
     */
    public function cancel($schedule, $refund)
    {
        $isRemoveLaundry = $schedule->isCleaning() && $schedule->scheduleable->laundry_order_id;
        $store = $schedule->scheduleable->laundryOrder?->store;
        $laundryType = $schedule->scheduleable->laundry_type;

        DB::transaction(function () use ($schedule, $refund, $isRemoveLaundry) {
            $adminId = request()->user()->id;

            // Update change request status
            $changeRequest = $schedule->changeRequest;
            if ($changeRequest && $changeRequest->status === ScheduleChangeStatusEnum::Pending()) {
                $changeRequest->update([
                    'status' => ScheduleChangeStatusEnum::Canceled(),
                    'causer_id' => $adminId,
                    'original_start_at' => $schedule->start_at,
                    'original_end_at' => $schedule->end_at,
                ]);
            }

            $schedule->update([
                'status' => ScheduleStatusEnum::Cancel(),
                'cancelable_type' => User::class,
                'cancelable_id' => $adminId,
                'canceled_at' => now(),
            ]);

            $schedule->scheduleEmployees()->update([
                'status' => ScheduleEmployeeStatusEnum::Cancel(),
                'description' => __('schedule canceled by admin'),
            ]);

            // Must refund items that are paid with credit
            $this->creditService->refundItems($schedule, $adminId);

            if ($refund) {
                $this->creditService->refund(
                    $schedule,
                    issuerId: $adminId,
                );

                scoped_localize('sv_SE', function () use ($schedule) {
                    [$order, $invoice] = $this->orderService->createOrder($schedule, true);
                    $this->orderService->cancelByAdmin($order, $schedule);
                    UpdateInvoiceSummationJob::dispatchAfterResponse($invoice);
                });
            }

            // remove laundry from schedule cleaning
            if ($isRemoveLaundry) {
                $schedule->scheduleable->update([
                    'laundry_order_id' => null,
                    'laundry_type' => null,
                ]);

                // remove laundry add on from schedule
                $schedule->items()
                    ->where('itemable_type', Addon::class)
                    ->where('itemable_id', config('downstairs.addons.laundry.id'))
                    ->delete();

                ScheduleTaskService::removeLaundryTask($schedule);
                ScheduleNoteService::removeLaundryNote($schedule);
            }

            // Delete schedule deviation if exist
            $schedule->deviation()->delete();
        });

        ScheduleService::sendNotif(
            $schedule,
            NotificationTypeEnum::ScheduleCancel(),
            'notification title schedule canceled',
            'notification body schedule canceled by admin',
            'notification body schedule canceled to customer',
            true,
        );

        if ($isRemoveLaundry) {
            ScheduleService::sendRemoveLaundryNotif($schedule, $store, $laundryType);
        }
    }
}
