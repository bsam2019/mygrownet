<?php

/**
 * Reward System V2.0 Configuration (REFERENCE ONLY)
 * 
 * This file contains reference configuration for the 4-tier starter kit system.
 * The actual implementation uses the existing LGR V1.0 system enhanced for 4 tiers.
 * 
 * See: docs/rewards/REWARD_SYSTEM_CURRENT_IMPLEMENTATION.md for specifications
 * See: docs/rewards/LGR_MIGRATION_DECISION.md for implementation approach
 * 
 * IMPORTANT: This config is NOT actively used by the application.
 * Tier rates are defined in:
 * - app/Domain/LoyaltyReward/Entities/LgrCycle.php (daily rates)
 * - app/Services/StarterKitService.php (prices, shop credits)
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Starter Kit Tiers Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the 4 starter kit tiers including pricing, LGR
    | multipliers, cycle durations, and daily caps.
    |
    */
    'starter_kits' => [
        'lite' => [
            'name' => 'Lite Knowledge Pack',
            'price' => 300.00,
            'lp_award' => 15,
            'lgr_multiplier' => 0.5,
            'cycle_days' => 30,
            'daily_cap' => 5.00,
            'shop_credit' => 50.00,
            'description' => 'Entry-level pack with 2 eBooks and 2 basic tools',
        ],
        'basic' => [
            'name' => 'Basic Growth Pack',
            'price' => 500.00,
            'lp_award' => 30,
            'lgr_multiplier' => 1.0,
            'cycle_days' => 50,
            'daily_cap' => 10.00,
            'shop_credit' => 100.00,
            'description' => 'Standard pack with 5 eBooks and 4 tools',
        ],
        'growth_plus' => [
            'name' => 'Growth Plus Pack',
            'price' => 1000.00,
            'lp_award' => 60,
            'lgr_multiplier' => 1.5,
            'cycle_days' => 70,
            'daily_cap' => 20.00,
            'shop_credit' => 200.00,
            'description' => 'Advanced pack with 10 eBooks and full tools bundle',
        ],
        'pro' => [
            'name' => 'Pro Access Pack',
            'price' => 2000.00,
            'lp_award' => 120,
            'lgr_multiplier' => 2.5,
            'cycle_days' => 90,
            'daily_cap' => 40.00,
            'shop_credit' => 400.00,
            'description' => 'Premium pack with full digital library and all tools',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Split Configuration
    |--------------------------------------------------------------------------
    |
    | How starter kit payments are divided between pools and company revenue.
    |
    */
    'payment_split' => [
        'lgr_pool' => 0.30,        // 30% to LGR Pool
        'referral_pool' => 0.20,   // 20% to Referral Pool
        'company' => 0.50,         // 50% to Company Revenue
    ],

    /*
    |--------------------------------------------------------------------------
    | Commission Configuration
    |--------------------------------------------------------------------------
    |
    | Commission calculation settings.
    |
    */
    'commission' => [
        'base_percentage' => 0.50,      // Commissions calculated on 50% of purchase
        'non_kit_multiplier' => 0.50,   // Members without kit earn 50% of commissions
    ],

    /*
    |--------------------------------------------------------------------------
    | LGR Activity Points
    |--------------------------------------------------------------------------
    |
    | BP awarded for different activities that qualify for LGR.
    |
    */
    'lgr_activities' => [
        'read_book_section' => 2,
        'use_tool' => 2,
        'complete_daily_task' => 3,
        'finish_lesson' => 3,
        'submit_progress_log' => 4,
        'complete_course_module' => 5,
        'refer_active_member' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Renewal Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for LGR cycle renewal.
    |
    */
    'renewal' => [
        'grace_period_days' => 7,
        'activity_threshold' => 0.80,  // 80% activity completion for discounted renewal
        
        'fees' => [
            'lite' => [
                'standard' => 100.00,
                'activity_based' => 60.00,
            ],
            'basic' => [
                'standard' => 150.00,
                'activity_based' => 100.00,
            ],
            'growth_plus' => [
                'standard' => 300.00,
                'activity_based' => 200.00,
            ],
            'pro' => [
                'standard' => 500.00,
                'activity_based' => 400.00,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Monthly BP Requirements
    |--------------------------------------------------------------------------
    |
    | Reduced BP requirements for monthly qualification (50% reduction).
    |
    */
    'monthly_bp_requirements' => [
        'associate' => 50,
        'professional' => 100,
        'senior' => 150,
        'manager' => 200,
        'director' => 250,
        'executive' => 300,
        'ambassador' => 400,
    ],
];
