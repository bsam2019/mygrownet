<?php

namespace Database\Seeders;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoCategory;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoTag;
use Illuminate\Database\Seeder;

class GrowStreamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedCategories();
        $this->seedTags();
    }

    /**
     * Seed video categories
     */
    protected function seedCategories(): void
    {
        $categories = [
            [
                'name' => 'Business & Entrepreneurship',
                'slug' => 'business-entrepreneurship',
                'description' => 'Learn business skills and entrepreneurship',
                'icon' => 'briefcase',
                'color' => '#2563eb',
                'sort_order' => 1,
                'children' => [
                    ['name' => 'Starting a Business', 'icon' => 'rocket'],
                    ['name' => 'Business Management', 'icon' => 'chart-bar'],
                    ['name' => 'Marketing & Sales', 'icon' => 'megaphone'],
                    ['name' => 'Finance & Accounting', 'icon' => 'calculator'],
                ],
            ],
            [
                'name' => 'Technology & Programming',
                'slug' => 'technology-programming',
                'description' => 'Master technology and coding skills',
                'icon' => 'code',
                'color' => '#7c3aed',
                'sort_order' => 2,
                'children' => [
                    ['name' => 'Web Development', 'icon' => 'globe'],
                    ['name' => 'Mobile Development', 'icon' => 'device-mobile'],
                    ['name' => 'Data Science', 'icon' => 'chart-pie'],
                    ['name' => 'Cybersecurity', 'icon' => 'shield-check'],
                ],
            ],
            [
                'name' => 'Personal Development',
                'slug' => 'personal-development',
                'description' => 'Grow personally and professionally',
                'icon' => 'user',
                'color' => '#059669',
                'sort_order' => 3,
                'children' => [
                    ['name' => 'Leadership', 'icon' => 'users'],
                    ['name' => 'Communication Skills', 'icon' => 'chat'],
                    ['name' => 'Time Management', 'icon' => 'clock'],
                    ['name' => 'Goal Setting', 'icon' => 'target'],
                ],
            ],
            [
                'name' => 'Finance & Investment',
                'slug' => 'finance-investment',
                'description' => 'Learn about money management and investing',
                'icon' => 'currency-dollar',
                'color' => '#d97706',
                'sort_order' => 4,
                'children' => [
                    ['name' => 'Personal Finance', 'icon' => 'wallet'],
                    ['name' => 'Investing Basics', 'icon' => 'trending-up'],
                    ['name' => 'Real Estate', 'icon' => 'home'],
                    ['name' => 'Cryptocurrency', 'icon' => 'currency-bitcoin'],
                ],
            ],
            [
                'name' => 'Health & Wellness',
                'slug' => 'health-wellness',
                'description' => 'Improve your health and wellbeing',
                'icon' => 'heart',
                'color' => '#dc2626',
                'sort_order' => 5,
                'children' => [
                    ['name' => 'Nutrition', 'icon' => 'apple'],
                    ['name' => 'Fitness', 'icon' => 'dumbbell'],
                    ['name' => 'Mental Health', 'icon' => 'brain'],
                    ['name' => 'Meditation', 'icon' => 'sparkles'],
                ],
            ],
            [
                'name' => 'Creative Arts',
                'slug' => 'creative-arts',
                'description' => 'Explore your creative side',
                'icon' => 'palette',
                'color' => '#ec4899',
                'sort_order' => 6,
                'children' => [
                    ['name' => 'Graphic Design', 'icon' => 'photo'],
                    ['name' => 'Video Production', 'icon' => 'video-camera'],
                    ['name' => 'Music', 'icon' => 'musical-note'],
                    ['name' => 'Writing', 'icon' => 'pencil'],
                ],
            ],
            [
                'name' => 'Agriculture & Farming',
                'slug' => 'agriculture-farming',
                'description' => 'Modern farming techniques and agribusiness',
                'icon' => 'leaf',
                'color' => '#16a34a',
                'sort_order' => 7,
                'children' => [
                    ['name' => 'Crop Production', 'icon' => 'plant'],
                    ['name' => 'Livestock Management', 'icon' => 'cow'],
                    ['name' => 'Agribusiness', 'icon' => 'store'],
                    ['name' => 'Sustainable Farming', 'icon' => 'recycle'],
                ],
            ],
            [
                'name' => 'Community Development',
                'slug' => 'community-development',
                'description' => 'Building stronger communities',
                'icon' => 'users-group',
                'color' => '#0891b2',
                'sort_order' => 8,
                'children' => [
                    ['name' => 'Social Enterprise', 'icon' => 'building-community'],
                    ['name' => 'Cooperative Management', 'icon' => 'handshake'],
                    ['name' => 'Community Organizing', 'icon' => 'megaphone'],
                    ['name' => 'Youth Empowerment', 'icon' => 'academic-cap'],
                ],
            ],
        ];

        foreach ($categories as $categoryData) {
            $children = $categoryData['children'] ?? [];
            unset($categoryData['children']);

            $category = VideoCategory::updateOrCreate(
                ['slug' => $categoryData['slug']],
                $categoryData
            );

            // Create subcategories
            foreach ($children as $index => $childData) {
                VideoCategory::updateOrCreate(
                    ['slug' => \Illuminate\Support\Str::slug($childData['name'])],
                    [
                        'name' => $childData['name'],
                        'slug' => \Illuminate\Support\Str::slug($childData['name']),
                        'parent_id' => $category->id,
                        'icon' => $childData['icon'],
                        'color' => $category->color,
                        'sort_order' => $index + 1,
                        'is_active' => true,
                    ]
                );
            }
        }

        $this->command->info('✓ Categories seeded successfully');
    }

    /**
     * Seed common video tags
     */
    protected function seedTags(): void
    {
        $tags = [
            'beginner-friendly',
            'advanced',
            'practical',
            'theory',
            'hands-on',
            'case-study',
            'tutorial',
            'workshop',
            'masterclass',
            'quick-tips',
            'in-depth',
            'step-by-step',
            'real-world',
            'certification',
            'free',
            'premium',
            'trending',
            'popular',
            'new',
            'updated',
        ];

        foreach ($tags as $tag) {
            VideoTag::updateOrCreate(
                ['slug' => $tag],
                [
                    'name' => ucwords(str_replace('-', ' ', $tag)),
                    'slug' => $tag,
                ]
            );
        }

        $this->command->info('✓ Tags seeded successfully');
    }
}
