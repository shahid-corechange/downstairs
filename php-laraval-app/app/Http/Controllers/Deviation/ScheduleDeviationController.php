<?php

namespace App\Http\Controllers\Deviation;

use App\DTOs\Deviation\ScheduleDeviationResponseDTO;
use App\DTOs\Deviation\ScheduleHandleDeviationRequestDTO;
use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Models\ScheduleDeviation;
use App\Services\ScheduleDeviationService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class ScheduleDeviationController extends Controller
{
    use ResponseTrait;

    /**
     * Additional fields to include in the response.
     */
    private array $includes = [
        'schedule.user',
        'schedule.team',
    ];

    /**
     * Send only these fields in the response.
     */
    private array $onlys = [
        'id',
        'types',
        'isHandled',
        'schedule.startAt',
        'schedule.endAt',
        'schedule.user.fullname',
        'schedule.team.id',
        'schedule.team.name',
    ];

    /**
     * Display the index view.
     */
    public function index(): Response
    {
        $queries = $this->getQueries(
            pagination: 'page',
            show: 'all',
            sort: [
                'is_handled' => 'asc',
                'created_at' => 'desc',
            ]
        );
        $paginatedData = ScheduleDeviation::applyFilterSortAndPaginate(
            $queries,
            fields: $this->onlys,
        );

        return Inertia::render('Deviation/Overview/index', [
            'deviations' => ScheduleDeviationResponseDTO::transformCollection(
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
        $paginatedData = ScheduleDeviation::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            ScheduleDeviationResponseDTO::transformCollection($paginatedData->data),
            pagination: $paginatedData->pagination
        );
    }

    /**
     * Display the show view as json.
     */
    public function jsonShow(int $deviationId): JsonResponse
    {
        $data = ScheduleDeviation::selectWithRelations(mergeFields: true)
            ->findOrFail($deviationId);

        return $this->successResponse(
            ScheduleDeviationResponseDTO::transformData($data),
        );
    }

    /**
     * To handle deviation.
     * For now, only cleaning is supported.
     */
    public function handle(
        ScheduleHandleDeviationRequestDTO $request,
        ScheduleDeviation $deviation,
        ScheduleDeviationService $deviationService,
    ) {
        if ($deviation->is_handled) {
            return back()->with('error', __('deviation already handled'));
        }

        /** @var \App\Models\Schedule */
        $schedule = $deviation->schedule;
        $totalIncompleteWorkersAttendance = $schedule->scheduleEmployees()
            ->whereNotNull('start_at')
            ->whereNull('end_at')
            ->count();

        if ($totalIncompleteWorkersAttendance > 0) {
            return back()->with('error', __('deviation has incomplete workers attendance'));
        }

        $ids = [];
        if ($request->items) {
            foreach ($request->items as $item) {
                if (! $item->is_charge) {
                    $ids[] = $item->id;
                }
            }
        }

        $items = $schedule->items()
            ->whereNotIn('id', $ids)
            ->get();

        $deviationService->handle(
            $deviation,
            $items,
            $request->actual_quarters,
            $request->toArray(),
            isHttp: true,
        );

        return back()->with('success', __('deviation handled successfully'));
    }
}
