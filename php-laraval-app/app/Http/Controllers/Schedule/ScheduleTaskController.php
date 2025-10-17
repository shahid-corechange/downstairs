<?php

namespace App\Http\Controllers\Schedule;

use App\DTOs\CustomTask\CreateCustomTaskRequestDTO;
use App\DTOs\CustomTask\UpdateCustomTaskRequestDTO;
use App\DTOs\Schedule\ScheduleResponseDTO;
use App\Enums\Schedule\ScheduleStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Models\Schedule;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ScheduleTaskController extends Controller
{
    use ResponseTrait;

    /**
     * Additional fields to include in the response.
     */
    private array $includes = [
        'allEmployees.user',
        'allEmployees.schedule.team.users',
        'service.tasks',
        'user',
        'subscription.tasks',
        'property.address.city',
        'refund',
        'team',
        'products.product.tasks',
        'tasks',
        'customer',
    ];

    /**
     * Send only these fields in the response.
     */
    private array $onlys = [
        'id',
        'customerId',
        'serviceId',
        'userId',
        'teamId',
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
        'property.address.fullAddress',
        'property.address.latitude',
        'property.address.longitude',
        'property.keyInformation.keyPlace',
        'refund.amount',
        'customer.membershipType',
        'team.id',
        'team.color',
        'team.name',
        'team.totalWorkers',
        'products.paymentMethod',
        'products.productId',
        'products.product.name',
        'products.product.deletedAt',
        'products.product.tasks.id',
        'products.product.tasks.name',
        'products.product.tasks.description',
        'products.product.creditPrice',
        'tasks.id',
        'tasks.name',
        'tasks.description',
        'tasks.translations',
    ];

    /**
     * Add custom task to schedule.
     */
    public function store(
        Schedule $schedule,
        CreateCustomTaskRequestDTO $request,
    ): JsonResponse {
        if (! in_array(
            $schedule->status,
            [ScheduleStatusEnum::Booked(), ScheduleStatusEnum::Progress()]
        )) {
            return $this->errorResponse(
                __('failed to add task to schedule due to schedule status'),
                Response::HTTP_BAD_REQUEST
            );
        }

        $data = $request->toArray();
        DB::transaction(function () use ($data, $schedule) {
            /** @var \App\Models\CustomTask $task */
            $task = $schedule->tasks()->create([]);
            $task->translations()->createMany([
                to_translation('name', $data['name']),
                to_translation('description', $data['description']),
            ]);

            if ($schedule->status === ScheduleStatusEnum::Progress()) {
                $schedule->scheduleTasks()->create([
                    'custom_task_id' => $task->id,
                ]);
            }
        });

        return $this->successResponse(
            ScheduleResponseDTO::transformData(
                $schedule,
                includes: $this->includes,
                onlys: $this->onlys,
            ),
            Response::HTTP_CREATED,
            message: __('task created successfully')
        );
    }

    /**
     * Update custom task.
     */
    public function update(
        Schedule $schedule,
        int $taskId,
        UpdateCustomTaskRequestDTO $request,
    ): JsonResponse {
        if (! in_array(
            $schedule->status,
            [ScheduleStatusEnum::Booked(), ScheduleStatusEnum::Progress()]
        )) {
            return $this->errorResponse(
                __('failed to update task due to schedule status'),
                Response::HTTP_BAD_REQUEST
            );
        }

        $task = $schedule->tasks()->find($taskId);

        if (! $task) {
            return $this->errorResponse(__('task not found'), Response::HTTP_NOT_FOUND);
        }

        $data = $request->toArray();
        DB::transaction(function () use ($data, $task) {
            /** @var \App\Models\CustomTask $task */
            $task->updateTranslations([
                to_translation('name', $data['name']),
                to_translation('description', $data['description']),
            ]);
        });

        return $this->successResponse(
            ScheduleResponseDTO::transformData(
                $schedule,
                includes: $this->includes,
                onlys: $this->onlys,
            ),
            message: __('task updated successfully')
        );
    }

    /**
     * Delete custom task from schedule.
     */
    public function destroy(
        Schedule $schedule,
        int $taskId,
    ): JsonResponse {
        if (! in_array(
            $schedule->status,
            [ScheduleStatusEnum::Booked(), ScheduleStatusEnum::Progress()]
        )) {
            return $this->errorResponse(
                __('failed to remove task due to schedule status'),
                Response::HTTP_BAD_REQUEST
            );
        }

        $task = $schedule->tasks()->find($taskId);

        if (! $task) {
            return $this->errorResponse(__('task not found'), Response::HTTP_NOT_FOUND);
        }

        DB::transaction(function () use ($task) {
            /** @var \App\Models\CustomTask $task */
            $task->translations()->forceDelete();
            $task->delete();
        });

        return $this->successResponse(
            ScheduleResponseDTO::transformData(
                $schedule,
                includes: $this->includes,
                onlys: $this->onlys,
            ),
            message: __('task deleted successfully')
        );
    }
}
