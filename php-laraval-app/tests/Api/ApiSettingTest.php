<?php

namespace Tests\Api;

use App\Enums\Auth\TokenAbilityEnum;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiSettingTest extends TestCase
{
    public function testAuthenticatedUserCanGetTheirOwnSettingByApi(): void
    {
        Sanctum::actingAs($this->user, [TokenAbilityEnum::APIAccess()]);
        $response = $this->getJson('/api/v0/settings');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'data' => 'array',
                    'meta' => 'array',
                ]));
    }

    public function testNotAuthenticatedUserCanNotGetSettingByApi(): void
    {
        $response = $this->getJson('/api/v0/settings');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'error' => 'array',
                ]));
    }

    public function testAuthenticatedUserCanUpdateExistingSettingByApi(): void
    {
        Sanctum::actingAs($this->user, [TokenAbilityEnum::APIAccess()]);

        $settingsResponse = $this->get('/api/v0/settings');
        $settingsResponseArray = json_decode($settingsResponse->getContent(), true);
        $response = $this->patchJson('/api/v0/settings', $settingsResponseArray['data']);
        $keys = array_keys($settingsResponseArray['data']);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'data' => 'array',
                ])
                ->where('data', fn (Collection $data) => $data->keys()->toArray() === $keys));
    }

    public function testNotAuthenticatedUserCanNotUpdateSettingByApi(): void
    {
        $response = $this->patchJson('/api/v0/settings', []);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'error' => 'array',
                ]));
    }

    public function testAuthenticatedUserCanAddNewSettingByApi(): void
    {
        Sanctum::actingAs($this->user, [TokenAbilityEnum::APIAccess()]);

        $settingsResponse = $this->get('/api/v0/settings');
        $settingsResponseArray = json_decode($settingsResponse->getContent(), true);
        $newData = ['testNotification' => 'true'];
        $response = $this->patchJson('/api/v0/settings', $newData);
        $keys = array_keys([...$settingsResponseArray['data'], ...$newData]);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'data' => 'array',
                ])
                ->where('data', fn (Collection $data) => $data->keys()->toArray() === $keys));
    }
}
