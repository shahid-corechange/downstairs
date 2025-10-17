<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

trait ResponseTrait
{
    /**
     * Get API version based on the request URL.
     */
    private function getApiVersion(): string
    {
        $uri = request()->getRequestUri();

        // Get the API version from the URI.
        // Example: /api/v1/..., get the v1.
        $apiVersion = explode('/', $uri)[2];

        return config('downstairs.apiVersions.'.$apiVersion);
    }

    /**
     * Create a new success response.
     *
     * @param  string[]  $headers
     */
    public function successResponse(
        mixed $data = [],
        int $status = Response::HTTP_OK,
        array $headers = [],
        array $meta = [],
        array $pagination = [],
        string $message = null,
    ): JsonResponse|Response {
        if ($status == Response::HTTP_NO_CONTENT) {
            return response()->noContent();
        }

        $response = array_merge(
            request()->is('api/*') ? ['apiVersion' => $this->getApiVersion()] : [],
            $message ? ['message' => $message] : [],
            ['data' => $data],
            $meta ? ['meta' => array_keys_to_camel_case($meta)] : [],
            $pagination ? ['pagination' => array_keys_to_camel_case($pagination)] : []
        );

        return response()->json($response, $status, $headers);
    }

    /**
     * Create a new error response.
     */
    public function errorResponse(
        string $message,
        int $status = Response::HTTP_BAD_REQUEST,
        array $headers = [],
        array $errors = [],
    ): JsonResponse {
        $response = array_merge(
            ! request()->is('api/health') && request()->is('api/*') ? ['apiVersion' => $this->getApiVersion()] : [],
            ['error' => [
                'code' => $status,
                'message' => $message,
                'errors' => $errors,
            ]]
        );

        return response()->json($response, $status, $headers);
    }

    public function deprecatedEndpointResponse()
    {
        return $this->errorResponse(
            message: __('deprecated'),
            status: Response::HTTP_UPGRADE_REQUIRED,
        );
    }
}
