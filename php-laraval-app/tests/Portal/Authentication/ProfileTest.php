<?php

namespace Tests\Portal\Authentication;

use App\Contracts\StorageService;
use Inertia\Testing\AssertableInertia as Assert;
use Mockery\MockInterface;
use Session;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    public function testAdminCanAccessProfile(): void
    {
        $this->actingAs($this->admin)
            ->get('/profile')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Profile/index'));
    }

    public function testCustomerCanNotAccessProfile(): void
    {
        $this->actingAs($this->user)
            ->get('/profile')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }

    public function testCanUpdateProfile(): void
    {
        $this->mock(StorageService::class, function (MockInterface $mock) {
            $mock->shouldReceive('upload')->never();
        });

        $data = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'timezone' => 'Europe/Amsterdam',
            'language' => 'en_US',
            'currency' => 'EUR',
        ];

        $response = $this->actingAs($this->admin)->patch('/profile', $data);

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('profile updated successfully'),
            Session::get('success')
        );

        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
            'first_name' => $data['firstName'],
            'last_name' => $data['lastName'],
        ]);

        $this->assertDatabaseHas('user_infos', [
            'user_id' => $this->admin->id,
            'timezone' => $data['timezone'],
            'language' => $data['language'],
            'currency' => $data['currency'],
        ]);
    }
}
