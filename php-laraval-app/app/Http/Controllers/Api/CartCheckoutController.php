<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;

class CartCheckoutController extends Controller
{
    use ResponseTrait;

    /**
     * Checkout the user cart.
     */
    public function store()
    {
        return $this->deprecatedEndpointResponse();
    }
}
