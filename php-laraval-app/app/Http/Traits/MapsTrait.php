<?php

namespace App\Http\Traits;

use App\Exceptions\ErrorResponseException;
use Illuminate\Support\Facades\Http;

trait MapsTrait
{
    public static function getGeoapifyGeocode(string $address)
    {
        $apiKey = config('services.geoapify.api_key');
        $url = 'https://api.geoapify.com/v1/geocode/search';
        $response = Http::retry(3, 1000)
            ->timeout(30)
            ->get($url, [
                'text' => $address,
                'format' => 'json',
                'apiKey' => $apiKey,
            ]);

        if ($response->failed()) {
            throw new ErrorResponseException($response['message'], $response->status());
        }

        return $response;
    }

    /*
    * Get Distance in meters with Haversine formula
    */
    public function getDistance(
        float $latitudeFrom,
        float $longitudeFrom,
        float $latitudeTo,
        float $longitudeTo
    ): float {
        // Earth radius in meters
        $earthRadius = 6371000;

        // Convert degrees to radians
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        // Calculate longitude and latitude differences
        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        // Calculate angle using haversine formula
        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        // Calculate distance
        $distance = $angle * $earthRadius;

        return $distance;
    }
}
