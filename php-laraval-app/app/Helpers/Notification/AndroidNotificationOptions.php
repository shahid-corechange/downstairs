<?php

namespace App\Helpers\Notification;

use App\Enums\Notification\NotificationRecipientProviderEnum;

class AndroidNotificationOptions
{
    /**
     * Set the notification recipient provider. This will be used to determine the format of
     * the notification message.
     */
    public string $recipientProvider;

    /**
     * This parameter identifies a group of messages (e.g., with collapse_key: "Updates Available")
     * that can be collapsed, so that only the last message gets sent when delivery can be
     * resumed. This is intended to avoid sending too many of the same messages when the device comes back
     * online or becomes active. Note that there is no guarantee of the order in which messages get
     * sent. Note: A maximum of 4 different collapse keys is allowed at any given time. This means
     * that FCM can simultaneously store 4 different messages per client app. If you exceed this
     * number, there is no guarantee which 4 collapse keys FCM will keep.
     */
    public ?string $collapseKey;

    /**
     * Sets the priority of the message. Valid values are "normal" and "high." On Apple platforms,
     * these correspond to APNs priorities 5 and 10. By default, notification messages are sent with
     * high priority, and data messages are sent with normal priority. Normal priority optimizes the
     * client app's battery consumption and should be used unless immediate delivery is required. For
     * messages with normal priority, the app may receive the message with unspecified delay. When a
     * message is sent with high priority, it is sent immediately, and the app can display a notification.
     */
    public ?string $priority;

    /**
     * How long (in seconds) the message should be kept in FCM storage if the device is offline.
     * The maximum time to live supported is 4 weeks, and the default value is 4 weeks if not set.
     * Set it to 0 if want to send the message immediately. In JSON format, the Duration type is encoded as
     * a string rather than an object, where the string ends in the suffix "s" (indicating seconds)
     * and is preceded by the number of seconds, with nanoseconds expressed as fractional seconds.
     * For example, 3 seconds with 0 nanoseconds should be encoded in JSON format as "3s", while 3 seconds
     * and 1 nanosecond should be expressed in JSON format as "3.000000001s". The ttl will be rounded down
     * to the nearest second.
     */
    public ?string $ttl;

    /**
     * The notification's title.
     */
    public ?string $title;

    /**
     * The notification's body text.
     */
    public ?string $body;

    /**
     * The notification's channel id (new in Android O). The app must create a channel with this channel
     * ID before any notification with this channel ID is received. If you don't send this channel ID in
     * the request, or if the channel ID provided has not yet been created by the app, FCM uses the
     * channel ID specified in the app manifest.
     */
    public ?string $channelId;

    /**
     * The notification's icon. Sets the notification icon to myicon for drawable resource myicon. If you
     * don't send this key in the request, FCM displays the launcher icon specified in your app manifest.
     */
    public ?string $icon;

    /**
     * The sound to play when the device receives the notification. Supports "default" or the filename of
     * a sound resource bundled in the app. Sound files must reside in /res/raw/.
     */
    public ?string $sound;

    /**
     * Identifier used to replace existing notifications in the notification drawer. If not specified, each
     * request creates a new notification. If specified and a notification with the same tag is already being
     * shown, the new notification replaces the existing one in the notification drawer.
     */
    public ?string $tag;

    /**
     * The notification's icon color, expressed in #rrggbb format.
     */
    public ?string $color;

    /**
     * The action associated with a user click on the notification. If specified, an activity with a matching
     * intent filter is launched when a user clicks on the notification.
     */
    public ?string $clickAction;

    /**
     * The key to the body string in the app's string resources to use to localize the body text to the user's
     * current localization. See
     * [String Resources](https://developer.android.com/guide/topics/resources/string-resource)
     * for more information.
     */
    public ?string $bodyLocKey;

    /**
     * Variable string values to be used in place of the format specifiers in body_loc_key to use to localize
     * the body text to the user's current localization. See
     * [Format and Styling](https://developer.android.com/guide/topics/resources/string-resource#FormattingAndStyling)
     * for more information.
     *
     * @var string[]
     */
    public ?array $bodyLocArgs;

    /**
     * The key to the title string in the app's string resources to use to localize the title text to the user's
     * current localization. See
     * [String Resources](https://developer.android.com/guide/topics/resources/string-resource)
     * for more information.
     */
    public ?string $titleLocKey;

    /**
     * Variable string values to be used in place of the format specifiers in title_loc_key to use to localize the
     * title text to the user's current localization. See
     * [Format and Styling](https://developer.android.com/guide/topics/resources/string-resource#FormattingAndStyling)
     * for more information.
     *
     * @var string[]
     */
    public ?array $titleLocArgs;

    /**
     * The notification's payload.
     */
    public ?array $payload;

    /**
     * Constructs a new instance of AndroidNotificationOptions.
     *
     * @param  ?string  $collapseKey
     * @param  ?string  $priority
     * @param  ?string  $ttl
     * @param  ?bool  $dryRun
     * @param  ?string  $title
     * @param  ?string  $body
     * @param  ?string  $channelId
     * @param  ?string  $icon
     * @param  ?string  $sound
     * @param  ?string  $tag
     * @param  ?string  $color
     * @param  ?string  $clickAction
     * @param  ?string  $bodyLocKey
     * @param  ?string[]  $bodyLocArgs
     * @param  ?string  $titleLocKey
     * @param  ?string[]  $titleLocArgs
     * @param  ?array  $payload
     * @return void
     */
    public function __construct(
        string $collapseKey = null,
        string $priority = null,
        string $ttl = null,
        string $title = null,
        string $body = null,
        string $channelId = null,
        string $icon = null,
        string $sound = null,
        string $tag = null,
        string $color = null,
        string $clickAction = null,
        string $bodyLocKey = null,
        array $bodyLocArgs = null,
        string $titleLocKey = null,
        array $titleLocArgs = null,
        array $payload = null,
    ) {
        $this->collapseKey = $collapseKey;
        $this->priority = $priority;
        $this->ttl = $ttl;
        $this->title = $title;
        $this->body = $body;
        $this->channelId = $channelId;
        $this->icon = $icon;
        $this->sound = $sound;
        $this->tag = $tag;
        $this->color = $color;
        $this->clickAction = $clickAction;
        $this->bodyLocKey = $bodyLocKey;
        $this->bodyLocArgs = $bodyLocArgs;
        $this->titleLocKey = $titleLocKey;
        $this->titleLocArgs = $titleLocArgs;
        $this->payload = $payload;
        $this->recipientProvider = NotificationRecipientProviderEnum::Expo();
    }

    /**
     * Create instance with default options.
     */
    public static function defaultOptions(): self
    {
        return new self(
            priority: 'high',
            channelId: 'default',
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
     * Convert instance to array representation that compatible with FCM format.
     */
    private function toFCMArray(): array
    {
        $array = [
            'message' => [
                'android' => [
                    'notification' => [],
                ],
                'notification' => [],
            ],
        ];

        if ($this->collapseKey) {
            $array['message']['android']['collapse_key'] = $this->collapseKey;
        }

        if ($this->priority) {
            $array['message']['android']['priority'] = $this->priority;
        }

        if ($this->ttl) {
            $array['message']['android']['ttl'] = $this->ttl;
        }

        if ($this->title) {
            $array['message']['notification']['title'] = $this->title;
        }

        if ($this->body) {
            $array['message']['notification']['body'] = $this->body;
        }

        if ($this->channelId) {
            $array['message']['android']['notification']['channel_id'] = $this->channelId;
        }

        if ($this->icon) {
            $array['message']['android']['notification']['icon'] = $this->icon;
        }

        if ($this->sound) {
            $array['message']['android']['notification']['sound'] = $this->sound;
        }

        if ($this->tag) {
            $array['message']['android']['notification']['tag'] = $this->tag;
        }

        if ($this->color) {
            $array['message']['android']['notification']['color'] = $this->color;
        }

        if ($this->clickAction) {
            $array['message']['android']['notification']['click_action'] = $this->clickAction;
        }

        if ($this->bodyLocKey) {
            $array['message']['android']['notification']['body_loc_key'] = $this->bodyLocKey;
        }

        if ($this->bodyLocArgs) {
            $array['message']['android']['notification']['body_loc_args'] = $this->bodyLocArgs;
        }

        if ($this->titleLocKey) {
            $array['message']['android']['notification']['title_loc_key'] = $this->titleLocKey;
        }

        if ($this->titleLocArgs) {
            $array['message']['android']['notification']['title_loc_args'] = $this->titleLocArgs;
        }

        if ($this->payload) {
            $array['message']['data'] = $this->payload;
        }

        return $array;
    }

    /**
     * Convert instance to array representation that compatible with Expo format.
     */
    private function toExpoArray(): array
    {
        $array = [
            'message' => [
                'android' => [],
                'data' => [],
            ],
        ];

        if ($this->collapseKey) {
            $array['message']['android']['collapse_key'] = $this->collapseKey;
        }

        if ($this->priority) {
            $array['message']['android']['priority'] = $this->priority;
            $array['message']['data']['priority'] = $this->priority;
        }

        if ($this->title) {
            $array['message']['data']['title'] = $this->title;
        }

        if ($this->body) {
            $array['message']['data']['message'] = $this->body;
        }

        if ($this->channelId) {
            $array['message']['data']['channelId'] = $this->channelId;
        }

        if ($this->icon) {
            $array['message']['data']['icon'] = $this->icon;
        }

        if ($this->sound) {
            $array['message']['data']['sound'] = $this->sound;
        }

        if ($this->payload) {
            $array['message']['data']['body'] = json_encode($this->payload, JSON_FORCE_OBJECT);
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

        return $this->toFCMArray();
    }
}
