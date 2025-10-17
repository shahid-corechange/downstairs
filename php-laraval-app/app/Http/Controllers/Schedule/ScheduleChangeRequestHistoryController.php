<?php

namespace App\Http\Controllers\Schedule;

use App\DTOs\Schedule\ScheduleChangeResponseDTO;
use App\Enums\Schedule\ScheduleChangeStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Traits\QueryStringTrait;
use App\Http\Traits\ResponseTrait;
use App\Models\ScheduleChangeRequest;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class ScheduleChangeRequestHistoryController extends Controller
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
        'causer',
    ];

    /**
     * Send only these fields in the response.
     */
    private array $onlys = [
        'id',
        'scheduleId',
        'originalStartAt',
        'startAtChanged',
        'originalEndAt',
        'endAtChanged',
        'status',
        'schedule.startAt',
        'schedule.teamId',
        'schedule.team.name',
        'schedule.status',
        'schedule.user.fullname',
        'schedule.property.address.fullAddress',
        'causerId',
        'causer.fullname',
    ];

    /**
     * Display the index view.
     */
    public function index(): Response
    {
        $queries = $this->getQueries(
            filter: [
                'status_neq' => ScheduleChangeStatusEnum::Pending(),
            ],
            sort: ['updated_at' => 'desc'],
            size: -1
        );
        $paginatedData = ScheduleChangeRequest::applyFilterSortAndPaginate($queries);

        return Inertia::render('Schedule/ChangeRequest/History/index', [
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
            'status_neq' => ScheduleChangeStatusEnum::Pending(),
        ]);

        $paginatedData = ScheduleChangeRequest::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            ScheduleChangeResponseDTO::transformCollection($paginatedData->data),
            pagination: $paginatedData->pagination
        );
    }
}
