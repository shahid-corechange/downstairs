<?php

namespace App\Http\Controllers\Deviation;

use App\DTOs\Deviation\DeviationResponseDTO;
use App\DTOs\Schedule\UpdateWorkerAttendanceRequestDTO;
use App\Enums\Deviation\DeviationTypeEnum;
use App\Enums\ScheduleEmployee\ScheduleEmployeeStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Jobs\SentWorkingHoursJob;
use App\Models\Deviation;
use App\Models\ScheduleEmployee;
use Carbon\Carbon;
use DB;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class DeviationController extends Controller
{
    use ResponseTrait;

    /**
     * Additional fields to include in the response.
     */
    private array $includes = [
        'user',
        'schedule',
    ];

    /**
     * Send only these fields in the response.
     */
    private array $onlys = [
        'id',
        'user.id',
        'user.fullname',
        'schedule.id',
        'schedule.actualStartAt',
        'schedule.actualEndAt',
        'schedule.startAt',
        'schedule.endAt',
        'schedule.status',
        'type',
        'reason',
        'isHandled',
        'createdAt',
    ];

    /**
     * Display the index view.
     */
    public function index(): Response
    {
        $queries = $this->getQueries(
            pagination: 'page',
            show: 'all',
            sort: ['created_at' => 'desc']
        );
        $paginatedData = Deviation::applyFilterSortAndPaginate(
            $queries,
            fields: $this->onlys,
        );

        return Inertia::render('Deviation/Employee/index', [
            'deviations' => DeviationResponseDTO::transformCollection(
                $paginatedData->data,
                $this->includes,
                onlys: $this->onlys,
            ),
            'pagination' => array_keys_to_camel_case($paginatedData->pagination),
        ]);
    }

    /**
     * Display the index as a json.
     */
    public function jsonIndex(): JsonResponse
    {
        $queries = $this->getQueries();
        $paginatedData = Deviation::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            DeviationResponseDTO::transformCollection($paginatedData->data),
            pagination: $paginatedData->pagination
        );
    }

    /**
     * To handle deviation.
     */
    public function handle(Deviation $deviation)
    {
        $deviation->update(['is_handled' => true]);

        return back()->with('success', __('deviation handled successfully'));
    }

    /**
     * Update worker attendance to handle not started deviation.
     */
    public function updateWorkerAttendance(Deviation $deviation, UpdateWorkerAttendanceRequestDTO $request)
    {
        /** @var ScheduleEmployee $scheduleEmployee */
        $scheduleEmployee = $deviation->schedule->scheduleEmployees()
            ->where('user_id', $deviation->user_id)
            ->first();

        if ($request->isNotOptional('time_adjustment')) {
            $startAt = Carbon::parse($request->start_at);
            $endAt = Carbon::parse($request->end_at);
            $workQuarters = ceil($startAt->diffInMinutes($endAt) / 15);

            if ($workQuarters + $request->time_adjustment->quarters < 0) {
                return back()
                    ->with('error', __('total quarters employee worked on cannot be less than 0'));
            }
        }

        DB::transaction(function () use ($deviation, $scheduleEmployee, $request) {
            $scheduleEmployee->update([
                'start_at' => $request->start_at,
                'end_at' => $request->end_at,
                'status' => ScheduleEmployeeStatusEnum::Done(),
            ]);

            Deviation::where('schedule_id', $deviation->schedule_id)
                ->where('user_id', $deviation->user_id)
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

        SentWorkingHoursJob::dispatchAfterResponse($scheduleEmployee);

        return back()->with('success', __('worker attendance updated successfully'));
    }
}
