<?php

namespace App\Http\Controllers\Schedule;

use App\DTOs\ScheduleItem\ScheduleItemResponseDTO;
use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Models\ScheduleItem;
use Illuminate\Http\JsonResponse;

class ScheduleItemController extends Controller
{
    use ResponseTrait;

    /**
     * Display the index view as json.
     */
    public function jsonIndex(): JsonResponse
    {
        $queries = $this->getQueries();
        $paginatedData = ScheduleItem::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            ScheduleItemResponseDTO::transformCollection($paginatedData->data),
            pagination: $paginatedData->pagination
        );
    }
}
