<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LgrPackage;

class LgrPackagesSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Package 1',
                'slug' => 'package-1',
                'package_amount' => 300.00,
                'daily_lgr_rate' => 10.00,
                'duration_days' => 50,
                'total_reward' => 500.00,
                'is_active' => true,
                'sort_order' => 1,
                'description' => 'Entry-level LGR package perfect for beginners',
                'features' => [
                    'K10 daily LGR earnings',
                    '50-day earning cycle',
                    'K500 total potential earnings',
                    '166.67% ROI',
                ],
            ],
            [
                'name' => 'Package 2',
                'slug' => 'package-2',
                'package_amount' => 500.00,
                'daily_lgr_rate' => 15.00,
                'duration_days' => 70,
                'total_reward' => 1050.00,
                'is_active' => true,
                'sort_order' => 2,
                'description' => 'Popular mid-tier package with extended earning period',
                'features' => [
                    'K15 daily LGR earnings',
                    '70-day earning cycle',
                    'K1,050 total potential earnings',
                    '210% ROI',
                ],
            ],
            [
                'name' => 'Package 3',
                'slug' => 'package-3',
                'package_amount' => 1000.00,
                'daily_lgr_rate' => 30.00,
                'duration_days' => 70,
                'total_reward' => 2100.00,
                'is_active' => true,
                'sort_order' => 3,
                'description' => 'Premium package with higher daily earnings',
                'features' => [
                    'K30 daily LGR earnings',
                    '70-day earning cycle',
                    'K2,100 total potential earnings',
                    '210% ROI',
                ],
            ],
            [
                'name' => 'Package 4',
                'slug' => 'package-4',
                'package_amount' => 2000.00,
                'daily_lgr_rate' => 60.00,
                'duration_days' => 70,
                'total_reward' => 4200.00,
                'is_active' => true,
                'sort_order' => 4,
                'description' => 'Elite package with maximum earning potential',
                'features' => [
                    'K60 daily LGR earnings',
                    '70-day earning cycle',
                    'K4,200 total potential earnings',
                    '210% ROI',
                ],
            ],
        ];

        foreach ($packages as $packageData) {
            LgrPackage::updateOrCreate(
                ['slug' => $packageData['slug']],
                $packageData
            );
        }

        $this->command->info('✅ LGR Packages seeded successfully!');
    }
}
