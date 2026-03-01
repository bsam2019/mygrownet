<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Control feature rollout and A/B testing.
    | Set to true to enable, false to disable.
    |
    */

    'financial' => [
        /*
        | Use new domain-driven wallet service
        | 
        | When enabled, uses DomainWalletService instead of UnifiedWalletService
        | Phase 2: Parallel running and comparison
        */
        'use_domain_wallet_service' => env('FEATURE_DOMAIN_WALLET', false),

        /*
        | Compare old and new wallet services
        | 
        | When enabled, runs both services and logs any discrepancies
        | Use for validation during Phase 2
        */
        'compare_wallet_services' => env('FEATURE_COMPARE_WALLETS', false),

        /*
        | Enable transaction source tracking
        | 
        | When enabled, new transactions will include module source
        */
        'track_transaction_source' => env('FEATURE_TRACK_SOURCE', true),
    ],
];
