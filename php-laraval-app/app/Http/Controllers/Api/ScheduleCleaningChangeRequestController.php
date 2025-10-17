<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;

class ScheduleCleaningChangeRequestController extends Controller
{
    use ResponseTrait;

    /**
     * Display a listing of the resource.
     */
    public function store()
    {
        return $this->deprecatedEndpointResponse();
    }
}
