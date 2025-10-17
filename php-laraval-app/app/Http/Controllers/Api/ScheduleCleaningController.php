<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;

class ScheduleCleaningController extends Controller
{
    use ResponseTrait;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->deprecatedEndpointResponse();
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        return $this->deprecatedEndpointResponse();
    }

    /**
     * Cancel the specified schedule.
     */
    public function cancel()
    {
        return $this->deprecatedEndpointResponse();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update()
    {
        return $this->deprecatedEndpointResponse();
    }
}
