<?php

namespace Tests\Api;

use App\Enums\Auth\TokenAbilityEnum;
use Illuminate\Http\Response;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiNotificationTest extends TestCase
{
    /**
     * Test unauthenticated user can not get notifications with api.
     */
    public function testUnauthenticatedUserCanNotGetNotificationsWithApi(): void
    {
        $response = $this->getJson('/api/v0/notifications');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'error' => 'array',
                ]));
    }

    /**
     * Test authenticated user can get notifications with api.
     */
    public function testAuthenticatedUserCanGetNotificationsWithApi(): void
    {
        Sanctum::actingAs($this->user, [TokenAbilityEnum::APIAccess()]);
        $response = $this->getJson('/api/v0/notifications');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'data' => 'array',
                    'pagination' => 'array',
                    'meta' => 'array',
                ]));
    }

    /**
     * Test worker can get notifications with api.
     */
    public function testWorkerCanGetNotificationsWithApi(): void
    {
        Sanctum::actingAs($this->worker, [TokenAbilityEnum::APIAccess()]);
        $response = $this->getJson('/api/v0/notifications');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'data' => 'array',
                    'pagination' => 'array',
                    'meta' => 'array',
                ]));
    }
}
