<?php

namespace App\Http\Controllers\WorkHour;

use App\DTOs\WorkHour\WorkHourResponseDTO;
use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Models\WorkHour;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class WorkHourController extends Controller
{
    use ResponseTrait;

    /**
     * Additional fields to include in the response.
     */
    private array $includes = [
        'user.employee',
    ];

    /**
     * Send only these fields in the response.
     */
    private array $onlys = [
        'id',
        'user.id',
        'user.fullname',
        'user.employee.identityNumber',
        'type',
        'date',
        'startTime',
        'endTime',
        'workHours',
        'timeAdjustmentHours',
        'totalHours',
        'bookingHours',
        'hasDeviation',
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
                'date' => 'desc',
                'id' => 'asc',
            ]
        );
        $paginatedData = WorkHour::applyFilterSortAndPaginate(
            $queries,
            fields: $this->onlys,
        );

        return Inertia::render('TimeReports/Daily/index', [
            'timeReports' => WorkHourResponseDTO::transformCollection(
                $paginatedData->data,
                $this->includes,
                onlys: $this->onlys,
            ),
            'pagination' => array_keys_to_camel_case($paginatedData->pagination),
        ]);
    }

    /**
     * Display the index view as json.
     */
    public function jsonIndex(): JsonResponse
    {
        $queries = $this->getQueries();
        $paginatedData = WorkHour::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            WorkHourResponseDTO::transformCollection($paginatedData->data)
        );
    }
}
