<?php

namespace App\Http\Controllers\Fortnox;

use App\Http\Controllers\Controller;
use App\Models\OauthRemoteToken;
use App\Services\Fortnox\FortnoxCustomerService;
use App\Services\Fortnox\FortnoxEmployeeService;
use Http;
use Illuminate\Http\Response;
use Str;

class FortnoxController extends Controller
{
    protected string $redirectUri;

    protected string $oauthUrl;

    protected string $clientId;

    protected string $clientSecret;

    protected string $scope;

    protected string $state;

    protected string $accessType;

    protected string $responseType;

    protected string $accountType;

    protected string $credentials;

    protected string $appName;

    public function __construct()
    {
        $this->oauthUrl = config('services.fortnox.oauth_url');
        $this->clientId = config('services.fortnox.client_id');
        $this->clientSecret = config('services.fortnox.client_secret');
        $this->state = config('services.fortnox.state');
        $this->accessType = config('services.fortnox.access_type');
        $this->responseType = config('services.fortnox.response_type');
        $this->accountType = config('services.fortnox.account_type');
        $this->credentials = base64_encode($this->clientId.':'.$this->clientSecret);
    }

    public function customer()
    {
        $this->appName = 'fortnox-customer';
        $this->scope = config('services.fortnox.customer_scope');

        return $this->activation();
    }

    public function employee()
    {
        $this->appName = 'fortnox-employee';
        $this->scope = config('services.fortnox.employee_scope');

        return $this->activation();
    }

    /**
     * Get Fortnox autorization code.
     */
    private function activation()
    {
        $uri = explode('?', request()->getUri());
        $this->redirectUri = $uri[0];
        $token = OauthRemoteToken::where('app_name', $this->appName)->first();
        $query = request()->query();
        $responseKey = 'success';
        $responseMessage = __('successfully connected to fortnox');

        if (! $token) {
            /**
             * Check the request param if there is a code.
             * if there is a code, get the access token.
             * if there is no code, get the authorization code.
             */
            if (count($query) > 0 && $query[$this->responseType]) {
                $response = $this->getAccessToken($query[$this->responseType]);

                if ($response->status() === Response::HTTP_OK) {
                    $data = $response->json();
                    OauthRemoteToken::create([
                        ...$this->getData($data),
                        'app_name' => $this->appName,
                    ]);
                } else {
                    $responseKey = 'error';
                    $responseMessage = __('failed to connect to Fortnox');
                }
            } else {
                return $this->getAuthorizationCode();
            }
        } else {
            /**
             * Check the request param if there is a code.
             * if there is a code, get the access token.
             * if there is no code, check if the token is still valid.
             * if not, get the authorization code.
             */
            if (count($query) > 0 && $query[$this->responseType]) {
                $response = $this->getAccessToken($query[$this->responseType]);

                if ($response->status() === Response::HTTP_OK) {
                    $data = $response->json();
                    $token->update($this->getData($data));
                } else {
                    $responseKey = 'error';
                    $responseMessage = __('failed to connect to Fortnox');
                }
            } else {
                try {
                    $service = $this->appName === 'fortnox-customer' ?
                         new FortnoxCustomerService() : new FortnoxEmployeeService();
                    $service->ping();
                } catch (\Throwable $th) {
                    return $this->getAuthorizationCode();
                }
            }
        }

        return redirect('/dashboard')->with($responseKey, $responseMessage);
    }

    /**
     * Sync existing data without fortnox_id to Fortnox.
     */
    public function sync(FortnoxCustomerService $customerService, FortnoxEmployeeService $employeeService)
    {
        scoped_localize('sv_SE', function () use ($customerService, $employeeService) {
            $customerService->syncAll();
            $employeeService->syncAll();
        });

        return back()->with('success', __('successfully synced data to fortnox'));
    }

    /**
     * Get Fortnox autorization code.
     */
    private function getAuthorizationCode()
    {
        $baseUrl = $this->oauthUrl.'/auth';
        $queryParams = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'scope' => $this->scope,
            'state' => $this->state,
            'access_type' => $this->accessType,
            'response_type' => $this->responseType,
            'account_type' => $this->accountType,
        ];
        $externalUrl = $baseUrl.'?'.http_build_query($queryParams);

        return redirect()->away($externalUrl);
    }

    /**
     * Get Fortnox access token.
     */
    private function getAccessToken(string $code)
    {
        $response = Http::send('POST', $this->oauthUrl.'/token', [
            'headers' => [
                'Authorization' => 'Basic '.$this->credentials,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $this->redirectUri,
            ],
        ]);

        return $response;
    }

    /**
     * Get data from Fortnox access token response.
     */
    private function getData(mixed $data)
    {
        return [
            'token_type' => Str::studly($data['token_type']),
            'scope' => $data['scope'],
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'],
            'access_expires_at' => now()->addSeconds($data['expires_in']),
            'refresh_expires_at' => now()->addDays(31),
        ];
    }
}
