<?php

namespace App\Helpers\Notification;

class SMSNotificationOptions
{
    /**
     * Create a new SMS notification options instance.
     *
     * @param  ?string  $title
     * The title of the notification.
     * @param  ?string  $body
     * The body of the notification.
     */
    public function __construct(
        public ?string $title = null,
        public ?string $body = null,
    ) {
    }
}
