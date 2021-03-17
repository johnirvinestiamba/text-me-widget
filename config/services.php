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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'telco' => [
        'url' => env('TELCO_API_URL'),
        'grant_type' => env('TELCO_API_GRANT_TYPE'),
        'client_id' => env('TELCO_CLIENT_ID'),
        'client_secret' => env('TELCO_CLIENT_SECRET'),
        'username' => env('TELCO_USERNAME'),
        'password' => env('TELCO_PASSWORD'),
        'scope' => env('TELCO_SCOPE'),
        'reseller_id' => env('TELCO_RESELLER_ID')
    ],

    'recipient' => [
        'user' => env('MESSAGE_RECIPIENT_USER'),
        'domain' => env('MESSAGE_RECIPIENT_DOMAIN')
    ]

];
