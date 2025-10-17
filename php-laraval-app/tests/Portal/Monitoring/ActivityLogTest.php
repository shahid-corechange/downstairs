<?php

namespace Tests\Portal\Monitoring;

use App\DTOs\Log\ActivityLogResponseDTO;
use App\Models\Activity;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Activity::whereNull('causer_type')->update([
            'causer_id' => $this->admin->id,
            'causer_type' => User::class,
        ]);
    }

    public function testAdminCanAccessActivityLog(): void
    {
        $pageSize = config('downstairs.pageSize');
        $count = Activity::where('causer_type', User::class)
            ->count();
        $total = $count > $pageSize ? $pageSize : $count;

        $this->actingAs($this->admin)
            ->get('/log/activities')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Log/Activity/index')
                ->has('activities', $total)
                ->has('activities.0', fn (Assert $page) => $page
                    ->has('id')
                    ->has('event')
                    ->has('subjectId')
                    ->has('subjectType')
                    ->has('createdAt')
                    ->etc()
                    ->has('user', fn (Assert $page) => $page
                        ->has('id')
                        ->has('fullname')
                        ->etc()))
                ->has('pagination', fn (Assert $page) => $page
                    ->where('total', $count)
                    ->where('size', $pageSize)
                    ->where('currentPage', 1)
                    ->etc()));
    }

    public function testCustomerCanNotAccessActivityLog(): void
    {
        $this->actingAs($this->user)
            ->get('/log/activities')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }

    public function testCanFilterActivityLog(): void
    {
        $data = Activity::first();
        $pageSize = config('downstairs.pageSize');

        $this->actingAs($this->admin)
            ->get("/log/activities?id.eq={$data->id}")
            ->assertInertia(fn (Assert $page) => $page
                ->component('Log/Activity/index')
                ->has('activities', 1)
                ->has('activities.0', fn (Assert $page) => $page
                    ->where('id', $data->id)
                    ->where('event', $data->event)
                    ->etc()
                    ->has('user', fn (Assert $page) => $page
                        ->where('id', $data->causer->id)
                        ->where('fullname', $data->causer->fullname)
                        ->etc()))
                ->has('pagination', fn (Assert $page) => $page
                    ->where('total', 1)
                    ->where('size', $pageSize)
                    ->where('currentPage', 1)
                    ->etc()));
    }

    public function testAdminCanAccessActivityLogJson(): void
    {
        $response = $this->actingAs($this->admin)->get('/log/activities/json');
        $keys = array_keys(
            ActivityLogResponseDTO::fromModel(Activity::first())->toArray()
        );

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                '*' => $keys,
            ],
            'meta' => [
                'etag',
            ],
        ]);
    }
}
