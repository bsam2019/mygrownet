<?php

return [
    /*
    |--------------------------------------------------------------------------
    | GrowMart Delivery Configuration
    |--------------------------------------------------------------------------
    |
    | Delivery methods and their fees in ngwee (ZMW minor units).
    | Example: 3000 ngwee = K30.00
    |
    */
    'delivery_fees' => [
        'yango' => 3000,
        'own_vehicle' => 0,
        'pickup' => 0,
    ],

    /*
    |--------------------------------------------------------------------------
    | GrowMart Contact Information
    |--------------------------------------------------------------------------
    |
    | Business contact details shown on legal pages and email footers.
    |
    */
    'contact' => [
        'email' => env('GROWMART_EMAIL', 'support@growmart.co.zm'),
        'phone' => env('GROWMART_PHONE', '+260 97 123 4567'),
        'address' => env('GROWMART_ADDRESS', 'Plot 123, Great East Road, Lusaka, Zambia'),
        'social' => [
            'facebook' => env('GROWMART_FACEBOOK', '#'),
            'instagram' => env('GROWMART_INSTAGRAM', '#'),
        ],
    ],
];
