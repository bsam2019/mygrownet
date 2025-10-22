<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            // LEVEL-BASED MONTHLY SUBSCRIPTIONS
            [
                'name' => 'Associate',
                'slug' => 'associate-monthly',
                'description' => 'Entry level - New member, learning (3 network members)',
                'price' => 100.00,
                'billing_cycle' => 'monthly',
                'professional_level' => 'associate',
                'lp_requirement' => 0,
                'duration_months' => 1,
                'features' => [
                    'Basic educational content',
                    'Peer circle access',
                    '7-level commission structure',
                    'Monthly qualification: 100 MAP',
                    'Profit-sharing: 1.0x base share'
                ],
                'is_active' => true,
                'sort_order' => 1
            ],
            
            [
                'name' => 'Professional',
                'slug' => 'professional-monthly',
                'description' => 'Skilled member, applying (9 network members)',
                'price' => 150.00,
                'billing_cycle' => 'monthly',
                'professional_level' => 'professional',
                'lp_requirement' => 500,
                'duration_months' => 1,
                'features' => [
                    'Advanced educational content',
                    'Group mentorship access',
                    'Enhanced commission rates',
                    'Monthly qualification: 200 MAP',
                    'Profit-sharing: 1.2x base share'
                ],
                'is_active' => true,
                'sort_order' => 2
            ],
            
            [
                'name' => 'Senior',
                'slug' => 'senior-monthly',
                'description' => 'Experienced, team building (27 network members)',
                'price' => 200.00,
                'billing_cycle' => 'monthly',
                'professional_level' => 'senior',
                'lp_requirement' => 1500,
                'duration_months' => 1,
                'features' => [
                    'Premium content library',
                    '1-on-1 mentorship sessions',
                    'Team building bonuses',
                    'Monthly qualification: 300 MAP',
                    'Profit-sharing: 1.5x base share'
                ],
                'is_active' => true,
                'sort_order' => 3
            ],
            
            [
                'name' => 'Manager',
                'slug' => 'manager-monthly',
                'description' => 'Team leader (81 network members)',
                'price' => 300.00,
                'billing_cycle' => 'monthly',
                'professional_level' => 'manager',
                'lp_requirement' => 4000,
                'duration_months' => 1,
                'features' => [
                    'Leadership training programs',
                    'Team performance bonuses',
                    'Booster fund: K5,000',
                    'Monthly qualification: 400 MAP',
                    'Profit-sharing: 2.0x base share'
                ],
                'is_active' => true,
                'sort_order' => 4
            ],
            
            [
                'name' => 'Director',
                'slug' => 'director-monthly',
                'description' => 'Strategic leader (243 network members)',
                'price' => 400.00,
                'billing_cycle' => 'monthly',
                'professional_level' => 'director',
                'lp_requirement' => 10000,
                'duration_months' => 1,
                'features' => [
                    'Strategic leadership content',
                    'Business facilitation services',
                    'Booster fund: K15,000',
                    'Monthly qualification: 500 MAP',
                    'Profit-sharing: 2.5x base share'
                ],
                'is_active' => true,
                'sort_order' => 5
            ],
            
            [
                'name' => 'Executive',
                'slug' => 'executive-monthly',
                'description' => 'Top performer (729 network members)',
                'price' => 500.00,
                'billing_cycle' => 'monthly',
                'professional_level' => 'executive',
                'lp_requirement' => 25000,
                'duration_months' => 1,
                'features' => [
                    'Executive coaching access',
                    'Innovation lab participation',
                    'Booster fund: K50,000',
                    'Monthly qualification: 600 MAP',
                    'Profit-sharing: 3.0x base share'
                ],
                'is_active' => true,
                'sort_order' => 6
            ],
            
            [
                'name' => 'Ambassador',
                'slug' => 'ambassador-monthly',
                'description' => 'Brand representative (2,187 network members)',
                'price' => 600.00,
                'billing_cycle' => 'monthly',
                'professional_level' => 'ambassador',
                'lp_requirement' => 50000,
                'duration_months' => 1,
                'features' => [
                    'VIP brand ambassador status',
                    'Exclusive events & retreats',
                    'Booster fund: K150,000',
                    'Monthly qualification: 800 MAP',
                    'Profit-sharing: 4.0x base share (MAX)'
                ],
                'is_active' => true,
                'sort_order' => 7
            ],
            
            // UPGRADE PACKAGES (Pay the difference to upgrade)
            [
                'name' => 'Upgrade to Professional',
                'slug' => 'upgrade-professional',
                'description' => 'Upgrade from Associate to Professional (pay the difference for current month).',
                'price' => 50.00, // K150 - K100
                'billing_cycle' => 'upgrade',
                'professional_level' => 'professional',
                'lp_requirement' => 500,
                'duration_months' => 0,
                'features' => [
                    'Immediate upgrade to Professional level',
                    'Pay only the difference (K50)',
                    'Next month: Full K150 subscription',
                    'Unlock Level 3 commissions',
                    'Enhanced profit-sharing (1.2x)'
                ],
                'is_active' => true,
                'sort_order' => 8
            ],
            
            [
                'name' => 'Upgrade to Senior',
                'slug' => 'upgrade-senior',
                'description' => 'Upgrade from Professional to Senior (pay the difference for current month).',
                'price' => 50.00, // K200 - K150
                'billing_cycle' => 'upgrade',
                'professional_level' => 'senior',
                'lp_requirement' => 1500,
                'duration_months' => 0,
                'features' => [
                    'Immediate upgrade to Senior level',
                    'Pay only the difference (K50)',
                    'Next month: Full K200 subscription',
                    'Unlock Level 4 commissions',
                    'Enhanced profit-sharing (1.5x)'
                ],
                'is_active' => true,
                'sort_order' => 9
            ],
            
            [
                'name' => 'Upgrade to Manager',
                'slug' => 'upgrade-manager',
                'description' => 'Upgrade from Senior to Manager (pay the difference for current month).',
                'price' => 100.00, // K300 - K200
                'billing_cycle' => 'upgrade',
                'professional_level' => 'manager',
                'lp_requirement' => 4000,
                'duration_months' => 0,
                'features' => [
                    'Immediate upgrade to Manager level',
                    'Pay only the difference (K100)',
                    'Next month: Full K300 subscription',
                    'Unlock Level 5 commissions',
                    'Enhanced profit-sharing (2.0x)',
                    'Booster fund eligibility'
                ],
                'is_active' => true,
                'sort_order' => 10
            ],
            
            [
                'name' => 'Upgrade to Director',
                'slug' => 'upgrade-director',
                'description' => 'Upgrade from Manager to Director (pay the difference for current month).',
                'price' => 100.00, // K400 - K300
                'billing_cycle' => 'upgrade',
                'professional_level' => 'director',
                'lp_requirement' => 10000,
                'duration_months' => 0,
                'features' => [
                    'Immediate upgrade to Director level',
                    'Pay only the difference (K100)',
                    'Next month: Full K400 subscription',
                    'Unlock Level 6 commissions',
                    'Enhanced profit-sharing (2.5x)',
                    'Enhanced booster funds'
                ],
                'is_active' => true,
                'sort_order' => 11
            ],
            
            [
                'name' => 'Upgrade to Executive',
                'slug' => 'upgrade-executive',
                'description' => 'Upgrade from Director to Executive (pay the difference for current month).',
                'price' => 100.00, // K500 - K400
                'billing_cycle' => 'upgrade',
                'professional_level' => 'executive',
                'lp_requirement' => 25000,
                'duration_months' => 0,
                'features' => [
                    'Immediate upgrade to Executive level',
                    'Pay only the difference (K100)',
                    'Next month: Full K500 subscription',
                    'Unlock Level 7 commissions',
                    'Enhanced profit-sharing (3.0x)',
                    'Major booster funds',
                    'Travel incentives'
                ],
                'is_active' => true,
                'sort_order' => 12
            ],
            
            [
                'name' => 'Upgrade to Ambassador',
                'slug' => 'upgrade-ambassador',
                'description' => 'Upgrade from Executive to Ambassador (pay the difference for current month).',
                'price' => 100.00, // K600 - K500
                'billing_cycle' => 'upgrade',
                'professional_level' => 'ambassador',
                'lp_requirement' => 50000,
                'duration_months' => 0,
                'features' => [
                    'Immediate upgrade to Ambassador level',
                    'Pay only the difference (K100)',
                    'Next month: Full K600 subscription',
                    'Full 7-level commission earnings',
                    'Maximum profit-sharing (4.0x MAX)',
                    'Premium booster funds',
                    'Brand ambassador status'
                ],
                'is_active' => true,
                'sort_order' => 13
            ]
        ];

        // Deactivate old packages
        Package::whereIn('slug', [
            'registration',
            'starter-kit-associate',
            'basic',
            'associate',
            'professional',
            'senior',
            'manager',
            'director',
            'executive',
            'ambassador',
            'professional-annual',
            'senior-annual'
        ])->update(['is_active' => false]);

        // Create/update new packages
        foreach ($packages as $package) {
            Package::updateOrCreate(
                ['slug' => $package['slug']],
                $package
            );
        }

        $this->command->info('✅ Packages seeded successfully!');
        $this->command->info('');
        $this->command->info('📦 Monthly Subscriptions (7 Levels):');
        $this->command->info('  1. Associate: K100/month (0 LP required)');
        $this->command->info('  2. Professional: K150/month (500 LP required)');
        $this->command->info('  3. Senior: K200/month (1,500 LP required)');
        $this->command->info('  4. Manager: K300/month (4,000 LP required)');
        $this->command->info('  5. Director: K400/month (10,000 LP required)');
        $this->command->info('  6. Executive: K500/month (25,000 LP required)');
        $this->command->info('  7. Ambassador: K600/month (50,000 LP required)');
        $this->command->info('');
        $this->command->info('🔄 Upgrade Packages (Pay the difference):');
        $this->command->info('  • To Professional: K50');
        $this->command->info('  • To Senior: K50');
        $this->command->info('  • To Manager: K100');
        $this->command->info('  • To Director: K100');
        $this->command->info('  • To Executive: K100');
        $this->command->info('  • To Ambassador: K100');
        $this->command->info('');
        $this->command->info('💡 Members advance by earning Life Points (LP) and paying upgrade fee.');
    }
}
