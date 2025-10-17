<?php

namespace App\Http\Controllers\CashierMisc;

use App\DTOs\Schedule\ScheduleResponseDTO;
use App\Http\Controllers\User\BaseUserController;
use App\Http\Traits\ResponseTrait;
use App\Models\Schedule;
use Illuminate\Http\JsonResponse;

class CashierScheduleController extends BaseUserController
{
    use ResponseTrait;

    /**
     * Display the index view as json.
     */
    public function jsonIndex(): JsonResponse
    {
        $queries = $this->getQueries();
        $paginatedData = Schedule::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            ScheduleResponseDTO::transformCollection($paginatedData->data),
            pagination: $paginatedData->pagination
        );
    }
}
