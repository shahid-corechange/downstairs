<?php

namespace Tests\Portal\Authentication;

use Event;
use Inertia\Testing\AssertableInertia as Assert;
use Password;
use Session;
use Tests\TestCase;

class ResetPasswordTest extends TestCase
{
    public function testResetPasswordScreenCanBeRendered(): void
    {
        $token = Password::createToken($this->admin);

        $this->get("/reset-password/{$token}")
            ->assertInertia(fn (Assert $page) => $page
                ->component('ResetPassword/index')
                ->where('token', $token));
    }

    public function testCanResetPassword(): void
    {
        $token = Password::createToken($this->admin);

        $data = [
            'token' => $token,
            'email' => $this->admin->email,
            'password' => 'Password123!',
            'passwordConfirmation' => 'Password123!',
        ];

        Event::fake();

        $response = $this->post('/reset-password', $data);

        $response->assertStatus(302);
        $response->assertRedirect('/login');

        $this->assertEquals(
            __('password reset successfully'),
            Session::get('success')
        );
    }

    public function testCanNotResetPassword(): void
    {
        $token = Password::createToken($this->admin);

        $data = [
            'token' => $token,
            'email' => 'dummy@email.com',
            'password' => 'Password123!',
            'passwordConfirmation' => 'Password123!',
        ];

        Event::fake();

        $response = $this->post('/reset-password', $data);

        $response->assertStatus(302);
        $response->assertRedirect();

        $response->assertSessionHasInput([
            'email' => $data['email'],
        ]);

        $response->assertSessionHasErrors(['email']);
    }
}
