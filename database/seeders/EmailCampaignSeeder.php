<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmailCampaignSeeder extends Seeder
{
    public function run(): void
    {
        // Get template IDs
        $welcomeTemplate = DB::table('email_templates')->where('name', 'Welcome to MyGrowNet')->first();
        $gettingStartedTemplate = DB::table('email_templates')->where('name', 'Getting Started Guide')->first();
        $completeProfileTemplate = DB::table('email_templates')->where('name', 'Complete Your Profile')->first();
        $weeklySummaryTemplate = DB::table('email_templates')->where('name', 'Weekly Activity Summary')->first();
        $weMissYouTemplate = DB::table('email_templates')->where('name', 'We Miss You')->first();
        $levelUpTemplate = DB::table('email_templates')->where('name', 'Level Up Opportunity')->first();

        // 1. Onboarding Campaign (7 emails over 14 days)
        $onboardingCampaign = DB::table('email_campaigns')->insertGetId([
            'name' => 'New Member Onboarding',
            'type' => 'onboarding',
            'status' => 'active',
            'trigger_type' => 'immediate',
            'trigger_config' => json_encode(['event' => 'user_registered']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Onboarding sequence
        if ($welcomeTemplate && $gettingStartedTemplate && $completeProfileTemplate) {
            DB::table('email_sequences')->insert([
                ['campaign_id' => $onboardingCampaign, 'sequence_order' => 1, 'delay_days' => 0, 'delay_hours' => 0, 'template_id' => $welcomeTemplate->id, 'created_at' => now(), 'updated_at' => now()],
                ['campaign_id' => $onboardingCampaign, 'sequence_order' => 2, 'delay_days' => 1, 'delay_hours' => 0, 'template_id' => $gettingStartedTemplate->id, 'created_at' => now(), 'updated_at' => now()],
                ['campaign_id' => $onboardingCampaign, 'sequence_order' => 3, 'delay_days' => 3, 'delay_hours' => 0, 'template_id' => $completeProfileTemplate->id, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }


        // 2. Monthly Engagement Campaign
        $engagementCampaign = DB::table('email_campaigns')->insertGetId([
            'name' => 'Monthly Engagement',
            'type' => 'engagement',
            'status' => 'active',
            'trigger_type' => 'scheduled',
            'trigger_config' => json_encode(['frequency' => 'monthly', 'day_of_month' => 1]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($weeklySummaryTemplate) {
            DB::table('email_sequences')->insert([
                'campaign_id' => $engagementCampaign,
                'sequence_order' => 1,
                'delay_days' => 0,
                'delay_hours' => 0,
                'template_id' => $weeklySummaryTemplate->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Quarterly Re-activation Campaign
        $reactivationCampaign = DB::table('email_campaigns')->insertGetId([
            'name' => 'Quarterly Re-activation',
            'type' => 'reactivation',
            'status' => 'active',
            'trigger_type' => 'behavioral',
            'trigger_config' => json_encode(['event' => 'inactive_30_days']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($weMissYouTemplate) {
            DB::table('email_sequences')->insert([
                'campaign_id' => $reactivationCampaign,
                'sequence_order' => 1,
                'delay_days' => 0,
                'delay_hours' => 0,
                'template_id' => $weMissYouTemplate->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 4. Triggered Upgrade Campaign
        $upgradeCampaign = DB::table('email_campaigns')->insertGetId([
            'name' => 'Level Upgrade Notification',
            'type' => 'upgrade',
            'status' => 'active',
            'trigger_type' => 'behavioral',
            'trigger_config' => json_encode(['event' => 'level_upgrade_eligible']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($levelUpTemplate) {
            DB::table('email_sequences')->insert([
                'campaign_id' => $upgradeCampaign,
                'sequence_order' => 1,
                'delay_days' => 0,
                'delay_hours' => 0,
                'template_id' => $levelUpTemplate->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
