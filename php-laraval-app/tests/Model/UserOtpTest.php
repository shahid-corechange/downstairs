<?php

namespace Tests\Model;

use App\Enums\Auth\OTPInfoEnum;
use App\Models\User;
use App\Models\UserOtp;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UserOtpTest extends TestCase
{
    /** @test */
    public function userOtpsDatabaseHasExpectedColumns(): void
    {
        $this->assertTrue(
            Schema::hasColumns('user_otps', [
                'id',
                'user_id',
                'otp',
                'info',
                'expire_at',
            ]),
        );
    }

    /** @test */
    public function userOtpHasUser(): void
    {
        $user = User::first();
        $userOtp = UserOtp::generate($user, OTPInfoEnum::Login());

        $this->assertInstanceOf(User::class, $userOtp->user);
        $this->assertIsString($userOtp->otp);
    }
}
