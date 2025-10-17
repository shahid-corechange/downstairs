<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class OauthRemoteToken extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'app_name',
        'token_type',
        'scope',
        'access_token',
        'refresh_token',
        'access_expires_at',
        'refresh_expires_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'access_expires_at' => 'datetime',
        'refresh_expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Check if the access token is almost or already expired and
     * need to reauthenticate.
     */
    public function needReauthenticate(): bool
    {
        return $this->access_expires_at->subMinutes(2)->isPast();
    }

    /**
     * Check if the refresh token is expired.
     */
    public function isExpired(): bool
    {
        return $this->refresh_expires_at->isPast();
    }
}
