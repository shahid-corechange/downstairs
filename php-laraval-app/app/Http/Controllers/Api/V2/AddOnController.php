<?php

namespace App\Http\Controllers\Api\V2;

use App\DTOs\Addon\AddonResponseDTO;
use App\Http\Controllers\Controller;
use App\Http\Traits\MetaTrait;
use App\Http\Traits\QueryStringTrait;
use App\Http\Traits\ResponseTrait;
use App\Models\Addon;
use Auth;
use Illuminate\Http\JsonResponse;

class AddOnController extends Controller
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
        'products',
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
        $paginatedData = Addon::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            AddonResponseDTO::transformCollection($paginatedData->data, $this->includes),
            pagination: $paginatedData->pagination
        );
    }
}
