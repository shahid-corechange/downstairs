<?php

namespace Tests\Unit;

use App\Checks\Api46ElksCheck;
use App\Checks\ApiFortnoxCustomerCheck;
use App\Checks\ApiFortnoxEmployeeCheck;
use Illuminate\Http\Response;
use Spatie\Health\Checks\Checks\DatabaseCheck;
use Spatie\Health\Checks\Checks\RedisCheck;
use Spatie\Health\Checks\Result;
use Spatie\Health\Enums\Status;
use Spatie\Health\Facades\Health;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    public function testHasAnErrorIfTheDatabaseIsNotAvailable()
    {
        Health::fake([
            DatabaseCheck::class => new Result(Status::failed()),
            ApiFortnoxCustomerCheck::class => new Result(Status::ok()),
            ApiFortnoxEmployeeCheck::class => new Result(Status::ok()),
            Api46ElksCheck::class => new Result(Status::ok()),
        ]);

        $this->getJson('/api/health')->assertStatus(Response::HTTP_SERVICE_UNAVAILABLE);
    }

    public function testHasAnErrorIfTheRedisIsNotAvailable()
    {
        Health::fake([
            RedisCheck::class => new Result(Status::failed()),
            ApiFortnoxCustomerCheck::class => new Result(Status::ok()),
            ApiFortnoxEmployeeCheck::class => new Result(Status::ok()),
            Api46ElksCheck::class => new Result(Status::ok()),
        ]);

        $this->getJson('/api/health')->assertStatus(Response::HTTP_SERVICE_UNAVAILABLE);
    }

    public function testIfTheRedisMemoryUsageIsFail()
    {
        Health::fake([
            DatabaseCheck::class => new Result(Status::failed()),
            ApiFortnoxCustomerCheck::class => new Result(Status::ok()),
            ApiFortnoxEmployeeCheck::class => new Result(Status::ok()),
            Api46ElksCheck::class => new Result(Status::ok()),
        ]);

        $this->getJson('/api/health')->assertStatus(Response::HTTP_SERVICE_UNAVAILABLE);
    }
}
