<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'azure_notification_hub' => [
        'connection_string' => env('AZURE_NOTIFICATION_HUB_CONNECTION_STRING'),
    ],

    'azure_storage' => [
        'connection_string' => env('AZURE_STORAGE_CONNECTION_STRING'),
    ],

    'elks46' => [
        /**
         * API base url
         */
        'host' => env('E46ELKS_HOST', 'https://api.46elks.com/a1'),

        /**
         * E.164 number or text
         * see more at: https://46elks.se/kb/e164 and https://46elks.se/kb/text-sender-id
         */
        'from' => env('E46ELKS_FROM', config('app.name')),

        /**
         * 46 Elks credentials
         */
        'username' => env('E46ELKS_USERNAME'),
        'password' => env('E46ELKS_PASSWORD'),
    ],

    'fortnox' => [
        'api_url' => env('FORTNOX_API_URL', 'https://api.fortnox.se/3'),
        'oauth_url' => env('FORTNOX_OAUTH_URL', 'https://apps.fortnox.se/oauth-v1'),
        'client_id' => env('FORTNOX_APP_CLIENT_ID'),
        'client_secret' => env('FORTNOX_APP_CLIENT_SECRET'),
        'customer_scope' => env('FORTNOX_APP_CUSTOMER_SCOPE', 'companyinformation customer order invoice article price'),
        'employee_scope' => env('FORTNOX_APP_EMPLOYEE_SCOPE', 'companyinformation price salary costcenter project'),
        'state' => env('FORTNOX_APP_STATE', 'somestate123'),
        'access_type' => env('FORTNOX_APP_ACCESS_TYPE', 'offline'),
        'response_type' => env('FORTNOX_APP_RESPONSE_TYPE', 'code'),
        'account_type' => env('FORTNOX_APP_ACCOUNT_TYPE', 'service'),
        'sales_account' => env('FORTNOX_SALES_ACCOUNT', '{}'),
    ],

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => env('MAILGUN_DOMAIN', 'https'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => '/auth/redirect/google',
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => '/auth/redirect/facebook',
    ],

    'apple' => [
        'client_id' => env('APPLE_CLIENT_ID'),
        'client_secret' => env('APPLE_CLIENT_SECRET'),
        'redirect' => env('APPLE_REDIRECT_URI'),
    ],

    'twitter' => [
        'client_id' => env('TWITTER_CLIENT_ID'),
        'client_secret' => env('TWITTER_CLIENT_SECRET'),
        'redirect' => env('TWITTER_REDIRECT_URI'),
    ],

    'geoapify' => [
        'api_key' => env('GEOAPIFY_API_KEY'),
    ],

];
