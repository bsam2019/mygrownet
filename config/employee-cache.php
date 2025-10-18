<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Employee Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for the employee management system caching layer.
    | These settings control cache behavior, TTL values, and invalidation strategies.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Cache Enabled
    |--------------------------------------------------------------------------
    |
    | Whether employee caching is enabled. When disabled, the system will
    | fall back to direct database queries without caching.
    |
    */
    'enabled' => env('EMPLOYEE_CACHE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Cache Driver
    |--------------------------------------------------------------------------
    |
    | The cache driver to use for employee data. Defaults to the application's
    | default cache driver. Redis is recommended for production environments.
    |
    */
    'driver' => env('EMPLOYEE_CACHE_DRIVER', env('CACHE_DRIVER', 'file')),

    /*
    |--------------------------------------------------------------------------
    | Cache TTL (Time To Live) Settings
    |--------------------------------------------------------------------------
    |
    | TTL values in seconds for different types of cached data.
    | Adjust these based on your application's data update frequency.
    |
    */
    'ttl' => [
        'default' => env('EMPLOYEE_CACHE_TTL_DEFAULT', 3600), // 1 hour
        'long' => env('EMPLOYEE_CACHE_TTL_LONG', 86400), // 24 hours
        'short' => env('EMPLOYEE_CACHE_TTL_SHORT', 300), // 5 minutes
        
        // Specific data type TTLs
        'department_hierarchy' => env('EMPLOYEE_CACHE_TTL_DEPT_HIERARCHY', 86400), // 24 hours
        'organizational_chart' => env('EMPLOYEE_CACHE_TTL_ORG_CHART', 3600), // 1 hour
        'employee_statistics' => env('EMPLOYEE_CACHE_TTL_STATS', 300), // 5 minutes
        'performance_analytics' => env('EMPLOYEE_CACHE_TTL_PERFORMANCE', 3600), // 1 hour
        'commission_analytics' => env('EMPLOYEE_CACHE_TTL_COMMISSION', 3600), // 1 hour
        'payroll_data' => env('EMPLOYEE_CACHE_TTL_PAYROLL', 300), // 5 minutes
        'employee_profile' => env('EMPLOYEE_CACHE_TTL_PROFILE', 3600), // 1 hour
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Key Prefix
    |--------------------------------------------------------------------------
    |
    | Prefix for all employee cache keys to avoid conflicts with other
    | application caches.
    |
    */
    'prefix' => env('EMPLOYEE_CACHE_PREFIX', 'employee:'),

    /*
    |--------------------------------------------------------------------------
    | Cache Invalidation Strategy
    |--------------------------------------------------------------------------
    |
    | Configuration for cache invalidation behavior.
    |
    */
    'invalidation' => [
        // Whether to use event-driven cache invalidation
        'event_driven' => env('EMPLOYEE_CACHE_EVENT_INVALIDATION', true),
        
        // Whether to use pattern-based cache clearing (requires Redis)
        'pattern_clearing' => env('EMPLOYEE_CACHE_PATTERN_CLEARING', true),
        
        // Whether to log cache operations for debugging
        'log_operations' => env('EMPLOYEE_CACHE_LOG_OPERATIONS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Warming Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for cache warming operations.
    |
    */
    'warming' => [
        // Whether to automatically warm caches on application boot
        'auto_warm' => env('EMPLOYEE_CACHE_AUTO_WARM', false),
        
        // Data sets to warm up
        'warm_sets' => [
            'department_hierarchy' => true,
            'organizational_chart' => true,
            'employee_statistics' => true,
            'recent_performance' => true,
            'recent_commissions' => true,
            'current_payroll' => true,
        ],
        
        // Date ranges for analytics warming
        'analytics_range_months' => env('EMPLOYEE_CACHE_ANALYTICS_RANGE', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Monitoring
    |--------------------------------------------------------------------------
    |
    | Configuration for cache performance monitoring and metrics.
    |
    */
    'monitoring' => [
        // Whether to collect cache hit/miss statistics
        'collect_stats' => env('EMPLOYEE_CACHE_COLLECT_STATS', false),
        
        // Whether to log slow cache operations
        'log_slow_operations' => env('EMPLOYEE_CACHE_LOG_SLOW_OPS', false),
        
        // Threshold in milliseconds for slow operation logging
        'slow_operation_threshold' => env('EMPLOYEE_CACHE_SLOW_THRESHOLD', 100),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Size Limits
    |--------------------------------------------------------------------------
    |
    | Limits for cached data to prevent memory issues.
    |
    */
    'limits' => [
        // Maximum number of employees to cache in department lists
        'max_department_employees' => env('EMPLOYEE_CACHE_MAX_DEPT_EMPLOYEES', 1000),
        
        // Maximum number of records in analytics results
        'max_analytics_records' => env('EMPLOYEE_CACHE_MAX_ANALYTICS', 5000),
        
        // Maximum size of individual cache entries (in KB)
        'max_entry_size_kb' => env('EMPLOYEE_CACHE_MAX_ENTRY_SIZE', 1024),
    ],

    /*
    |--------------------------------------------------------------------------
    | Development Settings
    |--------------------------------------------------------------------------
    |
    | Settings specific to development environments.
    |
    */
    'development' => [
        // Whether to disable caching in local environment
        'disable_in_local' => env('EMPLOYEE_CACHE_DISABLE_LOCAL', false),
        
        // Whether to use shorter TTLs in development
        'short_ttl_in_dev' => env('EMPLOYEE_CACHE_SHORT_TTL_DEV', true),
        
        // Development TTL multiplier (e.g., 0.1 = 10% of production TTL)
        'dev_ttl_multiplier' => env('EMPLOYEE_CACHE_DEV_TTL_MULTIPLIER', 0.1),
    ],
];