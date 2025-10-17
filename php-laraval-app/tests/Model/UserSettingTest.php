<?php

namespace Tests\Model;

use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UserSettingTest extends TestCase
{
    /** @test */
    public function userSettingsDatabaseHasExpectedColumns(): void
    {
        $this->assertTrue(
            Schema::hasColumns('user_settings', [
                'id',
                'user_id',
                'key',
                'value',
                'type',
            ]),
        );
    }

    /** @test */
    public function userSettingHasUser(): void
    {
        $userSetting = UserSetting::first();

        $this->assertInstanceOf(User::class, $userSetting->user);
    }
}
