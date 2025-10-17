<?php

namespace App\Helpers\Response;

use Illuminate\Testing\TestResponse;

class InertiaResponse
{
    /**
     * Get data from intertia response for debugging
     */
    public static function getTestResponse(TestResponse $response, string $key = null): array
    {
        $data = $response->getOriginalContent()->getData();

        return $key ? $data['page']['props'][$key] : $data['page']['props'];
    }
}
