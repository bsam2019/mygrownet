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
                'name' => 'Comedy',
                'slug' => 'comedy',
                'description' => 'Skits, stand-up, and comedy shows from local creators',
                'icon' => 'laugh',
                'color' => '#e2571f',
                'sort_order' => 1,
                'children' => [
                    ['name' => 'Skits', 'icon' => 'theater-comedy'],
                    ['name' => 'Stand-up Comedy', 'icon' => 'mic'],
                    ['name' => 'Comedy Sketches', 'icon' => 'movie'],
                ],
            ],
            [
                'name' => 'Movies',
                'slug' => 'movies',
                'description' => 'Full-length Zambian films and cinema',
                'icon' => 'movie',
                'color' => '#d97706',
                'sort_order' => 2,
                'children' => [
                    ['name' => 'Drama Films', 'icon' => 'drama-masks'],
                    ['name' => 'Action Films', 'icon' => 'bolt'],
                    ['name' => 'Romance Films', 'icon' => 'favorite'],
                    ['name' => 'Thriller Films', 'icon' => 'visibility'],
                ],
            ],
            [
                'name' => 'Series',
                'slug' => 'series',
                'description' => 'Original web series, soaps, and episodic dramas',
                'icon' => 'live_tv',
                'color' => '#7c3aed',
                'sort_order' => 3,
                'children' => [
                    ['name' => 'Soap Operas', 'icon' => 'favorite'],
                    ['name' => 'Web Series', 'icon' => 'smart_display'],
                    ['name' => 'Drama Series', 'icon' => 'theater_comedy'],
                ],
            ],
            [
                'name' => 'Music',
                'slug' => 'music',
                'description' => 'Music videos, live performances, and concerts',
                'icon' => 'music_note',
                'color' => '#ec4899',
                'sort_order' => 4,
                'children' => [
                    ['name' => 'Music Videos', 'icon' => 'play_circle'],
                    ['name' => 'Live Concerts', 'icon' => 'mic_external_on'],
                    ['name' => 'Traditional Music', 'icon' => 'music'],
                ],
            ],
            [
                'name' => 'Documentary',
                'slug' => 'documentary',
                'description' => 'Documentaries on Zambian life, culture, and history',
                'icon' => 'public',
                'color' => '#059669',
                'sort_order' => 5,
                'children' => [
                    ['name' => 'Culture & Heritage', 'icon' => 'landscape'],
                    ['name' => 'History', 'icon' => 'history'],
                    ['name' => 'Nature & Wildlife', 'icon' => 'park'],
                    ['name' => 'Society & People', 'icon' => 'group'],
                ],
            ],
            [
                'name' => 'Education',
                'slug' => 'education',
                'description' => 'Lessons, tutorials, and educational content',
                'icon' => 'school',
                'color' => '#2563eb',
                'sort_order' => 6,
                'children' => [
                    ['name' => 'Tutorials', 'icon' => 'menu_book'],
                    ['name' => 'Workshops', 'icon' => 'build'],
                    ['name' => 'Webinars', 'icon' => 'record_voice_over'],
                    ['name' => 'Business & Entrepreneurship', 'icon' => 'briefcase'],
                ],
            ],
            [
                'name' => 'Lifestyle',
                'slug' => 'lifestyle',
                'description' => 'Fashion, food, travel, and everyday living',
                'icon' => 'self_improvement',
                'color' => '#0891b2',
                'sort_order' => 7,
                'children' => [
                    ['name' => 'Fashion', 'icon' => 'checkroom'],
                    ['name' => 'Food & Cooking', 'icon' => 'restaurant'],
                    ['name' => 'Travel & Places', 'icon' => 'flight'],
                    ['name' => 'Home & Family', 'icon' => 'home'],
                ],
            ],
            [
                'name' => 'Sports & Fitness',
                'slug' => 'sports-fitness',
                'description' => 'Sports highlights, fitness training, and health',
                'icon' => 'sports_soccer',
                'color' => '#dc2626',
                'sort_order' => 8,
                'children' => [
                    ['name' => 'Football', 'icon' => 'sports_soccer'],
                    ['name' => 'Fitness', 'icon' => 'fitness_center'],
                    ['name' => 'Health & Wellness', 'icon' => 'favorite'],
                ],
            ],
            [
                'name' => 'News & Talk',
                'slug' => 'news-talk',
                'description' => 'Current affairs, talk shows, and interviews',
                'icon' => 'newspaper',
                'color' => '#4b5563',
                'sort_order' => 9,
                'children' => [
                    ['name' => 'News', 'icon' => 'newspaper'],
                    ['name' => 'Talk Shows', 'icon' => 'record_voice_over'],
                    ['name' => 'Interviews', 'icon' => 'mic'],
                ],
            ],
            [
                'name' => 'Kids',
                'slug' => 'kids',
                'description' => 'Family-friendly cartoons and children\u2019s shows',
                'icon' => 'toys',
                'color' => '#f59e0b',
                'sort_order' => 10,
                'children' => [
                    ['name' => 'Cartoons', 'icon' => 'toys'],
                    ['name' => 'Kids Songs', 'icon' => 'music_note'],
                    ['name' => 'Educational Kids', 'icon' => 'school'],
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
