<?php

namespace App\Http\Controllers\Api\V2;

use App\DTOs\Notification\NotificationSchedulePayloadDTO;
use App\DTOs\Schedule\ScheduleResponseDTO;
use App\DTOs\Schedule\ScheduleUpdateRequestDTO;
use App\Enums\Azure\NotificationHub\NotificationHubEnum;
use App\Enums\Deviation\DeviationTypeEnum;
use App\Enums\Notification\NotificationTypeEnum;
use App\Enums\Schedule\ScheduleChangeStatusEnum;
use App\Enums\Schedule\ScheduleStatusEnum;
use App\Enums\ScheduleEmployee\ScheduleEmployeeStatusEnum;
use App\Helpers\Notification\AppNotificationOptions;
use App\Helpers\Notification\SendNotificationOptions;
use App\Helpers\Notification\SMSNotificationOptions;
use App\Http\Controllers\Controller;
use App\Http\Traits\QueryStringTrait;
use App\Http\Traits\ResponseTrait;
use App\Jobs\SendNotificationJob;
use App\Jobs\UpdateInvoiceSummationJob;
use App\Models\Customer;
use App\Models\Deviation;
use App\Models\Schedule;
use App\Models\ScheduleEmployee;
use App\Services\CreditService;
use App\Services\Order\OrderCleaningService;
use Auth;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ScheduleController extends Controller
{
    use QueryStringTrait;
    use ResponseTrait;

    /**
     * List of additional fields to be included in the response.
     *
     * @var string[]
     */
    protected array $includes = [
        'detail',
        'service',
        'team',
        'customer.address.city.country',
        'property.address.city.country',
        'property.type',
        'items',
        'changeRequest',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $userId = Auth::id();

        $queries = $this->getQueries([
            'userId_eq' => $userId,
        ]);

        $paginatedData = Schedule::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            ScheduleResponseDTO::transformCollection($paginatedData->data, $this->includes),
            pagination: $paginatedData->pagination
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Schedule $schedule): JsonResponse
    {
        $this->authorize('view', $schedule);

        return $this->successResponse(
            ScheduleResponseDTO::transformData($schedule, $this->includes),
        );
    }

    /**
     * Cancel the specified schedule.
     */
    public function cancel(
        int $scheduleId,
        OrderCleaningService $orderService,
        CreditService $creditService,
    ): JsonResponse {
        /** @var \App\Models\Schedule */
        $data = Schedule::ofAuthUser()
            ->with(
                'scheduleEmployees.user.info',
                'products.product',
            )
            ->findOrFail($scheduleId);

        $this->authorize('cancel', $data);

        $isGetCredit = false;
        $sendNotifToEmployee = false;
        $totalCreditAmount = 0;

        DB::transaction(function () use (
            &$data,
            &$isGetCredit,
            &$sendNotifToEmployee,
            &$totalCreditAmount,
            $orderService,
            $creditService,
        ) {
            $isGetCredit = $data->can_refund;
            $data->update([
                'status' => ScheduleStatusEnum::Cancel(),
                'cancelable_type' => Customer::class,
                'cancelable_id' => $data->customer_id,
                'canceled_at' => now(),
            ]);

            // Cancel all employees
            $data->scheduleEmployees()->update([
                'status' => ScheduleEmployeeStatusEnum::Cancel(),
                'description' => __('schedule canceled by customer'),
            ]);

            // Delete all employee deviations
            Deviation::where('schedule_id', $data->id)
                ->whereIn('user_id', $data->scheduleEmployees->pluck('user_id'))
                ->whereNot('type', DeviationTypeEnum::Canceled())
                ->forceDelete();

            // Must refund add-ons that are paid with credit
            $totalCreditAmount = $creditService->refundItems($data);

            if ($isGetCredit) {
                $totalCreditAmount += $creditService->refund($data);
            }

            $sendNotifToEmployee = true;

            if ($isGetCredit) {
                scoped_localize('sv_SE', function () use ($data, $orderService) {
                    // Need improvement when implement laundry schedule
                    [$order, $invoice] = $orderService->createOrder($data, true);
                    $orderService->cancelByCustomer($order, $data);
                    UpdateInvoiceSummationJob::dispatchAfterResponse($invoice);
                });
            }

            // Remove change request if exist
            if ($data->changeRequest && $data->changeRequest->status === ScheduleChangeStatusEnum::Pending()) {
                $data->changeRequest->delete();
            }
        });

        $this->sendCancelNotif(
            $data,
            $isGetCredit,
            $sendNotifToEmployee,
            $totalCreditAmount
        );

        return $this->successResponse(
            ScheduleResponseDTO::transformData($data, $this->includes),
        );
    }

    /**
     * Send cancel notification to customer and employee.
     *
     * @param  ScheduleEmployee[]  $scheduleEmployees
     */
    private function sendCancelNotif(
        Schedule $schedule,
        bool $isGetCredit = false,
        bool $sendNotifToEmployee = false,
        int|float $totalCreditAmount = 0,
    ): void {
        if ($sendNotifToEmployee) {
            foreach ($schedule->scheduleEmployees as $scheduleEmployee) {
                scoped_localize($scheduleEmployee->user->info->language, function () use (
                    $scheduleEmployee,
                    $schedule,
                ) {
                    $displayDateTime = $schedule->start_at->copy()->timezone(
                        'Europe/Stockholm'
                    );

                    SendNotificationJob::dispatchAfterResponse(
                        $scheduleEmployee->user,
                        new SendNotificationOptions(
                            new AppNotificationOptions(
                                NotificationHubEnum::Employee(),
                                NotificationTypeEnum::ScheduleCancel(),
                                __('notification title schedule canceled'),
                                __('notification body schedule canceled', [
                                    'worker' => $scheduleEmployee->user->first_name,
                                    'date' => $displayDateTime->format('Y-m-d'),
                                    'time' => $displayDateTime->format('H:i'),
                                ]),
                                NotificationSchedulePayloadDTO::from([
                                    'id' => $scheduleEmployee->id,
                                    'start_at' => $schedule->start_at,
                                ])->toArray(),
                            ),
                            shouldSave: true,
                        )
                    );
                });
            }
        }

        scoped_localize(
            $schedule->user->info->language,
            function () use ($schedule) {
                $displayDateTime = $schedule->start_at->copy()->timezone(
                    'Europe/Stockholm'
                );

                SendNotificationJob::dispatchAfterResponse(
                    $schedule->user,
                    new SendNotificationOptions(
                        new AppNotificationOptions(
                            NotificationHubEnum::Customer(),
                            NotificationTypeEnum::ScheduleCancel(),
                            payload: NotificationSchedulePayloadDTO::from([
                                'id' => $schedule->id,
                                'start_at' => $schedule->start_at,
                            ])->toArray(),
                        ),
                        new SMSNotificationOptions(
                            body: __('notification body schedule canceled by customer sms', [
                                'date' => $displayDateTime->format('Y-m-d'),
                                'time' => $displayDateTime->format('H:i'),
                            ]),
                        ),
                        title: __('notification title schedule canceled'),
                        body: __('notification body schedule canceled by customer', [
                            'customer' => $schedule->user->first_name,
                            'date' => $displayDateTime->format('Y-m-d'),
                            'time' => $displayDateTime->format('H:i'),
                        ]),
                        shouldSave: true,
                        shouldInferMethod: true,
                    ),
                );
            }
        );

        if ($isGetCredit) {
            scoped_localize(
                $schedule->user->info->language,
                function () use ($schedule, $totalCreditAmount) {
                    SendNotificationJob::dispatchAfterResponse(
                        $schedule->user,
                        new SendNotificationOptions(
                            new AppNotificationOptions(
                                NotificationHubEnum::Customer(),
                                NotificationTypeEnum::CreditRefund(),
                                __('notification title credit refund'),
                                __('notification body credit refund', [
                                    'customer' => $schedule->user->first_name,
                                    'amount' => $totalCreditAmount,
                                ]),
                                NotificationSchedulePayloadDTO::from([
                                    'id' => $schedule->id,
                                    'start_at' => $schedule->start_at,
                                ])->toArray(),
                            ),
                            shouldSave: true,
                        ),
                    );
                }
            );
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        int $scheduleId,
        ScheduleUpdateRequestDTO $request
    ): JsonResponse {
        /** @var \App\Models\Schedule */
        $data = Schedule::ofAuthUser()
            ->with(['scheduleEmployees.user.info', 'service'])
            ->findOrFail($scheduleId);

        $this->authorize('update', $data);

        $requestData = $request->toArray();

        if ($request->isNotOptional('key_information') && $requestData['key_information'] !== $data->key_information) {
            return $this->errorResponse(
                __('key information cannot be updated from app'),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $data->update([
            'key_information' => $request->isNotOptional('key_information') ?
                $requestData['key_information'] : $data->key_information,
            'note->note' => $request->isNotOptional('note') ? $requestData['note'] : $data->note['note'],
        ]);

        // Send notification to employee
        $data->scheduleEmployees->each(function (ScheduleEmployee $scheduleEmployee) use ($data) {
            scoped_localize($scheduleEmployee->user->info->language, function () use ($scheduleEmployee, $data) {
                $displayDateTime = $data->start_at->copy()->timezone(
                    'Europe/Stockholm'
                );

                SendNotificationJob::dispatchAfterResponse(
                    $scheduleEmployee->user,
                    new SendNotificationOptions(
                        new AppNotificationOptions(
                            NotificationHubEnum::Employee(),
                            NotificationTypeEnum::ScheduleUpdated(),
                            __('notification title schedule updated'),
                            __('notification body schedule updated by customer', [
                                'worker' => $scheduleEmployee->user->first_name,
                                'date' => $displayDateTime->format('Y-m-d'),
                                'time' => $displayDateTime->format('H:i'),
                            ]),
                            NotificationSchedulePayloadDTO::from([
                                'id' => $scheduleEmployee->id,
                                'start_at' => $data->start_at,
                            ])->toArray(),
                        ),
                        shouldSave: true,
                    ),
                );
            });
        });

        return $this->successResponse(
            ScheduleResponseDTO::transformData($data, $this->includes),
        );
    }
}
