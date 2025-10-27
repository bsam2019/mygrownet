<?php

namespace Database\Seeders;

use App\Models\StarterKitContentItem;
use Illuminate\Database\Seeder;

class StarterKitContentSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // Training Modules (3 items)
            [
                'title' => 'Business Fundamentals Training',
                'description' => 'Master the basics of business operations, financial management, and strategic planning.',
                'category' => 'training',
                'unlock_day' => 0,
                'estimated_value' => 100,
                'sort_order' => 1,
            ],
            [
                'title' => 'Network Building Strategies',
                'description' => 'Learn proven techniques for building and managing your professional network effectively.',
                'category' => 'training',
                'unlock_day' => 7,
                'estimated_value' => 100,
                'sort_order' => 2,
            ],
            [
                'title' => 'Financial Success Planning',
                'description' => 'Develop a comprehensive financial plan to achieve your income and wealth goals.',
                'category' => 'training',
                'unlock_day' => 14,
                'estimated_value' => 100,
                'sort_order' => 3,
            ],

            // eBooks (3 items)
            [
                'title' => 'MyGrowNet Success Guide',
                'description' => 'Complete guide to maximizing your success on the MyGrowNet platform.',
                'category' => 'ebook',
                'unlock_day' => 0,
                'estimated_value' => 50,
                'sort_order' => 4,
            ],
            [
                'title' => 'Network Building Mastery',
                'description' => 'Advanced strategies for building a thriving network and community.',
                'category' => 'ebook',
                'unlock_day' => 10,
                'estimated_value' => 50,
                'sort_order' => 5,
            ],
            [
                'title' => 'Financial Freedom Blueprint',
                'description' => 'Step-by-step roadmap to achieving financial independence.',
                'category' => 'ebook',
                'unlock_day' => 20,
                'estimated_value' => 50,
                'sort_order' => 6,
            ],

            // Video Tutorials (3 items)
            [
                'title' => 'Platform Navigation Tutorial',
                'description' => 'Complete walkthrough of all MyGrowNet features and how to use them effectively.',
                'category' => 'video',
                'unlock_day' => 0,
                'estimated_value' => 70,
                'sort_order' => 7,
            ],
            [
                'title' => 'Building Your Network',
                'description' => 'Video guide on recruiting, onboarding, and supporting your team members.',
                'category' => 'video',
                'unlock_day' => 5,
                'estimated_value' => 70,
                'sort_order' => 8,
            ],
            [
                'title' => 'Maximizing Your Earnings',
                'description' => 'Learn how to optimize your income through all available revenue streams.',
                'category' => 'video',
                'unlock_day' => 15,
                'estimated_value' => 60,
                'sort_order' => 9,
            ],

            // Marketing Tools (3 items)
            [
                'title' => 'Marketing Templates Pack',
                'description' => 'Ready-to-use templates for social media, emails, and presentations.',
                'category' => 'tool',
                'unlock_day' => 0,
                'estimated_value' => 30,
                'sort_order' => 10,
            ],
            [
                'title' => 'Professional Pitch Deck',
                'description' => 'Customizable presentation for pitching MyGrowNet to prospects.',
                'category' => 'tool',
                'unlock_day' => 3,
                'estimated_value' => 40,
                'sort_order' => 11,
            ],
            [
                'title' => 'Social Media Content Calendar',
                'description' => '30 days of pre-planned social media posts and content ideas.',
                'category' => 'tool',
                'unlock_day' => 7,
                'estimated_value' => 30,
                'sort_order' => 12,
            ],

            // Resource Library (1 item)
            [
                'title' => 'Resource Library Access',
                'description' => 'Curated collection of 50+ free business resources including videos, courses, articles, templates, and tools. Covers business fundamentals, marketing, finance, leadership, and personal development.',
                'category' => 'library',
                'unlock_day' => 0,
                'estimated_value' => 200,
                'sort_order' => 13,
            ],
        ];

        foreach ($items as $item) {
            StarterKitContentItem::create($item);
        }
    }
}
