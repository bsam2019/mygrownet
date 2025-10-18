<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MyGrowNet Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration settings for the MyGrowNet platform including payment
    | processing, commission settings, and mobile money integration.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Payment Settings
    |--------------------------------------------------------------------------
    */
    'minimum_payment_threshold' => env('MYGROWNET_MIN_PAYMENT', 10.00),
    'payment_processing_delay_hours' => env('MYGROWNET_PAYMENT_DELAY', 24),
    'max_payment_retries' => env('MYGROWNET_MAX_RETRIES', 3),
    'payment_retry_window_days' => env('MYGROWNET_RETRY_WINDOW', 7),

    /*
    |--------------------------------------------------------------------------
    | Commission Settings
    |--------------------------------------------------------------------------
    */
    'commission_rates' => [
        1 => 12.0, // Level 1: 12%
        2 => 6.0,  // Level 2: 6%
        3 => 4.0,  // Level 3: 4%
        4 => 2.0,  // Level 4: 2%
        5 => 1.0,  // Level 5: 1%
    ],

    /*
    |--------------------------------------------------------------------------
    | Mobile Money Configuration
    |--------------------------------------------------------------------------
    */
    'mobile_money' => [
        /*
        |--------------------------------------------------------------------------
        | MTN Mobile Money
        |--------------------------------------------------------------------------
        */
        'mtn' => [
            'environment' => env('MTN_ENVIRONMENT', 'sandbox'), // sandbox or production
            'subscription_key' => env('MTN_SUBSCRIPTION_KEY'),
            'user_id' => env('MTN_USER_ID'),
            'api_key' => env('MTN_API_KEY'),
            'token_url' => env('MTN_TOKEN_URL', 'https://sandbox.momodeveloper.mtn.com/disbursement/token/'),
            'disbursement_url' => env('MTN_DISBURSEMENT_URL', 'https://sandbox.momodeveloper.mtn.com/disbursement/v1_0/transfer'),
        ],

        /*
        |--------------------------------------------------------------------------
        | Airtel Money
        |--------------------------------------------------------------------------
        */
        'airtel' => [
            'environment' => env('AIRTEL_ENVIRONMENT', 'staging'), // staging or production
            'client_id' => env('AIRTEL_CLIENT_ID'),
            'client_secret' => env('AIRTEL_CLIENT_SECRET'),
            'token_url' => env('AIRTEL_TOKEN_URL', 'https://openapiuat.airtel.africa/auth/oauth2/token'),
            'disbursement_url' => env('AIRTEL_DISBURSEMENT_URL', 'https://openapiuat.airtel.africa/standard/v1/disbursements/'),
        ],

        /*
        |--------------------------------------------------------------------------
        | Zamtel Kwacha
        |--------------------------------------------------------------------------
        */
        'zamtel' => [
            'environment' => env('ZAMTEL_ENVIRONMENT', 'test'), // test or production
            'username' => env('ZAMTEL_USERNAME'),
            'password' => env('ZAMTEL_PASSWORD'),
            'token_url' => env('ZAMTEL_TOKEN_URL', 'https://api.zamtel.co.zm/auth/token'),
            'disbursement_url' => env('ZAMTEL_DISBURSEMENT_URL', 'https://api.zamtel.co.zm/payments/disbursement'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Membership Tier Settings
    |--------------------------------------------------------------------------
    */
    'membership_tiers' => [
        'bronze' => [
            'name' => 'Bronze Member',
            'monthly_fee' => 50.00,
            'team_volume_requirement' => 0,
            'benefits' => [
                'Basic educational content',
                'Community project participation',
                'Level 1-3 commission eligibility'
            ]
        ],
        'silver' => [
            'name' => 'Silver Member',
            'monthly_fee' => 100.00,
            'team_volume_requirement' => 5000,
            'benefits' => [
                'Enhanced educational content',
                'Priority project voting',
                'Level 1-4 commission eligibility',
                'Performance bonus eligibility'
            ]
        ],
        'gold' => [
            'name' => 'Gold Member',
            'monthly_fee' => 200.00,
            'team_volume_requirement' => 15000,
            'benefits' => [
                'Premium educational content',
                'Project proposal rights',
                'Full 5-level commission eligibility',
                'Enhanced performance bonuses',
                'Asset reward eligibility'
            ]
        ],
        'diamond' => [
            'name' => 'Diamond Member',
            'monthly_fee' => 500.00,
            'team_volume_requirement' => 50000,
            'benefits' => [
                'Exclusive content access',
                'Leadership development programs',
                'Maximum commission rates',
                'Premium asset rewards',
                'Mentorship opportunities'
            ]
        ],
        'elite' => [
            'name' => 'Elite Member',
            'monthly_fee' => 1000.00,
            'team_volume_requirement' => 100000,
            'benefits' => [
                'VIP status and recognition',
                'Exclusive events access',
                'Maximum earning potential',
                'Luxury asset rewards',
                'Platform governance participation'
            ]
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Bonus Thresholds
    |--------------------------------------------------------------------------
    */
    'performance_bonuses' => [
        'team_volume_thresholds' => [
            100000 => 10.0, // K100,000+ = 10% bonus
            50000 => 7.0,   // K50,000+ = 7% bonus
            25000 => 5.0,   // K25,000+ = 5% bonus
            10000 => 2.0,   // K10,000+ = 2% bonus
        ],
        'leadership_bonuses' => [
            'elite_leader' => 3.0,
            'diamond_leader' => 2.5,
            'gold_leader' => 2.0,
            'developing_leader' => 1.0
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Asset Reward Settings
    |--------------------------------------------------------------------------
    */
    'asset_rewards' => [
        'eligibility_requirements' => [
            'minimum_tier' => 'gold',
            'minimum_team_volume' => 25000,
            'minimum_months_active' => 6
        ],
        'maintenance_requirements' => [
            'monthly_volume_threshold' => 5000,
            'max_inactive_months' => 3
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    */
    'notifications' => [
        'sms_enabled' => env('MYGROWNET_SMS_ENABLED', true),
        'email_enabled' => env('MYGROWNET_EMAIL_ENABLED', true),
        'commission_payment_notifications' => true,
        'tier_advancement_notifications' => true,
        'asset_allocation_notifications' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Compliance Settings
    |--------------------------------------------------------------------------
    */
    'compliance' => [
        'max_commission_cap_percentage' => 25.0, // Maximum 25% of revenue as commissions
        'sustainability_monitoring' => true,
        'legal_disclaimer_required' => true,
        'earnings_projection_disclaimer' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Achievement Bonus Settings
    |--------------------------------------------------------------------------
    */
    'tier_advancement_bonuses' => [
        'Bronze' => env('MYGROWNET_BRONZE_BONUS', 100.00),
        'Silver' => env('MYGROWNET_SILVER_BONUS', 250.00),
        'Gold' => env('MYGROWNET_GOLD_BONUS', 500.00),
        'Diamond' => env('MYGROWNET_DIAMOND_BONUS', 1000.00),
        'Elite' => env('MYGROWNET_ELITE_BONUS', 2500.00),
    ],

    'leadership_bonuses' => [
        'developing_leader' => 0.5, // 0.5% of team volume
        'gold_leader' => 1.0,       // 1.0% of team volume
        'diamond_leader' => 1.5,    // 1.5% of team volume
        'elite_leader' => 2.0,      // 2.0% of team volume
    ],

    'bonus_settings' => [
        'payment_delay_hours' => env('MYGROWNET_BONUS_DELAY', 24),
        'expiry_days' => env('MYGROWNET_BONUS_EXPIRY', 30),
        'tier_multipliers' => [
            'Bronze' => 1.0,
            'Silver' => 1.2,
            'Gold' => 1.5,
            'Diamond' => 2.0,
            'Elite' => 2.5,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Subscription Settings
    |--------------------------------------------------------------------------
    */
    'subscription' => [
        'max_failed_attempts' => env('MYGROWNET_SUBSCRIPTION_MAX_FAILED_ATTEMPTS', 3),
        'grace_period_days' => env('MYGROWNET_SUBSCRIPTION_GRACE_PERIOD', 7),
        'auto_downgrade_enabled' => env('MYGROWNET_AUTO_DOWNGRADE_ENABLED', true),
        'prorated_upgrades' => env('MYGROWNET_PRORATED_UPGRADES', true),
        'billing_retry_days' => env('MYGROWNET_BILLING_RETRY_DAYS', 7),
    ],

    /*
    |--------------------------------------------------------------------------
    | Development Settings
    |--------------------------------------------------------------------------
    */
    'development' => [
        'simulate_payments' => env('MYGROWNET_SIMULATE_PAYMENTS', false),
        'debug_mode' => env('MYGROWNET_DEBUG', false),
        'test_phone_numbers' => [
            '+260977123456', // Test MTN number
            '+260967123456', // Test Airtel number
            '+260955123456', // Test Zamtel number
        ]
    ]
];