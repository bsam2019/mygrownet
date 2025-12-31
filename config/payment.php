<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Automated Payments Enabled
    |--------------------------------------------------------------------------
    |
    | When set to true, the platform will use automated mobile money payments
    | via PawaPay or other payment gateways. When false, users will be shown
    | manual payment instructions (send to company number, submit proof).
    |
    | Set this to true once PawaPay integration is approved and configured.
    |
    */
    'automated_payments_enabled' => env('AUTOMATED_PAYMENTS_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Manual Payment Numbers
    |--------------------------------------------------------------------------
    |
    | Company payment numbers for manual payments when automated payments
    | are disabled.
    |
    */
    'manual_payment' => [
        'mtn' => [
            'number' => env('MANUAL_PAYMENT_MTN_NUMBER', '0760491206'),
            'name' => env('MANUAL_PAYMENT_MTN_NAME', 'Rockshield Investments Ltd'),
            'method' => 'withdraw', // 'withdraw' or 'send'
        ],
        'airtel' => [
            'number' => env('MANUAL_PAYMENT_AIRTEL_NUMBER', '0979230669'),
            'name' => env('MANUAL_PAYMENT_AIRTEL_NAME', 'Kafula Mbulo'),
            'method' => 'send',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | PawaPay Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for PawaPay payment gateway (when automated payments enabled)
    |
    */
    'pawapay' => [
        'api_key' => env('PAWAPAY_API_KEY'),
        'api_secret' => env('PAWAPAY_API_SECRET'),
        'base_url' => env('PAWAPAY_BASE_URL', 'https://api.pawapay.io'),
        'webhook_secret' => env('PAWAPAY_WEBHOOK_SECRET'),
    ],
];
