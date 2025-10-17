<?php

namespace App\Http\Controllers\Api;

use App\DTOs\Product\ProductResponseDTO;
use App\Http\Controllers\Controller;
use App\Http\Traits\MetaTrait;
use App\Http\Traits\QueryStringTrait;
use App\Http\Traits\ResponseTrait;
use App\Models\Product;
use Auth;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    use MetaTrait;
    use QueryStringTrait;
    use ResponseTrait;

    /**
     * List of additional fields to be included in the response.
     *
     * @var string[]
     */
    protected array $includes = [
        'service',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        // Eager load the customer discounts for the user
        Auth::user()->load(['customerDiscounts' => function ($query) {
            $query->where(function ($query) {
                $query->where(function ($query) {
                    $query->where('start_date', '<=', now())->where('end_date', '>=', now());
                })
                    ->orWhere(function ($query) {
                        $query->whereNull('start_date')->orWhereNull('end_date');
                    });
            })
                ->where(function ($query) {
                    $query->whereNull('usage_limit')
                        ->orWhere('usage_limit', '>', 0);
                })
                ->orderBy('value', 'desc');
        }]);

        $queries = $this->getQueries(sort: ['name' => 'asc']);
        $paginatedData = Product::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            ProductResponseDTO::transformCollection($paginatedData->data, $this->includes),
            pagination: $paginatedData->pagination
        );
    }
}
