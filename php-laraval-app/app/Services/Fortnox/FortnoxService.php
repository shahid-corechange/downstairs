<?php

namespace App\Services\Fortnox;

use App\Exceptions\InvalidOauthRemoteToken;
use App\Exceptions\OperationFailedException;
use App\Models\OauthRemoteToken;
use App\Services\Fortnox\Resources\AbsenceTransactionResource;
use App\Services\Fortnox\Resources\ArticleResource;
use App\Services\Fortnox\Resources\AttendanceTransactionResource;
use App\Services\Fortnox\Resources\CustomerResource;
use App\Services\Fortnox\Resources\EmployeeResource;
use App\Services\Fortnox\Resources\InvoicePaymentResource;
use App\Services\Fortnox\Resources\InvoiceResource;
use App\Services\Fortnox\Resources\TaxReductionResource;
use Http;
use Illuminate\Http\Client\Response as ClientResponse;
use Illuminate\Http\Response;
use Str;

class FortnoxService
{
    use AbsenceTransactionResource;
    use ArticleResource;
    use AttendanceTransactionResource;
    use CustomerResource;
    use EmployeeResource;
    use InvoicePaymentResource;
    use InvoiceResource;
    use TaxReductionResource;

    /**
     * OAuth App Name for Fortnox.
     */
    protected string $appName = 'fortnox';

    /**
     * Timeout for the API request.
     */
    protected int $timeout = 30;

    /**
     * Retry count for the API request.
     */
    protected int $tries = 1;

    /**
     * Base URL for Fortnox API.
     */
    protected string $apiUrl;

    /**
     * Base URL for Fortnox OAuth.
     */
    protected string $oauthUrl;

    /**
     * App Client ID for Fortnox OAuth.
     */
    protected string $clientId;

    /**
     * App Client Secret for Fortnox OAuth.
     */
    protected string $clientSecret;

    /**
     * App Scope for Fortnox OAuth.
     */
    protected string $scope;

    /**
     * Encoded base64 string of Client ID and Client Secret.
     */
    protected string $credentials;

    /**
     * OAuth token.
     */
    protected OauthRemoteToken $token;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->apiUrl = config('services.fortnox.api_url');
        $this->oauthUrl = config('services.fortnox.oauth_url');
        $this->clientId = config('services.fortnox.client_id');
        $this->clientSecret = config('services.fortnox.client_secret');
        $this->credentials = base64_encode($this->clientId.':'.$this->clientSecret);
    }

    /**
     * Get the OAuth token.
     */
    protected function getToken(): OauthRemoteToken
    {
        $token = OauthRemoteToken::where('app_name', $this->appName)->first();

        if (! $token) {
            throw new InvalidOauthRemoteToken(__('missing oauth token', ['app' => $this->appName]));
        }

        return $token;
    }

    /**
     * Refresh the OAuth token.
     */
    private function refreshToken(): void
    {
        $response = Http::send('POST', $this->oauthUrl.'/token', [
            'headers' => [
                'Authorization' => 'Basic '.$this->credentials,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => $this->token->refresh_token,
            ],
        ]);

        if ($response->status() !== Response::HTTP_OK) {
            throw new OperationFailedException(__('failed to refresh oauth token', ['app' => $this->appName]));
        }

        $data = $response->json();
        $this->token->fill([
            'token_type' => Str::studly($data['token_type']),
            'scope' => $data['scope'],
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'],
            'access_expires_at' => now()->addSeconds($data['expires_in']),
            'refresh_expires_at' => now()->addDays(31),
        ])->save();
    }

    /**
     * Construct the query string parameters.
     *
     * @param  array<string,mixed>  $query
     */
    private function constructQuery(array $query): string
    {
        $queryStrings = [];

        foreach ($query as $key => $value) {
            if ($value == null) {
                continue;
            }

            $queryStrings[] = $key.'='.$value;
        }

        return implode('&', $queryStrings);
    }

    /**
     * Send request to Fortnox API.
     *
     * @param  array<string,mixed>  $body
     * @param  array<string,string>  $headers
     * @param  array<string,mixed>  $query
     */
    private function sendRequest(
        string $endpoint,
        string $method,
        array $body = null,
        array $headers = [],
        array $query = [],
    ): ClientResponse {
        if ($this->token->needReauthenticate()) {
            $this->refreshToken();
        }

        $url = $this->apiUrl.'/'.$endpoint;
        $url .= $query ? '?'.$this->constructQuery($query) : '';

        $headers['Authorization'] = $this->token->token_type.' '.$this->token->access_token;
        $headers['Content-Type'] = 'application/json';

        $options = ['headers' => $headers];

        if ($body) {
            $options['json'] = $body;
        }

        $response = Http::timeout($this->timeout)
            ->retry($this->tries)
            ->send($method, $url, $options);

        if ($response->status() === Response::HTTP_UNAUTHORIZED) {
            return $this->sendRequest($endpoint, $method, $body, $headers);
        }

        return $response;
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
     * Ping the Fortnox API.
     */
    public function ping(): ClientResponse
    {
        $response = $this->sendRequest('companyinformation', 'GET');

        if ($response->status() !== Response::HTTP_OK) {
            throw new OperationFailedException(__('fortnox unreachable'), Response::HTTP_BAD_GATEWAY);
        }

        return $response;
    }
}
