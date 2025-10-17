<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;

class DeviationController extends Controller
{
    use ResponseTrait;

    /**
     * Store spesific resource.
     */
    public function store()
    {
        return $this->deprecatedEndpointResponse();
    }
}
