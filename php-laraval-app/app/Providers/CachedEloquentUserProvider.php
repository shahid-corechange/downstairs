<?php

namespace App\Providers;

use Cache;
use Illuminate\Auth\EloquentUserProvider;

class CachedEloquentUserProvider extends EloquentUserProvider
{
    /**
     * Retrieve a user by their unique identifier and cache it.
     *
     * @param  mixed  $identifier
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        $model = $this->createModel();

        return Cache::remember(
            "user_$identifier",
            60 * config('session.lifetime'),
            function () use ($model, $identifier) {
                return $this->newModelQuery($model)
                    ->with('info', 'permissions', 'roles.permissions', 'employee')
                    ->where($model->getAuthIdentifierName(), $identifier)
                    ->first();
            }
        );
    }
}
