<?php

namespace App\Domain\Employee\Constants;

/**
 * Delegated Permissions - Admin functions that can be delegated to employees
 * 
 * These permissions allow admins to delegate specific administrative tasks
 * to employees through the Workspace portal, with optional approval workflows.
 */
class DelegatedPermissions
{
    // ============================================
    // SUPPORT & COMMUNICATION (Safe to Delegate)
    // ============================================
    
    // Support Center - Primary employee function
    public const HANDLE_SUPPORT_TICKETS = 'delegated.support.handle_tickets';
    public const VIEW_SUPPORT_TICKETS = 'delegated.support.view_tickets';
    public const RESPOND_SUPPORT_TICKETS = 'delegated.support.respond_tickets';
    public const ESCALATE_SUPPORT_TICKETS = 'delegated.support.escalate_tickets';
    
    // Messages - Member communication
    public const VIEW_MESSAGES = 'delegated.messages.view';
    public const SEND_MESSAGES = 'delegated.messages.send';
    public const COMPOSE_MESSAGES = 'delegated.messages.compose';
    
    // ============================================
    // FINANCE (Delegate with Restrictions)
    // ============================================
    
    // Receipts - View only for support reference
    public const VIEW_RECEIPTS = 'delegated.finance.view_receipts';
    public const DOWNLOAD_RECEIPTS = 'delegated.finance.download_receipts';
    public const RESEND_RECEIPTS = 'delegated.finance.resend_receipts';
    
    // Payment Processing - Requires approval
    public const VIEW_PAYMENTS = 'delegated.finance.view_payments';
    public const PROCESS_PAYMENTS = 'delegated.finance.process_payments';
    public const APPROVE_PAYMENTS = 'delegated.finance.approve_payments'; // Manager only
    
    // Withdrawals - Requires approval
    public const VIEW_WITHDRAWALS = 'delegated.finance.view_withdrawals';
    public const PROCESS_WITHDRAWALS = 'delegated.finance.process_withdrawals';
    public const APPROVE_WITHDRAWALS = 'delegated.finance.approve_withdrawals'; // Manager only
    
    // ============================================
    // USER MANAGEMENT (Delegate with Restrictions)
    // ============================================
    
    // Users - Limited access
    public const VIEW_USERS = 'delegated.users.view';
    public const EDIT_USER_BASIC_INFO = 'delegated.users.edit_basic';
    public const VIEW_USER_SUBSCRIPTIONS = 'delegated.users.view_subscriptions';
    public const PROCESS_SUBSCRIPTIONS = 'delegated.users.process_subscriptions';
    
    // ============================================
    // INVESTOR RELATIONS (Delegate with Restrictions)
    // ============================================
    
    public const VIEW_INVESTOR_MESSAGES = 'delegated.investors.view_messages';
    public const RESPOND_INVESTOR_MESSAGES = 'delegated.investors.respond_messages';
    public const VIEW_INVESTOR_DOCUMENTS = 'delegated.investors.view_documents';
    public const UPLOAD_INVESTOR_DOCUMENTS = 'delegated.investors.upload_documents';
    
    // ============================================
    // ANALYTICS & REPORTS (Read-Only)
    // ============================================
    
    public const VIEW_MEMBER_ANALYTICS = 'delegated.analytics.members';
    public const VIEW_REWARD_ANALYTICS = 'delegated.analytics.rewards';
    public const VIEW_POINTS_ANALYTICS = 'delegated.analytics.points';
    public const VIEW_FINANCIAL_REPORTS = 'delegated.analytics.financial';
    public const EXPORT_REPORTS = 'delegated.analytics.export';
    
    // ============================================
    // BUSINESS GROWTH FUND (Delegate with Restrictions)
    // ============================================
    
    public const VIEW_BGF_APPLICATIONS = 'delegated.bgf.view_applications';
    public const REVIEW_BGF_APPLICATIONS = 'delegated.bgf.review_applications';
    public const RECOMMEND_BGF_APPLICATIONS = 'delegated.bgf.recommend_applications';
    public const VIEW_BGF_DISBURSEMENTS = 'delegated.bgf.view_disbursements';
    public const PROCESS_BGF_DISBURSEMENTS = 'delegated.bgf.process_disbursements';
    
    // ============================================
    // TASK MANAGEMENT (Team Leads)
    // ============================================
    
    public const VIEW_ALL_TASKS = 'delegated.tasks.view_all';
    public const ASSIGN_TASKS = 'delegated.tasks.assign';
    public const MANAGE_TEAM_TASKS = 'delegated.tasks.manage_team';
    
    /**
     * Get all delegatable permissions
     */
    public static function getAllPermissions(): array
    {
        return [
            // Support
            self::HANDLE_SUPPORT_TICKETS,
            self::VIEW_SUPPORT_TICKETS,
            self::RESPOND_SUPPORT_TICKETS,
            self::ESCALATE_SUPPORT_TICKETS,
            
            // Messages
            self::VIEW_MESSAGES,
            self::SEND_MESSAGES,
            self::COMPOSE_MESSAGES,
            
            // Finance
            self::VIEW_RECEIPTS,
            self::DOWNLOAD_RECEIPTS,
            self::RESEND_RECEIPTS,
            self::VIEW_PAYMENTS,
            self::PROCESS_PAYMENTS,
            self::APPROVE_PAYMENTS,
            self::VIEW_WITHDRAWALS,
            self::PROCESS_WITHDRAWALS,
            self::APPROVE_WITHDRAWALS,
            
            // Users
            self::VIEW_USERS,
            self::EDIT_USER_BASIC_INFO,
            self::VIEW_USER_SUBSCRIPTIONS,
            self::PROCESS_SUBSCRIPTIONS,
            
            // Investors
            self::VIEW_INVESTOR_MESSAGES,
            self::RESPOND_INVESTOR_MESSAGES,
            self::VIEW_INVESTOR_DOCUMENTS,
            self::UPLOAD_INVESTOR_DOCUMENTS,
            
            // Analytics
            self::VIEW_MEMBER_ANALYTICS,
            self::VIEW_REWARD_ANALYTICS,
            self::VIEW_POINTS_ANALYTICS,
            self::VIEW_FINANCIAL_REPORTS,
            self::EXPORT_REPORTS,
            
            // BGF
            self::VIEW_BGF_APPLICATIONS,
            self::REVIEW_BGF_APPLICATIONS,
            self::RECOMMEND_BGF_APPLICATIONS,
            self::VIEW_BGF_DISBURSEMENTS,
            self::PROCESS_BGF_DISBURSEMENTS,
            
            // Tasks
            self::VIEW_ALL_TASKS,
            self::ASSIGN_TASKS,
            self::MANAGE_TEAM_TASKS,
        ];
    }
    
    /**
     * Get permissions grouped by category with metadata
     */
    public static function getPermissionsByCategory(): array
    {
        return [
            'Support & Communication' => [
                'description' => 'Handle member and investor support tickets',
                'risk_level' => 'low',
                'permissions' => [
                    self::HANDLE_SUPPORT_TICKETS => [
                        'name' => 'Handle Support Tickets',
                        'description' => 'Full access to support ticket management',
                        'requires_approval' => false,
                    ],
                    self::VIEW_SUPPORT_TICKETS => [
                        'name' => 'View Support Tickets',
                        'description' => 'View all support tickets',
                        'requires_approval' => false,
                    ],
                    self::RESPOND_SUPPORT_TICKETS => [
                        'name' => 'Respond to Tickets',
                        'description' => 'Reply to support tickets',
                        'requires_approval' => false,
                    ],
                    self::ESCALATE_SUPPORT_TICKETS => [
                        'name' => 'Escalate Tickets',
                        'description' => 'Escalate tickets to managers',
                        'requires_approval' => false,
                    ],
                ],
            ],
            'Messages' => [
                'description' => 'Member communication and messaging',
                'risk_level' => 'low',
                'permissions' => [
                    self::VIEW_MESSAGES => [
                        'name' => 'View Messages',
                        'description' => 'View member messages',
                        'requires_approval' => false,
                    ],
                    self::SEND_MESSAGES => [
                        'name' => 'Send Messages',
                        'description' => 'Send messages to members',
                        'requires_approval' => false,
                    ],
                    self::COMPOSE_MESSAGES => [
                        'name' => 'Compose Messages',
                        'description' => 'Create new messages',
                        'requires_approval' => false,
                    ],
                ],
            ],
            'Finance - Receipts' => [
                'description' => 'View and manage payment receipts',
                'risk_level' => 'low',
                'permissions' => [
                    self::VIEW_RECEIPTS => [
                        'name' => 'View Receipts',
                        'description' => 'View payment receipts',
                        'requires_approval' => false,
                    ],
                    self::DOWNLOAD_RECEIPTS => [
                        'name' => 'Download Receipts',
                        'description' => 'Download receipt PDFs',
                        'requires_approval' => false,
                    ],
                    self::RESEND_RECEIPTS => [
                        'name' => 'Resend Receipts',
                        'description' => 'Resend receipts to members',
                        'requires_approval' => false,
                    ],
                ],
            ],
            'Finance - Payments' => [
                'description' => 'Payment processing and approvals',
                'risk_level' => 'medium',
                'permissions' => [
                    self::VIEW_PAYMENTS => [
                        'name' => 'View Payments',
                        'description' => 'View payment records',
                        'requires_approval' => false,
                    ],
                    self::PROCESS_PAYMENTS => [
                        'name' => 'Process Payments',
                        'description' => 'Process payment requests',
                        'requires_approval' => true,
                    ],
                    self::APPROVE_PAYMENTS => [
                        'name' => 'Approve Payments',
                        'description' => 'Final approval for payments',
                        'requires_approval' => false,
                        'manager_only' => true,
                    ],
                ],
            ],
            'Finance - Withdrawals' => [
                'description' => 'Withdrawal processing and approvals',
                'risk_level' => 'high',
                'permissions' => [
                    self::VIEW_WITHDRAWALS => [
                        'name' => 'View Withdrawals',
                        'description' => 'View withdrawal requests',
                        'requires_approval' => false,
                    ],
                    self::PROCESS_WITHDRAWALS => [
                        'name' => 'Process Withdrawals',
                        'description' => 'Process withdrawal requests',
                        'requires_approval' => true,
                    ],
                    self::APPROVE_WITHDRAWALS => [
                        'name' => 'Approve Withdrawals',
                        'description' => 'Final approval for withdrawals',
                        'requires_approval' => false,
                        'manager_only' => true,
                    ],
                ],
            ],
            'User Management' => [
                'description' => 'View and manage user accounts',
                'risk_level' => 'medium',
                'permissions' => [
                    self::VIEW_USERS => [
                        'name' => 'View Users',
                        'description' => 'View user profiles',
                        'requires_approval' => false,
                    ],
                    self::EDIT_USER_BASIC_INFO => [
                        'name' => 'Edit Basic Info',
                        'description' => 'Edit user contact information',
                        'requires_approval' => false,
                    ],
                    self::VIEW_USER_SUBSCRIPTIONS => [
                        'name' => 'View Subscriptions',
                        'description' => 'View user subscriptions',
                        'requires_approval' => false,
                    ],
                    self::PROCESS_SUBSCRIPTIONS => [
                        'name' => 'Process Subscriptions',
                        'description' => 'Process subscription changes',
                        'requires_approval' => true,
                    ],
                ],
            ],
            'Investor Relations' => [
                'description' => 'Investor communication and documents',
                'risk_level' => 'medium',
                'permissions' => [
                    self::VIEW_INVESTOR_MESSAGES => [
                        'name' => 'View Investor Messages',
                        'description' => 'View investor communications',
                        'requires_approval' => false,
                    ],
                    self::RESPOND_INVESTOR_MESSAGES => [
                        'name' => 'Respond to Investors',
                        'description' => 'Reply to investor messages',
                        'requires_approval' => false,
                    ],
                    self::VIEW_INVESTOR_DOCUMENTS => [
                        'name' => 'View Investor Documents',
                        'description' => 'View investor documents',
                        'requires_approval' => false,
                    ],
                    self::UPLOAD_INVESTOR_DOCUMENTS => [
                        'name' => 'Upload Documents',
                        'description' => 'Upload investor documents',
                        'requires_approval' => false,
                    ],
                ],
            ],
            'Analytics & Reports' => [
                'description' => 'View analytics and generate reports',
                'risk_level' => 'low',
                'permissions' => [
                    self::VIEW_MEMBER_ANALYTICS => [
                        'name' => 'Member Analytics',
                        'description' => 'View member statistics',
                        'requires_approval' => false,
                    ],
                    self::VIEW_REWARD_ANALYTICS => [
                        'name' => 'Reward Analytics',
                        'description' => 'View reward statistics',
                        'requires_approval' => false,
                    ],
                    self::VIEW_POINTS_ANALYTICS => [
                        'name' => 'Points Analytics',
                        'description' => 'View points statistics',
                        'requires_approval' => false,
                    ],
                    self::VIEW_FINANCIAL_REPORTS => [
                        'name' => 'Financial Reports',
                        'description' => 'View financial reports',
                        'requires_approval' => false,
                    ],
                    self::EXPORT_REPORTS => [
                        'name' => 'Export Reports',
                        'description' => 'Export report data',
                        'requires_approval' => false,
                    ],
                ],
            ],
            'Business Growth Fund' => [
                'description' => 'BGF application processing',
                'risk_level' => 'medium',
                'permissions' => [
                    self::VIEW_BGF_APPLICATIONS => [
                        'name' => 'View Applications',
                        'description' => 'View BGF applications',
                        'requires_approval' => false,
                    ],
                    self::REVIEW_BGF_APPLICATIONS => [
                        'name' => 'Review Applications',
                        'description' => 'Review and assess applications',
                        'requires_approval' => false,
                    ],
                    self::RECOMMEND_BGF_APPLICATIONS => [
                        'name' => 'Recommend Applications',
                        'description' => 'Make recommendations on applications',
                        'requires_approval' => false,
                    ],
                    self::VIEW_BGF_DISBURSEMENTS => [
                        'name' => 'View Disbursements',
                        'description' => 'View BGF disbursements',
                        'requires_approval' => false,
                    ],
                    self::PROCESS_BGF_DISBURSEMENTS => [
                        'name' => 'Process Disbursements',
                        'description' => 'Process BGF disbursements',
                        'requires_approval' => true,
                    ],
                ],
            ],
            'Task Management' => [
                'description' => 'Team task management',
                'risk_level' => 'low',
                'permissions' => [
                    self::VIEW_ALL_TASKS => [
                        'name' => 'View All Tasks',
                        'description' => 'View all team tasks',
                        'requires_approval' => false,
                    ],
                    self::ASSIGN_TASKS => [
                        'name' => 'Assign Tasks',
                        'description' => 'Assign tasks to team members',
                        'requires_approval' => false,
                    ],
                    self::MANAGE_TEAM_TASKS => [
                        'name' => 'Manage Team Tasks',
                        'description' => 'Full task management for team',
                        'requires_approval' => false,
                    ],
                ],
            ],
        ];
    }
    
    /**
     * Get recommended permission sets for common roles
     */
    public static function getRecommendedSets(): array
    {
        return [
            'Support Agent' => [
                'description' => 'Customer support representative',
                'permissions' => [
                    self::HANDLE_SUPPORT_TICKETS,
                    self::VIEW_SUPPORT_TICKETS,
                    self::RESPOND_SUPPORT_TICKETS,
                    self::ESCALATE_SUPPORT_TICKETS,
                    self::VIEW_MESSAGES,
                    self::SEND_MESSAGES,
                    self::VIEW_RECEIPTS,
                    self::VIEW_USERS,
                ],
            ],
            'Finance Assistant' => [
                'description' => 'Finance team member',
                'permissions' => [
                    self::VIEW_RECEIPTS,
                    self::DOWNLOAD_RECEIPTS,
                    self::RESEND_RECEIPTS,
                    self::VIEW_PAYMENTS,
                    self::VIEW_WITHDRAWALS,
                    self::VIEW_FINANCIAL_REPORTS,
                ],
            ],
            'Team Lead' => [
                'description' => 'Department team leader',
                'permissions' => [
                    self::VIEW_ALL_TASKS,
                    self::ASSIGN_TASKS,
                    self::MANAGE_TEAM_TASKS,
                    self::VIEW_MEMBER_ANALYTICS,
                ],
            ],
            'Investor Relations' => [
                'description' => 'Investor communication specialist',
                'permissions' => [
                    self::VIEW_INVESTOR_MESSAGES,
                    self::RESPOND_INVESTOR_MESSAGES,
                    self::VIEW_INVESTOR_DOCUMENTS,
                    self::UPLOAD_INVESTOR_DOCUMENTS,
                ],
            ],
            'BGF Analyst' => [
                'description' => 'Business Growth Fund analyst',
                'permissions' => [
                    self::VIEW_BGF_APPLICATIONS,
                    self::REVIEW_BGF_APPLICATIONS,
                    self::RECOMMEND_BGF_APPLICATIONS,
                    self::VIEW_BGF_DISBURSEMENTS,
                ],
            ],
        ];
    }
}
