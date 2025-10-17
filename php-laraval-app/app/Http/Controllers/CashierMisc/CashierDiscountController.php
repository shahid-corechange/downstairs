<?php

namespace App\Http\Controllers\CashierMisc;

use App\DTOs\CustomerDiscount\CustomerDiscountResponseDTO;
use App\Enums\Discount\CustomerDiscountTypeEnum;
use App\Http\Controllers\User\BaseUserController;
use App\Http\Traits\ResponseTrait;
use App\Models\CustomerDiscount;
use Illuminate\Http\JsonResponse;

class CashierDiscountController extends BaseUserController
{
    use ResponseTrait;

    /**
     * Display the index view as json.
     */
    public function jsonIndex(): JsonResponse
    {
        $queries = $this->getQueries(
            defaultFilter: [
                'type_eq' => CustomerDiscountTypeEnum::Laundry(),
            ],
        );
        $paginatedData = CustomerDiscount::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            CustomerDiscountResponseDTO::transformCollection($paginatedData->data),
        );
    }
}
