<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Memory Management Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for memory management and optimization settings
    |
    */

    // Default memory limit for web requests (256MB)
    'web_memory_limit' => '256M',
    
    // Memory limit for console commands (512MB)
    'console_memory_limit' => '512M',
    
    // Memory limit for heavy operations like seeding (1GB)
    'seeding_memory_limit' => '1G',
    
    // Chunk sizes for batch operations
    'chunk_sizes' => [
        'users' => 20,
        'investments' => 50,
        'team_volumes' => 25,
        'allocations' => 25,
    ],
    
    // Cache settings for frequently accessed data
    'cache_ttl' => [
        'user_auth_data' => 300, // 5 minutes
        'roles_permissions' => 3600, // 1 hour
        'investment_tiers' => 7200, // 2 hours
    ],
];