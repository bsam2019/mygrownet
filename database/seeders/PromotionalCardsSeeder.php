<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Infrastructure\Persistence\Eloquent\Promotion\PromotionalCardModel;
use Illuminate\Support\Facades\DB;

class PromotionalCardsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cards = [
            [
                'title' => 'Join MyGrowNet Today!',
                'slug' => 'join-mygrownet-today',
                'description' => 'Start your journey to financial growth and community empowerment. Learn, Earn, and Grow with MyGrowNet.',
                'image_path' => 'promotional-cards/join-mygrownet.jpg', // Placeholder
                'category' => 'opportunity',
                'sort_order' => 1,
                'is_active' => true,
                'og_title' => 'Join MyGrowNet - Learn, Earn, Grow',
                'og_description' => 'Discover a platform that empowers you through education, mentorship, and income opportunities. Join thousands of members building their future.',
                'share_count' => 0,
                'view_count' => 0,
                'created_by' => 1, // Admin user
            ],
            [
                'title' => '7-Level Professional Growth System',
                'slug' => '7-level-professional-growth-system',
                'description' => 'Progress from Associate to Ambassador. Build your network and unlock new opportunities at each level.',
                'image_path' => 'promotional-cards/7-level-system.jpg', // Placeholder
                'category' => 'training',
                'sort_order' => 2,
                'is_active' => true,
                'og_title' => 'MyGrowNet 7-Level Growth System',
                'og_description' => 'A structured path to success. Start as an Associate and grow to Ambassador level with our proven system.',
                'share_count' => 0,
                'view_count' => 0,
                'created_by' => 1,
            ],
            [
                'title' => 'Earn While You Learn',
                'slug' => 'earn-while-you-learn',
                'description' => 'Complete training modules, attend events, and build your network while earning rewards through our LGR system.',
                'image_path' => 'promotional-cards/earn-while-learn.jpg', // Placeholder
                'category' => 'opportunity',
                'sort_order' => 3,
                'is_active' => true,
                'og_title' => 'Earn While You Learn - MyGrowNet',
                'og_description' => 'Get rewarded for your growth! Earn up to K2,100 over 70 days through active participation and learning.',
                'share_count' => 0,
                'view_count' => 0,
                'created_by' => 1,
            ],
            [
                'title' => 'Success Story: From Zero to Hero',
                'slug' => 'success-story-zero-to-hero',
                'description' => 'Meet members who transformed their lives through MyGrowNet. Your success story could be next!',
                'image_path' => 'promotional-cards/success-story.jpg', // Placeholder
                'category' => 'success',
                'sort_order' => 4,
                'is_active' => true,
                'og_title' => 'Real Success Stories - MyGrowNet',
                'og_description' => 'Discover how ordinary people achieved extraordinary results. Join a community of winners.',
                'share_count' => 0,
                'view_count' => 0,
                'created_by' => 1,
            ],
            [
                'title' => 'Free Business Training Available',
                'slug' => 'free-business-training-available',
                'description' => 'Access our comprehensive library of business training modules. Learn financial literacy, time management, and more!',
                'image_path' => 'promotional-cards/free-training.jpg', // Placeholder
                'category' => 'training',
                'sort_order' => 5,
                'is_active' => true,
                'og_title' => 'Free Business Training - MyGrowNet',
                'og_description' => 'Master essential business skills with our free training modules. Start learning today!',
                'share_count' => 0,
                'view_count' => 0,
                'created_by' => 1,
            ],
            [
                'title' => 'Build Your 3x3 Network',
                'slug' => 'build-your-3x3-network',
                'description' => 'Our unique 3x3 forced matrix system ensures everyone has a fair chance to grow their network and earn commissions.',
                'image_path' => 'promotional-cards/3x3-network.jpg', // Placeholder
                'category' => 'general',
                'sort_order' => 6,
                'is_active' => true,
                'og_title' => '3x3 Matrix System - MyGrowNet',
                'og_description' => 'Fair, transparent, and powerful. Build a network of up to 3,279 members with our proven matrix system.',
                'share_count' => 0,
                'view_count' => 0,
                'created_by' => 1,
            ],
            [
                'title' => 'Quarterly Profit Sharing',
                'slug' => 'quarterly-profit-sharing',
                'description' => 'All active members receive direct profit-sharing from our community investment projects. Real profits, real rewards.',
                'image_path' => 'promotional-cards/profit-sharing.jpg', // Placeholder
                'category' => 'opportunity',
                'sort_order' => 7,
                'is_active' => true,
                'og_title' => 'Quarterly Profit Sharing - MyGrowNet',
                'og_description' => 'Share in the success! Active members receive quarterly profit distributions from community projects.',
                'share_count' => 0,
                'view_count' => 0,
                'created_by' => 1,
            ],
            [
                'title' => 'New: Venture Builder Program',
                'slug' => 'new-venture-builder-program',
                'description' => 'Co-invest in vetted business projects and become a legal shareholder. Build real wealth through business ownership.',
                'image_path' => 'promotional-cards/venture-builder.jpg', // Placeholder
                'category' => 'announcement',
                'sort_order' => 8,
                'is_active' => true,
                'og_title' => 'Venture Builder Program - MyGrowNet',
                'og_description' => 'Invest in real businesses, own real equity. The Venture Builder program is now open!',
                'share_count' => 0,
                'view_count' => 0,
                'created_by' => 1,
            ],
        ];

        foreach ($cards as $card) {
            PromotionalCardModel::updateOrInsert(
                ['slug' => $card['slug']],
                $card
            );
        }

        $this->command->info('✅ Promotional cards seeded successfully!');
        $this->command->info('📝 Note: Update image_path values with actual uploaded images.');
    }
}
