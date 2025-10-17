<?php

namespace Tests\Portal\Authentication;

use Crypt;
use Inertia\Testing\AssertableInertia as Assert;
use Session;
use Tests\TestCase;
use URL;

class NewPasswordTest extends TestCase
{
    public function testCanAccessCreatePassword(): void
    {
        $this->admin->update(['email_verified_at' => null]);
        $this->admin->refresh();

        $hash = $this->admin->generateCreatePasswordHash();
        $payload = Crypt::encryptString($this->admin->email);

        $url = URL::temporarySignedRoute(
            'password.create',
            now()->addMinutes(60),
            ['hash' => $hash, 'payload' => $payload]
        );

        $this->get($url)
            ->assertInertia(fn (Assert $page) => $page
                ->component('CreatePassword/index')
                ->where('payload', $payload)
                ->where('hash', $hash)
                ->where('isExpired', false));
    }

    public function testCanNotAccessCreatePasswordNotFoundCredential(): void
    {
        $hash = $this->admin->generateCreatePasswordHash();
        $url = URL::temporarySignedRoute(
            'password.create',
            now()->addMinutes(60),
            ['hash' => $hash, 'payload' => 'dummy']
        );

        $this->get($url)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index'));
    }

    public function testCanNotAccessCreatePasswordInvalidUser(): void
    {
        $hash = $this->admin->generateCreatePasswordHash();
        $payload = Crypt::encryptString('dummy');

        $url = URL::temporarySignedRoute(
            'password.create',
            now()->addMinutes(60),
            ['hash' => $hash, 'payload' => $payload]
        );

        $this->get($url)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index'));
    }

    public function testVerifiedEmailCanNotAccessCreatePassword(): void
    {
        $hash = $this->admin->generateCreatePasswordHash();
        $payload = Crypt::encryptString($this->admin->email);

        $url = URL::temporarySignedRoute(
            'password.create',
            now()->addMinutes(60),
            ['hash' => $hash, 'payload' => $payload]
        );

        $response = $this->get($url);

        $response->assertStatus(302);
        $response->assertRedirect('/login');

        $this->assertEquals(
            __('account already verified'),
            Session::get('success')
        );
    }

    public function testCanCreatePassword(): void
    {
        $hash = $this->admin->generateCreatePasswordHash();
        $payload = Crypt::encryptString($this->admin->email);

        $data = [
            'payload' => $payload,
            'hash' => $hash,
            'email' => $this->admin->email,
            'password' => 'Pasword123!',
            'passwordConfirmation' => 'Pasword123!',
        ];

        $response = $this->post('/create-password', $data);

        $response->assertStatus(302);
        $response->assertRedirect('/login');

        $this->assertEquals(
            __('password created successfully'),
            Session::get('success')
        );
    }

    public function testCanNotCreatePasswordIfPayloadError(): void
    {
        $hash = $this->admin->generateCreatePasswordHash();

        $data = [
            'payload' => 'dummy',
            'hash' => $hash,
            'email' => $this->admin->email,
            'password' => 'Pasword123!',
            'passwordConfirmation' => 'Pasword123!',
        ];

        $response = $this->post('/create-password', $data);

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('email not found'),
            Session::get('error')
        );
    }

    public function testCanNotCreatePasswordIfEmailNotMatch(): void
    {
        $hash = $this->admin->generateCreatePasswordHash();
        $payload = Crypt::encryptString($this->admin->email);

        $data = [
            'payload' => $payload,
            'hash' => $hash,
            'email' => 'dummy@email.com',
            'password' => 'Pasword123!',
            'passwordConfirmation' => 'Pasword123!',
        ];

        $response = $this->post('/create-password', $data);

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('email not found'),
            Session::get('error')
        );
    }

    public function testCanNotCreatePasswordIfNotMatchUserIndetifier(): void
    {
        $hash = $this->admin->generateCreatePasswordHash();
        $payload = Crypt::encryptString('dummy@email.com');

        $data = [
            'payload' => $payload,
            'hash' => $hash,
            'email' => 'dummy@email.com',
            'password' => 'Pasword123!',
            'passwordConfirmation' => 'Pasword123!',
        ];

        $response = $this->post('/create-password', $data);

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('invalid password creation request'),
            Session::get('error')
        );
    }

    public function testCanResendPassword(): void
    {
        $payload = Crypt::encryptString($this->admin->email);

        $data = [
            'payload' => $payload,
        ];

        $response = $this->post('/create-password/resend', $data);

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('password creation email sent'),
            Session::get('success')
        );
    }

    public function testCanNotResendPasswordIfNotValidPayload(): void
    {
        $data = [
            'payload' => 'dummy',
        ];

        $response = $this->post('/create-password/resend', $data);

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('account not found'),
            Session::get('error')
        );
    }

    public function testCanNotResendPasswordIfNotFoundUser(): void
    {
        $payload = Crypt::encryptString('dummy');

        $data = [
            'payload' => $payload,
        ];

        $response = $this->post('/create-password/resend', $data);

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('account not found'),
            Session::get('error')
        );
    }
}
