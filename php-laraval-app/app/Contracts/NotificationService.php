<?php

namespace App\Contracts;

use App\Helpers\Notification\AndroidNotificationOptions;
use App\Helpers\Notification\IOSNotificationOptions;
use Illuminate\Http\Client\Response;

interface NotificationService
{
    /**
     * Set the notification hub.
     */
    public function hub(string $hub): self;

    /**
     * Check if the given device is registered to the notification hub.
     */
    public function isRegistered(string $platform, string $deviceToken): bool;

    /**
     * Register a device to the notification hub.
     *
     * @param  string[]  $tags
     */
    public function register(string $platform, string $deviceToken, array $tags): void;

    /**
     * Unregister a device from the notification hub.
     */
    public function unregister(string $platform, string $deviceToken): void;

    /**
     * Get all registered devices from the notification hub.
     */
    public function getRegistrations(): array;

    /**
     * Send a notification to the notification hub.
     */
    public function send(
        string $tag,
        AndroidNotificationOptions $android,
        IOSNotificationOptions $ios,
        array $payload = [],
        string $title = '',
        string $body = ''
    ): Response;

    /**
     * Broadcast notifications to the notification hub.
     */
    public function broadcast(
        AndroidNotificationOptions $android,
        IOSNotificationOptions $ios,
        array $payload = [],
        string $title = '',
        string $body = ''
    ): Response;
}
