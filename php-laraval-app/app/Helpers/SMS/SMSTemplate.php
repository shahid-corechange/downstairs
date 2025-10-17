<?php

namespace App\Helpers\SMS;

class SMSTemplate
{
    /**
     * Template for sending OTP SMS to user.
     *
     * @args $otp
     *
     * @var string
     */
    const OTP_TEMPLATE = <<<'EOT'
        Your One-Time Password (OTP): %s.
        
        Use it to complete authentication within 10 minutes.
        Do not share this OTP with anyone.

        Thank you.
        EOT;

    /**
     * Template for sending notification SMS to user.
     *
     * @args $title, $body
     *
     * @var string
     */
    const NOTIFICATION_TEMPLATE = '%s; %s';
}
