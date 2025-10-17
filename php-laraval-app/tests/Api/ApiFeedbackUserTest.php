<?php

namespace Tests\Api;

use App\Enums\Auth\TokenAbilityEnum;
use App\Models\Feedback;
use Illuminate\Http\Response;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiFeedbackUserTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Feedback::factory(10)->create();
    }

    public function testAuthenticatedUserCanCreateFeedbackByApi(): void
    {
        Sanctum::actingAs($this->user, [TokenAbilityEnum::APIAccess()]);
        $option = fake()->title;
        $description = fake()->paragraph;

        $response = $this->postJson('/api/v0/feedbacks/user', [
            'option' => $option,
            'description' => $description,
        ]);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('data.option', $option)
                ->where('data.description', $description)
                ->etc());
    }

    public function testNotAuthenticatedUserCanNotCreateFeedbackByApi(): void
    {
        $response = $this->postJson('/api/v0/feedbacks/user', []);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'error' => 'array',
                ]));
    }
}
