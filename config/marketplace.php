<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Marketplace Commission Configuration
    |--------------------------------------------------------------------------
    |
    | Commission rates are based on seller trust levels. Higher trust levels
    | get lower commission rates as a reward for good performance.
    |
    */
    'commission' => [
        // Commission rates by trust level (percentage)
        'rates' => [
            'new' => 10.0,       // New sellers: 10%
            'verified' => 10.0,  // Verified sellers: 10%
            'trusted' => 8.0,    // Trusted sellers: 8%
            'top' => 5.0,        // Top sellers: 5%
        ],
        
        // Payment processing fee (passed through)
        'payment_processing_fee' => 2.5, // 2.5%
        
        // Minimum commission amount in ngwee (K1)
        'minimum_commission' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Seller Tier Requirements
    |--------------------------------------------------------------------------
    |
    | Criteria for automatic tier upgrades. Sellers must meet ALL requirements
    | for a tier to be eligible for upgrade.
    |
    */
    'tiers' => [
        // New -> Verified: Just needs KYC approval (handled separately)
        
        // Verified -> Trusted
        'trusted' => [
            'min_completed_orders' => 20,
            'min_total_sales' => 500000,      // K5,000 in ngwee
            'min_rating' => 4.0,
            'max_dispute_rate' => 5.0,        // Max 5% disputes
            'max_cancellation_rate' => 10.0,  // Max 10% cancellations
            'min_account_age_days' => 30,
        ],
        
        // Trusted -> Top
        'top' => [
            'min_completed_orders' => 100,
            'min_total_sales' => 5000000,     // K50,000 in ngwee
            'min_rating' => 4.5,
            'max_dispute_rate' => 2.0,        // Max 2% disputes
            'max_cancellation_rate' => 5.0,   // Max 5% cancellations
            'min_account_age_days' => 90,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Processing Configuration
    |--------------------------------------------------------------------------
    */
    'images' => [
        'processing_phase' => env('MARKETPLACE_IMAGE_PHASE', 'phase2'),
        'watermark' => [
            'enabled' => false,
        ],
        'enhancement' => [
            'enabled' => false,
        ],
    ],
];
