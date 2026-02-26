<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\Benefit;
use Illuminate\Database\Seeder;

class BenefitSeeder extends Seeder
{
    public function run(): void
    {
        $benefits = [
            // Storage Allocation
            [
                'name' => 'Cloud Storage Allocation',
                'slug' => 'cloud-storage-allocation',
                'category' => 'cloud',
                'benefit_type' => 'starter_kit',
                'description' => 'Secure cloud storage space for your files and backups',
                'icon' => 'CloudIcon',
                'unit' => 'GB',
                'tier_allocations' => [
                    'lite' => 5,
                    'basic' => 10,
                    'growth_plus' => 25,
                    'pro' => 50,
                ],
                'is_active' => true,
                'is_coming_soon' => false,
                'sort_order' => 1,
            ],

            // Learning & Knowledge
            [
                'name' => 'E-Books',
                'slug' => 'ebooks',
                'category' => 'learning',
                'benefit_type' => 'starter_kit',
                'description' => 'Digital e-books on business, life skills, and financial literacy',
                'icon' => 'BookOpenIcon',
                'is_active' => true,
                'is_coming_soon' => false,
                'sort_order' => 2,
            ],
            [
                'name' => 'Life-Plus App Access',
                'slug' => 'life-plus-app',
                'category' => 'apps',
                'benefit_type' => 'starter_kit',
                'description' => 'Personal development and life management application',
                'icon' => 'SparklesIcon',
                'is_active' => true,
                'is_coming_soon' => false,
                'sort_order' => 3,
            ],
            [
                'name' => 'Online Short Courses',
                'slug' => 'online-courses',
                'category' => 'learning',
                'benefit_type' => 'starter_kit',
                'description' => 'Access to curated online short courses for skill development',
                'icon' => 'AcademicCapIcon',
                'is_active' => true,
                'is_coming_soon' => false,
                'sort_order' => 4,
            ],
            [
                'name' => 'LGR Qualification',
                'slug' => 'lgr-qualification',
                'category' => 'resources',
                'benefit_type' => 'starter_kit',
                'description' => 'Loyalty Growth Rewards qualification for earning commissions and bonuses',
                'icon' => 'StarIcon',
                'is_active' => true,
                'is_coming_soon' => false,
                'sort_order' => 5,
            ],

            // Workshop Access
            [
                'name' => 'Introductory Workshop (Digital)',
                'slug' => 'workshop-intro-digital',
                'category' => 'learning',
                'benefit_type' => 'starter_kit',
                'description' => 'Digital introductory workshop for K300 tier members',
                'icon' => 'VideoCameraIcon',
                'tier_allocations' => ['lite' => true],
                'is_active' => true,
                'is_coming_soon' => false,
                'sort_order' => 5,
            ],
            [
                'name' => 'Recorded Workshop Access',
                'slug' => 'workshop-recorded',
                'category' => 'learning',
                'benefit_type' => 'starter_kit',
                'description' => 'Access to recorded workshop sessions for K500 tier members',
                'icon' => 'PlayCircleIcon',
                'tier_allocations' => ['basic' => true],
                'is_active' => true,
                'is_coming_soon' => false,
                'sort_order' => 6,
            ],
            [
                'name' => 'Priority Workshop Participation',
                'slug' => 'workshop-priority',
                'category' => 'learning',
                'benefit_type' => 'starter_kit',
                'description' => 'Priority access to live workshop participation for K1000 tier members',
                'icon' => 'UserGroupIcon',
                'tier_allocations' => ['growth_plus' => true],
                'is_active' => true,
                'is_coming_soon' => false,
                'sort_order' => 7,
            ],
            [
                'name' => 'Advanced Workshop Access',
                'slug' => 'workshop-advanced',
                'category' => 'learning',
                'benefit_type' => 'starter_kit',
                'description' => 'Access to advanced workshops and masterclasses for K2000 tier members',
                'icon' => 'AcademicCapIcon',
                'tier_allocations' => ['pro' => true],
                'is_active' => true,
                'is_coming_soon' => false,
                'sort_order' => 8,
            ],

            // Physical Items
            [
                'name' => 'Branded MyGrowNet T-Shirt',
                'slug' => 'branded-tshirt',
                'category' => 'resources',
                'benefit_type' => 'physical_item',
                'description' => 'Official MyGrowNet branded t-shirt (K500+ tiers)',
                'icon' => 'GiftIcon',
                'unit' => 'piece',
                'tier_allocations' => ['basic' => true, 'growth_plus' => true, 'pro' => true],
                'is_active' => true,
                'is_coming_soon' => false,
                'sort_order' => 9,
            ],
            [
                'name' => 'Custom Branded Shirt Option',
                'slug' => 'custom-branded-shirt',
                'category' => 'resources',
                'benefit_type' => 'physical_item',
                'description' => 'Option for custom branded shirt with your name (K1000+ tiers)',
                'icon' => 'SparklesIcon',
                'unit' => 'piece',
                'tier_allocations' => ['growth_plus' => true, 'pro' => true],
                'is_active' => true,
                'is_coming_soon' => false,
                'sort_order' => 10,
            ],
            [
                'name' => 'Premium Branding Package',
                'slug' => 'premium-branding-package',
                'category' => 'resources',
                'benefit_type' => 'physical_item',
                'description' => 'Premium branding package eligibility for K2000 tier members',
                'icon' => 'StarIcon',
                'unit' => 'package',
                'tier_allocations' => ['pro' => true],
                'is_active' => true,
                'is_coming_soon' => false,
                'sort_order' => 11,
            ],

            // Monthly Services
            [
                'name' => 'Storage Subscription Upgrades',
                'slug' => 'storage-upgrades',
                'category' => 'cloud',
                'benefit_type' => 'monthly_service',
                'description' => 'Upgrade your cloud storage with monthly subscription plans',
                'icon' => 'ArrowUpCircleIcon',
                'unit' => 'GB',
                'is_active' => true,
                'is_coming_soon' => false,
                'sort_order' => 20,
            ],
            [
                'name' => 'Video Streaming Service',
                'slug' => 'video-streaming',
                'category' => 'media',
                'benefit_type' => 'monthly_service',
                'description' => 'Premium video streaming and entertainment content',
                'icon' => 'FilmIcon',
                'is_active' => true,
                'is_coming_soon' => true,
                'sort_order' => 21,
            ],
            [
                'name' => 'Music Streaming Service',
                'slug' => 'music-streaming',
                'category' => 'media',
                'benefit_type' => 'monthly_service',
                'description' => 'Unlimited access to music streaming service',
                'icon' => 'MusicalNoteIcon',
                'is_active' => true,
                'is_coming_soon' => true,
                'sort_order' => 22,
            ],
            [
                'name' => 'Workshop Participation',
                'slug' => 'workshop-participation',
                'category' => 'learning',
                'benefit_type' => 'monthly_service',
                'description' => 'Monthly workshop participation and live training sessions',
                'icon' => 'UserGroupIcon',
                'is_active' => true,
                'is_coming_soon' => false,
                'sort_order' => 23,
            ],
            [
                'name' => 'MyGrowNet Apps Subscription',
                'slug' => 'mygrownet-apps-subscription',
                'category' => 'apps',
                'benefit_type' => 'monthly_service',
                'description' => 'Monthly subscription to MyGrowNet productivity apps',
                'icon' => 'DevicePhoneMobileIcon',
                'is_active' => true,
                'is_coming_soon' => false,
                'sort_order' => 24,
            ],
            [
                'name' => 'MyGrowNet Products Purchase',
                'slug' => 'mygrownet-products',
                'category' => 'resources',
                'benefit_type' => 'monthly_service',
                'description' => 'Access to purchase MyGrowNet products and merchandise',
                'icon' => 'ShoppingBagIcon',
                'is_active' => true,
                'is_coming_soon' => false,
                'sort_order' => 25,
            ],
        ];

        foreach ($benefits as $benefit) {
            Benefit::updateOrCreate(
                ['slug' => $benefit['slug']],
                $benefit
            );
        }
    }
}
