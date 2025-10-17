<?php

namespace Tests\Api;

use App\Contracts\SMSService;
use App\Enums\Auth\OTPInfoEnum;
use App\Enums\Azure\NotificationHub\PlatformTypeEnum;
use App\Helpers\SMS\SMSTemplate;
use App\Jobs\NotificationRegistrationManagerJob;
use App\Models\UserOtp;
use Bus;
use Illuminate\Http\Response;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class ApiAuthenticationTest extends TestCase
{
    public function testUserCanLoginWithApiUsingEmail(): void
    {
        $response = $this->postJson(
            '/api/v0/login',
            [
                'email' => $this->user->email,
                'password' => 'password',
                'devicePlatform' => PlatformTypeEnum::Android(),
                'deviceToken' => 'token',
            ],
            ['X-Requested-By' => 'Customer']
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'data' => 'array',
                ])
                ->has('data.accessToken')
                ->has('data.refreshToken')
                ->etc());

        Bus::assertDispatchedAfterResponse(NotificationRegistrationManagerJob::class);
    }

    public function testUserCanNotLoginWithApiUsingEmail(): void
    {
        $response = $this->postJson('/api/v0/login', [
            'email' => 'test@email.com',
            'password' => 'password',
            'devicePlatform' => PlatformTypeEnum::Android(),
            'deviceToken' => 'token',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'error' => 'array',
                ]));

        Bus::assertNotDispatchedAfterResponse(NotificationRegistrationManagerJob::class);
    }

    public function testUserCanResetPasswordWithApi(): void
    {
        $response = $this->postJson('/api/v0/forgot-password', [
            'email' => $this->user->email,
        ]);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function testUserCanNotResetPasswordWithApi(): void
    {
        $response = $this->postJson('/api/v0/forgot-password', [
            'email' => 'test@email.com',
        ]);

        $response->assertStatus(554)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'error' => 'array',
                ]));
    }

    public function testUserCanGenerateOtpWithApi(): void
    {
        $this->mock(SMSService::class, function (MockInterface $mock) {
            $mock->shouldReceive('personalize')
                ->once()
                ->with(SMSTemplate::OTP_TEMPLATE, Mockery::any())
                ->andReturnSelf();
            $mock->shouldReceive('send')
                ->once()
                ->with($this->user->cellphone);
        });

        $response = $this->postJson('/api/v0/otp/generate', [
            'cellphone' => $this->user->cellphone,
        ]);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'data' => 'array',
                ])
                ->has('data.otp')
                ->has('data.expireAt'));

        $this->forgetMock(SMSService::class);
    }

    public function testUserCanNotGenerateOtpWithDeletedUserByApi(): void
    {
        $this->mock(SMSService::class);
        $phone = $this->user->cellphone;
        $this->user->delete();

        $response = $this->postJson('/api/v0/otp/generate', [
            'cellphone' => $phone,
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'error' => 'array',
                ])
                ->where('error.message', __('cellphone is not registered')));

        $this->forgetMock(SMSService::class);
    }

    public function testUserCanNotGenerateOtpWithInvalidCellphoneByApi(): void
    {
        $response = $this->postJson('/api/v0/otp/generate', [
            'cellphone' => '4646',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'error' => 'array',
                ]));
    }

    public function testUserCanLoginWithApiUsingPhoneNumber(): void
    {
        $otp = '1234';

        UserOtp::create([
            'user_id' => $this->user->id,
            'otp' => $otp,
            'info' => OTPInfoEnum::Login(),
            'expire_at' => now()->addMinutes(10),
        ]);
        $response = $this->postJson(
            '/api/v0/otp/login',
            [
                'cellphone' => $this->user->cellphone,
                'otp' => $otp,
                'devicePlatform' => PlatformTypeEnum::Android(),
                'deviceToken' => 'token',
            ],
            ['X-Requested-By' => 'Customer']
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'data' => 'array',
                ])
                ->has('data.accessToken')
                ->has('data.refreshToken'));

        Bus::assertDispatchedAfterResponse(NotificationRegistrationManagerJob::class);
    }

    public function testDeletedUserCanNotLoginWithApiUsingPhoneNumber(): void
    {
        $otp = '1234';
        $phone = $this->user->cellphone;
        UserOtp::create([
            'user_id' => $this->user->id,
            'otp' => $otp,
            'info' => OTPInfoEnum::Login(),
            'expire_at' => now()->addMinutes(10),
        ]);
        $this->user->delete();

        $response = $this->postJson(
            '/api/v0/otp/login',
            [
                'cellphone' => $phone,
                'otp' => $otp,
                'devicePlatform' => PlatformTypeEnum::Android(),
                'deviceToken' => 'token',
            ],
            ['X-Requested-By' => 'Customer']
        );

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'error' => 'array',
                ])
                ->where('error.message', __('cellphone is not registered')));

        Bus::assertNotDispatchedAfterResponse(NotificationRegistrationManagerJob::class);
    }

    public function testUserCanNotLoginWithApiUsingPhoneNumber(): void
    {
        $otp = '1234';

        UserOtp::create([
            'user_id' => $this->user->id,
            'otp' => $otp,
            'info' => OTPInfoEnum::Login(),
            'expire_at' => now()->addMinutes(10),
        ]);
        $response = $this->postJson('/api/v0/otp/login', [
            'cellphone' => $this->user->cellphone,
            'otp' => $otp,
            'devicePlatform' => PlatformTypeEnum::Android(),
            'deviceToken' => 'token',
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'error' => 'array',
                ]));

        Bus::assertNotDispatchedAfterResponse(NotificationRegistrationManagerJob::class);
    }

    public function testUserCanNotLoginWithApiUsingIncorectOtp(): void
    {
        UserOtp::create([
            'user_id' => $this->user->id,
            'otp' => '1234',
            'info' => 'Login',
            'expire_at' => now()->addMinutes(10),
        ]);
        $response = $this->postJson('/api/v0/otp/login', [
            'cellphone' => $this->user->cellphone,
            'otp' => '0000',
            'devicePlatform' => PlatformTypeEnum::Android(),
            'deviceToken' => 'token',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'error' => 'array',
                ])
                ->where('error.message', __('otp is not correct')));

        Bus::assertNotDispatchedAfterResponse(NotificationRegistrationManagerJob::class);
    }

    public function testUserCanNotLoginWithApiUsingExpiredOtp(): void
    {
        $otp = '1234';

        UserOtp::create([
            'user_id' => $this->user->id,
            'otp' => $otp,
            'info' => 'Login',
            'expire_at' => now()->subMinutes(30),
        ]);
        $response = $this->postJson('/api/v0/otp/login', [
            'cellphone' => $this->user->cellphone,
            'otp' => $otp,
            'devicePlatform' => PlatformTypeEnum::Android(),
            'deviceToken' => 'token',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'error' => 'array',
                ])
                ->where('error.message', __('otp has been expired')));

        Bus::assertNotDispatchedAfterResponse(NotificationRegistrationManagerJob::class);
    }

    public function testUserCanNotLoginWithApiIfDoNotHaveSubscription(): void
    {
        $otp = '1234';
        $phone = $this->user->cellphone;
        UserOtp::create([
            'user_id' => $this->user->id,
            'otp' => $otp,
            'info' => OTPInfoEnum::Login(),
            'expire_at' => now()->addMinutes(10),
        ]);
        $this->user->subscriptions()->forceDelete();

        $response = $this->postJson(
            '/api/v0/otp/login',
            [
                'cellphone' => $phone,
                'otp' => $otp,
                'devicePlatform' => PlatformTypeEnum::Android(),
                'deviceToken' => 'token',
            ],
            ['X-Requested-By' => 'Customer']
        );

        $response->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'error' => 'array',
                ])
                ->where('error.message', __('you do not have any subscription')));

        Bus::assertNotDispatchedAfterResponse(NotificationRegistrationManagerJob::class);
    }

    public function testAuthenticatedUserCanLogOutWithApi(): void
    {
        $tokens = $this->user->generateTokens(true);

        /** @var \Illuminate\Contracts\Auth\Authenticatable|\Laravel\Sanctum\HasApiTokens */
        $user = Sanctum::actingAs($this->user);

        /** @var \Laravel\Sanctum\NewAccessToken */
        $token = $tokens['access_token'];
        $user->withAccessToken($token->accessToken);

        $response = $this->postJson(
            '/api/v0/logout',
            [
                'devicePlatform' => PlatformTypeEnum::Android(),
                'deviceToken' => 'token',
            ],
            ['X-Requested-By' => 'Customer']
        );

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        Bus::assertDispatchedAfterResponse(NotificationRegistrationManagerJob::class);
    }

    public function testNotAuthenticatedUserCanNotLogOutWithApi(): void
    {
        $response = $this->postJson(
            '/api/v0/logout',
            [
                'devicePlatform' => PlatformTypeEnum::Android(),
                'deviceToken' => 'token',
            ],
            ['X-Requested-By' => 'Customer']
        );

        $response->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'error' => 'array',
                ]));

        Bus::assertNotDispatchedAfterResponse(NotificationRegistrationManagerJob::class);
    }
}
