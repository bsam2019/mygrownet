<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Employee Management Monitoring Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration file defines monitoring and logging settings
    | specifically for the employee management system operations.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Configure logging channels and levels for employee operations
    |
    */
    'logging' => [
        'channels' => [
            'employee_operations' => [
                'driver' => 'daily',
                'path' => storage_path('logs/employee-operations.log'),
                'level' => env('EMPLOYEE_LOG_LEVEL', 'info'),
                'days' => 30,
                'permission' => 0664,
            ],
            
            'employee_performance' => [
                'driver' => 'daily',
                'path' => storage_path('logs/employee-performance.log'),
                'level' => env('EMPLOYEE_PERFORMANCE_LOG_LEVEL', 'info'),
                'days' => 90,
                'permission' => 0664,
            ],
            
            'employee_commissions' => [
                'driver' => 'daily',
                'path' => storage_path('logs/employee-commissions.log'),
                'level' => env('EMPLOYEE_COMMISSION_LOG_LEVEL', 'info'),
                'days' => 365, // Keep commission logs for a full year
                'permission' => 0664,
            ],
            
            'employee_security' => [
                'driver' => 'daily',
                'path' => storage_path('logs/employee-security.log'),
                'level' => env('EMPLOYEE_SECURITY_LOG_LEVEL', 'warning'),
                'days' => 365,
                'permission' => 0664,
            ],
            
            'employee_audit' => [
                'driver' => 'daily',
                'path' => storage_path('logs/employee-audit.log'),
                'level' => 'info',
                'days' => 2555, // 7 years for compliance
                'permission' => 0664,
            ],
        ],
        
        'events_to_log' => [
            // Employee lifecycle events
            'employee_created' => 'info',
            'employee_updated' => 'info',
            'employee_terminated' => 'warning',
            'employee_promoted' => 'info',
            'employee_hired' => 'info',
            
            // Performance events
            'performance_review_created' => 'info',
            'performance_review_updated' => 'info',
            'performance_goals_set' => 'info',
            'performance_metrics_calculated' => 'debug',
            
            // Commission events
            'commission_calculated' => 'info',
            'commission_approved' => 'info',
            'commission_paid' => 'info',
            'commission_disputed' => 'warning',
            'payroll_processed' => 'info',
            
            // Security events
            'unauthorized_employee_access' => 'error',
            'employee_data_export' => 'warning',
            'employee_permission_changed' => 'warning',
            'employee_login_failed' => 'warning',
            
            // System events
            'employee_cache_cleared' => 'info',
            'employee_backup_created' => 'info',
            'employee_migration_run' => 'info',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Monitoring
    |--------------------------------------------------------------------------
    |
    | Configure performance monitoring thresholds and alerts
    |
    */
    'performance' => [
        'query_time_threshold' => env('EMPLOYEE_QUERY_TIME_THRESHOLD', 1000), // milliseconds
        'memory_usage_threshold' => env('EMPLOYEE_MEMORY_THRESHOLD', 128), // MB
        'cache_hit_ratio_threshold' => env('EMPLOYEE_CACHE_HIT_THRESHOLD', 0.8), // 80%
        
        'slow_operations' => [
            'employee_search' => 2000, // 2 seconds
            'performance_calculation' => 5000, // 5 seconds
            'commission_calculation' => 3000, // 3 seconds
            'payroll_processing' => 30000, // 30 seconds
            'department_hierarchy_load' => 1000, // 1 second
        ],
        
        'monitoring_enabled' => env('EMPLOYEE_PERFORMANCE_MONITORING', true),
        'detailed_profiling' => env('EMPLOYEE_DETAILED_PROFILING', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Monitoring
    |--------------------------------------------------------------------------
    |
    | Configure security monitoring and alerting
    |
    */
    'security' => [
        'failed_login_threshold' => 5,
        'failed_login_window' => 300, // 5 minutes
        'suspicious_activity_threshold' => 10,
        'data_export_alert' => true,
        'permission_change_alert' => true,
        
        'monitored_actions' => [
            'employee_data_access',
            'employee_data_modification',
            'employee_data_export',
            'employee_permission_changes',
            'employee_role_assignments',
            'commission_calculations',
            'payroll_access',
        ],
        
        'ip_whitelist' => env('EMPLOYEE_IP_WHITELIST', ''),
        'require_2fa_for_sensitive_operations' => env('EMPLOYEE_REQUIRE_2FA', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Business Metrics Monitoring
    |--------------------------------------------------------------------------
    |
    | Configure business metrics and KPI monitoring
    |
    */
    'business_metrics' => [
        'track_employee_productivity' => true,
        'track_commission_accuracy' => true,
        'track_performance_trends' => true,
        'track_department_efficiency' => true,
        
        'productivity_metrics' => [
            'investments_facilitated_per_agent',
            'client_retention_rate',
            'commission_generation_rate',
            'goal_achievement_percentage',
            'client_satisfaction_score',
        ],
        
        'commission_metrics' => [
            'calculation_accuracy',
            'payment_timeliness',
            'dispute_rate',
            'total_commissions_paid',
            'commission_to_investment_ratio',
        ],
        
        'performance_metrics' => [
            'review_completion_rate',
            'goal_setting_frequency',
            'improvement_plan_success_rate',
            'promotion_rate',
            'retention_rate',
        ],
        
        'alert_thresholds' => [
            'low_productivity' => 0.7, // 70% of expected
            'high_commission_dispute_rate' => 0.05, // 5%
            'low_goal_achievement' => 0.6, // 60%
            'high_turnover_rate' => 0.15, // 15% annually
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Health Checks
    |--------------------------------------------------------------------------
    |
    | Configure health check endpoints and monitoring
    |
    */
    'health_checks' => [
        'enabled' => env('EMPLOYEE_HEALTH_CHECKS', true),
        'endpoint' => '/health/employee-management',
        
        'checks' => [
            'database_connectivity' => [
                'enabled' => true,
                'timeout' => 5,
                'critical' => true,
            ],
            
            'employee_tables_accessible' => [
                'enabled' => true,
                'tables' => ['employees', 'departments', 'positions'],
                'critical' => true,
            ],
            
            'cache_connectivity' => [
                'enabled' => true,
                'timeout' => 3,
                'critical' => false,
            ],
            
            'commission_calculation_service' => [
                'enabled' => true,
                'timeout' => 10,
                'critical' => true,
            ],
            
            'performance_tracking_service' => [
                'enabled' => true,
                'timeout' => 5,
                'critical' => false,
            ],
            
            'employee_permissions' => [
                'enabled' => true,
                'required_permissions' => [
                    'view_employees',
                    'create_employees',
                    'view_commissions',
                ],
                'critical' => true,
            ],
        ],
        
        'notification' => [
            'enabled' => env('EMPLOYEE_HEALTH_NOTIFICATIONS', false),
            'channels' => ['mail', 'slack'],
            'recipients' => [
                'admin@vbif.com',
                'hr@vbif.com',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Alerting Configuration
    |--------------------------------------------------------------------------
    |
    | Configure alerting rules and notification channels
    |
    */
    'alerting' => [
        'enabled' => env('EMPLOYEE_ALERTING_ENABLED', true),
        
        'channels' => [
            'email' => [
                'enabled' => true,
                'recipients' => explode(',', env('EMPLOYEE_ALERT_EMAILS', 'admin@vbif.com')),
            ],
            
            'slack' => [
                'enabled' => env('EMPLOYEE_SLACK_ALERTS', false),
                'webhook_url' => env('EMPLOYEE_SLACK_WEBHOOK'),
                'channel' => env('EMPLOYEE_SLACK_CHANNEL', '#employee-alerts'),
            ],
            
            'database' => [
                'enabled' => true,
                'table' => 'employee_alerts',
                'retention_days' => 90,
            ],
        ],
        
        'rules' => [
            'high_error_rate' => [
                'threshold' => 10, // errors per minute
                'window' => 300, // 5 minutes
                'severity' => 'critical',
            ],
            
            'slow_performance' => [
                'threshold' => 5000, // 5 seconds
                'window' => 600, // 10 minutes
                'severity' => 'warning',
            ],
            
            'failed_commission_calculations' => [
                'threshold' => 3,
                'window' => 3600, // 1 hour
                'severity' => 'critical',
            ],
            
            'unauthorized_access_attempts' => [
                'threshold' => 5,
                'window' => 300, // 5 minutes
                'severity' => 'critical',
            ],
            
            'cache_miss_rate_high' => [
                'threshold' => 0.5, // 50% miss rate
                'window' => 1800, // 30 minutes
                'severity' => 'warning',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Retention
    |--------------------------------------------------------------------------
    |
    | Configure data retention policies for monitoring data
    |
    */
    'data_retention' => [
        'performance_metrics' => 365, // days
        'security_logs' => 2555, // 7 years
        'business_metrics' => 1095, // 3 years
        'health_check_results' => 90, // days
        'alert_history' => 365, // days
        
        'cleanup_schedule' => '0 2 * * 0', // Weekly at 2 AM on Sunday
        'cleanup_enabled' => env('EMPLOYEE_CLEANUP_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Integration Settings
    |--------------------------------------------------------------------------
    |
    | Configure integration with external monitoring services
    |
    */
    'integrations' => [
        'new_relic' => [
            'enabled' => env('NEW_RELIC_ENABLED', false),
            'app_name' => env('NEW_RELIC_APP_NAME', 'VBIF Employee Management'),
            'license_key' => env('NEW_RELIC_LICENSE_KEY'),
        ],
        
        'datadog' => [
            'enabled' => env('DATADOG_ENABLED', false),
            'api_key' => env('DATADOG_API_KEY'),
            'app_key' => env('DATADOG_APP_KEY'),
            'service_name' => 'vbif-employee-management',
        ],
        
        'sentry' => [
            'enabled' => env('SENTRY_LARAVEL_DSN', false) !== false,
            'dsn' => env('SENTRY_LARAVEL_DSN'),
            'environment' => env('APP_ENV', 'production'),
            'release' => env('APP_VERSION', '1.0.0'),
        ],
    ],
];