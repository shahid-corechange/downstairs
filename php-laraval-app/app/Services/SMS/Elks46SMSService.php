<?php

namespace App\Services\SMS;

use App\Contracts\SMSService;
use App\Exceptions\InvalidSMSException;
use App\Exceptions\OperationFailedException;
use Http;
use Illuminate\Http\Client\Response as ClientResponse;
use Illuminate\Http\Response;

class Elks46SMSService implements SMSService
{
    /**
     * Timeout for the API request.
     */
    protected int $timeout = 30;

    /**
     * Retry count for the API request.
     */
    protected int $tries = 1;

    /**
     * Message of the SMS.
     */
    protected string $message;

    /**
     * Elks46 API URL.
     */
    protected string $url;

    /**
     * Elks46 username.
     */
    protected string $username;

    /**
     * Elks46 password.
     */
    protected string $password;

    /**
     * Elks46 sender.
     */
    protected string $from;

    /**
     * Elks46 authorization header.
     */
    protected string $authorizationHeader;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->message = '';
        $this->url = config('services.elks46.host');
        $this->username = config('services.elks46.username');
        $this->password = config('services.elks46.password');
        $this->from = config('services.elks46.from');
        $this->authorizationHeader = $this->createAuthorizationHeader();
    }

    /**
     * Create Elk46 authorization header.
     */
    private function createAuthorizationHeader(): string
    {
        return 'Basic '.base64_encode($this->username.':'.$this->password);
    }

    /**
     * Send HTTP request to Elks46 API.
     */
    private function sendRequest(
        string $endpoint,
        string $method,
        array $data = [],
        array $headers = [],
    ): ClientResponse {
        $headers = array_merge([
            'Authorization' => $this->authorizationHeader,
            'Content-Type' => 'application/x-www-form-urlencoded',
        ], $headers);

        $url = $this->url.'/'.$endpoint;

        return Http::timeout($this->timeout)
            ->retry($this->tries)
            ->send($method, $url, [
                'headers' => $headers,
                'form_params' => $data,
            ]);
    }

    /**
     * Set the timeout for the API request.
     */
    public function timeout(int $seconds): self
    {
        $this->timeout = $seconds;

        return $this;
    }

    /**
     * Set the retry count for the API request.
     */
    public function retry(int $tries): self
    {
        $this->tries = $tries;

        return $this;
    }

    /**
     * Personalize the SMS message.
     *
     * @param  string  $template The template to use.
     * see \App\Helpers\SMS\SMSTemplate class for available templates.
     * @param  string[]  $args
     */
    public function personalize(string $template, string ...$args): self
    {
        $this->message = sprintf($template, ...$args);

        return $this;
    }

    /**
     * Ping the SMS service.
     */
    public function ping(): ClientResponse
    {
        $response = $this->sendRequest('me', 'GET');

        if ($response->status() !== Response::HTTP_OK) {
            throw new OperationFailedException(__('sms service provider unreachable'), Response::HTTP_BAD_GATEWAY);
        }

        return $response;
    }

    /**
     * Send a SMS to the given phone number.
     */
    public function send(string $phoneNumber, string $message = null): void
    {
        if (! $message && ! $this->message) {
            throw new InvalidSMSException(__('sms message is required'), Response::HTTP_INTERNAL_SERVER_ERROR);
        } elseif ($this->message) {
            $message = $this->message;
        }

        $response = $this->sendRequest('sms', 'POST', [
            'from' => $this->from,
            'to' => $phoneNumber,
            'message' => $message,
        ]);

        if ($response->status() !== Response::HTTP_OK) {
            throw new OperationFailedException($response->body(), $response->status());
        } elseif ($response->json('status') === 'failed') {
            throw new OperationFailedException(__('unable to send sms'), Response::HTTP_BAD_GATEWAY);
        }
    }

    /**
     * Broadcast a SMS to the given phone numbers.
     */
    public function broadcast(array $phoneNumbers, string $message = null): void
    {
        foreach ($phoneNumbers as $phoneNumber) {
            $this->send($phoneNumber, $message);
        }
    }
}
