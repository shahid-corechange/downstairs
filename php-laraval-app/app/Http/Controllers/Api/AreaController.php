<?php

namespace App\Http\Controllers\Api;

use App\DTOs\Address\AddressResponseDTO;
use App\Http\Controllers\Controller;
use App\Http\Traits\AddressTrait;
use App\Http\Traits\QueryStringTrait;
use App\Http\Traits\ResponseTrait;
use App\Models\Address;
use Illuminate\Http\JsonResponse;

class AreaController extends Controller
{
    use AddressTrait;
    use QueryStringTrait;
    use ResponseTrait;

    /**
     * List of fields to be included in the response.
     *
     * @var string[]
     */
    protected array $onlys = [
        'postalCode',
        'area',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $queries = $this->getQueries();
        $paginatedData = Address::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            AddressResponseDTO::transformCollection($paginatedData->data, onlys: $this->onlys),
            pagination: $paginatedData->pagination
        );
    }

    /**
     * Display a listing of the resource based on postal code.
     */
    public function findByPostalCode(string $postalCode): JsonResponse
    {
        $address = Address::where('postal_code', $postalCode)->firstOrFail();

        return $this->successResponse(
            AddressResponseDTO::transformData($address, onlys: $this->onlys),
        );
    }
}
