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

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Social Media API Credentials (BizBoost)
    |--------------------------------------------------------------------------
    */

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'api_version' => env('FACEBOOK_API_VERSION', 'v18.0'),
    ],

    'tiktok' => [
        'client_key' => env('TIKTOK_CLIENT_KEY'),
        'client_secret' => env('TIKTOK_CLIENT_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Firebase Cloud Messaging
    |--------------------------------------------------------------------------
    */

    'firebase' => [
        'project_id' => env('FIREBASE_PROJECT_ID'),
        'credentials' => env('FIREBASE_CREDENTIALS', storage_path('app/firebase-credentials.json')),
        'server_key' => env('FIREBASE_SERVER_KEY'), // Legacy fallback
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Gateway Configuration
    |--------------------------------------------------------------------------
    */

    'payment' => [
        'default_gateway' => env('PAYMENT_DEFAULT_GATEWAY', 'moneyunify'),
    ],

    'moneyunify' => [
        'base_url' => env('MONEYUNIFY_BASE_URL', 'https://api.moneyunify.com/v2'),
        'muid' => env('MONEYUNIFY_MUID'), // MoneyUnify ID - get from dashboard
        'webhook_secret' => env('MONEYUNIFY_WEBHOOK_SECRET'),
        'mock_mode' => env('MONEYUNIFY_MOCK_MODE', false), // Enable for testing without real API
    ],

    'pawapay' => [
        'base_url' => env('PAWAPAY_BASE_URL', 'https://api.pawapay.io'),
        'api_token' => env('PAWAPAY_API_TOKEN'),
        'webhook_secret' => env('PAWAPAY_WEBHOOK_SECRET'),
        'mock_mode' => env('PAWAPAY_MOCK_MODE', false), // Enable for testing without real API
    ],

];
