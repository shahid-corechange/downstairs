<?php

namespace App\Http\Controllers\Api;

use App\DTOs\Address\CityResponseDTO;
use App\Http\Controllers\Controller;
use App\Http\Traits\QueryStringTrait;
use App\Http\Traits\ResponseTrait;
use App\Models\City;
use Illuminate\Http\JsonResponse;

class CityController extends Controller
{
    use QueryStringTrait;
    use ResponseTrait;

    /**
     * List of additional fields to be included in the response.
     *
     * @var string[]
     */
    protected array $includes = [
        'country',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $queries = $this->getQueries(sort: ['name' => 'asc']);
        $paginatedData = City::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            CityResponseDTO::transformCollection($paginatedData->data),
            pagination: $paginatedData->pagination
        );
    }
}
