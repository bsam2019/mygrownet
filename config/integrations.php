<?php

return [
    /*
    |--------------------------------------------------------------------------
    | GrowBuilder Integration
    |--------------------------------------------------------------------------
    |
    | Configuration for GrowBuilder e-commerce integration with CMS
    |
    */
    'growbuilder' => [
        'enabled' => env('GROWBUILDER_INTEGRATION_ENABLED', true),
        'auto_sync_products' => env('GROWBUILDER_AUTO_SYNC', true),
        'auto_create_invoices' => true,
        'sync_inventory' => true,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | GrowMarket Integration
    |--------------------------------------------------------------------------
    |
    | Configuration for GrowMarket marketplace integration with CMS
    |
    */
    'growmarket' => [
        'enabled' => env('GROWMARKET_INTEGRATION_ENABLED', true),
        'auto_sync_inventory' => true,
        'commission_rate' => env('GROWMARKET_COMMISSION_RATE', 10),
        'auto_approve_sellers' => false,
    ],
];
