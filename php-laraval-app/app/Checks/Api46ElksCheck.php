<?php

namespace App\Checks;

use App\Exceptions\OperationFailedException;
use App\Services\SMS\Elks46SMSService;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class Api46ElksCheck extends Check
{
    public function run(): Result
    {
        $result = Result::make();

        try {
            $smsService = new Elks46SMSService();
            $smsService->retry(config('health.checks.ping.timeout'))->ping();

            return $result->ok();
        } catch (OperationFailedException $e) {
            return $result->failed("Request failed ({$e->getCode()}), {$e->getMessage()}");
        }
    }
}
