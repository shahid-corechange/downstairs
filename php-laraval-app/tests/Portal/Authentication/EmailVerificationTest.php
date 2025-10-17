<?php

namespace Tests\Portal\Authentication;

use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;
use Session;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    public function testEmailVerificationScreenIfHasVerifiedEmail(): void
    {
        $response = $this->actingAs($this->admin)->get('/verify-email');

        $response->assertStatus(302);
        $response->assertRedirect(RouteServiceProvider::HOME);
    }

    public function testEmailVerificationScreenIfHasNotVerifiedEmail(): void
    {
        $this->admin->update(['email_verified_at' => null]);
        $this->admin->refresh();

        $this->actingAs($this->admin)
            ->get('/verify-email')
            ->assertInertia(fn (Assert $page) => $page
                ->component('VerifyEmail/index'));
    }

    public function testEmailCanBeVerified(): void
    {
        $this->admin->update(['email_verified_at' => null]);
        $this->admin->refresh();

        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $this->admin->id, 'hash' => sha1($this->admin->email)]
        );

        $response = $this->actingAs($this->admin)->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($this->admin->fresh()->hasVerifiedEmail());
        $response->assertRedirect(RouteServiceProvider::HOME);
    }

    public function testEmailIsNotVerifiedWithInvalidHash(): void
    {
        $this->admin->update(['email_verified_at' => null]);
        $this->admin->refresh();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $this->admin->id, 'hash' => sha1('wrong-email')]
        );

        $this->actingAs($this->admin)->get($verificationUrl);

        $this->assertFalse($this->admin->fresh()->hasVerifiedEmail());
    }

    public function testSendEmailVerificationNotificationToVerifiedEmail(): void
    {
        $this->admin->update(['email_verified_at' => null]);
        $this->admin->refresh();

        $response = $this->actingAs($this->admin)
            ->post('/email/verification-notification');

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('verification link sent'),
            Session::get('success')
        );
    }

    public function testSendEmailVerificationNotificationToNotVerifiedEmail(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/email/verification-notification');

        $response->assertStatus(302);
        $response->assertRedirect(RouteServiceProvider::HOME);

        $this->assertNotNull($this->admin->refresh()->email_verified_at);
    }
}
