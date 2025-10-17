<?php

namespace Tests\Portal\Authentication;

use Inertia\Testing\AssertableInertia as Assert;
use Session;
use Tests\TestCase;

class ForgetPasswordTest extends TestCase
{
    public function testForgetPasswordScreenCanBeRendered(): void
    {
        $this->get('/forgot-password')
            ->assertInertia(fn (Assert $page) => $page
                ->component('ForgetPassword/index'));
    }

    public function testCanForgetPassword(): void
    {
        $data = [
            'email' => $this->admin->email,
        ];

        $response = $this->post('/forgot-password', $data);

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertNotEmpty(Session::get('success'));
    }

    public function testCanNotForgetPassword(): void
    {
        $data = [
            'email' => 'dummy@email.com',
        ];

        $response = $this->post('/forgot-password', $data);

        $response->assertStatus(302);
        $response->assertRedirect();

        $response->assertSessionHasInput([
            'email' => $data['email'],
        ]);

        $response->assertSessionHasErrors(['email']);
    }
}
