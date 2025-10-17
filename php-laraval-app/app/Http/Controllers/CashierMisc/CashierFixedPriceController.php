<?php

namespace App\Http\Controllers\CashierMisc;

use App\DTOs\FixedPrice\FixedPriceResponseDTO;
use App\Enums\FixedPrice\FixedPriceTypeEnum;
use App\Http\Controllers\User\BaseUserController;
use App\Http\Traits\ResponseTrait;
use App\Models\FixedPrice;
use Illuminate\Http\JsonResponse;

class CashierFixedPriceController extends BaseUserController
{
    use ResponseTrait;

    /**
     * Display the index view as json.
     */
    public function jsonIndex(): JsonResponse
    {
        $laundryTypes = [
            FixedPriceTypeEnum::Laundry(),
            FixedPriceTypeEnum::CleaningAndLaundry(),
        ];
        $queries = $this->getQueries(
            defaultFilter: [
                'type_in' => implode(',', $laundryTypes),
            ],
        );
        $paginatedData = FixedPrice::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            FixedPriceResponseDTO::transformCollection($paginatedData->data),
        );
    }

    public function findFixedPriceByUser(
        int $userId,
    ): JsonResponse {
        $fixedPrice = FixedPrice::selectWithRelations(mergeFields: true)
            ->where('user_id', $userId)
            ->whereIn('type', [
                FixedPriceTypeEnum::Laundry(),
                FixedPriceTypeEnum::CleaningAndLaundry(),
            ])
            ->firstOrFail();

        return $this->successResponse(
            FixedPriceResponseDTO::transformData($fixedPrice),
        );
    }
}
