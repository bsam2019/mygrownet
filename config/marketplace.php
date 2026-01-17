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
    | Payout Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for seller payouts and withdrawals
    |
    */
    'payouts' => [
        // Minimum payout amount in ngwee (K50 = 5000 ngwee)
        'minimum_amount' => 5000,
        
        // Processing time in business days
        'processing_days' => 2,
        
        // Auto-approve threshold (payouts below this are auto-approved)
        // Set to 0 to disable auto-approval
        'auto_approve_threshold' => 0, // Disabled by default
        
        // Available payout methods
        'methods' => [
            'momo' => [
                'enabled' => true,
                'label' => 'MTN Mobile Money',
                'fee' => 0, // No additional fee
            ],
            'airtel' => [
                'enabled' => true,
                'label' => 'Airtel Money',
                'fee' => 0,
            ],
            'bank' => [
                'enabled' => true,
                'label' => 'Bank Transfer',
                'fee' => 0,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Escrow Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for escrow fund management
    |
    */
    'escrow' => [
        // Auto-release funds X days after delivery if no dispute
        'auto_release_days' => 7,
        
        // Hold funds during dispute resolution
        'dispute_hold_days' => 14,
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
