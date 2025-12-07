<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BizBoostLearningSeeder extends Seeder
{
    public function run(): void
    {
        $courses = [
            [
                'title' => 'Social Media Marketing Fundamentals',
                'slug' => 'social-media-marketing-fundamentals',
                'description' => 'Learn the basics of social media marketing for small businesses. Create engaging content, grow your audience, and drive sales.',
                'category' => 'Marketing',
                'difficulty' => 'beginner',
                'duration_minutes' => 45,
                'tier_required' => 'free',
                'has_certificate' => true,
                'lessons' => [
                    ['title' => 'Introduction to Social Media Marketing', 'duration' => 8],
                    ['title' => 'Choosing the Right Platforms', 'duration' => 10],
                    ['title' => 'Creating Engaging Content', 'duration' => 12],
                    ['title' => 'Building Your Audience', 'duration' => 10],
                    ['title' => 'Measuring Success', 'duration' => 5],
                ],
            ],
            [
                'title' => 'Building Your Brand Identity',
                'slug' => 'building-brand-identity',
                'description' => 'Develop a strong brand identity that resonates with your target audience and sets you apart from competitors.',
                'category' => 'Branding',
                'difficulty' => 'beginner',
                'duration_minutes' => 60,
                'tier_required' => 'free',
                'has_certificate' => true,
                'lessons' => [
                    ['title' => 'What is Brand Identity?', 'duration' => 10],
                    ['title' => 'Defining Your Brand Values', 'duration' => 12],
                    ['title' => 'Creating Visual Identity', 'duration' => 15],
                    ['title' => 'Brand Voice and Messaging', 'duration' => 13],
                    ['title' => 'Consistency Across Channels', 'duration' => 10],
                ],
            ],
            [
                'title' => 'Customer Service Excellence',
                'slug' => 'customer-service-excellence',
                'description' => 'Master the art of customer service to build loyalty and turn customers into brand advocates.',
                'category' => 'Customer Service',
                'difficulty' => 'beginner',
                'duration_minutes' => 40,
                'tier_required' => 'basic',
                'has_certificate' => true,
                'lessons' => [
                    ['title' => 'The Importance of Customer Service', 'duration' => 8],
                    ['title' => 'Communication Skills', 'duration' => 10],
                    ['title' => 'Handling Complaints', 'duration' => 12],
                    ['title' => 'Building Customer Loyalty', 'duration' => 10],
                ],
            ],
            [
                'title' => 'Sales Techniques for SMEs',
                'slug' => 'sales-techniques-smes',
                'description' => 'Learn proven sales techniques tailored for small and medium enterprises to close more deals.',
                'category' => 'Sales',
                'difficulty' => 'intermediate',
                'duration_minutes' => 75,
                'tier_required' => 'basic',
                'has_certificate' => true,
                'lessons' => [
                    ['title' => 'Understanding Your Customer', 'duration' => 12],
                    ['title' => 'The Sales Process', 'duration' => 15],
                    ['title' => 'Objection Handling', 'duration' => 15],
                    ['title' => 'Closing Techniques', 'duration' => 18],
                    ['title' => 'Follow-up Strategies', 'duration' => 15],
                ],
            ],
            [
                'title' => 'Content Marketing Strategy',
                'slug' => 'content-marketing-strategy',
                'description' => 'Create a content marketing strategy that attracts, engages, and converts your target audience.',
                'category' => 'Marketing',
                'difficulty' => 'intermediate',
                'duration_minutes' => 90,
                'tier_required' => 'professional',
                'has_certificate' => true,
                'lessons' => [
                    ['title' => 'Content Marketing Overview', 'duration' => 12],
                    ['title' => 'Content Planning and Calendar', 'duration' => 18],
                    ['title' => 'Creating Different Content Types', 'duration' => 20],
                    ['title' => 'Content Distribution', 'duration' => 15],
                    ['title' => 'Measuring Content Performance', 'duration' => 15],
                    ['title' => 'Repurposing Content', 'duration' => 10],
                ],
            ],
            [
                'title' => 'Advanced Social Media Advertising',
                'slug' => 'advanced-social-media-advertising',
                'description' => 'Master paid social media advertising on Facebook, Instagram, and other platforms.',
                'category' => 'Marketing',
                'difficulty' => 'advanced',
                'duration_minutes' => 120,
                'tier_required' => 'professional',
                'has_certificate' => true,
                'lessons' => [
                    ['title' => 'Introduction to Paid Social', 'duration' => 15],
                    ['title' => 'Facebook Ads Manager', 'duration' => 25],
                    ['title' => 'Audience Targeting', 'duration' => 20],
                    ['title' => 'Ad Creative Best Practices', 'duration' => 20],
                    ['title' => 'Campaign Optimization', 'duration' => 20],
                    ['title' => 'Analytics and Reporting', 'duration' => 20],
                ],
            ],
            [
                'title' => 'Business Growth Strategies',
                'slug' => 'business-growth-strategies',
                'description' => 'Learn strategic approaches to scale your business sustainably and profitably.',
                'category' => 'Business',
                'difficulty' => 'advanced',
                'duration_minutes' => 100,
                'tier_required' => 'business',
                'has_certificate' => true,
                'lessons' => [
                    ['title' => 'Growth Mindset', 'duration' => 15],
                    ['title' => 'Market Expansion', 'duration' => 20],
                    ['title' => 'Product Development', 'duration' => 20],
                    ['title' => 'Strategic Partnerships', 'duration' => 20],
                    ['title' => 'Scaling Operations', 'duration' => 25],
                ],
            ],
        ];

        foreach ($courses as $index => $courseData) {
            $lessons = $courseData['lessons'];
            unset($courseData['lessons']);

            $courseId = DB::table('bizboost_courses')->insertGetId([
                ...$courseData,
                'lessons_count' => count($lessons),
                'is_published' => true,
                'sort_order' => $index,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($lessons as $lessonIndex => $lesson) {
                DB::table('bizboost_lessons')->insert([
                    'course_id' => $courseId,
                    'title' => $lesson['title'],
                    'slug' => Str::slug($lesson['title']),
                    'content' => $this->generateLessonContent($lesson['title']),
                    'duration_minutes' => $lesson['duration'],
                    'sort_order' => $lessonIndex,
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
<p>Welcome to this lesson on {$title}. In this module, you'll learn key concepts and practical strategies that you can apply to your business immediately.</p>

<h3>Key Learning Objectives</h3>
<ul>
    <li>Understand the fundamentals of this topic</li>
    <li>Learn practical techniques you can implement today</li>
    <li>Discover common mistakes to avoid</li>
    <li>Get actionable tips for your business</li>
</ul>

<h3>Main Content</h3>
<p>This lesson covers essential knowledge that every small business owner should know. Pay attention to the examples and case studies provided, as they illustrate real-world applications of these concepts.</p>

<h3>Action Items</h3>
<ol>
    <li>Review the key concepts covered in this lesson</li>
    <li>Apply at least one technique to your business this week</li>
    <li>Track your results and adjust as needed</li>
</ol>

<p><strong>Pro Tip:</strong> Take notes as you go through this lesson. Writing things down helps reinforce learning and gives you a reference to come back to later.</p>";
    }
}
