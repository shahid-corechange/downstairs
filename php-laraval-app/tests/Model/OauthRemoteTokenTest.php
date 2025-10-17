<?php

namespace Tests\Model;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OauthRemoteTokenTest extends TestCase
{
    /** @test */
    public function oauthRemoteTokensDatabaseHasExpectedColumns(): void
    {
        $this->assertTrue(
            Schema::hasColumns('oauth_remote_tokens', [
                'id',
                'app_name',
                'token_type',
                'scope',
                'access_token',
                'refresh_token',
                'access_expires_at',
                'refresh_expires_at',
                'created_at',
                'updated_at',
            ]),
        );
    }
}
