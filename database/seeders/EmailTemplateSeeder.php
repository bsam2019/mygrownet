<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            // Onboarding Templates
            [
                'name' => 'Welcome to MyGrowNet',
                'category' => 'onboarding',
                'subject' => 'Welcome to MyGrowNet - Your Journey Starts Here!',
                'html_content' => $this->getWelcomeTemplate(),
                'variables' => json_encode(['first_name', 'username', 'referrer_name']),
            ],
            [
                'name' => 'Getting Started Guide',
                'category' => 'onboarding',
                'subject' => 'Your MyGrowNet Quick Start Guide',
                'html_content' => $this->getGettingStartedTemplate(),
                'variables' => json_encode(['first_name', 'dashboard_url']),
            ],
            [
                'name' => 'Complete Your Profile',
                'category' => 'onboarding',
                'subject' => 'Complete Your Profile to Unlock All Features',
                'html_content' => $this->getCompleteProfileTemplate(),
                'variables' => json_encode(['first_name', 'profile_url']),
            ],

            // Engagement Templates
            [
                'name' => 'Weekly Activity Summary',
                'category' => 'engagement',
                'subject' => 'Your Weekly MyGrowNet Activity Summary',
                'html_content' => $this->getWeeklySummaryTemplate(),
                'variables' => json_encode(['first_name', 'points_earned', 'level', 'network_size']),
            ],
            [
                'name' => 'New Learning Content Available',
                'category' => 'engagement',
                'subject' => 'New Learning Resources Just Added!',
                'html_content' => $this->getNewContentTemplate(),
                'variables' => json_encode(['first_name', 'content_title', 'content_url']),
            ],

            // Re-activation Templates
            [
                'name' => 'We Miss You',
                'category' => 'reactivation',
                'subject' => 'We Miss You at MyGrowNet!',
                'html_content' => $this->getWeMissYouTemplate(),
                'variables' => json_encode(['first_name', 'days_inactive', 'login_url']),
            ],
            [
                'name' => 'Special Comeback Offer',
                'category' => 'reactivation',
                'subject' => 'Special Offer to Welcome You Back!',
                'html_content' => $this->getComebackOfferTemplate(),
                'variables' => json_encode(['first_name', 'offer_details', 'claim_url']),
            ],

            // Upgrade Templates
            [
                'name' => 'Level Up Opportunity',
                'category' => 'upgrade',
                'subject' => 'You\'re Ready to Level Up!',
                'html_content' => $this->getLevelUpTemplate(),
                'variables' => json_encode(['first_name', 'current_level', 'next_level', 'benefits']),
            ],
        ];

        foreach ($templates as $template) {
            DB::table('email_templates')->insert([
                'name' => $template['name'],
                'category' => $template['category'],
                'subject' => $template['subject'],
                'html_content' => $template['html_content'],
                'variables' => $template['variables'],
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }


    private function getWelcomeTemplate(): string
    {
        return <<<'HTML'
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
    <h1 style="color: #2563eb;">Welcome to MyGrowNet, {{first_name}}!</h1>
    <p>We're thrilled to have you join our community of growth-focused individuals!</p>
    <p>MyGrowNet is your platform for:</p>
    <ul>
        <li><strong>Learning</strong> - Access practical skills training and mentorship</li>
        <li><strong>Earning</strong> - Build your network and earn through referrals</li>
        <li><strong>Growing</strong> - Achieve milestones and unlock rewards</li>
    </ul>
    <p>You were referred by <strong>{{referrer_name}}</strong> - they'll be here to support your journey!</p>
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{dashboard_url}}" style="background: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px;">
            Go to Your Dashboard
        </a>
    </div>
    <p>Best regards,<br>The MyGrowNet Team</p>
</div>
HTML;
    }

    private function getGettingStartedTemplate(): string
    {
        return <<<'HTML'
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
    <h1 style="color: #2563eb;">Your Quick Start Guide</h1>
    <p>Hi {{first_name}},</p>
    <p>Here's how to get the most out of MyGrowNet:</p>
    <h3>Step 1: Complete Your Profile</h3>
    <p>Add your details to unlock all features and connect with your network.</p>
    <h3>Step 2: Explore Learning Resources</h3>
    <p>Access our library of courses, e-books, and training materials.</p>
    <h3>Step 3: Build Your Network</h3>
    <p>Share your referral link and start building your 7-level network.</p>
    <h3>Step 4: Track Your Progress</h3>
    <p>Monitor your points, level, and earnings in your dashboard.</p>
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{dashboard_url}}" style="background: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px;">
            Get Started Now
        </a>
    </div>
</div>
HTML;
    }


    private function getCompleteProfileTemplate(): string
    {
        return <<<'HTML'
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
    <h1 style="color: #2563eb;">Complete Your Profile</h1>
    <p>Hi {{first_name}},</p>
    <p>Your profile is almost ready! Complete it to:</p>
    <ul>
        <li>Unlock all platform features</li>
        <li>Connect with your network</li>
        <li>Start earning points and bonuses</li>
        <li>Access exclusive content</li>
    </ul>
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{profile_url}}" style="background: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px;">
            Complete Profile
        </a>
    </div>
</div>
HTML;
    }

    private function getWeeklySummaryTemplate(): string
    {
        return <<<'HTML'
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
    <h1 style="color: #2563eb;">Your Weekly Summary</h1>
    <p>Hi {{first_name}},</p>
    <p>Here's what you accomplished this week:</p>
    <div style="background: #f3f4f6; padding: 20px; border-radius: 8px; margin: 20px 0;">
        <p><strong>Points Earned:</strong> {{points_earned}}</p>
        <p><strong>Current Level:</strong> {{level}}</p>
        <p><strong>Network Size:</strong> {{network_size}} members</p>
    </div>
    <p>Keep up the great work! Every action brings you closer to your goals.</p>
</div>
HTML;
    }

    private function getNewContentTemplate(): string
    {
        return <<<'HTML'
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
    <h1 style="color: #2563eb;">New Learning Content!</h1>
    <p>Hi {{first_name}},</p>
    <p>We've just added new content to help you grow:</p>
    <h3>{{content_title}}</h3>
    <p>This resource will help you develop valuable skills and advance your journey.</p>
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{content_url}}" style="background: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px;">
            Access Now
        </a>
    </div>
</div>
HTML;
    }


    private function getWeMissYouTemplate(): string
    {
        return <<<'HTML'
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
    <h1 style="color: #2563eb;">We Miss You!</h1>
    <p>Hi {{first_name}},</p>
    <p>It's been {{days_inactive}} days since we last saw you at MyGrowNet.</p>
    <p>Your network is waiting, and there's so much happening:</p>
    <ul>
        <li>New learning resources added</li>
        <li>Your network may have grown</li>
        <li>New opportunities to earn points</li>
    </ul>
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{login_url}}" style="background: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px;">
            Welcome Back
        </a>
    </div>
</div>
HTML;
    }

    private function getComebackOfferTemplate(): string
    {
        return <<<'HTML'
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
    <h1 style="color: #2563eb;">Special Welcome Back Offer!</h1>
    <p>Hi {{first_name}},</p>
    <p>We want to welcome you back with a special offer:</p>
    <div style="background: #fef3c7; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #f59e0b;">
        <p><strong>{{offer_details}}</strong></p>
    </div>
    <p>This offer is available for a limited time. Don't miss out!</p>
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{claim_url}}" style="background: #f59e0b; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px;">
            Claim Your Offer
        </a>
    </div>
</div>
HTML;
    }

    private function getLevelUpTemplate(): string
    {
        return <<<'HTML'
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
    <h1 style="color: #2563eb;">You're Ready to Level Up!</h1>
    <p>Hi {{first_name}},</p>
    <p>Congratulations! You've reached the requirements to advance from <strong>{{current_level}}</strong> to <strong>{{next_level}}</strong>!</p>
    <h3>New Benefits Await:</h3>
    <div style="background: #f3f4f6; padding: 20px; border-radius: 8px; margin: 20px 0;">
        {{benefits}}
    </div>
    <p>Take the next step in your MyGrowNet journey today!</p>
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{upgrade_url}}" style="background: #7c3aed; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px;">
            Level Up Now
        </a>
    </div>
</div>
HTML;
    }
}
