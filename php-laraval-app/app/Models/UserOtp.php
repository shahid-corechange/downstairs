<?php

namespace App\Models;

use App\Enums\GlobalSetting\GlobalSettingEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserOtp extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'otp', 'info', 'expire_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'expire_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'is_expired',
    ];

    /**
     * Check if OTP is expired.
     */
    public function getIsExpiredAttribute(): bool
    {
        return now()->isAfter($this->expire_at);
    }

    /**
     * Generate OTP for user by given cellphone number.
     */
    public static function generate(User $user, string $info): self
    {
        /* User Does not Have Any Existing OTP */
        $userOTP = self::where('user_id', $user->id)
            ->where('info', $info)
            ->latest()
            ->first();

        if ($userOTP && ! $userOTP->is_expired) {
            return $userOTP;
        }

        return self::create([
            'user_id' => $user->id,
            'otp' => self::getOtp($user->cellphone),
            'info' => $info,
            'expire_at' => now()->addMinutes(10),
        ]);
    }

    private static function getOtp(string $cellphone): string
    {
        if (config('app.env') === 'staging'
            && $cellphone === config('downstairs.test.cellphone')) {
            return config('downstairs.test.otp');
        }

        $length = get_setting(GlobalSettingEnum::OtpLength(), 4);
        $randomNumber = mt_rand(0, pow(10, $length) - 1);

        return str_pad($randomNumber, $length, '0', STR_PAD_LEFT);
    }

    /**
     * User OTP relation to user model.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
}
