<?php

namespace App\Http\Controllers\CashierSale;

use App\DTOs\StoreSale\StoreSaleResponseDTO;
use App\Http\Controllers\User\BaseUserController;
use App\Http\Traits\ResponseTrait;
use App\Models\StoreSale;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class CashierSaleHistoryController extends BaseUserController
{
    use ResponseTrait;

    /**
     * Additional fields to include in the response.
     */
    private array $includes = [
        'causer',
        'products',
    ];

    /**
     * Send only these fields in the response.
     */
    private array $onlys = [
        'id',
        'status',
        'paymentMethod',
        'totalToPay',
        'roundedTotalToPay',
        'createdAt',
        'causer.fullname',
        'products.name',
    ];

    /**
     * Display the index view.
     */
    public function index(): Response
    {
        $storeId = request()->session()->get('store_id');
        $queries = $this->getQueries(
            sort: [
                'created_at' => 'desc',
            ],
            defaultFilter: [
                'stores_id_eq' => $storeId,
            ],
            show: 'all'
        );
        $paginatedData = StoreSale::applyFilterSortAndPaginate(
            $queries,
            fields: $this->onlys,
        );

        return Inertia::render('DirectSale/History/index', [
            'storeSales' => StoreSaleResponseDTO::transformCollection(
                $paginatedData->data,
                onlys: $this->onlys,
                includes: $this->includes,
            ),
        ]);
    }

    /**
     * Display the index view as json.
     */
    public function jsonIndex(): JsonResponse
    {
        $storeId = request()->session()->get('store_id');
        $queries = $this->getQueries(
            defaultFilter: [
                'store_id_eq' => $storeId,
            ],
        );
        $paginatedData = StoreSale::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            StoreSaleResponseDTO::transformCollection($paginatedData->data),
        );
    }

    /**
     * Display the show view as json.
     */
    public function jsonShow(int $storeSaleId): JsonResponse
    {
        $data = StoreSale::selectWithRelations(mergeFields: true)
            ->findOrFail($storeSaleId);

        return $this->successResponse(
            StoreSaleResponseDTO::transformData($data),
        );
    }
}
