<?php

namespace App\Http\Controllers\CashierOrder;

use App\DTOs\LaundryPreference\LaundryPreferenceResponseDTO;
use App\Http\Controllers\User\BaseUserController;
use App\Http\Traits\ResponseTrait;
use App\Models\LaundryPreference;
use Illuminate\Http\JsonResponse;

class LaundryOrderPreferenceController extends BaseUserController
{
    use ResponseTrait;

    /**
     * Display the index view as json.
     */
    public function jsonIndex(): JsonResponse
    {
        $queries = $this->getQueries();
        $paginatedData = LaundryPreference::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            LaundryPreferenceResponseDTO::transformCollection($paginatedData->data),
        );
    }
}
