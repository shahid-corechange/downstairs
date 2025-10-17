<?php

namespace Tests\Api;

use App\Enums\Auth\TokenAbilityEnum;
use Illuminate\Http\Response;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiUserTest extends TestCase
{
    public function testAuthenticatedUserCanGetUserInfoWithApi(): void
    {
        Sanctum::actingAs($this->user, [TokenAbilityEnum::APIAccess()]);
        $response = $this->getJson('/api/v0/users/info');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json->has('data.email')
                ->has('data.cellphone')
                ->etc());
    }

    public function testNotAuthenticatedUserCanNotGetUserInfoWithApi(): void
    {
        $response = $this->getJson('/api/v0/users/info');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'error' => 'array',
                ]));
    }

    public function testAuthenticatedUserCanUpdateUserInfoWithApi(): void
    {
        Sanctum::actingAs($this->user, [TokenAbilityEnum::APIAccess()]);
        $firstName = 'First Name';
        $lastName = 'Last Name';
        $response = $this->patchJson('/api/v0/users/info', [
            'firstName' => $firstName,
            'lastName' => $lastName,
        ]);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('data.firstName', $firstName)
                ->where('data.lastName', $lastName)
                ->etc());
    }

    public function testNotAuthenticatedUserCanNotUpdateUserInfoWithApi(): void
    {
        $response = $this->patchJson('/api/v0/users/info', []);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'error' => 'array',
                ]));
    }

    /**
     * Test unauthenticated user can not get credit information with api.
     */
    public function testUnauthenticatedUserCanNotGetCreditWithApi(): void
    {
        $response = $this->getJson('/api/v0/users/credits');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'error' => 'array',
                ]));
    }

    /**
     * Test authenticated user can get credit information with api.
     */
    public function testAuthenticatedUserCanGetCreditWithApi(): void
    {
        Sanctum::actingAs($this->user, [TokenAbilityEnum::APIAccess()]);
        $response = $this->getJson('/api/v0/users/credits');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'data' => 'array',
                    'meta' => 'array',
                ]));
    }
}
