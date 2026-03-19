<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'monthly_price' => 500.00,
                'annual_price' => 5000.00,
                'site_limit' => 10,
                'storage_limit_mb' => 20480, // 20GB
                'team_member_limit' => 3,
                'client_limit' => null, // Unlimited
                'features_json' => [
                    'white_label' => false,
                    'client_portal' => true,
                    'advanced_analytics' => false,
                    'priority_support' => false,
                    'api_access' => false,
                    'custom_domains' => 3,
                    'email_campaigns' => false,
                ],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'monthly_price' => 2000.00,
                'annual_price' => 20000.00,
                'site_limit' => 50,
                'storage_limit_mb' => 102400, // 100GB
                'team_member_limit' => 10,
                'client_limit' => null, // Unlimited
                'features_json' => [
                    'white_label' => true,
                    'client_portal' => true,
                    'advanced_analytics' => true,
                    'priority_support' => false,
                    'api_access' => false,
                    'custom_domains' => 20,
                    'email_campaigns' => true,
                ],
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Premium',
                'slug' => 'premium',
                'monthly_price' => 8000.00,
                'annual_price' => 80000.00,
                'site_limit' => 200,
                'storage_limit_mb' => 512000, // 500GB
                'team_member_limit' => 50,
                'client_limit' => null, // Unlimited
                'features_json' => [
                    'white_label' => true,
                    'client_portal' => true,
                    'advanced_analytics' => true,
                    'priority_support' => true,
                    'api_access' => true,
                    'custom_domains' => 100,
                    'email_campaigns' => true,
                ],
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'monthly_price' => 0.00, // Custom pricing
                'annual_price' => 0.00, // Custom pricing
                'site_limit' => 999999,
                'storage_limit_mb' => 2097152, // 2TB
                'team_member_limit' => 999999,
                'client_limit' => null, // Unlimited
                'features_json' => [
                    'white_label' => true,
                    'client_portal' => true,
                    'advanced_analytics' => true,
                    'priority_support' => true,
                    'api_access' => true,
                    'custom_domains' => 999999,
                    'email_campaigns' => true,
                ],
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }

        $this->command->info('Subscription plans seeded successfully!');
    }
}
