<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            // ONE-TIME REGISTRATION (Required for all new members)
            [
                'name' => 'MyGrowNet Registration',
                'slug' => 'registration',
                'description' => 'One-time registration fee for new members. Includes welcome package, first month Associate membership, and platform access setup.',
                'price' => 500.00,
                'billing_cycle' => 'one-time',
                'duration_months' => 1,
                'features' => [
                    'One-time registration fee',
                    'First month Associate membership included',
                    'Welcome learning pack',
                    'Getting started guide',
                    'Community access setup',
                    'Initial mentorship session',
                    'Starter resources bundle',
                    'Matrix position assignment',
                    'Referral code generation'
                ],
                'is_active' => true,
                'sort_order' => 0
            ],
            
            // LEVEL-BASED MONTHLY SUBSCRIPTIONS
            [
                'name' => 'Associate Membership',
                'slug' => 'associate-monthly',
                'description' => 'Entry-level monthly subscription. Start your journey with MyGrowNet.',
                'price' => 100.00,
                'billing_cycle' => 'monthly',
                'duration_months' => 1,
                'features' => [
                    'Basic learning materials',
                    'Community forum access',
                    'Monthly group coaching',
                    'Matrix participation',
                    'Profit-sharing eligibility (1.0x)',
                    'Level 1-2 commission earnings',
                    'Workshop access',
                    'Email support'
                ],
                'is_active' => true,
                'sort_order' => 1
            ],
            
            [
                'name' => 'Professional Membership',
                'slug' => 'professional-monthly',
                'description' => 'Enhanced membership for growing professionals.',
                'price' => 150.00,
                'billing_cycle' => 'monthly',
                'duration_months' => 1,
                'features' => [
                    'All Associate features',
                    'Advanced learning materials',
                    'Weekly group coaching',
                    'One-on-one mentorship (monthly)',
                    'Profit-sharing eligibility (1.2x)',
                    'Level 1-3 commission earnings',
                    'Priority support'
                ],
                'is_active' => true,
                'sort_order' => 2
            ],
            
            [
                'name' => 'Senior Membership',
                'slug' => 'senior-monthly',
                'description' => 'Premium membership for experienced team builders.',
                'price' => 200.00,
                'billing_cycle' => 'monthly',
                'duration_months' => 1,
                'features' => [
                    'All Professional features',
                    'Premium learning content',
                    'Bi-weekly one-on-one coaching',
                    'Business planning support',
                    'Profit-sharing eligibility (1.5x)',
                    'Level 1-4 commission earnings',
                    'Certificate programs',
                    'Dedicated support'
                ],
                'is_active' => true,
                'sort_order' => 3
            ],
            
            [
                'name' => 'Manager Membership',
                'slug' => 'manager-monthly',
                'description' => 'Executive membership for team leaders.',
                'price' => 300.00,
                'billing_cycle' => 'monthly',
                'duration_months' => 1,
                'features' => [
                    'All Senior features',
                    'Leadership development program',
                    'Team building resources',
                    'Weekly one-on-one coaching',
                    'Profit-sharing eligibility (2.0x)',
                    'Level 1-5 commission earnings',
                    'Booster fund eligibility (K5,000+)',
                    'VIP support'
                ],
                'is_active' => true,
                'sort_order' => 4
            ],
            
            [
                'name' => 'Director Membership',
                'slug' => 'director-monthly',
                'description' => 'Elite membership for strategic leaders.',
                'price' => 400.00,
                'billing_cycle' => 'monthly',
                'duration_months' => 1,
                'features' => [
                    'All Manager features',
                    'Strategic business consulting',
                    'Unlimited one-on-one coaching',
                    'Private networking events',
                    'Profit-sharing eligibility (2.5x)',
                    'Level 1-6 commission earnings',
                    'Enhanced booster funds (K10,000+)',
                    'Concierge support'
                ],
                'is_active' => true,
                'sort_order' => 5
            ],
            
            [
                'name' => 'Executive Membership',
                'slug' => 'executive-monthly',
                'description' => 'Top-tier membership for high performers.',
                'price' => 500.00,
                'billing_cycle' => 'monthly',
                'duration_months' => 1,
                'features' => [
                    'All Director features',
                    'Business ecosystem access',
                    'Partnership opportunities',
                    'International networking',
                    'Profit-sharing eligibility (3.0x)',
                    'Level 1-7 commission earnings',
                    'Major booster funds (K25,000+)',
                    'White-glove support',
                    'Travel incentives'
                ],
                'is_active' => true,
                'sort_order' => 6
            ],
            
            [
                'name' => 'Ambassador Membership',
                'slug' => 'ambassador-monthly',
                'description' => 'Ultimate membership for brand ambassadors.',
                'price' => 600.00,
                'billing_cycle' => 'monthly',
                'duration_months' => 1,
                'features' => [
                    'All Executive features',
                    'Brand ambassador status',
                    'Speaking opportunities',
                    'Maximum profit-sharing (3.5x)',
                    'Full 7-level commission earnings',
                    'Premium booster funds (K50,000+)',
                    'Luxury rewards eligibility',
                    'Global networking access',
                    'Personal success team',
                    'Lifetime achievement recognition'
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
                'duration_months' => 0,
                'features' => [
                    'Immediate upgrade to Ambassador level',
                    'Pay only the difference (K100)',
                    'Next month: Full K600 subscription',
                    'Full 7-level commission earnings',
                    'Maximum profit-sharing (3.5x)',
                    'Premium booster funds',
                    'Brand ambassador status'
                ],
                'is_active' => true,
                'sort_order' => 13
            ]
        ];

        // Deactivate old tier-based packages (they don't align with MyGrowNet model)
        Package::whereIn('slug', [
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

        $this->command->info('Packages seeded successfully!');
        $this->command->info('');
        $this->command->info('📦 Active Packages:');
        $this->command->info('  Registration: K500 (one-time)');
        $this->command->info('');
        $this->command->info('  Monthly Subscriptions:');
        $this->command->info('    • Associate: K100/month');
        $this->command->info('    • Professional: K150/month');
        $this->command->info('    • Senior: K200/month');
        $this->command->info('    • Manager: K300/month');
        $this->command->info('    • Director: K400/month');
        $this->command->info('    • Executive: K500/month');
        $this->command->info('    • Ambassador: K600/month');
        $this->command->info('');
        $this->command->info('  Upgrade Packages (Pay the difference):');
        $this->command->info('    • To Professional: K50');
        $this->command->info('    • To Senior: K50');
        $this->command->info('    • To Manager: K100');
        $this->command->info('    • To Director: K100');
        $this->command->info('    • To Executive: K100');
        $this->command->info('    • To Ambassador: K100');
        $this->command->info('');
        $this->command->info('💡 Members advance through levels by earning Life Points (LP)');
        $this->command->info('   and pay upgrade fee (difference) to unlock new subscription tier.');
    }
}
