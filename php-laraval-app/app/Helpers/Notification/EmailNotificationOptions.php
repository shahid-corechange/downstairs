<?php

namespace App\Helpers\Notification;

class EmailNotificationOptions
{
    /**
     * Create a new Email notification options instance.
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
