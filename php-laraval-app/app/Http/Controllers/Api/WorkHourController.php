<?php

namespace App\Http\Controllers\Api;

use App\DTOs\WorkHour\WorkHourResponseDTO;
use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Models\WorkHour;
use Auth;
use Illuminate\Http\JsonResponse;

class WorkHourController extends Controller
{
    use ResponseTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $queries = $this->getQueries(
            filter: ['userId_eq' => Auth::id()],
            sort: ['date' => 'desc'],
        );
        $paginatedData = WorkHour::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            WorkHourResponseDTO::transformCollection($paginatedData->data),
            pagination: $paginatedData->pagination
        );
    }
}
