<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            // Starter Kits (One-time purchase for new members)
            [
                'name' => 'Starter Kit - Associate',
                'slug' => 'starter-kit-associate',
                'description' => 'Complete starter package for new members. Includes registration fee, first month subscription, and welcome materials.',
                'price' => 150.00, // Registration fee (50) + First month Associate (100)
                'billing_cycle' => 'one-time',
                'duration_months' => 1,
                'features' => [
                    'One-time registration fee',
                    'First month Associate membership included',
                    'Welcome learning pack',
                    'Getting started guide',
                    'Community access',
                    'Initial mentorship session',
                    'Starter resources bundle'
                ],
                'is_active' => true,
                'sort_order' => 0
            ],
            [
                'name' => 'Associate',
                'slug' => 'associate',
                'description' => 'Entry-level membership with access to basic learning materials and community features',
                'price' => 100.00,
                'billing_cycle' => 'monthly',
                'duration_months' => 1,
                'features' => [
                    'Access to basic learning packs',
                    'Community forum access',
                    'Monthly group coaching sessions',
                    'Basic resource library',
                    'Email support'
                ],
                'is_active' => true,
                'sort_order' => 1
            ],
            [
                'name' => 'Professional',
                'slug' => 'professional',
                'description' => 'Enhanced membership with advanced learning materials and mentorship',
                'price' => 250.00,
                'billing_cycle' => 'monthly',
                'duration_months' => 1,
                'features' => [
                    'All Associate features',
                    'Advanced learning packs',
                    'Weekly group coaching',
                    'One-on-one mentorship (monthly)',
                    'Skills training workshops',
                    'Priority support'
                ],
                'is_active' => true,
                'sort_order' => 2
            ],
            [
                'name' => 'Senior',
                'slug' => 'senior',
                'description' => 'Premium membership with comprehensive training and business support',
                'price' => 500.00,
                'billing_cycle' => 'monthly',
                'duration_months' => 1,
                'features' => [
                    'All Professional features',
                    'Premium learning content',
                    'Bi-weekly one-on-one coaching',
                    'Business planning support',
                    'Networking events access',
                    'Certificate programs',
                    'Dedicated support'
                ],
                'is_active' => true,
                'sort_order' => 3
            ],
            [
                'name' => 'Manager',
                'slug' => 'manager',
                'description' => 'Executive membership with leadership training and team building resources',
                'price' => 1000.00,
                'billing_cycle' => 'monthly',
                'duration_months' => 1,
                'features' => [
                    'All Senior features',
                    'Leadership development program',
                    'Team building resources',
                    'Weekly one-on-one coaching',
                    'Exclusive masterclasses',
                    'Business startup capital eligibility',
                    'VIP support'
                ],
                'is_active' => true,
                'sort_order' => 4
            ],
            [
                'name' => 'Director',
                'slug' => 'director',
                'description' => 'Elite membership with strategic business support and investment opportunities',
                'price' => 2000.00,
                'billing_cycle' => 'monthly',
                'duration_months' => 1,
                'features' => [
                    'All Manager features',
                    'Strategic business consulting',
                    'Investment opportunity access',
                    'Unlimited one-on-one coaching',
                    'Private networking events',
                    'Higher profit-sharing percentage',
                    'Concierge support'
                ],
                'is_active' => true,
                'sort_order' => 5
            ],
            [
                'name' => 'Executive',
                'slug' => 'executive',
                'description' => 'Top-tier membership with comprehensive business ecosystem access',
                'price' => 3500.00,
                'billing_cycle' => 'monthly',
                'duration_months' => 1,
                'features' => [
                    'All Director features',
                    'Business ecosystem access',
                    'Partnership opportunities',
                    'International networking',
                    'Premium investment access',
                    'Personal brand development',
                    'White-glove support'
                ],
                'is_active' => true,
                'sort_order' => 6
            ],
            [
                'name' => 'Ambassador',
                'slug' => 'ambassador',
                'description' => 'Ultimate membership with brand ambassador privileges and maximum benefits',
                'price' => 5000.00,
                'billing_cycle' => 'monthly',
                'duration_months' => 1,
                'features' => [
                    'All Executive features',
                    'Brand ambassador status',
                    'Speaking opportunities',
                    'Maximum profit-sharing',
                    'Exclusive investment deals',
                    'Global networking access',
                    'Personal success team',
                    'Lifetime achievement recognition'
                ],
                'is_active' => true,
                'sort_order' => 7
            ],
            // Annual packages with discounts
            [
                'name' => 'Professional Annual',
                'slug' => 'professional-annual',
                'description' => 'Professional membership paid annually (save 2 months)',
                'price' => 2500.00,
                'billing_cycle' => 'annual',
                'duration_months' => 12,
                'features' => [
                    'All Professional features',
                    '2 months free (annual payment)',
                    'Annual planning session',
                    'Bonus learning materials'
                ],
                'is_active' => true,
                'sort_order' => 8
            ],
            [
                'name' => 'Senior Annual',
                'slug' => 'senior-annual',
                'description' => 'Senior membership paid annually (save 2 months)',
                'price' => 5000.00,
                'billing_cycle' => 'annual',
                'duration_months' => 12,
                'features' => [
                    'All Senior features',
                    '2 months free (annual payment)',
                    'Annual strategic planning',
                    'Exclusive annual retreat access'
                ],
                'is_active' => true,
                'sort_order' => 9
            ]
        ];

        foreach ($packages as $package) {
            Package::updateOrCreate(
                ['slug' => $package['slug']],
                $package
            );
        }

        $this->command->info('Packages seeded successfully!');
    }
}
