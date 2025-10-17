<?php

namespace App\Helpers\Notification;

class AppNotificationOptions
{
    /**
     * Create a new app notification options instance.
     *
     * @param  string  $hub
     * The name of the notification hub to use.
     * @param  string  $type
     * The type of the notification, this will be used to handle the notification in the app.
     * @param  ?string  $title
     * The title of the notification.
     * @param  ?string  $body
     * The body of the notification.
     * @param  array  $payload
     * The payload of the notification.
     * @param  ?AndroidNotificationOptions  $androidOptions
     * Options for the notification on Android.
     * @param  ?IOSNotificationOptions  $iosOptions
     * Options for the notification on iOS.
     */
    public function __construct(
        public string $hub,
        public string $type,
        public ?string $title = null,
        public ?string $body = null,
        public array $payload = [],
        public ?AndroidNotificationOptions $androidOptions = null,
        public ?IOSNotificationOptions $iosOptions = null,
    ) {
    }
}
