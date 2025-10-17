<?php

namespace App\Http\Controllers\Team;

use App\Contracts\StorageService;
use App\DTOs\Schedule\ScheduleResponseDTO;
use App\DTOs\Team\CreateTeamRequestDTO;
use App\DTOs\Team\TeamResponseDTO;
use App\DTOs\Team\UpdateTeamRequestDTO;
use App\DTOs\User\UserResponseDTO;
use App\Enums\Azure\BlobStorage\BlobStorageContainerEnum;
use App\Enums\Azure\BlobStorage\BlobStorageUploadSourceEnum;
use App\Enums\ScheduleCleaning\ScheduleCleaningStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Team;
use App\Models\User;
use DB;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TeamController extends Controller
{
    /**
     * Additional fields to include in the response.
     */
    private array $includes = [
        'users',
    ];

    /**
     * Send only these fields in the response.
     */
    private array $onlys = [
        'id',
        'name',
        'avatar',
        'color',
        'description',
        'isActive',
        'deletedAt',
        'users.id',
        'users.fullname',
    ];

    /**
     * Display the index view.
     */
    public function index(): Response
    {
        $queries = $this->getQueries(size: -1, show: 'all');
        $paginatedData = Team::applyFilterSortAndPaginate(
            $queries,
            fields: $this->onlys,
        );

        return Inertia::render('Team/Overview/index', [
            'teams' => TeamResponseDTO::transformCollection(
                $paginatedData->data,
                $this->includes,
                onlys: $this->onlys,
            ),
            'workers' => $this->getWorkers(),
        ]);
    }

    private function getWorkers()
    {
        $onlys = [
            'id',
            'fullname',
        ];

        $workers = User::selectWithRelations($onlys)
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['Worker', 'Superadmin']);
            })
            ->get();

        return UserResponseDTO::collection($workers)->only(...$onlys);
    }

    /**
     * Store resource in storage.
     */
    public function store(CreateTeamRequestDTO $request, StorageService $storage): RedirectResponse
    {
        $data = $request->toArray();

        if (! $request->isOptional('thumbnail')) {
            $filename = generate_filename('team', $request->thumbnail->extension());
            $url = $storage->upload(
                BlobStorageContainerEnum::Images(),
                BlobStorageUploadSourceEnum::Request(),
                'thumbnail',
                $filename
            );
            $data['avatar'] = $url;
        }

        DB::transaction(function () use ($data, $request) {
            $team = Team::create($data);
            $team->users()->sync($request->user_ids);
        });

        return back()->with('success', __('team created successfully'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        UpdateTeamRequestDTO $request,
        Team $team,
        StorageService $storage
    ): RedirectResponse {
        $data = $request->toArray();

        if (! $request->isOptional('thumbnail')) {
            if ($team->thumbnail_image) {
                $oldFilename = basename($team->thumbnail_image);
                $storage->delete(BlobStorageContainerEnum::Images(), $oldFilename);
            }

            $filename = generate_filename('team', $request->thumbnail->extension());
            $url = $storage->upload(
                BlobStorageContainerEnum::Images(),
                BlobStorageUploadSourceEnum::Request(),
                'thumbnail',
                $filename
            );
            $data['avatar'] = $url;
        }

        $userIds = $request->isNotOptional('user_ids') ? $request->user_ids : [];
        $originalUserIds = $team->users->pluck('id')->toArray();
        $addedUsers = array_diff($userIds, $originalUserIds);
        $removedUsers = array_diff($originalUserIds, $userIds);

        if (count($addedUsers) == 0 && count($removedUsers) == 0) {
            $team->update($data);

            return back()->with('success', __('team updated successfully'));
        }

        /** @var \Illuminate\Support\Collection<array-key,\App\Models\Schedule> */
        $schedules = $team->schedules()->future()->orderBy('start_at')->get();
        $modifiedSchedules = [];
        $collidedSchedules = [];

        for ($i = 0; $i < count($schedules); $i++) {
            $schedule = $schedules[$i];

            /** @var \Illuminate\Support\Collection<array-key,\App\Models\ScheduleEmployee> */
            $employees = $schedule->scheduleEmployees()->withTrashed()->get();
            $employeeIds = $employees->pluck('user_id')->toArray();
            $newEmployees = array_diff($addedUsers, $employeeIds);

            $activeEmployeeIds = $employees->whereNull('deleted_at')->pluck('user_id')->toArray();
            $removedEmployeeIds = array_intersect($removedUsers, $activeEmployeeIds);
            $totalActiveWorkers = count($activeEmployeeIds) - count($removedEmployeeIds) + count($newEmployees);

            $newEndTime = calculate_end_time(
                $schedule->start_at,
                calculate_calendar_quarters($schedule->quarters, $totalActiveWorkers),
                format: 'Y-m-d H:i:s'
            );

            if ($i < count($schedules) - 1) {
                $nextSchedule = $schedules[$i + 1];

                if ($nextSchedule->start_at->isBefore($newEndTime) || $nextSchedule->start_at->equalTo($newEndTime)) {
                    $collidedSchedules[] = $schedule;
                }
            }

            if (count($newEmployees) > 0 || count($removedEmployeeIds) > 0) {
                $modifiedSchedules[$schedule->id] = [
                    'new_end_at' => $newEndTime,
                    'new_user_ids' => $newEmployees,
                ];
            }
        }

        if (count($collidedSchedules) > 0) {
            return back()->with([
                'error' => __('team schedules collisions'),
                'errorPayload' => ScheduleResponseDTO::transformCollection(
                    $collidedSchedules,
                    includes: ['subscription.user', 'team'],
                    onlys: [
                        'id',
                        'subscription.user.fullname',
                        'team.name',
                        'teamId',
                        'startAt',
                        'endAt',
                    ]
                ),
            ]);
        }

        /** @var \Illuminate\Support\Collection<array-key,\App\Models\Subscription> */
        $subscriptions = $team->subscriptions()->active()->get();

        DB::transaction(function () use (
            $data,
            $request,
            $team,
            $addedUsers,
            $removedUsers,
            $subscriptions,
            $schedules,
            $modifiedSchedules,
        ) {
            $team->update($data);
            $team->users()->sync($request->user_ids);

            foreach ($subscriptions as $subscription) {
                $subscription->staffs()->whereIn('user_id', $removedUsers)->forceDelete();
                $subscription->staffs()->createMany(
                    array_map(function ($item) use ($subscription) {
                        return [
                            'user_id' => $item,
                            'quarters' => $subscription->quarters,
                            'is_active' => true,
                        ];
                    }, $addedUsers)
                );
            }

            foreach ($schedules as $schedule) {
                $schedule->scheduleEmployees()->withTrashed()->whereIn('user_id', $removedUsers)->forceDelete();

                if (isset($modifiedSchedules[$schedule->id])) {
                    $schedule->scheduleEmployees()->createMany(
                        array_map(function ($item) {
                            return [
                                'user_id' => $item,
                                'status' => ScheduleCleaningStatusEnum::Pending(),
                            ];
                        }, $modifiedSchedules[$schedule->id]['new_user_ids'])
                    );

                    $schedule->update(['end_at' => $modifiedSchedules[$schedule->id]['new_end_at']]);
                }
            }
        });

        return back()->with('success', __('team updated successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Team $team): RedirectResponse
    {
        $count = Subscription::where('team_id', $team->id)->count();

        if ($count > 0) {
            return back()->with('error', __('team has active subscriptions', ['count' => $count]));
        }

        $exists = $team->schedules()->active()->exists();

        if ($exists) {
            return back()->with('error', __('team has active schedules'));
        }

        DB::transaction(function () use ($team) {
            $team->is_active = false;
            $team->save();
            $team->delete();
        });

        return back()->with('success', __('team deleted successfully'));
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(Team $team): RedirectResponse
    {
        DB::transaction(function () use ($team) {
            $team->is_active = true;
            $team->save();
            $team->restore();
        });

        return back()->with('success', __('team restored successfully'));
    }
}
