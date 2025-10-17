<?php

namespace App\Checks;

use App\Exceptions\OperationFailedException;
use App\Services\Fortnox\FortnoxEmployeeService;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class ApiFortnoxEmployeeCheck extends Check
{
    public function run(): Result
    {
        $result = Result::make();

        try {
            $service = new FortnoxEmployeeService();
            $service->retry(config('health.checks.ping.timeout'))->ping();

            return $result->ok();
        } catch (OperationFailedException $e) {
            return $result->failed("Request failed ({$e->getCode()}), {$e->getMessage()}");
        }
    }
}
