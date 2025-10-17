<?php

namespace App\Http\Controllers\Api\V2;

use App\DTOs\Notification\NotificationSchedulePayloadDTO;
use App\DTOs\ScheduleEmployee\CancelScheduleEmployeeRequestDTO;
use App\DTOs\ScheduleEmployee\EndScheduleEmployeeRequestDTO;
use App\DTOs\ScheduleEmployee\ScheduleEmployeeResponseDTO;
use App\DTOs\ScheduleEmployee\StartScheduleEmployeeRequestDTO;
use App\Enums\Azure\NotificationHub\NotificationHubEnum;
use App\Enums\Deviation\DeviationTypeEnum;
use App\Enums\GlobalSetting\GlobalSettingEnum;
use App\Enums\LaundryOrder\LaundryOrderStatusEnum;
use App\Enums\Notification\NotificationTypeEnum;
use App\Enums\Schedule\ScheduleStatusEnum;
use App\Enums\ScheduleEmployee\ScheduleEmployeeStatusEnum;
use App\Enums\ScheduleLaundry\ScheduleLaundryTypeEnum;
use App\Helpers\Notification\AppNotificationOptions;
use App\Helpers\Notification\SendNotificationOptions;
use App\Http\Controllers\Controller;
use App\Http\Traits\ArrayTrait;
use App\Http\Traits\MetaTrait;
use App\Http\Traits\QueryStringTrait;
use App\Http\Traits\ResponseTrait;
use App\Jobs\Order\CreateOrderFromScheduleJob;
use App\Jobs\SendNotificationJob;
use App\Jobs\SentWorkingHoursJob;
use App\Models\Deviation;
use App\Models\Schedule;
use App\Models\ScheduleDeviation;
use App\Models\ScheduleEmployee;
use App\Models\Service;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ScheduleEmployeeController extends Controller
{
    use MetaTrait;
    use QueryStringTrait;
    use ResponseTrait;

    /**
     * List of additional fields to be included in the response.
     *
     * @var string[]
     */
    protected array $includes = [
        'user',
        'schedule.service',
        'schedule.subscription.user',
        'schedule.team',
        'schedule.property.address.city.country',
        'schedule.property.type',
        'schedule.products',
        'schedule.addons',
        'schedule.scheduleTasks',
    ];

    /**
     * Get all schedule employees.
     */
    public function index(): JsonResponse
    {
        $queries = $this->getQueries([
            'userId_equal' => Auth::id(),
        ]);
        $paginatedData = ScheduleEmployee::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            ScheduleEmployeeResponseDTO::transformCollection($paginatedData->data, $this->includes),
            pagination: $paginatedData->pagination
        );
    }

    /**
     * Get spesific schedule.
     */
    public function show(int $scheduleId): JsonResponse
    {
        $data = ScheduleEmployee::selectWithRelations(mergeFields: true)
            ->ofAuthUser()
            ->findOrFail($scheduleId);

        return $this->successResponse(
            ScheduleEmployeeResponseDTO::transformData($data, $this->includes),
        );
    }

    /**
     * To start schedule employee
     * Create tasks from product, schedule cleaning, subscription, and service
     */
    public function start(
        int $scheduleId,
        StartScheduleEmployeeRequestDTO $request
    ): JsonResponse {
        $scheduleEmployee = ScheduleEmployee::with([
            'schedule.products.tasks',
            'schedule.addons.tasks',
        ])->findOrFail($scheduleId);
        $this->authorize('start', $scheduleEmployee);

        $sendNotif = false;
        $now = Carbon::now()->utc();

        /** @var \App\Models\Schedule */
        $schedule = $scheduleEmployee->schedule;

        DB::transaction(function () use ($request, $now, &$scheduleEmployee, &$sendNotif, &$schedule) {
            $startJobLateTime = get_setting(
                GlobalSettingEnum::StartJobLateTime(),
                config('downstairs.schedule.employee.maxStartMinutes')
            );

            if ($schedule->start_at->diffInMinutes($now, false) > $startJobLateTime) {
                Deviation::create([
                    'schedule_id' => $schedule->id,
                    'user_id' => $scheduleEmployee->user_id,
                    'type' => DeviationTypeEnum::StartWrongTime(),
                    'reason' => $request->isNotOptional('description') ? $request->description : null,
                ]);

                if (is_null($schedule->actual_start_at)) {
                    $deviation = ScheduleDeviation::findOrCreate($schedule->id);
                    $deviation->update(['types' => [...$deviation->types, DeviationTypeEnum::StartWrongTime()]]);
                }
            }

            $scheduleEmployee->update([
                ...$request->toArray(),
                'start_at' => $now,
                'status' => ScheduleEmployeeStatusEnum::Progress(),
            ]);

            if ($schedule->status == ScheduleStatusEnum::Booked()) {
                /**
                 * create employee tasks
                 */
                foreach ($schedule->products as $product) {
                    $this->createTasks($schedule, $product->tasks);
                }
                foreach ($schedule->addons as $addon) {
                    $this->createTasks($schedule, $addon->tasks);
                }
                $this->createTasks($schedule, $schedule->tasks);
                $this->createTasks($schedule, $schedule->subscription->tasks);
                $this->createTasks($schedule, $schedule->subscription->service->tasks);

                $schedule->update([
                    'status' => ScheduleStatusEnum::Progress(),
                ]);
                $sendNotif = true;
            }

            /**
             * For tracking laundry order status,
             * update laundry order status to in progress pickup or in progress delivery
             */
            $laundryType = $schedule->isCleaning() ? $schedule->scheduleable->laundry_type :
                $schedule->scheduleable->type;

            if ($laundryType === ScheduleLaundryTypeEnum::Pickup()) {
                $schedule->scheduleable->laundryOrder([
                    'status' => LaundryOrderStatusEnum::InProgressPickup(),
                ]);
            } elseif ($laundryType === ScheduleLaundryTypeEnum::Delivery()) {
                $schedule->scheduleable->laundryOrder([
                    'status' => LaundryOrderStatusEnum::InProgressDelivery(),
                ]);
            }
        });

        if ($sendNotif) {

            /**
             * send notification cleaning has started to customer
             * using notification service
             */
            scoped_localize($schedule->user->info->language, function () use ($schedule) {
                $displayDateTime = $schedule->start_at->copy()->timezone(
                    'Europe/Stockholm'
                );

                SendNotificationJob::dispatchAfterResponse(
                    $schedule->user,
                    new SendNotificationOptions(
                        new AppNotificationOptions(
                            NotificationHubEnum::Customer(),
                            NotificationTypeEnum::ScheduleStart(),
                            __('notification title schedule started'),
                            __('notification body schedule started', [
                                'customer' => $schedule->user->first_name,
                                'date' => $displayDateTime->format('Y-m-d'),
                                'time' => $displayDateTime->format('H:i'),
                            ]),
                            NotificationSchedulePayloadDTO::from([
                                'id' => $schedule->id,
                                'start_at' => $schedule->start_at,
                            ])->toArray(),
                        ),
                        shouldSave: true,
                    ),
                );
            });
        }

        return $this->successResponse(
            ScheduleEmployeeResponseDTO::transformData($scheduleEmployee, $this->includes),
        );
    }

    /**
     * @param  \App\Models\Schedule  $schedule
     * @param  \Illuminate\Database\Eloquent\Collection<int, \App\Models\CustomTask>  $tasks
     */
    private function createTasks($schedule, $tasks)
    {
        $schedule->scheduleTasks()->createMany($tasks->map(function ($task) {
            return [
                'custom_task_id' => $task->id,
            ];
        })->toArray());
    }

    /**
     * End schedule employee
     * Update shcedule cleaning
     * Create order
     * Update tasks
     */
    public function end(
        int $scheduleId,
        EndScheduleEmployeeRequestDTO $request,
    ): JsonResponse {
        $scheduleEmployee = ScheduleEmployee::with('schedule')->findOrFail($scheduleId);
        $this->authorize('end', $scheduleEmployee);

        $sendNotif = false;
        $now = now();

        /** @var \App\Models\Schedule */
        $schedule = $scheduleEmployee->schedule;

        /** @var \App\Models\ScheduleTask|null */
        $uncompletedServiceTask = $schedule->scheduleTasks()
            ->whereHas('customTask', function (Builder $query) {
                $query->where('taskable_type', Service::class);
            })
            ->whereNotIn('id', $request->completed_task_ids)
            ->where('is_completed', false)
            ->first();

        if ($uncompletedServiceTask) {
            return $this->errorResponse(
                __('uncompleted service task', ['task' => $uncompletedServiceTask->name])
            );
        }

        /** @var bool $isAllTaskCompleted */
        $isAllTaskCompleted = $schedule->scheduleTasks()
            ->whereNotIn('id', $request->completed_task_ids)
            ->where('is_completed', false)
            ->doesntExist();

        $data = ArrayTrait::filterKeys($request->toArray(), ['completed_task_ids']);
        $description = $request->isNotOptional('description') ? $request->description : null;

        DB::transaction(function () use (
            $request,
            $now,
            $isAllTaskCompleted,
            $data,
            &$scheduleEmployee,
            &$sendNotif,
            &$schedule,
            $description,
        ) {
            $endJobLateTime = get_setting(
                GlobalSettingEnum::EndJobLateTime(),
                config('downstairs.schedule.employee.maxEndMinutes')
            );
            $endJobEarlyTime = get_setting(
                GlobalSettingEnum::EndJobEarlyTime(),
                config('downstairs.schedule.employee.minEndMinutes')
            );

            $deviation = null;

            if ($schedule->end_at->diffInMinutes($now, false) > $endJobLateTime) {
                Deviation::create([
                    'schedule_id' => $schedule->id,
                    'user_id' => $scheduleEmployee->user_id,
                    'type' => DeviationTypeEnum::StopWrongTime(),
                    'reason' => $description,
                ]);

                if (is_null($schedule->actual_end_at)) {
                    $deviation = ScheduleDeviation::findOrCreate($schedule->id);
                    $deviation->update(['types' => [...$deviation->types, DeviationTypeEnum::StopWrongTime()]]);
                }
            }

            if ($schedule->end_at->diffInMinutes($now, false) <= (-$endJobEarlyTime)) {
                Deviation::create([
                    'schedule_id' => $schedule->id,
                    'user_id' => $scheduleEmployee->user_id,
                    'type' => DeviationTypeEnum::FinishedEarly(),
                    'reason' => $description,
                ]);

                if (is_null($schedule->actual_end_at)) {
                    $deviation = ScheduleDeviation::findOrCreate($schedule->id);
                    $deviation->update(['types' => [...$deviation->types, DeviationTypeEnum::FinishedEarly()]]);
                }
            }

            if (! $isAllTaskCompleted) {
                if (is_null($deviation)) {
                    $deviation = ScheduleDeviation::findOrCreate($schedule->id);
                }

                if (! in_array(DeviationTypeEnum::IncompleteTask(), $deviation->types)) {
                    $deviation->update(['types' => [...$deviation->types, DeviationTypeEnum::IncompleteTask()]]);
                }
            } else {
                if (is_null($deviation)) {
                    $deviation = ScheduleDeviation::where('schedule_id', $schedule->id)->first();
                }

                if (! is_null($deviation)) {
                    if (count($deviation->types) === 1 &&
                        in_array(DeviationTypeEnum::IncompleteTask(), $deviation->types)
                    ) {
                        $deviation->delete();
                    } elseif (count($deviation->types) > 1 &&
                        in_array(DeviationTypeEnum::IncompleteTask(), $deviation->types)
                    ) {
                        $deviation->update([
                            'types' => array_values(
                                array_diff($deviation->types, [DeviationTypeEnum::IncompleteTask()])
                            ),
                        ]);
                    }
                }
            }

            // update schedule cleaning task where id in completed_task_ids
            $schedule->scheduleTasks()
                ->whereIn('id', $request->completed_task_ids)
                ->update(['is_completed' => true]);

            // update data
            $scheduleEmployee->update([
                ...$data,
                'end_at' => Carbon::now()->utc(),
                'status' => ScheduleEmployeeStatusEnum::Done(),
            ]);

            // update schedule cleaning status
            if ($schedule->status == ScheduleStatusEnum::Progress()) {
                $schedule->update(['status' => ScheduleStatusEnum::Done()]);

                /**
                 * Update not started schedule employee to done
                 * because employee can't start done schedule
                 *
                 * @var \Illuminate\Support\Collection<array-key,\App\Models\ScheduleEmployee>
                 */
                $scheduleEmployeePendings = $schedule->scheduleEmployees()
                    ->where('status', ScheduleEmployeeStatusEnum::Pending())
                    ->get();

                foreach ($scheduleEmployeePendings as $scheduleEmployeePending) {
                    $scheduleEmployeePending->update([
                        'status' => ScheduleEmployeeStatusEnum::Done(),
                    ]);

                    Deviation::create([
                        'schedule_id' => $schedule->id,
                        'user_id' => $scheduleEmployeePending->user_id,
                        'type' => DeviationTypeEnum::NotStarted(),
                        'reason' => null,
                    ]);
                }

                $sendNotif = true;

                if (is_null($schedule->deviation)) {
                    CreateOrderFromScheduleJob::dispatch($schedule);
                }
            }

            /**
             * For tracking laundry order status,
             * update laundry order status to picked up or delivered
             */
            $laundryType = $schedule->isCleaning() ? $schedule->scheduleable->laundry_type :
                $schedule->scheduleable->type;

            if ($laundryType === ScheduleLaundryTypeEnum::Pickup()) {
                $schedule->scheduleable->laundryOrder([
                    'status' => LaundryOrderStatusEnum::PickedUp(),
                ]);
            } elseif ($laundryType === ScheduleLaundryTypeEnum::Delivery()) {
                $schedule->scheduleable->laundryOrder([
                    'status' => LaundryOrderStatusEnum::Delivered(),
                ]);
            }
        });

        if ($sendNotif) {
            /**
             * send notification cleaning has done to customer
             * using notification service
             */
            scoped_localize($schedule->user->info->language, function () use ($schedule) {
                $displayDateTime = $schedule->start_at->copy()->timezone(
                    'Europe/Stockholm'
                );

                SendNotificationJob::dispatchAfterResponse(
                    $schedule->user,
                    new SendNotificationOptions(
                        new AppNotificationOptions(
                            NotificationHubEnum::Customer(),
                            NotificationTypeEnum::ScheduleEnd(),
                            __('notification title schedule done'),
                            __('notification body schedule done', [
                                'customer' => $schedule->user->first_name,
                                'date' => $displayDateTime->format('Y-m-d'),
                                'time' => $displayDateTime->format('H:i'),
                            ]),
                            NotificationSchedulePayloadDTO::from([
                                'id' => $schedule->id,
                                'start_at' => $schedule->start_at,
                            ])->toArray(),
                        ),
                        shouldSave: true,
                    ),
                );
            });
        }

        $totalTimeDeviations = Deviation::where('schedule_id', $schedule->id)
            ->where('user_id', $scheduleEmployee->user_id)
            ->whereIn(
                'type',
                [
                    DeviationTypeEnum::StartWrongTime(),
                    DeviationTypeEnum::StopWrongTime(),
                    DeviationTypeEnum::FinishedEarly(),
                ]
            )
            ->count();

        if (! $schedule->has_deviation && $totalTimeDeviations === 0) {
            // send to fortnox working hours
            SentWorkingHoursJob::dispatchAfterResponse($scheduleEmployee);
        }

        return $this->successResponse(
            ScheduleEmployeeResponseDTO::transformData($scheduleEmployee, $this->includes),
        );
    }

    /**
     * Cancel schedule employee.
     */
    public function cancel(
        int $scheduleId,
        CancelScheduleEmployeeRequestDTO $request,
    ): JsonResponse {
        $scheduleEmployee = ScheduleEmployee::findOrFail($scheduleId);
        $this->authorize('cancel', $scheduleEmployee);

        $isAllWorkersCanceled = ScheduleEmployee::where('schedule_id', $scheduleEmployee->schedule_id)
            ->whereNot('status', ScheduleEmployeeStatusEnum::Cancel())
            ->whereNot('id', $scheduleEmployee->id)
            ->doesntExist();

        $payload = $request->toArray();

        DB::transaction(function () use (
            $payload,
            $scheduleEmployee,
            $isAllWorkersCanceled,
        ) {
            $scheduleEmployee->update([
                ...$payload,
                'status' => ScheduleEmployeeStatusEnum::Cancel(),
            ]);

            // Create deviation for the worker that canceled
            Deviation::create([
                'schedule_id' => $scheduleEmployee->schedule_id,
                'user_id' => $scheduleEmployee->user_id,
                'type' => DeviationTypeEnum::Canceled(),
                'reason' => $payload['description'] ?? null,
            ]);
            $scheduleDeviation = ScheduleDeviation::findOrCreate($scheduleEmployee->schedule_id);

            if ($isAllWorkersCanceled) {
                // Remove partly canceled deviation if all workers are canceled
                if (in_array(DeviationTypeEnum::PartlyCanceled(), $scheduleDeviation->types)) {
                    // remove partly canceled deviation
                    $types = array_values(
                        array_diff($scheduleDeviation->types, [DeviationTypeEnum::PartlyCanceled()])
                    );
                    $scheduleDeviation->update([
                        'types' => $types,
                    ]);
                }
            } else {
                // Add partly canceled deviation if not all workers are canceled
                if (! in_array(DeviationTypeEnum::PartlyCanceled(), $scheduleDeviation->types)) {
                    $scheduleDeviation->update([
                        'types' => [...$scheduleDeviation->types, DeviationTypeEnum::PartlyCanceled()],
                    ]);
                }
            }
        });

        return $this->successResponse(
            ScheduleEmployeeResponseDTO::transformData($scheduleEmployee, $this->includes),
        );
    }
}
