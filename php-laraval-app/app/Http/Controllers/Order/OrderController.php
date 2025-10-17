<?php

namespace App\Http\Controllers\Order;

use App\DTOs\Order\OrderResponseDTO;
use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    use ResponseTrait;

    /**
     * Additional fields to include in the response.
     */
    private array $includes = [
        'user',
        'customer.address',
    ];

    /**
     * Send only these fields in the response.
     */
    private array $onlys = [
        'id',
        'user.fullname',
        'customer.membershipType',
        'customer.address.fullAddress',
        'paidBy',
        'paidAt',
        'status',
        'orderedAt',
    ];

    /**
     * Display the index view.
     */
    public function index(): Response
    {
        $queries = $this->getQueries(
            sort: ['id' => 'desc'],
            pagination: 'page',
            show: 'all',
        );
        $paginatedData = Order::applyFilterSortAndPaginate(
            $queries,
            fields: $this->onlys,
        );

        return Inertia::render('Order/Overview/index', [
            'orders' => OrderResponseDTO::transformCollection(
                $paginatedData->data,
                $this->includes,
                onlys: $this->onlys,
            ),
            'pagination' => array_keys_to_camel_case($paginatedData->pagination),
            'extraArticleIds' => $this->getExtraArticleIds(),
        ]);
    }

    /**
     * Display the index view as json.
     */
    public function jsonIndex(): JsonResponse
    {
        $queries = $this->getQueries();
        $paginatedData = Order::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            OrderResponseDTO::transformCollection($paginatedData->data)
        );
    }

    /**
     * Display the show view as json.
     */
    public function jsonShow(int $orderId): JsonResponse
    {
        $data = Order::selectWithRelations(mergeFields: true)
            ->findOrFail($orderId);

        return $this->successResponse(
            OrderResponseDTO::transformData($data),
        );
    }

    private function getExtraArticleIds(): array
    {
        $transport = get_transport();
        $material = get_material();

        return [
            $transport->fortnox_article_id,
            $material->fortnox_article_id,
        ];
    }
}
