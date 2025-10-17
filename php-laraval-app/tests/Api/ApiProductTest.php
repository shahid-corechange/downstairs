<?php

namespace Tests\Api;

use App\Enums\Auth\TokenAbilityEnum;
use Illuminate\Http\Response;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiProductTest extends TestCase
{
    public function testAuthenticatedAdminCanGetProductsWithApi(): void
    {
        Sanctum::actingAs($this->admin, [TokenAbilityEnum::APIAccess()]);
        $response = $this->getJson('/api/v0/products');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'data' => 'array',
                    'pagination' => 'array',
                    'meta' => 'array',
                ]));
    }

    public function testAuthenticatedAdminCanGetProductsByServiceIdWithApi(): void
    {
        Sanctum::actingAs($this->admin, [TokenAbilityEnum::APIAccess()]);
        $response = $this->getJson('/api/v0/products?serviceId.equal=1');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'data' => 'array',
                    'pagination' => 'array',
                    'meta' => 'array',
                ])
                ->where('data.0.serviceId', 1)->etc());
    }

    public function testNotAuthenticatedUserCanNotGetProductsWithApi(): void
    {
        $response = $this->getJson('/api/v0/products');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'error' => 'array',
                ]));
    }
}
