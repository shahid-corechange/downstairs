<?php

namespace App\Http\Controllers\KeyPlace;

use App\DTOs\KeyPlace\KeyPlaceResponseDTO;
use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Models\KeyPlace;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class KeyPlaceController extends Controller
{
    use ResponseTrait;

    /**
     * Additional fields to include in the response.
     */
    private array $includes = [
        'property.address',
        'property.users',
    ];

    /**
     * Send only these fields in the response.
     */
    private array $onlys = [
        'id',
        'propertyId',
        'property.address.fullAddress',
        'property.membershipType',
        'property.users.id',
        'property.users.fullname',
    ];

    /**
     * Display the index view.
     */
    public function index(): Response
    {
        $queries = $this->getQueries(
            pagination: 'page',
            show: 'all'
        );
        $paginatedData = KeyPlace::applyFilterSortAndPaginate(
            $queries,
            fields: $this->onlys,
        );

        return Inertia::render('KeyPlace/Overview/index', [
            'keyPlaces' => KeyPlaceResponseDTO::transformCollection(
                $paginatedData->data,
                $this->includes,
                onlys: $this->onlys,
            ),
            'pagination' => array_keys_to_camel_case($paginatedData->pagination),
        ]);
    }

    /**
     * Display the index view as json.
     */
    public function jsonIndex(): JsonResponse
    {
        $queries = $this->getQueries();
        $paginatedData = KeyPlace::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            KeyPlaceResponseDTO::transformCollection($paginatedData->data)
        );
    }
}
