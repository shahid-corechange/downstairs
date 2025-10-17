<?php

namespace App\Http\Controllers\LaundryOrder;

use App\DTOs\LaundryOrder\LaundryOrderResponseDTO;
use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Models\LaundryOrder;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class LaundryOrderController extends Controller
{
    use ResponseTrait;

    /**
     * Additional fields to include in the response.
     */
    private array $includes = [
        'store',
        'user',
        'pickupInCleaning',
        'deliveryInCleaning',
    ];

    /**
     * Send only these fields in the response.
     */
    private array $onlys = [
        'id',
        'paymentMethod',
        'paidAt',
        'status',
        'createdAt',
        'store.name',
        'user.fullname',
        'pickupInCleaning.startAt',
        'deliveryInCleaning.startAt',
    ];

    /**
     * Display the index view.
     */
    public function index(): Response
    {
        $queries = $this->getQueries(
            sort: ['ordered_at' => 'desc'],
        );
        $paginatedData = LaundryOrder::applyFilterSortAndPaginate(
            $queries,
            fields: $this->onlys,
        );

        return Inertia::render('LaundryOrder/Overview/index', [
            'laundryOrders' => LaundryOrderResponseDTO::transformCollection(
                $paginatedData->data,
                $this->includes,
                onlys: $this->onlys
            ),
        ]);
    }

    public function jsonIndex(): JsonResponse
    {
        $queries = $this->getQueries();
        $paginatedData = LaundryOrder::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            LaundryOrderResponseDTO::transformCollection($paginatedData->data),
        );
    }

    /**
     * Display the specified resource as json.
     */
    public function jsonShow(int $laundryOrderId): JsonResponse
    {
        $data = LaundryOrder::selectWithRelations(mergeFields: true)
            ->findOrFail($laundryOrderId);

        return $this->successResponse(
            LaundryOrderResponseDTO::transformData($data),
        );
    }
}
