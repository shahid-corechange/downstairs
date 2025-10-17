<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;

class ScheduleEmployeeController extends Controller
{
    use ResponseTrait;

    /**
     * Get all schedule employees.
     */
    public function index()
    {
        return $this->deprecatedEndpointResponse();
    }

    /**
     * Get spesific schedule.
     */
    public function show()
    {
        return $this->deprecatedEndpointResponse();
    }

    /**
     * To start schedule employee
     * Create tasks from product, schedule cleaning, subscription, and service
     */
    public function start()
    {
        return $this->deprecatedEndpointResponse();
    }

    /**
     * End schedule employee
     * Update shcedule cleaning
     * Create order
     * Update tasks
     */
    public function end()
    {
        return $this->deprecatedEndpointResponse();
    }

    /**
     * Cancel schedule employee.
     */
    public function cancel()
    {
        return $this->deprecatedEndpointResponse();
    }
}
