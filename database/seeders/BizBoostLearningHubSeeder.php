<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BizBoostLearningHubSeeder extends Seeder
{
    public function run(): void
    {
        $courses = [
            [
                'title' => 'Marketing Fundamentals',
                'slug' => 'marketing-fundamentals',
                'description' => 'Learn the basics of marketing for small businesses. Understand your audience, create compelling messageience, and convert followers into customers.',
                'category' => 'Marketing',
                'difficulty' => 'beginner',
                'duration_minutes' => 45,
                'tier_required' => 'free',
                'has_certificate' => true,
                'is_published' => true,
                'sort_order' => 1,
                'lessons' => [
                    ['title' => 'Introduction to Social Media Marketing', 'duration' => 8],
                    ['title' => 'Choosing the Right Platforms', 'duration' => 10],
                    ['title' => 'Creating Your Content Strategy', 'duration' => 12],
                    ['title' => 'Engaging Your Audience', 'duration' => 10],
                    ['title' => 'Measuring Success', 'duration' => 5],
                ],
            ],
            [
                'title' => 'Building Your Brand Identity',
                'slug' => 'building-your-brand-identity',
                'description' => 'Discover how to create a memorable brand that resonates with your target audience. From logo design to brand voice.',
                'category' => 'Branding',
                'difficulty' => 'beginner',
                'duration_minutes' => 60,
                'tier_required' => 'free',
                'has_certificate' => true,
                'is_published' => true,
                'sort_order' => 2,
                'lessons' => [
                    ['title' => 'What is Brand Identity?', 'duration' => 10],
                    ['title' => 'Defining Your Brand Values', 'duration' => 12],
                    ['title' => 'Visual Identity Basics', 'duration' => 15],
                    ['title' => 'Finding Your Brand Voice', 'duration' => 13],
                    ['title' => 'Consistency Across Channels', 'duration' => 10],
                ],
            ],
            [
                'title' => 'Content Creation Masterclass',
                'slug' => 'content-creation-masterclass',
                'description' => 'Master the art of creating compelling content that drives engagement and sales. Photos, videos, and copywriting.',
                'category' => 'Marketing',
                'difficulty' => 'intermediate',
                'duration_minutes' => 90,
                'tier_required' => 'basic',
                'has_certificate' => true,
                'is_published' => true,
                'sort_order' => 3,
                'lessons' => [
                    ['title' => 'Content Strategy Deep Dive', 'duration' => 15],
                    ['title' => 'Photography for Social Media', 'duration' => 20],
                    ['title' => 'Video Content That Converts', 'duration' => 20],
                    ['title' => 'Writing Compelling Copy', 'duration' => 20],
                    ['title' => 'Content Calendar Planning', 'duration' => 15],
                ],
            ],
            [
                'title' => 'Customer Service Excellence',
                'slug' => 'customer-service-excellence',
                'description' => 'Learn how to deliver exceptional customer service that builds loyalty and generates referrals.',
                'category' => 'Customer Service',
                'difficulty' => 'beginner',
                'duration_minutes' => 40,
                'tier_required' => 'free',
                'has_certificate' => false,
                'is_published' => true,
                'sort_order' => 4,
                'lessons' => [
                    ['title' => 'The Customer Service Mindset', 'duration' => 8],
                    ['title' => 'Handling Complaints Gracefully', 'duration' => 12],
                    ['title' => 'Building Customer Relationships', 'duration' => 10],
                    ['title' => 'Going Above and Beyond', 'duration' => 10],
                ],
            ],
            [
                'title' => 'Sales Techniques for SMEs',
                'slug' => 'sales-techniques-for-smes',
                'description' => 'Proven sales techniques tailored for small and medium businesses. Close more deals and grow your revenue.',
                'category' => 'Sales',
                'difficulty' => 'intermediate',
                'duration_minutes' => 75,
                'tier_required' => 'professional',
                'has_certificate' => true,
                'is_published' => true,
                'sort_order' => 5,
                'lessons' => [
                    ['title' => 'Understanding Your Customer', 'duration' => 15],
                    ['title' => 'The Art of the Pitch', 'duration' => 15],
                    ['title' => 'Handling Objections', 'duration' => 15],
                    ['title' => 'Closing Techniques', 'duration' => 15],
                    ['title' => 'Follow-up Strategies', 'duration' => 15],
                ],
            ],
            [
                'title' => 'Advanced Marketing Analytics',
                'slug' => 'advanced-marketing-analytics',
                'description' => 'Deep dive into marketing analytics. Track ROI, optimize campaigns, and make data-driven decisions.',
                'category' => 'Marketing',
                'difficulty' => 'advanced',
                'duration_minutes' => 120,
                'tier_required' => 'business',
                'has_certificate' => true,
                'is_published' => true,
                'sort_order' => 6,
                'lessons' => [
                    ['title' => 'Analytics Fundamentals', 'duration' => 20],
                    ['title' => 'Setting Up Tracking', 'duration' => 25],
                    ['title' => 'Understanding Metrics', 'duration' => 25],
                    ['title' => 'A/B Testing Strategies', 'duration' => 25],
                    ['title' => 'Reporting and Optimization', 'duration' => 25],
                ],
            ],
        ];

        foreach ($courses as $courseData) {
            $lessons = $courseData['lessons'];
            unset($courseData['lessons']);
            
            $courseData['lessons_count'] = count($lessons);
            $courseData['created_at'] = now();
            $courseData['updated_at'] = now();
            
            $courseId = DB::table('bizboost_courses')->insertGetId($courseData);
            
            foreach ($lessons as $index => $lesson) {
                DB::table('bizboost_lessons')->insert([
                    'course_id' => $courseId,
                    'title' => $lesson['title'],
                    'slug' => Str::slug($lesson['title']),
                    'content' => $this->generateLessonContent($lesson['title']),
                    'duration_minutes' => $lesson['duration'],
                    'sort_order' => $index + 1,
                    'is_published' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function generateLessonContent(string $title): string
    {
        return "<h2>{$title}</h2>
<p>Welcome to this lesson on {$title}. In this module, you'll learn practical strategies and techniques that you can apply immediately to your business.</p>

<h3>Key Learning Objectives</h3>
<ul>
    <li>Understand the core concepts and principles</li>
    <li>Learn practical implementation strategies</li>
    <li>Discover common mistakes to avoid</li>
    <li>Apply what you've learned to your business</li>
</ul>

<h3>Getting Started</h3>
<p>Before we dive in, take a moment to think about how this topic relates to your current business challenges. What specific outcomes are you hoping to achieve?</p>

<h3>Summary</h3>
<p>Remember, the key to success is consistent application. Don't try to implement everything at once - start with one or two strategies and build from there.</p>";
    }
}
