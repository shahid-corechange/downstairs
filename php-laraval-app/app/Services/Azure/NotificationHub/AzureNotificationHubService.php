<?php

namespace App\Services\Azure\NotificationHub;

use App\Contracts\NotificationService;
use App\Enums\Azure\NotificationHub\NotificationHubEnum;
use App\Enums\Azure\NotificationHub\PlatformTypeEnum;
use App\Exceptions\Azure\InvalidConnectionStringException;
use App\Exceptions\Azure\InvalidNotificationHubException;
use App\Exceptions\Azure\InvalidPlatformException;
use App\Exceptions\OperationFailedException;
use App\Helpers\Notification\AndroidNotificationOptions;
use App\Helpers\Notification\IOSNotificationOptions;
use Http;
use Illuminate\Http\Client\Response as ClientResponse;
use Illuminate\Http\Response;
use Str;

class AzureNotificationHubService implements NotificationService
{
    /**
     * Constant for the notification hub API version.
     *
     * @var string
     */
    const API_VERSION = '2015-01';

    /**
     * Template for APNS registration.
     *
     * @var string
     */
    const APNS_REGISTRATION_TEMPLATE = '<?xml version="1.0" encoding="utf-8"?>
    <entry xmlns="http://www.w3.org/2005/Atom">
        <content type="application/xml">
            <AppleRegistrationDescription 
                xmlns:i="http://www.w3.org/2001/XMLSchema-instance" 
                xmlns="http://schemas.microsoft.com/netservices/2010/10/servicebus/connect"
            >
                <Tags>%s</Tags>
                <DeviceToken>%s</DeviceToken> 
            </AppleRegistrationDescription>
        </content>
    </entry>
    ';

    /**
     * Template for FCM registration.
     *
     * @var string
     */
    const FCM_REGISTRATION_TEMPLATE = '<?xml version="1.0" encoding="utf-8"?>
    <entry xmlns="http://www.w3.org/2005/Atom">
        <content type="application/xml">
            <FcmV1RegistrationDescription xmlns:i="http://www.w3.org/2001/XMLSchema-instance"
                xmlns="http://schemas.microsoft.com/netservices/2010/10/servicebus/connect">
                <Tags>%s</Tags>
                <FcmV1RegistrationId>%s</FcmV1RegistrationId>
            </FcmV1RegistrationDescription>
        </content>
    </entry>
    ';

    /**
     * Shared access key.
     */
    protected string $accessKey;

    /**
     * Shared access key name.
     */
    protected string $accessKeyName;

    /**
     * Service bus endpoint.
     */
    protected string $endpoint;

    /**
     * Notification hub.
     */
    protected string $hub;

    /**
     * Authorization token.
     */
    protected string $authorizationToken;

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $config = $this->parseConnectionString();

        $this->accessKey = $config['sharedAccessKey'];
        $this->accessKeyName = $config['sharedAccessKeyName'];
        $this->endpoint = str_replace('sb:', 'https:', $config['endpoint']);
    }

    /**
     * Read the connection string config and extract it.
     *
     * @return string[]
     *
     * @throws InvalidConnectionStringException
     */
    private function parseConnectionString(): array
    {
        $connectionString = config('services.azure_notification_hub.connection_string');

        if (! $connectionString) {
            throw new InvalidConnectionStringException(__('missing connection string'));
        }

        $properties = explode(';', $connectionString);
        $config = array_reduce($properties, function ($accumulator, $value) {
            $items = explode('=', $value, 2);

            if (count($items) !== 2) {
                throw new InvalidConnectionStringException(__('invalid value for', ['value' => $items[0]]));
            }

            $accumulator[Str::camel($items[0])] = $items[1];

            return $accumulator;
        }, []);

        if (! isset($config['sharedAccessKey'])) {
            throw new InvalidConnectionStringException(__('missing the access key'));
        }

        if (! isset($config['sharedAccessKeyName'])) {
            throw new InvalidConnectionStringException(__('missing the access key name'));
        }

        if (! isset($config['endpoint'])) {
            throw new InvalidConnectionStringException(__('missing the service bus endpoint'));
        }

        return $config;
    }

    /**
     * Create a shared access signature token.
     */
    private function createSASToken(string $uri): string
    {
        $expiresInMins = 5;
        $expires = now('UTC')->addMinutes($expiresInMins)->timestamp;
        $targetUri = strtolower(rawurlencode(strtolower($uri)));
        $stringToSign = $targetUri."\n".$expires;
        $signature = rawurlencode(
            base64_encode(
                hash_hmac('sha256', $stringToSign, $this->accessKey, true)
            )
        );

        return 'SharedAccessSignature sig='.$signature
            .'&se='.$expires
            .'&skn='.$this->accessKeyName
            .'&sr='.$targetUri;
    }

    /**
     * Get the registration template for the given platform.
     *
     * @throws InvalidPlatformException
     */
    private function getRegistrationTemplate(string $platform): string
    {
        switch ($platform) {
            case PlatformTypeEnum::Android():
                return self::FCM_REGISTRATION_TEMPLATE;
            case PlatformTypeEnum::IOS():
                return self::APNS_REGISTRATION_TEMPLATE;
            default:
                throw new InvalidPlatformException(__('invalid platform', ['platform' => $platform]));
        }
    }

    /**
     * Get the channel for the given platform.
     *
     * @throws InvalidPlatformException
     */
    private function getChannel(string $platform): string
    {
        switch ($platform) {
            case PlatformTypeEnum::Android():
                return 'FcmV1RegistrationId';
            case PlatformTypeEnum::IOS():
                return 'DeviceToken';
            default:
                throw new InvalidPlatformException(__('invalid platform', ['platform' => $platform]));
        }
    }

    /**
     * Extract the registration id from the entry id.
     */
    private function getRegistrationId(string $entryId): string
    {
        $url = explode('?', $entryId, 2)[0];
        $parts = explode('/', $url);

        return end($parts);
    }

    /**
     * Send a request to the notification hub REST API.
     *
     * @param  string[]  $headers
     *
     * @throws InvalidNotificationHubException
     */
    private function sendRequest(
        string $endpoint,
        string $method,
        string $body = null,
        array $headers = []
    ): ClientResponse {
        if (! isset($this->hub)) {
            throw new InvalidNotificationHubException(__('missing notification hub'));
        }

        $url = $this->endpoint.$this->hub.'/'.$endpoint;

        $headers['Authorization'] = $this->authorizationToken;
        $headers['ms-version'] = $this::API_VERSION;

        $options = [
            'headers' => $headers,
        ];

        if ($body) {
            $options['body'] = $body;
        }

        return Http::send($method, $url, $options);
    }

    /**
     * Set the notification hub and create authorization token.
     *
     * @throws InvalidNotificationHubException
     */
    public function hub(string $hub): self
    {
        if (! in_array($hub, NotificationHubEnum::values())) {
            throw new InvalidNotificationHubException(__('invalid notification hub', ['hub' => $hub]));
        }

        $this->hub = $hub;
        $this->authorizationToken = $this->createSASToken($this->endpoint.$this->hub);

        return $this;
    }

    /**
     * Check if the given device is registered to the notification hub.
     */
    public function isRegistered(string $platform, string $deviceToken): bool
    {
        $registrationEndpoint = 'registrations/?api-version='.$this::API_VERSION
            .'&$filter='.urlencode($this->getChannel($platform).' eq \''.$deviceToken.'\'');
        $response = $this->sendRequest($registrationEndpoint, 'GET');

        if ($response->status() !== Response::HTTP_OK) {
            throw new OperationFailedException(
                __('failed to check device', ['reason' => $response->reason()]),
                $response->status()
            );
        }

        $xml = simplexml_load_string($response->body());
        $json = json_encode($xml);
        $array = json_decode($json, true);

        return isset($array['entry']);
    }

    /**
     * Register a device to the notification hub.
     *
     * @param  string[]  $tags
     *
     * @throws OperationFailedException
     */
    public function register(string $platform, string $deviceToken, array $tags): void
    {
        $registrationTemplate = $this->getRegistrationTemplate($platform);
        $registrationPayload = sprintf($registrationTemplate, implode(',', $tags), $deviceToken);
        $registrationEndpoint = 'registrations/?api-version='.$this::API_VERSION;

        $response = $this->sendRequest($registrationEndpoint, 'POST', $registrationPayload, [
            'Content-Type' => 'application/atom+xml;type=entry;charset=utf-8',
        ]);

        if ($response->status() !== Response::HTTP_OK) {
            throw new OperationFailedException(
                __('failed to register device', ['reason' => $response->reason()]),
                $response->status()
            );
        }
    }

    /**
     * Unregister a device from the notification hub.
     */
    public function unregister(string $platform, string $deviceToken): void
    {
        $registrationEndpoint = 'registrations/?api-version='.$this::API_VERSION
            .'&$filter='.urlencode($this->getChannel($platform).' eq \''.$deviceToken.'\'');
        $response = $this->sendRequest($registrationEndpoint, 'GET');

        if ($response->status() !== Response::HTTP_OK) {
            throw new OperationFailedException(
                __('failed to unregister device', ['reason' => $response->reason()]),
                $response->status()
            );
        }

        $xml = simplexml_load_string($response->body());
        $json = json_encode($xml);
        $array = json_decode($json, true);

        if (! isset($array['entry'])) {
            return;
        } elseif (isset($array['entry']['id'])) {
            $registrationId = $this->getRegistrationId($array['entry']['id']);
        } else {
            $registrationId = $this->getRegistrationId($array['entry'][0]['id']);
        }

        $unregisterEndpoint = 'registrations/'.$registrationId.'/?api-version='.$this::API_VERSION;

        $response = $this->sendRequest($unregisterEndpoint, 'DELETE', headers: [
            'If-Match' => '*',
        ]);

        if ($response->status() !== Response::HTTP_OK) {
            throw new OperationFailedException(
                __('failed to unregister device', ['reason' => $response->reason()]),
                $response->status()
            );
        }
    }

    /**
     * Get all registered devices from the notification hub.
     */
    public function getRegistrations(): array
    {
        $registrationEndpoint = 'registrations/?api-version='.$this::API_VERSION;
        $response = $this->sendRequest($registrationEndpoint, 'GET');

        $xml = simplexml_load_string($response->body());
        $json = json_encode($xml);
        $array = json_decode($json, true);

        if (! isset($array['entry'])) {
            return [];
        } else {
            return $array['entry'];
        }
    }

    /**
     * Send a notification to the notification hub.
     *
     * @throws OperationFailedException
     */
    public function send(
        string $tag,
        AndroidNotificationOptions $android,
        IOSNotificationOptions $ios,
        array $payload = [],
        string $title = '',
        string $body = ''
    ): ClientResponse {
        $android->title($title)->body($body)->payload($payload);
        $ios->title($title)->body($body)->payload($payload);

        $notificationEndpoint = 'messages/?api-version='.$this::API_VERSION;

        $response = $this->sendRequest(
            $notificationEndpoint,
            'POST',
            json_encode($android->toArray(), JSON_FORCE_OBJECT),
            [
                'Content-Type' => 'application/json;charset=utf-8',
                'ServiceBusNotification-Format' => 'FcmV1',
                'ServiceBusNotification-Tags' => $tag,
            ]
        );

        if ($response->status() !== Response::HTTP_CREATED) {
            throw new OperationFailedException(
                __('failed to send notification', ['reason' => $response->reason()]),
                $response->status()
            );
        }

        $response = $this->sendRequest(
            $notificationEndpoint,
            'POST',
            json_encode($ios->toArray(), JSON_FORCE_OBJECT),
            [
                'Content-Type' => 'application/json;charset=utf-8',
                'ServiceBusNotification-Format' => 'apple',
                'ServiceBusNotification-Tags' => $tag,
            ]
        );

        if ($response->status() !== Response::HTTP_CREATED) {
            throw new OperationFailedException(
                __('failed to send notification', ['reason' => $response->reason()]),
                $response->status()
            );
        }

        return $response;
    }

    /**
     * Broadcast notifications to the notification hub.
     */
    public function broadcast(
        AndroidNotificationOptions $android,
        IOSNotificationOptions $ios,
        array $payload = [],
        string $title = '',
        string $body = ''
    ): ClientResponse {
        $android->title($title)->body($body)->payload($payload);
        $ios->title($title)->body($body)->payload($payload);

        $notificationEndpoint = 'messages/?api-version='.$this::API_VERSION;

        $response = $this->sendRequest(
            $notificationEndpoint,
            'POST',
            json_encode($android->toArray(), JSON_FORCE_OBJECT),
            [
                'Content-Type' => 'application/json;charset=utf-8',
                'ServiceBusNotification-Format' => 'FcmV1',
            ]
        );

        if ($response->status() !== Response::HTTP_CREATED) {
            throw new OperationFailedException(
                __('failed to send notification', ['reason' => $response->reason()]),
                $response->status()
            );
        }

        $response = $this->sendRequest(
            $notificationEndpoint,
            'POST',
            json_encode($ios->toArray(), JSON_FORCE_OBJECT),
            [
                'Content-Type' => 'application/json;charset=utf-8',
                'ServiceBusNotification-Format' => 'apple',
            ]
        );

        if ($response->status() !== Response::HTTP_CREATED) {
            throw new OperationFailedException(
                __('failed to send notification', ['reason' => $response->reason()]),
                $response->status()
            );
        }

        return $response;
    }
}
