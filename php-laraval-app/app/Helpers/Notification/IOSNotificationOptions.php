<?php

namespace App\Helpers\Notification;

use App\Enums\Notification\NotificationRecipientProviderEnum;

class IOSNotificationOptions
{
    /**
     * Set the notification recipient provider. This will be used to determine the format of
     * the notification message.
     */
    public string $recipientProvider;

    /**
     * The number to display in a badge on your app’s icon. Specify 0 to remove the current badge, if any.
     */
    public ?int $badge;

    /**
     * The name of a sound file in your app’s main bundle or in the Library/Sounds folder of your app’s
     * container directory. Specify the string “default” to play the system sound. Use this key for regular
     * notifications. For critical alerts, use the sound dictionary instead. For information about how to
     * prepare sounds, see
     * [UNNotificationSound](https://developer.apple.com/documentation/usernotifications/unnotificationsound).
     */
    public ?string $sound;

    /**
     * An app-specific identifier for grouping related notifications. This value corresponds to the
     * [threadIdentifier](
     * https://developer.apple.com/documentation/usernotifications/unmutablenotificationcontent/1649872-threadidentifier
     * ) property in the UNNotificationContent object.
     */
    public ?string $threadId;

    /**
     * The notification’s type. This string must correspond to the [identifier](
     * https://developer.apple.com/documentation/usernotifications/unnotificationcategory/1649276-identifier
     * ) of one of the UNNotificationCategory objects you register at launch time. See
     * [Declaring your actionable notification types](
     * https://developer.apple.com/documentation/usernotifications/declaring_your_actionable_notification_types
     * ).
     */
    public ?string $category;

    /**
     * The background notification flag. To perform a silent background update, specify the value 1 and don’t
     * include the alert, badge, or sound keys in your payload.
     */
    public ?int $contentAvailable;

    /**
     * The notification service app extension flag. If this key is present with a value of 1, the system passes
     * the notification to your notification service app extension before delivery. Use your extension to modify
     * the notification’s content. See [Modifying Content in Newly Delivered Notifications](
     * https://developer.apple.com/documentation/usernotifications/modifying_content_in_newly_delivered_notifications
     * ).
     */
    public ?int $mutableContent;

    /**
     * The identifier of the window brought forward. The value of this key will be populated on the
     * [UNNotificationContent](
     * https://developer.apple.com/documentation/usernotifications/unnotificationcontent
     * ) object created from the push payload. Access the value using the [UNNotificationContent](
     * https://developer.apple.com/documentation/usernotifications/unnotificationcontent
     * ) object’s [targetContentIdentifier](
     * https://developer.apple.com/documentation/usernotifications/unnotificationcontent/3235764-targetcontentidentifier
     * ) property.
     */
    public ?string $targetContentId;

    /**
     * The importance and delivery timing of a notification. The string values “passive”, “active”, “time-sensitive”,
     * or “critical” correspond to the [UNNotificationInterruptionLevel](
     * https://developer.apple.com/documentation/usernotifications/unnotificationinterruptionlevel
     * ) enumeration cases.
     */
    public ?string $interruptionLevel;

    /**
     * The relevance score, a number between 0 and 1, that the system uses to sort the notifications from your app. The
     * highest score gets featured in the notification summary. See [relevanceScore](
     * https://developer.apple.com/documentation/usernotifications/unnotificationcontent/3821031-relevancescore
     * ). If your remote notification updates a Live Activity, you can set any Double value;
     * for example, 25, 50, 75, or 100.
     */
    public ?float $relevanceScore;

    /**
     * The criteria the system evaluates to determine if it displays the notification in the current Focus. For more
     * information, see [SetFocusFilterIntent](
     * https://developer.apple.com/documentation/appintents/setfocusfilterintent
     * ).
     */
    public ?string $filterCriteria;

    /**
     * The UNIX timestamp that represents the date at which a Live Activity becomes stale, or out of date. For more
     * information, see [Displaying live data with Live Activities](
     * https://developer.apple.com/documentation/activitykit/displaying-live-data-with-live-activities
     * ).
     */
    public ?int $staleDate;

    /**
     * The updated or final content for a Live Activity. The content of this dictionary must match the data you
     * describe with your custom [ActivityAttributes](
     * https://developer.apple.com/documentation/activitykit/activityattributes
     * ) implementation. For more information, see Updating and ending your Live Activity with remote push
     * notifications.
     */
    public ?array $contentState;

    /**
     * The UNIX timestamp that marks the time when you send the remote notification that updates or ends a Live
     * Activity. For more information, see Updating and ending your Live Activity with remote push notifications.
     */
    public ?int $timestamp;

    /**
     * The string that describes whether you update or end an ongoing Live Activity with the remote push
     * notification. To update the Live Activity, use update. To end the Live Activity, use end. For more
     * information, see Updating and ending your Live Activity with remote push notifications.
     */
    public ?string $events;

    /**
     * The title of the notification. Apple Watch displays this string in the short look notification
     * interface. Specify a string that’s quickly understood by the user.
     */
    public ?string $title;

    /**
     * Additional information that explains the purpose of the notification.
     */
    public ?string $subtitle;

    /**
     * The content of the alert message.
     */
    public ?string $body;

    /**
     * The name of the launch image file to display. If the user chooses to launch your app, the contents of
     * the specified image or storyboard file are displayed instead of your app’s normal launch image.
     */
    public ?string $launchImage;

    /**
     * The key for a localized title string. Specify this key instead of the title key to retrieve the title
     * from your app’s Localizable.strings files. The value must contain the name of a key in your strings file.
     */
    public ?string $titleLocKey;

    /**
     * An array of strings containing replacement values for variables in your title string. Each %@ character
     * in the string specified by the title-loc-key is replaced by a value from this array. The first item in
     * the array replaces the first instance of the %@ character in the string, the second item replaces the
     * second instance, and so on.
     *
     * @var string[]
     */
    public ?array $titleLocArgs;

    /**
     * The key for a localized subtitle string. Use this key, instead of the subtitle key, to retrieve the
     * subtitle from your app’s Localizable.strings file. The value must contain the name of a key in your
     * strings file.
     */
    public ?string $subtitleLocKey;

    /**
     * An array of strings containing replacement values for variables in your subtitle string. Each %@ character
     * in the string specified by subtitle-loc-key is replaced by a value from this array. The first item in the
     * array replaces the first instance of the %@ character in the string, the second item replaces the second
     * instance, and so on.
     *
     * @var string[]
     */
    public ?array $subtitleLocArgs;

    /**
     * The key for a localized message string. Use this key, instead of the body key, to retrieve the message text
     * from your app’s Localizable.strings file. The value must contain the name of a key in your strings file.
     */
    public ?string $locKey;

    /**
     * An array of strings containing replacement values for variables in your message text. Each %@ character in
     * the string specified by loc-key is replaced by a value from this array. The first item in the array replaces
     * the first instance of the %@ character in the string, the second item replaces the second instance, and so on.
     *
     * @var string[]
     */
    public ?array $locArgs;

    /**
     * The notification's payload.
     */
    public ?array $payload;

    /**
     * Constructs a new instance of the IOSNotification.
     *
     * @param  ?int  $badge
     * @param  ?string  $sound
     * @param  ?string  $threadId
     * @param  ?string  $category
     * @param  ?int  $contentAvailable
     * @param  ?int  $mutableContent
     * @param  ?string  $targetContentId
     * @param  ?string  $interruptionLevel
     * @param  ?float  $relevanceScore
     * @param  ?string  $filterCriteria
     * @param  ?int  $staleDate
     * @param  ?array  $contentState
     * @param  ?int  $timestamp
     * @param  ?string  $events
     * @param  ?string  $title
     * @param  ?string  $subtitle
     * @param  ?string  $body
     * @param  ?string  $launchImage
     * @param  ?string  $titleLocKey
     * @param  ?string[]  $titleLocArgs
     * @param  ?string  $subtitleLocKey
     * @param  ?string[]  $subtitleLocArgs
     * @param  ?string  $locKey
     * @param  ?string[]  $locArgs
     * @param  ?array  $payload
     */
    public function __construct(
        int $badge = null,
        string $sound = null,
        string $threadId = null,
        string $category = null,
        int $contentAvailable = null,
        int $mutableContent = null,
        string $targetContentId = null,
        string $interruptionLevel = null,
        float $relevanceScore = null,
        string $filterCriteria = null,
        int $staleDate = null,
        array $contentState = null,
        int $timestamp = null,
        string $events = null,
        string $title = null,
        string $subtitle = null,
        string $body = null,
        string $launchImage = null,
        string $titleLocKey = null,
        array $titleLocArgs = null,
        string $subtitleLocKey = null,
        array $subtitleLocArgs = null,
        string $locKey = null,
        array $locArgs = null,
        array $payload = null
    ) {
        $this->badge = $badge;
        $this->sound = $sound;
        $this->threadId = $threadId;
        $this->category = $category;
        $this->contentAvailable = $contentAvailable;
        $this->mutableContent = $mutableContent;
        $this->targetContentId = $targetContentId;
        $this->interruptionLevel = $interruptionLevel;
        $this->relevanceScore = $relevanceScore;
        $this->filterCriteria = $filterCriteria;
        $this->staleDate = $staleDate;
        $this->contentState = $contentState;
        $this->timestamp = $timestamp;
        $this->events = $events;
        $this->title = $title;
        $this->subtitle = $subtitle;
        $this->body = $body;
        $this->launchImage = $launchImage;
        $this->titleLocKey = $titleLocKey;
        $this->titleLocArgs = $titleLocArgs;
        $this->subtitleLocKey = $subtitleLocKey;
        $this->subtitleLocArgs = $subtitleLocArgs;
        $this->locKey = $locKey;
        $this->locArgs = $locArgs;
        $this->payload = $payload;
        $this->recipientProvider = NotificationRecipientProviderEnum::Expo();
    }

    /**
     * Create instance with default options.
     */
    public static function defaultOptions(): self
    {
        return new self(
            contentAvailable: 1,
            sound: 'default',
        );
    }

    /**
     * Set the recipient provider.
     */
    public function recipientProvider(string $recipientProvider): self
    {
        $this->recipientProvider = $recipientProvider;

        return $this;
    }

    /**
     * Set the title.
     */
    public function title(string $title): self
    {
        if ($title) {
            $this->title = $title;
        }

        return $this;
    }

    /**
     * Set the body.
     */
    public function body(string $body): self
    {
        if ($body) {
            $this->body = $body;
        }

        return $this;
    }

    /**
     * Set the payload.
     */
    public function payload(array $payload): self
    {
        if ($payload) {
            $this->payload = $payload;
        }

        return $this;
    }

    /**
     * Convert instance to array representation that compatible with APNS format.
     */
    private function toAPNSArray(): array
    {
        $array = [
            'aps' => [
                'alert' => [],
            ],
        ];

        if ($this->badge) {
            $array['aps']['badge'] = $this->badge;
        }

        if ($this->sound) {
            $array['aps']['sound'] = $this->sound;
        }

        if ($this->threadId) {
            $array['aps']['thread-id'] = $this->threadId;
        }

        if ($this->category) {
            $array['aps']['category'] = $this->category;
        }

        if ($this->contentAvailable) {
            $array['aps']['content-available'] = $this->contentAvailable;
        }

        if ($this->mutableContent) {
            $array['aps']['mutable-content'] = $this->mutableContent;
        }

        if ($this->targetContentId) {
            $array['aps']['target-content-id'] = $this->targetContentId;
        }

        if ($this->interruptionLevel) {
            $array['aps']['interruption-level'] = $this->interruptionLevel;
        }

        if ($this->relevanceScore) {
            $array['aps']['relevance-score'] = $this->relevanceScore;
        }

        if ($this->filterCriteria) {
            $array['aps']['filter-criteria'] = $this->filterCriteria;
        }

        if ($this->staleDate) {
            $array['aps']['stale-date'] = $this->staleDate;
        }

        if ($this->contentState) {
            $array['aps']['content-state'] = $this->contentState;
        }

        if ($this->timestamp) {
            $array['aps']['timestamp'] = $this->timestamp;
        }

        if ($this->events) {
            $array['aps']['events'] = $this->events;
        }

        if ($this->title) {
            $array['aps']['alert']['title'] = $this->title;
        }

        if ($this->subtitle) {
            $array['aps']['alert']['subtitle'] = $this->subtitle;
        }

        if ($this->body) {
            $array['aps']['alert']['body'] = $this->body;
        }

        if ($this->launchImage) {
            $array['aps']['alert']['launch-image'] = $this->launchImage;
        }

        if ($this->titleLocKey) {
            $array['aps']['alert']['title-loc-key'] = $this->titleLocKey;
        }

        if ($this->titleLocArgs) {
            $array['aps']['alert']['title-loc-args'] = $this->titleLocArgs;
        }

        if ($this->subtitleLocKey) {
            $array['aps']['alert']['subtitle-loc-key'] = $this->subtitleLocKey;
        }

        if ($this->subtitleLocArgs) {
            $array['aps']['alert']['subtitle-loc-args'] = $this->subtitleLocArgs;
        }

        if ($this->locKey) {
            $array['aps']['alert']['loc-key'] = $this->locKey;
        }

        if ($this->locArgs) {
            $array['aps']['alert']['loc-args'] = $this->locArgs;
        }

        if ($this->payload) {
            $array = [...$array, ...$this->payload];
        }

        return $array;
    }

    /**
     * Convert instance to array representation that compatible with Expo format.
     */
    private function toExpoArray(): array
    {
        $array = [
            'aps' => [
                'alert' => [],
            ],
        ];

        if ($this->title) {
            $array['aps']['alert']['title'] = $this->title;
        }

        if ($this->subtitle) {
            $array['aps']['alert']['subtitle'] = $this->subtitle;
        }

        if ($this->body) {
            $array['aps']['alert']['body'] = $this->body;
        }

        if ($this->launchImage) {
            $array['aps']['alert']['launch-image'] = $this->launchImage;
        }

        if ($this->category) {
            $array['aps']['category'] = $this->category;
        }

        if ($this->badge) {
            $array['aps']['badge'] = $this->badge;
        }

        if ($this->sound) {
            $array['aps']['sound'] = $this->sound;
        }

        if ($this->threadId) {
            $array['aps']['thread-id'] = $this->threadId;
        }

        if ($this->contentAvailable) {
            $array['aps']['content-available'] = $this->contentAvailable;
        }

        if ($this->payload) {
            $array['body'] = $this->payload;
        }

        return $array;
    }

    /**
     * Convert instance to array representation.
     */
    public function toArray(): array
    {
        if ($this->recipientProvider === NotificationRecipientProviderEnum::Expo()) {
            return $this->toExpoArray();
        }

        return $this->toAPNSArray();
    }
}
