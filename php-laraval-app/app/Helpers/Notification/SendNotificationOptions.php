<?php

namespace App\Helpers\Notification;

class SendNotificationOptions
{
    /**
     * Create a new Send notification options instance.
     *
     * @param  AppNotificationOptions  $appOptions
     * Options for the app notification.
     * @param  ?SMSNotificationOptions  $smsOptions
     * Options for the SMS notification.
     * @param  ?EmailNotificationOptions  $emailOptions
     * Options for the email notification.
     * @param  string  $title
     * The general title of the notification.
     * This will be used as a fallback if the title for specific method is not set.
     * @param  string  $body
     * The general body of the notification.
     * This will be used as a fallback if the body for specific method is not set.
     * @param  bool  $shouldSave
     * Whether to save the notification to the database.
     * @param  bool  $shouldInferMethod
     * Whether to infer the method from the user info to use to send the notification.
     */
    public function __construct(
        public AppNotificationOptions $appOptions,
        public ?SMSNotificationOptions $smsOptions = null,
        public ?EmailNotificationOptions $emailOptions = null,
        public string $title = '',
        public string $body = '',
        public bool $shouldSave = false,
        public bool $shouldInferMethod = false,
    ) {
    }
}
