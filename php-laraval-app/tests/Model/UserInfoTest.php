<?php

namespace Tests\Model;

use App\Models\User;
use App\Models\UserInfo;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UserInfoTest extends TestCase
{
    /** @test */
    public function userInfosDatabaseHasExpectedColumns(): void
    {
        $this->assertTrue(
            Schema::hasColumns('user_infos', [
                'id',
                'avatar',
                'language',
                'timezone',
                'currency',
                'marketing',
                'created_at',
                'updated_at',
                'deleted_at',
            ]),
        );
    }

    /** @test */
    public function userInfoHasUser(): void
    {
        $userInfo = UserInfo::first();

        $this->assertInstanceOf(User::class, $userInfo->user);
    }
}
