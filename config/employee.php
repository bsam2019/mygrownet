<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Employee Management Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for the employee management system
    |
    */

    'defaults' => [
        'employment_status' => 'active',
        'probation_period_months' => 6,
        'annual_leave_days' => 21,
        'sick_leave_days' => 10,
        'maternity_leave_days' => 90,
        'paternity_leave_days' => 14,
    ],

    'validation' => [
        'min_age' => 16,
        'max_age' => 80,
        'max_salary' => 1000000,
        'employee_number_prefix' => 'EMP',
        'employee_number_length' => 6,
    ],

    'performance' => [
        'review_frequency_months' => 6,
        'rating_scale' => [
            'min' => 1,
            'max' => 5,
        ],
        'performance_categories' => [
            'job_knowledge',
            'quality_of_work',
            'productivity',
            'communication',
            'teamwork',
            'leadership',
            'initiative',
            'reliability',
        ],
    ],

    'commission' => [
        'default_rate' => 2.5,
        'max_rate' => 15.0,
        'calculation_frequency' => 'monthly',
        'payment_delay_days' => 30,
        'minimum_payout' => 100,
    ],

    'notifications' => [
        'probation_end_reminder_days' => 30,
        'performance_review_reminder_days' => 14,
        'birthday_notifications' => true,
        'work_anniversary_notifications' => true,
    ],

    'security' => [
        'data_retention_years' => 7,
        'audit_log_retention_months' => 24,
        'sensitive_fields' => [
            'national_id',
            'current_salary',
            'emergency_contacts',
            'notes',
        ],
        'require_approval_for' => [
            'salary_changes_above_percent' => 20,
            'department_transfers' => true,
            'position_changes' => true,
            'manager_assignments' => true,
        ],
    ],

    'reporting' => [
        'cache_duration_minutes' => 15,
        'export_formats' => ['csv', 'excel', 'pdf'],
        'max_export_records' => 10000,
        'dashboard_refresh_interval_seconds' => 300,
    ],

    'integration' => [
        'sync_with_payroll' => env('EMPLOYEE_SYNC_PAYROLL', false),
        'sync_with_hr_system' => env('EMPLOYEE_SYNC_HR', false),
        'external_api_timeout' => 30,
        'webhook_endpoints' => [
            'employee_created' => env('WEBHOOK_EMPLOYEE_CREATED'),
            'employee_updated' => env('WEBHOOK_EMPLOYEE_UPDATED'),
            'employee_terminated' => env('WEBHOOK_EMPLOYEE_TERMINATED'),
        ],
    ],

    'features' => [
        'enable_self_service' => env('EMPLOYEE_SELF_SERVICE', true),
        'enable_manager_dashboard' => env('EMPLOYEE_MANAGER_DASHBOARD', true),
        'enable_performance_tracking' => env('EMPLOYEE_PERFORMANCE_TRACKING', true),
        'enable_commission_calculation' => env('EMPLOYEE_COMMISSION_CALC', true),
        'enable_document_management' => env('EMPLOYEE_DOCUMENT_MGMT', false),
        'enable_time_tracking' => env('EMPLOYEE_TIME_TRACKING', false),
    ],
];