<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            // Wallet notifications
            [
                'type' => 'wallet.topup.received',
                'category' => 'wallet',
                'name' => 'Wallet Top-up Received',
                'description' => 'Sent when a wallet top-up is confirmed',
                'email_subject' => 'Wallet Top-up Confirmed - {{amount}}',
                'email_body' => 'Your wallet has been topped up with {{amount}}. Your new balance is {{new_balance}}.',
                'sms_body' => 'Wallet topped up: {{amount}}. New balance: {{new_balance}}',
                'in_app_title' => 'Wallet Topped Up',
                'in_app_body' => 'Your wallet has been topped up with {{amount}}',
                'priority' => 'normal',
            ],
            [
                'type' => 'wallet.withdrawal.approved',
                'category' => 'withdrawals',
                'name' => 'Withdrawal Approved',
                'description' => 'Sent when a withdrawal request is approved',
                'email_subject' => 'Withdrawal Approved - {{amount}}',
                'email_body' => 'Your withdrawal of {{amount}} has been approved and will be processed within 24-48 hours.',
                'sms_body' => 'Withdrawal approved: {{amount}}. Funds will be sent soon.',
                'in_app_title' => 'Withdrawal Approved',
                'in_app_body' => 'Your withdrawal of {{amount}} has been approved',
                'priority' => 'high',
            ],
            [
                'type' => 'wallet.withdrawal.rejected',
                'category' => 'withdrawals',
                'name' => 'Withdrawal Rejected',
                'description' => 'Sent when a withdrawal request is rejected',
                'email_subject' => 'Withdrawal Rejected - {{amount}}',
                'email_body' => 'Your withdrawal request of {{amount}} has been rejected. Reason: {{reason}}',
                'sms_body' => 'Withdrawal rejected: {{amount}}. Check email for details.',
                'in_app_title' => 'Withdrawal Rejected',
                'in_app_body' => 'Your withdrawal of {{amount}} was rejected: {{reason}}',
                'priority' => 'high',
            ],
            
            // Commission notifications
            [
                'type' => 'commission.earned',
                'category' => 'commissions',
                'name' => 'Commission Earned',
                'description' => 'Sent when a referral commission is earned',
                'email_subject' => 'You Earned a Commission - {{amount}}',
                'email_body' => 'Congratulations! You earned {{amount}} from {{from_user}}\'s activity (Level {{level}}).',
                'sms_body' => 'Commission earned: {{amount}} from {{from_user}}',
                'in_app_title' => 'Commission Earned',
                'in_app_body' => 'You earned {{amount}} from {{from_user}} (Level {{level}})',
                'priority' => 'normal',
            ],
            
            // Subscription notifications
            [
                'type' => 'subscription.expiring_soon',
                'category' => 'subscriptions',
                'name' => 'Subscription Expiring Soon',
                'description' => 'Sent when subscription is about to expire',
                'email_subject' => 'Your Subscription Expires in {{days_remaining}} Days',
                'email_body' => 'Your MyGrowNet subscription will expire on {{expiry_date}}. Renew now to continue enjoying all benefits.',
                'sms_body' => 'Subscription expires in {{days_remaining}} days. Renew now!',
                'in_app_title' => 'Subscription Expiring',
                'in_app_body' => 'Your subscription expires in {{days_remaining}} days',
                'priority' => 'high',
            ],
            [
                'type' => 'subscription.expired',
                'category' => 'subscriptions',
                'name' => 'Subscription Expired',
                'description' => 'Sent when subscription has expired',
                'email_subject' => 'Your Subscription Has Expired',
                'email_body' => 'Your MyGrowNet subscription expired on {{expiry_date}}. Renew now to regain access.',
                'sms_body' => 'Your subscription has expired. Renew now!',
                'in_app_title' => 'Subscription Expired',
                'in_app_body' => 'Your subscription has expired. Renew to continue.',
                'priority' => 'urgent',
            ],
            
            // Referral notifications
            [
                'type' => 'referral.new_signup',
                'category' => 'referrals',
                'name' => 'New Referral Signup',
                'description' => 'Sent when someone joins using your referral code',
                'email_subject' => 'New Referral - {{referral_name}} Joined!',
                'email_body' => '{{referral_name}} just joined MyGrowNet using your referral code!',
                'sms_body' => 'New referral: {{referral_name}} joined!',
                'in_app_title' => 'New Referral',
                'in_app_body' => '{{referral_name}} joined using your referral code',
                'priority' => 'normal',
            ],
            
            // Points notifications
            [
                'type' => 'points.level_upgraded',
                'category' => 'points',
                'name' => 'Professional Level Upgraded',
                'description' => 'Sent when user advances to a new professional level',
                'email_subject' => 'Congratulations! You\'re Now a {{new_level}}',
                'email_body' => 'You\'ve been promoted to {{new_level}}! Enjoy your new benefits and higher earning potential.',
                'sms_body' => 'Promoted to {{new_level}}! Congratulations!',
                'in_app_title' => 'Level Up!',
                'in_app_body' => 'You\'ve been promoted to {{new_level}}',
                'priority' => 'high',
            ],
            [
                'type' => 'points.milestone_reached',
                'category' => 'points',
                'name' => 'Points Milestone Reached',
                'description' => 'Sent when user reaches a points milestone',
                'email_subject' => 'Milestone Achieved - {{milestone}} Points!',
                'email_body' => 'Congratulations! You\'ve reached {{milestone}} Lifetime Points!',
                'sms_body' => 'Milestone: {{milestone}} points reached!',
                'in_app_title' => 'Milestone Reached',
                'in_app_body' => 'You\'ve reached {{milestone}} Lifetime Points!',
                'priority' => 'normal',
            ],
            
            // Venture Builder notifications
            [
                'type' => 'venture.investment_approved',
                'category' => 'ventures',
                'name' => 'Venture Investment Approved',
                'description' => 'Sent when venture investment is approved',
                'email_subject' => 'Investment Approved - {{venture_name}}',
                'email_body' => 'Your investment of {{amount}} in {{venture_name}} has been approved!',
                'sms_body' => 'Investment approved: {{amount}} in {{venture_name}}',
                'in_app_title' => 'Investment Approved',
                'in_app_body' => 'Your {{amount}} investment in {{venture_name}} is approved',
                'priority' => 'high',
            ],
            [
                'type' => 'venture.dividend_paid',
                'category' => 'ventures',
                'name' => 'Venture Dividend Paid',
                'description' => 'Sent when venture dividend is distributed',
                'email_subject' => 'Dividend Received - {{amount}}',
                'email_body' => 'You received {{amount}} dividend from {{venture_name}}!',
                'sms_body' => 'Dividend: {{amount}} from {{venture_name}}',
                'in_app_title' => 'Dividend Received',
                'in_app_body' => 'You received {{amount}} from {{venture_name}}',
                'priority' => 'normal',
            ],
            
            // BGF notifications
            [
                'type' => 'bgf.application_received',
                'category' => 'bgf',
                'name' => 'BGF Application Received',
                'description' => 'Sent when BGF application is received',
                'email_subject' => 'BGF Application Received',
                'email_body' => 'Your Business Growth Fund application has been received and is under review.',
                'sms_body' => 'BGF application received. We\'ll review it soon.',
                'in_app_title' => 'Application Received',
                'in_app_body' => 'Your BGF application is under review',
                'priority' => 'normal',
            ],
            [
                'type' => 'bgf.application_approved',
                'category' => 'bgf',
                'name' => 'BGF Application Approved',
                'description' => 'Sent when BGF application is approved',
                'email_subject' => 'BGF Application Approved - {{amount}}',
                'email_body' => 'Congratulations! Your BGF application for {{amount}} has been approved!',
                'sms_body' => 'BGF approved: {{amount}}. Congratulations!',
                'in_app_title' => 'Application Approved',
                'in_app_body' => 'Your BGF application for {{amount}} is approved!',
                'priority' => 'urgent',
            ],
            
            // Security notifications
            [
                'type' => 'security.login_new_device',
                'category' => 'security',
                'name' => 'Login from New Device',
                'description' => 'Sent when account is accessed from a new device',
                'email_subject' => 'New Device Login Detected',
                'email_body' => 'Your account was accessed from a new device. If this wasn\'t you, please secure your account immediately.',
                'sms_body' => 'New device login detected. Secure your account if not you.',
                'in_app_title' => 'New Device Login',
                'in_app_body' => 'Your account was accessed from a new device',
                'priority' => 'urgent',
            ],
            [
                'type' => 'security.password_changed',
                'category' => 'security',
                'name' => 'Password Changed',
                'description' => 'Sent when password is changed',
                'email_subject' => 'Password Changed Successfully',
                'email_body' => 'Your password was changed successfully. If you didn\'t make this change, contact support immediately.',
                'sms_body' => 'Password changed. Contact support if not you.',
                'in_app_title' => 'Password Changed',
                'in_app_body' => 'Your password was changed successfully',
                'priority' => 'high',
            ],
        ];

        foreach ($templates as $template) {
            DB::table('notification_templates')->insert(array_merge($template, [
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
