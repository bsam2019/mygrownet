<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Sponsor Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration determines how users without a referral code are
    | handled in the network structure.
    |
    */

    // Enable automatic placement under default sponsor
    'use_default_sponsor' => env('USE_DEFAULT_SPONSOR', true),

    // Default sponsor email (typically the admin/company account)
    'default_sponsor_email' => env('DEFAULT_SPONSOR_EMAIL', 'admin@mygrownet.com'),

    // Alternative: Use the first registered user as default sponsor
    'use_first_user_as_default' => env('USE_FIRST_USER_AS_DEFAULT', false),

    /*
    |--------------------------------------------------------------------------
    | Referral Code Settings
    |--------------------------------------------------------------------------
    */

    // Length of generated referral codes
    'code_length' => 8,

    // Prefix for referral codes (optional)
    'code_prefix' => env('REFERRAL_CODE_PREFIX', 'MGN'),

    /*
    |--------------------------------------------------------------------------
    | Matrix Settings
    |--------------------------------------------------------------------------
    */

    // Maximum matrix depth (levels)
    'max_matrix_levels' => 7,

    // Children per position (3x3 matrix)
    'children_per_position' => 3,

    /*
    |--------------------------------------------------------------------------
    | Commission Settings
    |--------------------------------------------------------------------------
    */

    // Enable commission tracking
    'enable_commissions' => env('ENABLE_COMMISSIONS', true),

    // Commission levels (how many levels deep to pay commissions)
    'commission_levels' => 7,
];
