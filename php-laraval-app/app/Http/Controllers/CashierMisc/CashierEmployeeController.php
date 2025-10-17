<?php

namespace App\Http\Controllers\CashierMisc;

use App\DTOs\Employee\EmployeeResponseDTO;
use App\Http\Controllers\User\BaseUserController;
use App\Http\Traits\ResponseTrait;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;

class CashierEmployeeController extends BaseUserController
{
    use ResponseTrait;

    /**
     * Display the index view as json.
     */
    public function jsonIndex(): JsonResponse
    {
        $storeId = request()->session()->get('store_id');
        $queries = $this->getQueries(
            defaultFilter: [
                'store_id_eq' => $storeId,
            ]
        );

        $paginatedData = Employee::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            EmployeeResponseDTO::transformCollection($paginatedData->data),
        );
    }
}
