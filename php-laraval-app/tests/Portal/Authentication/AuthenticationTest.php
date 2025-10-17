<?php

namespace Tests\Portal\Authentication;

use App\Providers\RouteServiceProvider;
use Inertia\Testing\AssertableInertia as Assert;
use Session;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    public function testLoginScreenCanBeRendered(): void
    {
        $this->get('/login')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Login/index'));
    }

    public function testAdminCanLoginUsingEmail(): void
    {
        $response = $this->post('/login', [
            'user' => $this->admin->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();

        $response->assertRedirect(RouteServiceProvider::HOME);
    }

    public function testAdminCanLoginUsingCellphone(): void
    {
        $response = $this->post('/login', [
            'user' => $this->admin->cellphone,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();

        $response->assertRedirect(RouteServiceProvider::HOME);
    }

    public function testCustomerCanNotLoginUsingEmail(): void
    {
        $response = $this->post('/login', [
            'user' => $this->user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();

        $response->assertRedirect();
    }

    public function testCustomerCanNotLoginUsingCellphone(): void
    {
        $response = $this->post('/login', [
            'user' => $this->user->cellphone,
            'password' => 'password',
        ]);

        $this->assertGuest();

        $response->assertRedirect();
    }

    public function testAdminCanNotLoginWithInvalidPassword(): void
    {
        $this->post('/login', [
            'user' => $this->user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();

        $this->assertCredentials([
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $this->assertEquals(
            __('the provided credentials do not match our records'),
            Session::get('errors')->first('user')
        );
    }

    public function testAdminCanLogout(): void
    {
        $this->actingAs($this->admin)
            ->post('/logout')
            ->assertStatus(302)
            ->assertRedirect();

        $this->assertGuest();
    }
}
