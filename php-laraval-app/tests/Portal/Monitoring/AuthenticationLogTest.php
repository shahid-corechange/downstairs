<?php

namespace Tests\Portal\Monitoring;

use App\DTOs\Log\AuthLogResponseDTO;
use App\Models\AuthenticationLog;
use App\Models\User;
use DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AuthenticationLogTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $data = [
            'authenticatable_id' => $this->admin->id,
            'authenticatable_type' => User::class,
            'ip_address' => fake()->ipv4,
            'user_agent' => fake()->userAgent,
            'login_at' => now(),
            'login_successful' => true,
        ];
        $sql = 'INSERT INTO authentication_log ('
            .implode(', ', array_keys($data)).') VALUES ('
            .implode(', ', array_fill(0, count($data), '?')).')';

        DB::insert($sql, array_values($data));
    }

    public function testAdminCanAccessAuthenticationLog(): void
    {
        $pageSize = config('downstairs.pageSize');
        $count = AuthenticationLog::count();
        $total = $count > $pageSize ? $pageSize : $count;

        $this->actingAs($this->admin)
            ->get('/log/authentications')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Log/Auth/index')
                ->has('authentications', $total)
                ->has('authentications.0', fn (Assert $page) => $page
                    ->has('id')
                    ->has('ipAddress')
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

    public function testCustomerCanNotAccessAuthenticationLog(): void
    {
        $this->actingAs($this->user)
            ->get('/log/authentications')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }

    public function testCanFilterAuthenticationLog(): void
    {
        $data = AuthenticationLog::first();
        $pageSize = config('downstairs.pageSize');

        $this->actingAs($this->admin)
            ->get("/log/authentications?id.eq={$data->id}")
            ->assertInertia(fn (Assert $page) => $page
                ->component('Log/Auth/index')
                ->has('authentications', 1)
                ->has('authentications.0', fn (Assert $page) => $page
                    ->where('id', $data->id)
                    ->where('ipAddress', $data->ip_address)
                    ->etc()
                    ->has('user', fn (Assert $page) => $page
                        ->where('id', $data->authenticatable->id)
                        ->where('fullname', $data->authenticatable->fullname)
                        ->etc()))
                ->has('pagination', fn (Assert $page) => $page
                    ->where('total', 1)
                    ->where('size', $pageSize)
                    ->where('currentPage', 1)
                    ->etc()));
    }

    public function testAdminCanAccessAuthenticationLogJson(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/log/authentications/json');
        $keys = array_keys(
            AuthLogResponseDTO::fromModel(AuthenticationLog::first())->toArray()
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
