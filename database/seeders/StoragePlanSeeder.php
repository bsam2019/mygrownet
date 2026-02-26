<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Infrastructure\Storage\Persistence\Eloquent\StoragePlan;

class StoragePlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free Plan',
                'slug' => 'free',
                'quota_bytes' => 5 * 1024 * 1024 * 1024, // 5 GB
                'max_file_size_bytes' => 25 * 1024 * 1024, // 25 MB
                'price_monthly' => 0, // Free
                'price_annual' => 0,
                'allowed_mime_types' => json_encode([
                    'application/pdf',
                    'image/*',
                    'text/*',
                    // Microsoft Office
                    'application/msword',
                    'application/vnd.ms-excel',
                    'application/vnd.ms-powerpoint',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                ]),
                'allow_sharing' => false,
                'allow_public_profile_files' => false,
                'is_active' => true,
                'features' => json_encode([
                    'Basic backup & web access',
                    'Limited version history (7 days)',
                    'Single device',
                ]),
            ],
            [
                'name' => 'Personal Plan',
                'slug' => 'personal',
                'quota_bytes' => 50 * 1024 * 1024 * 1024, // 50 GB
                'max_file_size_bytes' => 100 * 1024 * 1024, // 100 MB
                'price_monthly' => 60, // K60/month
                'price_annual' => 600, // K600/year (17% discount)
                'allowed_mime_types' => null, // All types allowed
                'allow_sharing' => true,
                'allow_public_profile_files' => false,
                'is_active' => true,
                'is_popular' => true,
                'features' => json_encode([
                    'Automatic backup & sync',
                    'Phone photo backup',
                    '30-day file recovery',
                    'Up to 2 devices',
                    'File sharing',
                ]),
            ],
            [
                'name' => 'Standard Plan',
                'slug' => 'standard',
                'quota_bytes' => 200 * 1024 * 1024 * 1024, // 200 GB
                'max_file_size_bytes' => 500 * 1024 * 1024, // 500 MB
                'price_monthly' => 150, // K150/month
                'price_annual' => 1500, // K1500/year (17% discount)
                'allowed_mime_types' => null,
                'allow_sharing' => true,
                'allow_public_profile_files' => true,
                'is_active' => true,
                'features' => json_encode([
                    'Full device backup',
                    '90-day version history',
                    'Up to 5 devices',
                    'Priority sync speed',
                    'Public profile files',
                ]),
            ],
            [
                'name' => 'Business Plan',
                'slug' => 'business',
                'quota_bytes' => 1024 * 1024 * 1024 * 1024, // 1 TB
                'max_file_size_bytes' => 2 * 1024 * 1024 * 1024, // 2 GB
                'price_monthly' => 450, // K450/month
                'price_annual' => 4500, // K4500/year (17% discount)
                'allowed_mime_types' => null,
                'allow_sharing' => true,
                'allow_public_profile_files' => true,
                'is_active' => true,
                'features' => json_encode([
                    'Team & office backup',
                    '1-year version history',
                    'Multi-device backup',
                    'Ransomware recovery protection',
                    'Priority support',
                ]),
            ],
        ];

        foreach ($plans as $plan) {
            StoragePlan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }

        $this->command->info('Storage plans seeded successfully!');
    }
}
