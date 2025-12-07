<?php

/**
 * GrowBiz Module Configuration
 * 
 * Centralized tier configuration for GrowBiz task/employee management module.
 * All tier limits, features, and pricing defined here.
 */

return [
    'id' => 'growbiz',
    'name' => 'GrowBiz',
    'description' => 'Task and employee management for small businesses',
    'category' => 'operations',
    
    'tiers' => [
        'free' => [
            'name' => 'Free',
            'description' => 'Get started with basic task management',
            'price_monthly' => 0,
            'price_annual' => 0,
            'limits' => [
                'tasks_per_month' => 25,
                'employees' => 3,
                'task_comments_per_task' => 10,
                'file_storage_mb' => 0,
                'task_templates' => 0,
                'projects' => 0,
            ],
            'features' => [
                'dashboard',
                'basic_tasks',
                'basic_employees',
                'email_notifications',
                'mobile_access',
            ],
        ],
        
        'basic' => [
            'name' => 'Basic',
            'description' => 'Essential tools for small teams',
            'price_monthly' => 79,
            'price_annual' => 758, // 20% discount
            'limits' => [
                'tasks_per_month' => -1, // unlimited
                'employees' => 10,
                'task_comments_per_task' => -1,
                'file_storage_mb' => 100,
                'task_templates' => 5,
                'projects' => 0,
            ],
            'features' => [
                'dashboard',
                'tasks',
                'employees',
                'time_tracking',
                'csv_export',
                'email_notifications',
                'task_templates',
                'mobile_access',
                'task_priorities',
                'task_categories',
            ],
        ],
        
        'professional' => [
            'name' => 'Professional',
            'description' => 'Advanced features for growing teams',
            'price_monthly' => 199,
            'price_annual' => 1910, // 20% discount
            'popular' => true,
            'limits' => [
                'tasks_per_month' => -1,
                'employees' => 25,
                'task_comments_per_task' => -1,
                'file_storage_mb' => 1024,
                'task_templates' => -1,
                'projects' => -1,
            ],
            'features' => [
                'dashboard',
                'tasks',
                'employees',
                'time_tracking',
                'csv_export',
                'pdf_export',
                'email_notifications',
                'task_templates',
                'projects',
                'gantt_charts',
                'task_dependencies',
                'custom_fields',
                'recurring_tasks',
                'integrations',
                'mobile_access',
                'task_priorities',
                'task_categories',
                'performance_reports',
            ],
        ],
        
        'business' => [
            'name' => 'Business',
            'description' => 'Complete solution for larger organizations',
            'price_monthly' => 399,
            'price_annual' => 3830, // 20% discount
            'limits' => [
                'tasks_per_month' => -1,
                'employees' => -1, // unlimited
                'task_comments_per_task' => -1,
                'file_storage_mb' => 5120,
                'task_templates' => -1,
                'projects' => -1,
            ],
            'features' => [
                'dashboard',
                'tasks',
                'employees',
                'time_tracking',
                'csv_export',
                'pdf_export',
                'email_notifications',
                'task_templates',
                'projects',
                'gantt_charts',
                'task_dependencies',
                'custom_fields',
                'recurring_tasks',
                'integrations',
                'multi_location',
                'advanced_analytics',
                'api_access',
                'white_label',
                'custom_integrations',
                'mobile_access',
                'task_priorities',
                'task_categories',
                'performance_reports',
                'audit_trail',
                'sso',
            ],
        ],
    ],
    
    // Usage metric definitions for this module
    'usage_metrics' => [
        'tasks_per_month' => [
            'label' => 'Tasks',
            'period' => 'monthly',
            'reset_day' => 1,
        ],
        'employees' => [
            'label' => 'Employees',
            'period' => 'lifetime',
        ],
        'task_comments_per_task' => [
            'label' => 'Comments per Task',
            'period' => 'per_resource',
        ],
        'file_storage_mb' => [
            'label' => 'File Storage',
            'period' => 'lifetime',
            'unit' => 'MB',
        ],
        'task_templates' => [
            'label' => 'Task Templates',
            'period' => 'lifetime',
        ],
        'projects' => [
            'label' => 'Projects',
            'period' => 'lifetime',
        ],
    ],
];
