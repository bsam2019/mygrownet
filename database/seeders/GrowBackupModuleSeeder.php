<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GrowBackupModuleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('modules')->updateOrInsert(
            ['id' => 'growbackup'],
            [
                'name' => 'GrowBackup',
                'slug' => 'growbackup',
                'category' => 'personal',
                'description' => 'Secure cloud storage for your files with direct S3 integration',
                'icon' => '☁️',
                'color' => '#3B82F6', // Blue
                'thumbnail' => null,
                'account_types' => json_encode(['member', 'business']),
                'required_roles' => null,
                'min_user_level' => null,
                'routes' => json_encode([
                    'integrated' => '/growbackup/dashboard',
                    'standalone' => '/growbackup/dashboard',
                    'welcome' => '/growbackup',
                ]),
                'pwa_config' => json_encode([
                    'enabled' => true,
                    'installable' => true,
                    'offline_capable' => false
                ]),
                'features' => json_encode([
                    'offline' => false,
                    'dataSync' => true,
                    'notifications' => true,
                    'direct_s3_upload' => true,
                    'folder_management' => true,
                    'quota_enforcement' => true,
                ]),
                'subscription_tiers' => json_encode([
                    'starter' => [
                        'name' => 'Starter',
                        'description' => 'Perfect for personal use',
                        'price' => 50,
                        'price_annual' => 500,
                        'billing_cycle' => 'monthly',
                        'features' => [
                            'storage' => '2GB',
                            'max_file_size' => '25MB',
                            'sharing' => false,
                            'versioning' => false,
                        ],
                    ],
                    'basic' => [
                        'name' => 'Basic',
                        'description' => 'For growing storage needs',
                        'price' => 150,
                        'price_annual' => 1500,
                        'billing_cycle' => 'monthly',
                        'is_popular' => true,
                        'features' => [
                            'storage' => '20GB',
                            'max_file_size' => '100MB',
                            'sharing' => true,
                            'versioning' => false,
                        ],
                    ],
                    'growth' => [
                        'name' => 'Growth',
                        'description' => 'For teams and businesses',
                        'price' => 500,
                        'price_annual' => 5000,
                        'billing_cycle' => 'monthly',
                        'features' => [
                            'storage' => '100GB',
                            'max_file_size' => '500MB',
                            'sharing' => true,
                            'versioning' => true,
                            'team_folders' => true,
                        ],
                    ],
                    'pro' => [
                        'name' => 'Pro',
                        'description' => 'Unlimited storage for power users',
                        'price' => 1500,
                        'price_annual' => 15000,
                        'billing_cycle' => 'monthly',
                        'features' => [
                            'storage' => '500GB',
                            'max_file_size' => '2GB',
                            'sharing' => true,
                            'versioning' => true,
                            'team_folders' => true,
                            'priority_support' => true,
                        ],
                    ],
                ]),
                'requires_subscription' => true,
                'version' => '1.0.0',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
