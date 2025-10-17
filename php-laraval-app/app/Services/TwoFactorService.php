<?php

namespace App\Services;

use App\Contracts\SMSService;
use App\Enums\User\User2FAEnum;
use App\Helpers\SMS\SMSTemplate;
use App\Models\User;
use App\Notifications\UserOtpNotification;

class TwoFactorService
{
    public function __construct(
        private SMSService $smsService
    ) {
    }

    /**
     * Send OTP to SMS or Email.
     */
    public function sendOtp(User $user, string $otp)
    {
        scoped_localize($user->info->language, function () use ($user, $otp) {
            if ($user->info->two_factor_auth === User2FAEnum::SMS()) {
                $this->smsService->personalize(SMSTemplate::OTP_TEMPLATE, $otp)
                    ->send($user->cellphone);
            } elseif ($user->info->two_factor_auth === User2FAEnum::Email()) {
                $user->notify(new UserOtpNotification($otp));
            }
        });
    }

    /**
     * Get the recipient for the OTP.
     */
    public function getRecipient(User $user, string $inputType, string $inputValue): string
    {
        if ($inputType === 'email' && $user->info->two_factor_auth === User2FAEnum::SMS()) {
            // Obscure the phone number if user 2FA is SMS but user input the email
            return obscure_phone_number($user->cellphone);
        } elseif ($inputType === 'cellphone' && $user->info->two_factor_auth === User2FAEnum::Email()) {
            // Obscure the email if user 2FA is Email but user input the phone number
            return obscure_email($user->email);
        }

        // Return the input value if the 2FA is the same as the input type
        return $inputValue;
    }
}
