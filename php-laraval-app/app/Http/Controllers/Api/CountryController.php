<?php

namespace App\Http\Controllers\Api;

use App\DTOs\Address\CountryResponseDTO;
use App\Http\Controllers\Controller;
use App\Http\Traits\QueryStringTrait;
use App\Http\Traits\ResponseTrait;
use App\Models\Country;
use Illuminate\Http\JsonResponse;

class CountryController extends Controller
{
    use QueryStringTrait;
    use ResponseTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $queries = $this->getQueries(sort: ['name' => 'asc']);
        $paginatedData = Country::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            CountryResponseDTO::transformCollection($paginatedData->data),
            pagination: $paginatedData->pagination
        );
    }
}
