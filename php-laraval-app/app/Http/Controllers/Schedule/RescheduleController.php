<?php

namespace App\Http\Controllers\Schedule;

use App\DTOs\Notification\NotificationSchedulePayloadDTO;
use App\DTOs\Schedule\RescheduleRequestDTO;
use App\DTOs\Schedule\ScheduleResponseDTO;
use App\DTOs\ScheduleEmployee\ScheduleEmployeeResponseDTO;
use App\Enums\Azure\NotificationHub\NotificationHubEnum;
use App\Enums\GlobalSetting\GlobalSettingEnum;
use App\Enums\Notification\NotificationTypeEnum;
use App\Enums\Schedule\ScheduleChangeStatusEnum;
use App\Enums\Schedule\ScheduleStatusEnum;
use App\Enums\ScheduleEmployee\ScheduleEmployeeStatusEnum;
use App\Enums\ScheduleLaundry\ScheduleLaundryTypeEnum;
use App\Enums\Subscription\SubscriptionFrequencyEnum;
use App\Enums\Subscription\SubscriptionRefillSequenceEnum;
use App\Helpers\Notification\AppNotificationOptions;
use App\Helpers\Notification\SendNotificationOptions;
use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Jobs\SendNotificationJob;
use App\Models\BlockDay;
use App\Models\Schedule;
use App\Models\ScheduleEmployee;
use App\Models\Subscription;
use App\Models\Team;
use App\Models\User;
use App\Services\ChangeRequestService;
use Auth;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class RescheduleController extends Controller
{
    use ResponseTrait;

    /**
     * Additional fields to include in the response.
     */
    private array $includes = [
        'customer',
        'property.address.city',
        'user',
        'team',
    ];

    /**
     * Send only these fields in the response.
     */
    private array $onlys = [
        'id',
        'isFixed',
        'hasDeviation',
        'workStatus',
        'startAt',
        'endAt',
        'quarters',
        'status',
        'customer.membershipType',
        'property.address.city.name',
        'user.fullname',
        'team.id',
        'team.color',
        'team.name',
        'team.totalWorkers',
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

    public function store(
        Schedule $schedule,
        RescheduleRequestDTO $request,
        ChangeRequestService $changeRequestService
    ) {
        if (! in_array(
            $schedule->status,
            [ScheduleStatusEnum::Booked(), ScheduleStatusEnum::Progress()]
        )) {
            return $this->errorResponse(__('failed to reschedule due to schedule status'), Response::HTTP_BAD_REQUEST);
        }

        $refillSequence = get_setting(
            GlobalSettingEnum::SubscriptionRefillSequence(),
            config('downstairs.subscription.refillSequence')
        );
        $days = weeks_to_days($refillSequence);
        $refillSequences = SubscriptionRefillSequenceEnum::options();
        $time = array_search($refillSequence, $refillSequences);
        $limit = now()->addDays($days)->endOfDay();

        if ($schedule->start_at->isAfter($limit)) {
            return $this->errorResponse(
                __('failed to reschedule a certain time ahead', [
                    'time' => __($time),
                ]),
                Response::HTTP_BAD_REQUEST
            );
        }

        $startAt = Carbon::parse($request->start_at);

        if ($startAt->isAfter($limit)) {
            return $this->errorResponse(
                __('failed to reschedule to a certain time ahead', [
                    'time' => __($time),
                ]),
                Response::HTTP_BAD_REQUEST
            );
        }

        $workers = $request->team_id !== $schedule->team_id ?
            Team::find($request->team_id)->users :
            $schedule->scheduleEmployees;
        $endAt = Carbon::parse(calculate_end_time(
            $request->start_at,
            calculate_calendar_quarters(
                $schedule->quarters,
                $workers->count(),
            ),
            format: 'Y-m-d H:i:s'
        ));

        $isManuallyUpdated = $this->isManuallyUpdated($schedule, $request);

        $isBlockDay = BlockDay::where('block_date', $startAt->format('Y-m-d'))
            ->orWhere('block_date', $endAt->format('Y-m-d'))
            ->exists();

        if ($isBlockDay) {
            return $this->errorResponse(__('failed to reschedule due to block day'), Response::HTTP_BAD_REQUEST);
        }

        $collidedSchedule = Schedule::where('team_id', $request->team_id)
            ->where('status', '!=', ScheduleStatusEnum::Cancel())
            ->where('id', '!=', $schedule->id)
            ->whereNot(function (Builder $query) use ($request, $endAt) {
                $query->where('start_at', '>=', $endAt)
                    ->orWhere('end_at', '<=', $request->start_at);
            })->first();

        if ($collidedSchedule) {
            return $this->errorResponse(__('schedule collision'), Response::HTTP_CONFLICT);
        }

        $workerIds = $workers->pluck($request->team_id !== $schedule->team_id ? 'id' : 'user_id');

        /** @var \Illuminate\Database\Eloquent\Collection<array-key,ScheduleEmployee> $collidedWorkers */
        $collidedWorkers = ScheduleEmployee::active()
            ->whereIn('user_id', $workerIds)
            ->whereHas('schedule', function (Builder $query) use ($schedule, $request, $endAt) {
                $query->booked()
                    ->where('id', '!=', $schedule->id)
                    ->whereNot(function (Builder $query) use ($request, $endAt) {
                        $query->where('start_at', '>=', $endAt)
                            ->orWhere('end_at', '<=', $request->start_at);
                    });
            })
            ->get();

        if ($collidedWorkers->isNotEmpty()) {
            $scheduleCollidedWorkers = $request->team_id !== $schedule->team_id ? [] :
                $workers->filter(function (ScheduleEmployee $worker) use ($collidedWorkers) {
                    return $collidedWorkers->contains('user_id', $worker->user_id);
                })->values();

            return $this->errorResponse(
                __('failed to reschedule due to worker collision'),
                status: Response::HTTP_CONFLICT,
                errors: [
                    'scheduleWorkerIds' => $workerIds,
                    'scheduleCollidedWorkers' => ScheduleEmployeeResponseDTO::transformCollection(
                        $scheduleCollidedWorkers,
                        includes: $this->workerCollisionIncludes,
                        onlys: $this->workerCollisionOnlys,
                    ),
                    'workerCollisions' => ScheduleEmployeeResponseDTO::transformCollection(
                        $collidedWorkers,
                        includes: $this->workerCollisionIncludes,
                        onlys: $this->workerCollisionOnlys,
                    ),
                ]
            );
        }

        $sendChangeRequestNotif = false;
        $originalStartAt = $schedule->start_at;
        $originalEndAt = $schedule->end_at;
        $originalTeamId = $schedule->team_id;

        DB::transaction(function () use (
            $schedule,
            $request,
            $workers,
            $startAt,
            $endAt,
            $isManuallyUpdated,
            &$sendChangeRequestNotif,
        ) {
            // Update change request status
            $changeRequest = $schedule->changeRequest;
            if ($changeRequest && $changeRequest->status === ScheduleChangeStatusEnum::Pending()) {
                $timesMatch = $changeRequest->isTimeMatch($startAt, $endAt);
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

            $schedule->update([
                'start_at' => $startAt,
                'end_at' => $endAt,
            ]);

            $schedule->deleteMeta('manually_updated');
            $schedule->saveMeta('manually_updated', $isManuallyUpdated);

            if ($schedule->team_id !== $request->team_id) {
                $schedule->update([
                    'team_id' => $request->team_id,
                ]);

                $schedule->scheduleEmployees()->forceDelete();
                $schedule->scheduleEmployees()->createMany(
                    $workers->map(function (User $worker) {
                        return [
                            'user_id' => $worker->id,
                            'status' => ScheduleEmployeeStatusEnum::Pending(),
                        ];
                    })
                );
            }

            $this->updateSubscription($schedule, $startAt, $endAt);
        });

        if ($request->is_notify) {
            if ($sendChangeRequestNotif) {
                $changeRequestService->sendApprovedNotif($schedule, $originalStartAt, $originalEndAt, $originalTeamId);
            }

            $this->sendNotification($schedule);
        }

        return $this->successResponse(
            ScheduleResponseDTO::transformData(
                $schedule,
                includes: $this->includes,
                onlys: $this->onlys
            ),
            message: __('reschedule successfully')
        );
    }

    private function sendNotification(Schedule $schedule)
    {
        foreach ($schedule->scheduleEmployees as $scheduleEmployee) {
            // send notification to employee
            scoped_localize($scheduleEmployee->user->info->language, function () use ($schedule, $scheduleEmployee) {
                $displayDateTime = $schedule->start_at->copy()->timezone(
                    'Europe/Stockholm'
                );

                SendNotificationJob::dispatchAfterResponse(
                    $scheduleEmployee->user,
                    new SendNotificationOptions(
                        new AppNotificationOptions(
                            NotificationHubEnum::Employee(),
                            NotificationTypeEnum::ScheduleUpdated(),
                            __('notification title schedule updated'),
                            __('notification body schedule updated by admin', [
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
                    ),
                );
            });
        }

        // send notification to customer
        scoped_localize($schedule->user->info->language, function () use ($schedule) {
            $displayDateTime = $schedule->start_at->copy()->timezone(
                'Europe/Stockholm'
            );

            SendNotificationJob::dispatchAfterResponse(
                $schedule->user,
                new SendNotificationOptions(
                    new AppNotificationOptions(
                        NotificationHubEnum::Customer(),
                        NotificationTypeEnum::ScheduleUpdated(),
                        __('notification title schedule updated'),
                        __('notification body schedule updated to customer', [
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

    /**
     * Check if the schedule is manually updated.
     */
    private function isManuallyUpdated(Schedule $schedule, RescheduleRequestDTO $request)
    {
        /** @var Subscription $subscription */
        $subscription = $schedule->subscription;

        if ($schedule->isCleaning()) {
            if ($request->team_id !== $subscription->subscribable->team_id) {
                return true;
            } elseif ($schedule->start_at->format('H:i:s') !== $subscription->subscribable->start_time) {
                return true;
            }
        }

        if ($schedule->isLaundry() && $schedule->scheduleable->type === ScheduleLaundryTypeEnum::Pickup()) {
            if ($request->team_id !== $subscription->subscribable->pickup_team_id) {
                return true;
            } elseif ($schedule->start_at->format('H:i:s') !== $subscription->subscribable->pickup_time) {
                return true;
            }
        }

        if ($schedule->isLaundry() && $schedule->scheduleable->type === ScheduleLaundryTypeEnum::Delivery()) {
            if ($request->team_id !== $subscription->subscribable->delivery_team_id) {
                return true;
            } elseif ($schedule->start_at->format('H:i:s') !== $subscription->subscribable->delivery_time) {
                return true;
            }
        }

        return $schedule->start_at->dayOfWeek !== $subscription->start_at->dayOfWeek;
    }

    /**
     * Update the subscription.
     *
     * @param  Schedule  $schedule
     * @param  Carbon  $startAt
     * @param  Carbon  $endAt
     * @return void
     */
    private function updateSubscription($schedule, $startAt, $endAt)
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
                    'team_id' => $schedule->team_id,
                ]);
            } elseif ($schedule->isLaundry() && $schedule->scheduleable->type === ScheduleLaundryTypeEnum::Pickup()) {
                $subscription->update([
                    'start_at' => $startAt->format('Y-m-d'),
                    'end_at' => $endAt->format('Y-m-d'),
                ]);

                $subscription->subscribable()->update([
                    'pickup_time' => $startAt->format('H:i:s'),
                    'pickup_team_id' => $schedule->team_id,
                ]);
            } elseif ($schedule->isLaundry() && $schedule->scheduleable->type === ScheduleLaundryTypeEnum::Delivery()) {
                $subscription->update([
                    'start_at' => $startAt->format('Y-m-d'),
                    'end_at' => $endAt->format('Y-m-d'),
                ]);

                $subscription->subscribable()->update([
                    'delivery_time' => $startAt->format('H:i:s'),
                    'delivery_team_id' => $schedule->team_id,
                ]);
            }
        }
    }
}
