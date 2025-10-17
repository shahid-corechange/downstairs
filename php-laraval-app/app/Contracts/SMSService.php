<?php

namespace App\Contracts;

use Illuminate\Http\Client\Response;

interface SMSService
{
    /**
     * Set the timeout for the API request.
     */
    public function timeout(int $seconds): self;

    /**
     * Set the retry count for the API request.
     */
    public function retry(int $tries): self;

    /**
     * Personalize the SMS message.
     *
     * @param  string  $template The template to use.
     * see \App\Helpers\SMS\SMSTemplate class for available templates.
     * @param  string[]  $args
     */
    public function personalize(string $template, string ...$args): self;

    /**
     * Ping the SMS service.
     */
    public function ping(): Response;

    /**
     * Send a SMS to the given phone number.
     */
    public function send(string $phoneNumber, string $message = null): void;

    /**
     * Broadcast a SMS to the given phone numbers.
     */
    public function broadcast(array $phoneNumbers, string $message = null): void;
}
