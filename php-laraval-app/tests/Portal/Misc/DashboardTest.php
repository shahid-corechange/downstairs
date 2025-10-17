<?php

namespace Tests\Portal\Misc;

use Illuminate\Http\Response;
use Illuminate\Testing\Fluent\AssertableJson;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    public function testUserCannotAccessDashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertStatus(302);

        $response->assertRedirect('/login');
    }

    public function testAdminCanAccessDashboard(): void
    {
        $this->actingAs($this->admin)
            ->get('/dashboard')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard/index')
                ->whereType('unsyncData', 'integer')
                ->whereType('totalCredit', 'integer')
                ->whereType('planedToStartTomorrow', 'array')
                ->whereType('planedToStartNextWeek', 'array')
                ->whereType('alreadyPassed', 'array')
                ->has('servicesStatus'));
    }

    public function testWorkerCanAccessDashboard(): void
    {
        $this->actingAs($this->worker)
            ->get('/dashboard')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard/index')
                ->whereType('unsyncData', 'integer')
                ->whereType('totalCredit', 'integer')
                ->whereType('planedToStartTomorrow', 'array')
                ->whereType('planedToStartNextWeek', 'array')
                ->whereType('alreadyPassed', 'array')
                ->has('servicesStatus'));
    }

    public function testCustomerCanNotAccessDashboard(): void
    {
        $this->actingAs($this->user)
            ->get('/dashboard')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }

    public function testAdminCanAccessWidgetAddOnsStatistic(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/dashboard/widget/addons/statistic');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'data' => 'array',
                    'meta' => 'array',
                ])
                ->etc());

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'productId',
                    'credit',
                    'currency',
                    'total',
                    'product' => [
                        'name',
                    ],
                ],
            ],
            'meta' => [
                'etag',
            ],
        ]);
    }
}
