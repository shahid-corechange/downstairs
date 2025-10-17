<?php

namespace App\Models;

use Cache;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    /**
     * Find the token instance matching the given token.
     *
     * @param  string  $token
     * @return static|null
     */
    public static function findToken($token)
    {
        [$id, $token] = ! str_contains($token, '|')
            ? [null, $token]
            : explode('|', $token, 2);
        $hashedToken = hash('sha256', $token);

        $cachedToken = Cache::remember(
            "personal_access_token_$hashedToken",
            config('sanctum.access_token_expiration') * 60,
            function () use ($id, $hashedToken) {
                $query = static::with('tokenable.info', 'tokenable.permissions', 'tokenable.roles.permissions')
                    ->where('token', $hashedToken);

                if ($id) {
                    $query->where('id', $id);
                }

                return $query->first();
            }
        );

        return $cachedToken;
    }
}
