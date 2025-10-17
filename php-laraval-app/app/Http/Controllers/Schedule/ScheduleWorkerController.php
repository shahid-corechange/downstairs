<?php

namespace App\Http\Controllers\Schedule;

use App\DTOs\Schedule\AddWorkerRequestDTO;
use App\DTOs\Schedule\BulkChangeWorkerRequestDTO;
use App\DTOs\Schedule\FindAvailableWorkersRequestDTO;
use App\DTOs\Schedule\ScheduleResponseDTO;
use App\DTOs\Schedule\UpdateWorkerAttendanceRequestDTO;
use App\DTOs\ScheduleEmployee\ScheduleEmployeeResponseDTO;
use App\DTOs\User\UserResponseDTO;
use App\Enums\Credit\CreditTransactionTypeEnum;
use App\Enums\Deviation\DeviationTypeEnum;
use App\Enums\Notification\NotificationTypeEnum;
use App\Enums\PermissionsEnum;
use App\Enums\Schedule\ScheduleChangeStatusEnum;
use App\Enums\Schedule\ScheduleItemPaymentMethodEnum;
use App\Enums\Schedule\ScheduleStatusEnum;
use App\Enums\ScheduleEmployee\ScheduleEmployeeStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Models\BlockDay;
use App\Models\CreditTransaction;
use App\Models\Deviation;
use App\Models\Schedule;
use App\Models\ScheduleDeviation;
use App\Models\ScheduleEmployee;
use App\Models\User;
use App\Services\CreditService;
use App\Services\Schedule\ScheduleService;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ScheduleWorkerController extends Controller
{
    use ResponseTrait;

    /**
     * Additional fields to include in the response.
     */
    private array $includes = [
        'notes',
        'allEmployees.user',
        'allEmployees.schedule.team.users',
        'subscription.tasks',
        'service.tasks',
        'user',
        'property.address.city',
        'property.keyInformation',
        'refund',
        'customer',
        'team',
        'items.item.tasks',
        'tasks',
    ];

    /**
     * Send only these fields in the response.
     */
    private array $onlys = [
        'id',
        'customerId',
        'teamId',
        'serviceId',
        'userId',
        'startAt',
        'endAt',
        'quarters',
        'keyInformation',
        'notes.propertyNote',
        'notes.subscriptionNote',
        'notes.note',
        'note',
        'isFixed',
        'hasDeviation',
        'workStatus',
        'status',
        'allEmployees.userId',
        'allEmployees.status',
        'allEmployees.deletedAt',
        'allEmployees.user.fullname',
        'allEmployees.schedule.team.users.id',
        'subscription.fixedPriceId',
        'subscription.tasks.id',
        'subscription.tasks.name',
        'subscription.tasks.description',
        'service.name',
        'service.tasks.id',
        'service.tasks.name',
        'service.tasks.description',
        'user.id',
        'user.fullname',
        'user.totalCredits',
        'property.address.city.name',
        'property.address.address',
        'property.address.fullAddress',
        'property.address.latitude',
        'property.address.longitude',
        'property.keyInformation.keyPlace',
        'refund.amount',
        'customer.membershipType',
        'team.id',
        'team.name',
        'team.color',
        'team.totalWorkers',
        'items.paymentMethod',
        'items.itemableType',
        'items.itemableId',
        'items.item.id',
        'items.item.name',
        'items.item.deletedAt',
        'items.item.tasks.id',
        'items.item.tasks.name',
        'items.item.tasks.description',
        'items.item.creditPrice',
        'tasks.id',
        'tasks.name',
        'tasks.description',
        'tasks.translations',
    ];

    /**
     * Additional fields to include when workers collision.
     */
    private array $workerCollisionIncludes = [
        'user',
        'schedule.team.users',
        'schedule.user',
    ];

    /**
     * Send only these fields in the response when workers collision.
     */
    private array $workerCollisionOnlys = [
        'id',
        'userId',
        'scheduleId',
        'user.fullname',
        'schedule.startAt',
        'schedule.endAt',
        'schedule.team.name',
        'schedule.team.users.id',
        'schedule.user.fullname',
    ];

    public function __construct(
        public ScheduleService $scheduleService,
    ) {
    }

    /**
     * Display the index as a json.
     */
    public function jsonIndex(): JsonResponse
    {
        $queries = $this->getQueries();
        $paginatedData = ScheduleEmployee::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            ScheduleEmployeeResponseDTO::transformCollection($paginatedData->data),
            pagination: $paginatedData->pagination
        );
    }

    /**
     * Display single data as a json.
     */
    public function jsonShow(Schedule $schedule, User $worker): JsonResponse
    {
        $scheduleEmployee = $schedule->scheduleEmployees()
            ->where('user_id', $worker->id)
            ->first();

        if (! $scheduleEmployee) {
            throw new NotFoundHttpException();
        }

        return $this->successResponse(ScheduleEmployeeResponseDTO::transformData($scheduleEmployee));
    }

    /**
     * Add worker to schedule.
     */
    public function add(
        Schedule $schedule,
        AddWorkerRequestDTO $request,
    ): JsonResponse {
        if (! in_array(
            $schedule->status,
            [ScheduleStatusEnum::Booked(), ScheduleStatusEnum::Progress()]
        )) {
            return $this->errorResponse(
                __('failed to add worker due to schedule status'),
                Response::HTTP_BAD_REQUEST
            );
        }

        $endTime = calculate_end_time(
            $schedule->start_at,
            calculate_calendar_quarters(
                $schedule->quarters,
                $schedule->scheduleEmployees->count() + count($request->worker_ids)
            ),
            format: 'Y-m-d H:i:s'
        );

        $workers = array_map(
            fn ($workerId) => ['user_id' => $workerId],
            $request->worker_ids
        );

        DB::transaction(function () use ($schedule, $workers, $endTime) {
            $schedule->scheduleEmployees()->createMany($workers);
            $schedule->update(['end_at' => $endTime]);
        });

        $this->scheduleService->sendAddedWorkerNotif($schedule, $request->worker_ids);

        return $this->successResponse(
            ScheduleResponseDTO::transformData(
                $schedule,
                includes: $this->includes,
                onlys: $this->onlys
            ),
            Response::HTTP_CREATED,
            message: __('worker added to schedule successfully')
        );
    }

    /**
     * enable worker to schedule.
     */
    public function enable(
        Schedule $schedule,
        User $worker,
    ): JsonResponse {
        if (! in_array(
            $schedule->status,
            [ScheduleStatusEnum::Booked(), ScheduleStatusEnum::Progress()]
        )) {
            return $this->errorResponse(
                __('failed to enable worker due to schedule status'),
                Response::HTTP_BAD_REQUEST
            );
        } elseif (! $worker->can(PermissionsEnum::AccessEmployeeApp())) {
            return $this->errorResponse(__('user not worker'), Response::HTTP_BAD_REQUEST);
        }

        $endTime = calculate_end_time(
            $schedule->start_at,
            calculate_calendar_quarters(
                $schedule->quarters,
                $schedule->scheduleEmployees->count() + 1
            ),
            format: 'Y-m-d H:i:s'
        );

        /** @var \Illuminate\Database\Eloquent\Collection<array-key,ScheduleEmployee> $collidedWorkers */
        $collidedWorkers = ScheduleEmployee::active()
            ->where('user_id', $worker->id)
            ->whereHas('schedule', function (Builder $query) use ($schedule, $endTime) {
                $query->booked()
                    ->where('id', '!=', $schedule->id)
                    ->whereNot(function (Builder $query) use ($schedule, $endTime) {
                        $query->where('start_at', '>=', $endTime)
                            ->orWhere('end_at', '<=', $schedule->start_at);
                    });
            })
            ->get();

        if ($collidedWorkers->isNotEmpty()) {
            $workerIds = $schedule->scheduleEmployees->pluck('user_id')->toArray();

            return $this->errorResponse(
                __('failed to enable worker due to worker collision'),
                status: Response::HTTP_CONFLICT,
                errors: [
                    'scheduleWorkerIds' => $workerIds,
                    'workerCollisions' => ScheduleEmployeeResponseDTO::transformCollection(
                        $collidedWorkers,
                        includes: $this->workerCollisionIncludes,
                        onlys: $this->workerCollisionOnlys,
                    ),
                ]
            );
        }

        $scheduleEmployee = ScheduleEmployee::withTrashed()
            ->where('schedule_id', $schedule->id)
            ->where('user_id', $worker->id)
            ->first();

        if ($scheduleEmployee) {
            DB::transaction(function () use ($schedule, $scheduleEmployee, $endTime) {
                $scheduleEmployee->restore();
                $schedule->update(['end_at' => $endTime]);
            });

            $this->scheduleService->sendEnabledDisableWorkerNotif(
                $schedule,
                $scheduleEmployee,
                $worker->id,
                'notification title schedule added',
                'notification body schedule added',
                NotificationTypeEnum::ScheduleAdded()
            );
        } else {
            return $this->errorResponse(__('worker not in schedule'), Response::HTTP_BAD_REQUEST);
        }

        return $this->successResponse(
            ScheduleResponseDTO::transformData(
                $schedule,
                includes: $this->includes,
                onlys: $this->onlys
            ),
            message: __('worker enabled successfully')
        );
    }

    /**
     * disable worker to schedule.
     */
    public function disable(
        Schedule $schedule,
        User $worker,
    ): JsonResponse {
        if (! in_array(
            $schedule->status,
            [ScheduleStatusEnum::Booked(), ScheduleStatusEnum::Progress()]
        )) {
            return $this->errorResponse(
                __('failed to disable worker due to schedule status'),
                Response::HTTP_BAD_REQUEST
            );
        } elseif (! $worker->can(PermissionsEnum::AccessEmployeeApp())) {
            return $this->errorResponse(__('user not worker'), Response::HTTP_BAD_REQUEST);
        }

        $endTime = calculate_end_time(
            $schedule->start_at,
            calculate_calendar_quarters(
                $schedule->quarters,
                $schedule->scheduleEmployees->count() - 1
            ),
            format: 'Y-m-d H:i:s'
        );
        $conflictSchedule = ScheduleService::getConflictSchedule(
            $schedule,
            $schedule->team_id,
            $schedule->start_at,
            $endTime
        );

        if ($conflictSchedule) {
            return $this->errorResponse(
                __('this action causes conflict with other schedules'),
                Response::HTTP_CONFLICT
            );
        }

        $scheduleEmployee = $schedule->scheduleEmployees()
            ->where('user_id', $worker->id)
            ->first();

        if ($scheduleEmployee) {
            DB::transaction(function () use ($schedule, $scheduleEmployee, $endTime) {
                $scheduleEmployee->delete();
                $schedule->update(['end_at' => $endTime]);
            });

            $this->scheduleService->sendEnabledDisableWorkerNotif(
                $schedule,
                $scheduleEmployee,
                $worker->id,
                'notification title schedule removed',
                'notification body schedule removed',
                NotificationTypeEnum::ScheduleUpdated()
            );
        } else {
            return $this->errorResponse(__('worker not in schedule'), Response::HTTP_BAD_REQUEST);
        }

        return $this->successResponse(
            ScheduleResponseDTO::transformData(
                $schedule,
                includes: $this->includes,
                onlys: $this->onlys
            ),
            message: __('worker disabled successfully')
        );
    }

    /**
     * Remove worker to schedule.
     */
    public function remove(
        Schedule $schedule,
        User $worker,
    ): JsonResponse {
        if (! in_array(
            $schedule->status,
            [ScheduleStatusEnum::Booked(), ScheduleStatusEnum::Progress()]
        )) {
            return $this->errorResponse(
                __('failed to remove worker due to schedule status'),
                Response::HTTP_BAD_REQUEST
            );
        } elseif (! $worker->can(PermissionsEnum::AccessEmployeeApp())) {
            return $this->errorResponse(__('user not worker'), Response::HTTP_BAD_REQUEST);
        }

        /** @var \App\Models\ScheduleEmployee|null */
        $scheduleEmployee = ScheduleEmployee::withTrashed()
            ->where('schedule_id', $schedule->id)
            ->where('user_id', $worker->id)
            ->first();

        // If remove when still enabled
        if ($scheduleEmployee && ! $scheduleEmployee->trashed()) {
            $endTime = calculate_end_time(
                $schedule->start_at,
                calculate_calendar_quarters(
                    $schedule->quarters,
                    $schedule->scheduleEmployees->count() - 1
                ),
                format: 'Y-m-d H:i:s'
            );
            $conflictSchedule = ScheduleService::getConflictSchedule(
                $schedule,
                $schedule->team_id,
                $schedule->start_at,
                $endTime
            );

            if ($conflictSchedule) {
                return $this->errorResponse(
                    __('this action causes conflict with other schedules'),
                    Response::HTTP_CONFLICT
                );
            }

            DB::transaction(function () use ($schedule, $scheduleEmployee, $endTime) {
                $scheduleEmployee->forceDelete();
                $schedule->update(['end_at' => $endTime]);
            });

            $this->scheduleService->sendEnabledDisableWorkerNotif(
                $schedule,
                $scheduleEmployee,
                $worker->id,
                'notification title schedule removed',
                'notification body schedule removed',
                NotificationTypeEnum::ScheduleUpdated()
            );
        } elseif ($scheduleEmployee && $scheduleEmployee->trashed()) {
            $scheduleEmployee->forceDelete();
        } else {
            return $this->errorResponse(__('worker not in schedule'), Response::HTTP_BAD_REQUEST);
        }

        return $this->successResponse(
            ScheduleResponseDTO::transformData(
                $schedule,
                includes: $this->includes,
                onlys: $this->onlys
            ),
            message: __('worker removed successfully')
        );
    }

    /**
     * Revert canceled worker to not done schedule.
     */
    public function revert(
        Schedule $schedule,
        User $worker,
        CreditService $creditService,
    ): JsonResponse {
        if ($schedule->status === ScheduleStatusEnum::Done()) {
            return $this->errorResponse(
                __('failed to revert worker due to schedule status'),
                Response::HTTP_BAD_REQUEST
            );
        } elseif (! $worker->can(PermissionsEnum::AccessEmployeeApp())) {
            return $this->errorResponse(__('user not worker'), Response::HTTP_BAD_REQUEST);
        } elseif ($schedule->status === ScheduleStatusEnum::Cancel()) {
            $isBlockDay = BlockDay::where('block_date', $schedule->start_at->format('Y-m-d'))
                ->orWhere('block_date', $schedule->end_at->format('Y-m-d'))
                ->exists();

            if ($isBlockDay) {
                return $this->errorResponse(__('failed to revert worker due to block day'), Response::HTTP_BAD_REQUEST);
            }

            $collidedSchedule = Schedule::where('team_id', $schedule->team_id)
                ->where('status', '!=', ScheduleStatusEnum::Cancel())
                ->where('id', '!=', $schedule->id)
                ->whereNot(function (Builder $query) use ($schedule) {
                    $query->where('start_at', '>', $schedule->end_at)
                        ->orWhere('end_at', '<', $schedule->start_at);
                })->first();

            if ($collidedSchedule) {
                return $this->errorResponse(
                    __('failed to revert worker due to schedule collision'),
                    Response::HTTP_BAD_REQUEST
                );
            }
        }

        // Validation for credit deduction
        $transactionAmount = $creditService->calculateRefund($schedule);
        $creditTransaction = CreditTransaction::where('schedule_id', $schedule->id)
            ->where('type', CreditTransactionTypeEnum::Refund())
            ->where('total_amount', $transactionAmount)
            ->latest()
            ->first();

        /** @var \Illuminate\Database\Eloquent\Collection<array-key,ScheduleItem> $items */
        $items = $schedule->items()
            ->where('payment_method', ScheduleItemPaymentMethodEnum::Credit())
            ->get();

        $totalCredit = $creditTransaction ? $creditTransaction->total_amount : 0;
        $itemsCreditSum = $items->sum(function ($item) {
            return $item->item->credit_price;
        });
        $totalCredit += $itemsCreditSum;

        if (! $creditService->hasEnough($totalCredit, $schedule->subscription->user_id)) {
            return $this->errorResponse(
                __(
                    'failed to revert worker due to insufficient credit',
                    ['total_credit' => $totalCredit]
                ),
                Response::HTTP_BAD_REQUEST
            );
        }

        /** @var \App\Models\ScheduleEmployee|null */
        $scheduleEmployee = ScheduleEmployee::withTrashed()
            ->where('schedule_id', $schedule->id)
            ->where('user_id', $worker->id)
            ->first();

        if ($scheduleEmployee) {
            $sendNotifToCustomer = false;
            $sendNotifCredit = false;

            DB::transaction(function () use (
                $scheduleEmployee,
                $schedule,
                $creditService,
                $creditTransaction,
                $items,
                &$sendNotifToCustomer,
                &$sendNotifCredit,
            ) {
                /**
                 * Revert canceled schedule when revert worker.
                 * This case is when all workers are canceled and revert worker.
                 * If need to revert worker, we need to revert the schedule to booked.
                 */
                if ($schedule->status === ScheduleStatusEnum::Cancel()) {
                    // Remove order
                    // Schedule cleaning not refundable does not have order
                    if ($schedule->order) {
                        $invoice = $schedule->order->invoice;
                        $schedule->order->forceDelete();

                        if ($invoice->orders()->count() === 0) {
                            $invoice->forceDelete();
                        }
                    }

                    $schedule->update([
                        'status' => ScheduleStatusEnum::Booked(),
                        'cancelable_type' => null,
                        'cancelable_id' => null,
                        'canceled_at' => null,
                    ]);

                    // Restore deviation if exist
                    $deviation = ScheduleDeviation::withTrashed()
                        ->where('schedule_id', $schedule->id)
                        ->first();
                    $deviation?->restore();

                    // Restore change request if exist
                    $changeRequest = $schedule->changeRequest;
                    if ($changeRequest && $changeRequest->status === ScheduleChangeStatusEnum::Canceled()) {
                        $changeRequest->update([
                            'status' => ScheduleChangeStatusEnum::Pending(),
                            'causer_id' => null,
                            'original_start_at' => null,
                            'original_end_at' => null,
                        ]);
                    }

                    // Update credit transaction for service
                    if ($creditTransaction) {
                        $creditService->createTransaction(
                            $schedule->user_id,
                            CreditTransactionTypeEnum::Updated(),
                            $creditTransaction->total_amount,
                            __('update credit due to schedule reverted by admin'),
                            $schedule->id,
                            auth()->id(),
                        );
                    }

                    // Update credit transaction for product
                    foreach ($items as $item) {
                        $creditService->createTransaction(
                            $schedule->user_id,
                            CreditTransactionTypeEnum::Updated(),
                            $item->itemable->credit_price,
                            __('update credit due to schedule reverted by admin'),
                            $schedule->id,
                            auth()->id(),
                        );
                    }

                    $sendNotifCredit = $creditTransaction || $items->isNotEmpty();

                    $sendNotifToCustomer = true;
                }

                // Revert worker to pending
                $scheduleEmployee->update(['status' => ScheduleEmployeeStatusEnum::Pending()]);

                // Remove canceled deviation
                Deviation::where('schedule_id', $scheduleEmployee->schedule_id)
                    ->where('user_id', $scheduleEmployee->user_id)
                    ->where('type', DeviationTypeEnum::Canceled())
                    ->where('is_handled', false)
                    ->forceDelete();
            });

            $this->scheduleService->sendEnabledDisableWorkerNotif(
                $schedule,
                $scheduleEmployee,
                $worker->id,
                'notification title schedule added',
                'notification body schedule added',
                NotificationTypeEnum::ScheduleAdded()
            );

            if ($sendNotifToCustomer) {
                $this->scheduleService->sendRevertNotifToCustomer($schedule, $sendNotifCredit);
            }
        } else {
            return $this->errorResponse(__('worker not in schedule'), Response::HTTP_BAD_REQUEST);
        }

        return $this->successResponse(
            ScheduleResponseDTO::transformData(
                $schedule,
                includes: $this->includes,
                onlys: $this->onlys
            ),
            message: __('worker reverted successfully')
        );
    }

    /**
     * Find all available workers.
     */
    public function findAvailable(FindAvailableWorkersRequestDTO $request): JsonResponse
    {
        if ($request->isOptional('worker_ids')) {
            $request->worker_ids = [];
        }

        $workers = User::where(function (Builder $query) {
            $query->permission(PermissionsEnum::AccessEmployeeApp())
                ->orWhereHas('roles', function (Builder $query) {
                    $query->where('name', 'Superadmin');
                });
        })
            ->whereNotIn('id', $request->worker_ids)
            ->whereDoesntHave('scheduleEmployees', function (Builder $query) use ($request) {
                $query->where('status', '!=', ScheduleEmployeeStatusEnum::Cancel())
                    ->whereHas(
                        'schedule',
                        function (Builder $query) use ($request) {
                            $query->where('status', '!=', ScheduleStatusEnum::Cancel())
                                ->whereNot(function (Builder $query) use ($request) {
                                    $query->where('start_at', '>', $request->end_at)
                                        ->orWhere('end_at', '<', $request->start_at);
                                });
                        }
                    );
            })->get();

        return $this->successResponse(UserResponseDTO::transformCollection($workers));
    }

    /**
     * Update attendance record of worker.
     */
    public function updateAttendance(
        Schedule $schedule,
        User $worker,
        UpdateWorkerAttendanceRequestDTO $request,
    ): RedirectResponse {
        /** @var \App\Models\ScheduleEmployee|null */
        $scheduleEmployee = $schedule->scheduleEmployees()
            ->where('user_id', $worker->id)
            ->first();

        if (! $scheduleEmployee) {
            return back()->with('error', __('worker not in schedule'));
        } elseif ($request->isNotOptional('time_adjustment')) {
            $startAt = Carbon::parse($request->start_at);
            $endAt = Carbon::parse($request->end_at);
            $workQuarters = ceil($startAt->diffInMinutes($endAt) / 15);

            if ($workQuarters + $request->time_adjustment->quarters < 0) {
                return back()
                    ->with('error', __('total quarters employee worked on cannot be less than 0'));
            }
        }

        DB::transaction(function () use ($scheduleEmployee, $request, $schedule) {
            $scheduleEmployee->update([
                'start_at' => $request->start_at,
                'end_at' => $request->end_at,
                'status' => ScheduleEmployeeStatusEnum::Done(),
            ]);

            Deviation::where('schedule_id', $schedule->id)
                ->where('user_id', $scheduleEmployee->user_id)
                ->whereIn('type', [
                    DeviationTypeEnum::StartWrongTime(),
                    DeviationTypeEnum::StopWrongTime(),
                    DeviationTypeEnum::NotStarted(),
                    DeviationTypeEnum::FinishedEarly(),
                ])
                ->where('is_handled', false)
                ->update(['is_handled' => true]);

            /**
             * Update or create time adjustment if exists.
             */
            if ($request->isNotOptional('time_adjustment')) {
                $scheduleEmployee->timeAdjustment()->updateOrCreate(
                    ['schedule_employee_id' => $scheduleEmployee->id],
                    [
                        'quarters' => $request->time_adjustment->quarters,
                        'reason' => $request->time_adjustment->reason,
                        'causer_id' => auth()->id(),
                    ]
                );
            }
        });

        return back()->with('success', __('worker attendance updated successfully'));
    }

    /**
     * Bulk change the worker of a schedules.
     */
    public function bulkChange(BulkChangeWorkerRequestDTO $request): JsonResponse
    {
        DB::transaction(function () use ($request) {
            /** @var \App\DTOs\Schedule\ChangeWorkerRequestDTO */
            foreach ($request->changes as $change) {
                $existingUser = ScheduleEmployee::where('schedule_id', $change->schedule_id)
                    ->where('user_id', $change->user_id)
                    ->where('id', '!=', $change->schedule_employee_id)
                    ->exists();

                if ($existingUser) {
                    return $this->errorResponse(__('worker already in the schedule'), Response::HTTP_BAD_REQUEST);
                }

                ScheduleEmployee::where('id', $change->schedule_employee_id)
                    ->update(['user_id' => $change->user_id]);
            }
        });

        return $this->successResponse(message: __('workers changed successfully'));
    }
}
