<?php

namespace App\Http\Controllers\Schedule;

use App\DTOs\Notification\NotificationSchedulePayloadDTO;
use App\DTOs\Schedule\ScheduleChangeResponseDTO;
use App\Enums\Azure\NotificationHub\NotificationHubEnum;
use App\Enums\Notification\NotificationTypeEnum;
use App\Enums\Schedule\ScheduleChangeStatusEnum;
use App\Enums\ScheduleLaundry\ScheduleLaundryTypeEnum;
use App\Enums\Subscription\SubscriptionFrequencyEnum;
use App\Helpers\Notification\AppNotificationOptions;
use App\Helpers\Notification\SendNotificationOptions;
use App\Http\Controllers\Controller;
use App\Http\Traits\QueryStringTrait;
use App\Http\Traits\ResponseTrait;
use App\Jobs\SendNotificationJob;
use App\Models\BlockDay;
use App\Models\Schedule;
use App\Models\ScheduleChangeRequest;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ScheduleChangeRequestController extends Controller
{
    use QueryStringTrait;
    use ResponseTrait;

    /**
     * Additional fields to include in the response.
     */
    private array $includes = [
        'schedule.team',
        'schedule.user',
        'schedule.property.address',
    ];

    /**
     * Send only these fields in the response.
     */
    private array $onlys = [
        'id',
        'scheduleId',
        'startAtChanged',
        'endAtChanged',
        'canReschedule',
        'schedule.teamId',
        'schedule.startAt',
        'schedule.endAt',
        'schedule.team.name',
        'schedule.user.fullname',
        'schedule.property.address.fullAddress',
    ];

    /**
     * Display the index view.
     */
    public function index(): Response
    {
        $queries = $this->getQueries(
            filter: [
                'status_eq' => ScheduleChangeStatusEnum::Pending(),
            ],
            sort: ['start_at_changed' => 'desc'],
            size: -1
        );
        $paginatedData = ScheduleChangeRequest::applyFilterSortAndPaginate($queries);

        return Inertia::render('Schedule/ChangeRequest/Overview/index', [
            'changeRequests' => ScheduleChangeResponseDTO::transformCollection(
                $paginatedData->data,
                $this->includes,
                onlys: $this->onlys
            ),
        ]);
    }

    /**
     * Display the index as a json.
     */
    public function jsonIndex(): JsonResponse
    {
        $queries = $this->getQueries(filter: [
            'status_eq' => ScheduleChangeStatusEnum::Pending(),
        ]);

        $paginatedData = ScheduleChangeRequest::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            ScheduleChangeResponseDTO::transformCollection($paginatedData->data),
            pagination: $paginatedData->pagination
        );
    }

    /**
     * Approve the change request.
     */
    public function approve(ScheduleChangeRequest $changeRequest): RedirectResponse
    {
        $isBlockDay = BlockDay::where('block_date', $changeRequest->start_at_changed->format('Y-m-d'))
            ->orWhere('block_date', $changeRequest->end_at_changed->format('Y-m-d'))
            ->exists();

        if ($isBlockDay) {
            return back()->with('error', __('failed to reschedule due to block day'));
        } elseif (! $changeRequest->can_reschedule) {
            return back()->with('error', __('change request not possible reschedule'));
        }

        $schedule = $changeRequest->schedule;
        $originalStartAt = $schedule->start_at;
        $originalEndAt = $schedule->end_at;
        $newStartAt = $changeRequest->start_at_changed ?? $schedule->start_at;
        $newEndAt = $changeRequest->end_at_changed ?? $schedule->end_at;
        $totalWorkers = $schedule->scheduleEmployees->count();
        $totalQuarters = $schedule->calendar_quarters * $totalWorkers;

        DB::transaction(function () use (
            $changeRequest,
            $newStartAt,
            $newEndAt,
            $totalQuarters,
            $schedule,
            $originalStartAt,
            $originalEndAt,
        ) {
            $schedule->update([
                'start_at' => $newStartAt,
                'end_at' => $newEndAt,
                'quarters' => $totalQuarters,
            ]);

            $this->updateSubscription($schedule, $newStartAt, $newEndAt, $totalQuarters);

            $changeRequest->update([
                'causer_id' => Auth::user()->id,
                'status' => ScheduleChangeStatusEnum::Approved(),
                'original_start_at' => $originalStartAt,
                'original_end_at' => $originalEndAt,
            ]);
        });

        scoped_localize(
            $schedule->user->info->language,
            function () use ($schedule, $originalStartAt, $originalEndAt, $newStartAt, $newEndAt) {
                $localizedOriginalStartAt = $originalStartAt->copy()->timezone(
                    'Europe/Stockholm'
                );
                $localizedOriginalEndAt = $originalEndAt->copy()->timezone('Europe/Stockholm');
                $localizedNewStartAt = $newStartAt->copy()->timezone('Europe/Stockholm');
                $localizedNewEndAt = $newEndAt->copy()->timezone('Europe/Stockholm');

                SendNotificationJob::dispatchAfterResponse(
                    $schedule->user,
                    new SendNotificationOptions(
                        new AppNotificationOptions(
                            NotificationHubEnum::Customer(),
                            NotificationTypeEnum::ChangeRequestApproved(),
                            __('notification title change request approved'),
                            __('notification body change request approved', [
                                'customer' => $schedule->user->first_name,
                                'original_date' => $localizedOriginalStartAt->format('Y-m-d'),
                                'original_time' => $localizedOriginalStartAt->format('H:i').' - '.
                                    $localizedOriginalEndAt->format('H:i'),
                                'new_date' => $localizedNewStartAt->format('Y-m-d'),
                                'new_time' => $localizedNewStartAt->format('H:i').' - '.
                                    $localizedNewEndAt->format('H:i'),
                            ]),
                            NotificationSchedulePayloadDTO::from([
                                'id' => $schedule->id,
                                'start_at' => $newStartAt,
                                'end_at' => $newEndAt,
                            ])->toArray(),
                        ),
                        shouldSave: true,
                    ),
                );
            }
        );

        foreach ($schedule->scheduleEmployees as $scheduleEmployee) {
            scoped_localize(
                $scheduleEmployee->user->info->language,
                function () use ($scheduleEmployee, $newStartAt) {
                    $localizedNewStartAt = $newStartAt->copy()->timezone(
                        'Europe/Stockholm'
                    );

                    SendNotificationJob::dispatchAfterResponse(
                        $scheduleEmployee->user,
                        new SendNotificationOptions(
                            new AppNotificationOptions(
                                NotificationHubEnum::Employee(),
                                NotificationTypeEnum::ScheduleUpdated(),
                                __('notification title schedule updated'),
                                __('notification body schedule updated', [
                                    'worker' => $scheduleEmployee->user->first_name,
                                    'date' => $localizedNewStartAt->format('Y-m-d'),
                                    'time' => $localizedNewStartAt->format('H:i'),
                                ]),
                                NotificationSchedulePayloadDTO::from([
                                    'id' => $scheduleEmployee->id,
                                    'start_at' => $newStartAt,
                                ])->toArray(),
                            ),
                            shouldSave: true,
                        ),
                    );
                }
            );
        }

        return back()->with('success', __('change request approved'));
    }

    /**
     * Reject the change request.
     */
    public function reject(ScheduleChangeRequest $changeRequest): RedirectResponse
    {
        $schedule = $changeRequest->schedule;
        $originalStartAt = $schedule->start_at;
        $originalEndAt = $schedule->end_at;

        $changeRequest->update([
            'causer_id' => Auth::user()->id,
            'status' => ScheduleChangeStatusEnum::Rejected(),
            'original_start_at' => $originalStartAt,
            'original_end_at' => $originalEndAt,
        ]);

        scoped_localize(
            $schedule->user->info->language,
            function () use ($schedule, $originalStartAt, $originalEndAt) {
                $localizedOriginalStartAt = $originalStartAt->copy()->timezone(
                    'Europe/Stockholm'
                );
                $localizedOriginalEndAt = $originalEndAt->copy()->timezone(
                    'Europe/Stockholm'
                );

                SendNotificationJob::dispatchAfterResponse(
                    $schedule->user,
                    new SendNotificationOptions(
                        new AppNotificationOptions(
                            NotificationHubEnum::Customer(),
                            NotificationTypeEnum::ChangeRequestRejected(),
                            __('notification title change request rejected'),
                            __('notification body change request rejected', [
                                'customer' => $schedule->user->first_name,
                                'original_date' => $localizedOriginalStartAt->format('Y-m-d'),
                                'original_time' => $localizedOriginalStartAt->format('H:i').' - '.
                                    $localizedOriginalEndAt->format('H:i'),
                            ]),
                            NotificationSchedulePayloadDTO::from([
                                'id' => $schedule->id,
                                'start_at' => $originalStartAt,
                                'end_at' => $originalEndAt,
                            ])->toArray(),
                        ),
                        shouldSave: true,
                    ),
                );
            }
        );

        return back()->with('success', __('change request rejected'));
    }

    /**
     * Update the subscription.
     *
     * @param  Schedule  $schedule
     * @param  Carbon  $startAt
     * @param  Carbon  $endAt
     * @param  int  $totalQuarters
     * @return void
     */
    private function updateSubscription($schedule, $startAt, $endAt, $totalQuarters)
    {
        /** @var Subscription $subscription */
        $subscription = $schedule->subscription;

        if ($subscription->frequency === SubscriptionFrequencyEnum::Once()) {
            if ($schedule->isCleaning()) {
                $subscription->update([
                    'start_at' => $startAt->format('Y-m-d'),
                    'end_at' => $endAt->format('Y-m-d'),
                ]);

                $subscription->subscribable()->update([
                    'start_time' => $startAt->format('H:i:s'),
                    'end_time' => $endAt->format('H:i:s'),
                    'quarters' => $totalQuarters,
                ]);
            } elseif ($schedule->isLaundry() && $schedule->scheduleable->type === ScheduleLaundryTypeEnum::Pickup()) {
                $subscription->update([
                    'start_at' => $startAt->format('Y-m-d'),
                    'end_at' => $endAt->format('Y-m-d'),
                ]);

                $subscription->subscribable()->update([
                    'pickup_time' => $startAt->format('H:i:s'),
                ]);
            } elseif ($schedule->isLaundry() && $schedule->scheduleable->type === ScheduleLaundryTypeEnum::Delivery()) {
                $subscription->update([
                    'start_at' => $startAt->format('Y-m-d'),
                    'end_at' => $endAt->format('Y-m-d'),
                ]);

                $subscription->subscribable()->update([
                    'delivery_time' => $startAt->format('H:i:s'),
                ]);
            }
        }
    }
}
