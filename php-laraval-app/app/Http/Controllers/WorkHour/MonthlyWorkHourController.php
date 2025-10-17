<?php

namespace App\Http\Controllers\WorkHour;

use App\DTOs\WorkHour\MonthlyWorkHourResponseDTO;
use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Models\MonthlyWorkHour;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class MonthlyWorkHourController extends Controller
{
    use ResponseTrait;

    private array $includes = [
        'employee',
    ];

    /**
     * Send only these fields in the response.
     */
    private array $onlys = [
        'userId',
        'fortnoxId',
        'employeeId',
        'fullname',
        'month',
        'year',
        'totalWorkHours',
        'adjustmentHours',
        'totalHours',
        'bookingHours',
        'hasDeviation',
        'employee.identityNumber',
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
                'year' => 'desc',
                'month' => 'desc',
                'user_id' => 'asc',
            ],
        );
        $paginatedData = MonthlyWorkHour::applyFilterSortAndPaginate($queries);

        return Inertia::render('TimeReports/Overview/index', [
            'monthlyTimeReports' => MonthlyWorkHourResponseDTO::transformCollection(
                $paginatedData->data,
                includes: $this->includes,
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
        $queries = $this->getQueries(
            sort: [
                'month' => 'desc',
                'year' => 'desc',
            ]
        );
        $paginatedData = MonthlyWorkHour::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            MonthlyWorkHourResponseDTO::transformCollection($paginatedData->data)
        );
    }
}
