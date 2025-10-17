<?php

namespace App\Http\Controllers\Api;

use App\DTOs\Geocode\GeocodeRequestDTO;
use App\DTOs\Property\GeocodeResponseDTO;
use App\Http\Controllers\Controller;
use App\Http\Traits\MapsTrait;
use App\Http\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;

class GeocodeController extends Controller
{
    use MapsTrait;
    use ResponseTrait;

    /**
     * Get Geocode.
     */
    public function show(GeocodeRequestDTO $request): JsonResponse
    {
        $response = MapsTrait::getGeoapifyGeocode($request->address);
        $results = $response->json()['results'];
        if ($results && count($results) > 0) {
            return $this->successResponse(
                GeocodeResponseDTO::from([
                    'latitude' => $results[0]['lat'],
                    'longitude' => $results[0]['lon'],
                    'partialMatch' => $results[0]['rank']['match_type'] !== 'full_match',
                ])
            );
        }

        return $this->successResponse(GeocodeResponseDTO::empty());
    }
}
