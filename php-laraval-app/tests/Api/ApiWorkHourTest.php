<?php

namespace Tests\Api;

use App\Enums\Auth\TokenAbilityEnum;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiWorkHourTest extends TestCase
{
    public function testAuthenticatedWorkerCanGetWorkHoursWithApi(): void
    {
        $worker = User::role('Worker')->first();
        Sanctum::actingAs($worker, [TokenAbilityEnum::APIAccess()]);
        $response = $this->getJson('/api/v1/work-hours');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'data' => 'array',
                    'pagination' => 'array',
                    'meta' => 'array',
                ])
                ->has('data.0', fn (AssertableJson $json) => $json
                    ->has('fortnoxAttendanceId')
                    ->has('workHours')
                    ->has('timeAdjustmentHours')
                    ->has('totalHours')
                    ->has('bookingHours')
                    ->has('unapprovedHours')
                    ->whereAllType([
                        'id' => 'integer',
                        'userId' => 'integer',
                        'date' => 'string',
                        'startTime' => 'string',
                        'endTime' => 'string',
                        'hasDeviation' => 'boolean',
                        'createdAt' => 'string',
                        'updatedAt' => 'string',
                    ])
                    ->etc()));
    }

    public function testAuthenticatedWorkerCanGetWorkHoursWithApiUsingQuery(): void
    {
        $worker = User::role('Worker')->first();
        Sanctum::actingAs($worker, [TokenAbilityEnum::APIAccess()]);
        $response = $this->getJson('/api/v1/work-hours?page=0&size=1&sort=name.desc');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'data' => 'array',
                    'pagination' => 'array',
                    'meta' => 'array',
                ]));
    }

    public function testAuthenticatedWorkerCanNotGetWorkHoursWithApiUsingQuery(): void
    {
        Sanctum::actingAs($this->worker, [TokenAbilityEnum::APIAccess()]);
        $response = $this->getJson('/api/v1/work-hours?page=0&size=1&name.id.between=1');

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'error' => 'array',
                ]));
    }

    public function testAuthenticatedUserCanNotGetWorkHoursWithApi(): void
    {
        Sanctum::actingAs($this->user, [TokenAbilityEnum::APIAccess()]);
        $response = $this->getJson('/api/v1/work-hours');

        $response->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'error' => 'array',
                ]));
    }
}
