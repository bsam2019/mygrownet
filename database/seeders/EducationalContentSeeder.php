<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\User;
use Illuminate\Database\Seeder;

class EducationalContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->createBronzeTierCourses();
        $this->createSilverTierCourses();
        $this->createGoldTierCourses();
        $this->createDiamondTierCourses();
        $this->createEliteTierCourses();
    }

    private function createBronzeTierCourses(): void
    {
        $courses = [
            [
                'title' => 'Financial Literacy Basics',
                'category' => 'financial_literacy',
                'difficulty_level' => 'beginner',
                'description' => 'Learn the fundamentals of personal finance, budgeting, and money management.',
                'tiers' => ['Bronze'],
                'lessons' => [
                    'Understanding Money and Its Value',
                    'Creating Your First Budget',
                    'Saving Strategies for Beginners',
                    'Understanding Debt and Credit'
                ]
            ],
            [
                'title' => 'Life Skills for Success',
                'category' => 'life_skills',
                'difficulty_level' => 'beginner',
                'description' => 'Essential life skills for personal and professional development.',
                'tiers' => ['Bronze'],
                'lessons' => [
                    'Time Management Fundamentals',
                    'Communication Skills',
                    'Goal Setting and Achievement',
                    'Building Healthy Habits'
                ]
            ]
        ];

        $this->createCoursesWithLessons($courses);
    }

    private function createSilverTierCourses(): void
    {
        $courses = [
            [
                'title' => 'Advanced Financial Planning',
                'category' => 'financial_literacy',
                'difficulty_level' => 'intermediate',
                'description' => 'Advanced concepts in financial planning and wealth building.',
                'tiers' => ['Silver', 'Gold', 'Diamond', 'Elite'],
                'lessons' => [
                    'Investment Fundamentals',
                    'Risk Management Strategies',
                    'Tax Planning Basics',
                    'Emergency Fund Planning'
                ]
            ],
            [
                'title' => 'Business Skills Development',
                'category' => 'business_skills',
                'difficulty_level' => 'intermediate',
                'description' => 'Essential business skills for entrepreneurs and professionals.',
                'tiers' => ['Silver', 'Gold', 'Diamond', 'Elite'],
                'lessons' => [
                    'Business Planning Fundamentals',
                    'Marketing and Sales Basics',
                    'Customer Service Excellence',
                    'Team Building and Leadership'
                ]
            ]
        ];

        $this->createCoursesWithLessons($courses);
    }

    private function createGoldTierCourses(): void
    {
        $courses = [
            [
                'title' => 'Investment Strategies Masterclass',
                'category' => 'investment_strategies',
                'difficulty_level' => 'advanced',
                'description' => 'Advanced investment strategies and portfolio management.',
                'tiers' => ['Gold', 'Diamond', 'Elite'],
                'is_premium' => true,
                'lessons' => [
                    'Portfolio Diversification Strategies',
                    'Real Estate Investment Fundamentals',
                    'Stock Market Analysis',
                    'Alternative Investment Options'
                ]
            ],
            [
                'title' => 'MLM Success Training',
                'category' => 'mlm_training',
                'difficulty_level' => 'intermediate',
                'description' => 'Comprehensive training for MLM success and team building.',
                'tiers' => ['Gold', 'Diamond', 'Elite'],
                'lessons' => [
                    'Understanding MLM Business Models',
                    'Effective Recruitment Strategies',
                    'Team Building and Motivation',
                    'Compliance and Ethics in MLM'
                ]
            ]
        ];

        $this->createCoursesWithLessons($courses);
    }

    private function createDiamondTierCourses(): void
    {
        $courses = [
            [
                'title' => 'Leadership Excellence Program',
                'category' => 'leadership_development',
                'difficulty_level' => 'advanced',
                'description' => 'Advanced leadership skills for high-performing individuals.',
                'tiers' => ['Diamond', 'Elite'],
                'is_premium' => true,
                'certificate_eligible' => true,
                'lessons' => [
                    'Strategic Leadership Thinking',
                    'Leading High-Performance Teams',
                    'Change Management and Innovation',
                    'Executive Decision Making'
                ]
            ],
            [
                'title' => 'Advanced MLM Strategies',
                'category' => 'mlm_training',
                'difficulty_level' => 'advanced',
                'description' => 'Advanced strategies for MLM leaders and top performers.',
                'tiers' => ['Diamond', 'Elite'],
                'is_premium' => true,
                'lessons' => [
                    'Advanced Team Development',
                    'Leadership in MLM Organizations',
                    'Scaling Your MLM Business',
                    'Mentoring and Coaching Others'
                ]
            ]
        ];

        $this->createCoursesWithLessons($courses);
    }

    private function createEliteTierCourses(): void
    {
        $courses = [
            [
                'title' => 'Elite Business Mastery',
                'category' => 'business_skills',
                'difficulty_level' => 'advanced',
                'description' => 'Exclusive business mastery content for elite members.',
                'tiers' => ['Elite'],
                'is_premium' => true,
                'certificate_eligible' => true,
                'lessons' => [
                    'Strategic Business Planning',
                    'Innovation and Disruption',
                    'Global Business Perspectives',
                    'Building Business Empires'
                ]
            ],
            [
                'title' => 'Wealth Creation Mastery',
                'category' => 'investment_strategies',
                'difficulty_level' => 'advanced',
                'description' => 'Advanced wealth creation strategies for elite investors.',
                'tiers' => ['Elite'],
                'is_premium' => true,
                'certificate_eligible' => true,
                'lessons' => [
                    'Advanced Investment Vehicles',
                    'Wealth Preservation Strategies',
                    'Tax Optimization for High Net Worth',
                    'Legacy and Estate Planning'
                ]
            ]
        ];

        $this->createCoursesWithLessons($courses);
    }

    private function createCoursesWithLessons(array $courses): void
    {
        $admin = User::where('is_admin', true)->first() ?? User::factory()->create(['is_admin' => true]);

        foreach ($courses as $courseData) {
            $course = Course::create([
                'title' => $courseData['title'],
                'slug' => \Illuminate\Support\Str::slug($courseData['title']),
                'description' => $courseData['description'],
                'learning_objectives' => 'Upon completion, you will have mastered the key concepts covered in this course.',
                'category' => $courseData['category'],
                'difficulty_level' => $courseData['difficulty_level'],
                'estimated_duration_minutes' => count($courseData['lessons']) * 45, // 45 min per lesson
                'required_subscription_packages' => [],
                'required_membership_tiers' => $courseData['tiers'],
                'is_premium' => $courseData['is_premium'] ?? false,
                'certificate_eligible' => $courseData['certificate_eligible'] ?? false,
                'status' => 'published',
                'created_by' => $admin->id,
                'published_at' => now()->subDays(rand(1, 30)),
                'order' => 0,
                'content_update_frequency' => 'monthly',
                'last_content_update' => now()->subDays(rand(1, 15)),
                'tier_specific_content' => $this->generateTierSpecificContent($courseData['tiers']),
                'monthly_content_schedule' => $this->generateMonthlySchedule()
            ]);

            // Create lessons for the course
            foreach ($courseData['lessons'] as $index => $lessonTitle) {
                CourseLesson::create([
                    'course_id' => $course->id,
                    'title' => $lessonTitle,
                    'slug' => \Illuminate\Support\Str::slug($lessonTitle),
                    'description' => "Comprehensive lesson covering {$lessonTitle}",
                    'content' => $this->generateLessonContent($lessonTitle),
                    'content_type' => 'text',
                    'duration_minutes' => 45,
                    'order' => $index + 1,
                    'is_preview' => $index === 0, // First lesson is preview
                    'required_tier_level' => $courseData['tiers'][0] ?? null,
                    'status' => 'published'
                ]);
            }
        }
    }

    private function generateTierSpecificContent(array $tiers): array
    {
        $content = [];
        foreach ($tiers as $tier) {
            $content[$tier] = [
                'additional_resources' => [
                    'Tier-specific worksheets',
                    'Bonus video content',
                    'Interactive exercises'
                ],
                'tier_specific_modules' => rand(1, 3),
                'bonus_content' => "Exclusive {$tier} tier bonus materials and resources"
            ];
        }
        return $content;
    }

    private function generateMonthlySchedule(): array
    {
        $schedule = [];
        $currentMonth = now()->format('Y-m');
        $nextMonth = now()->addMonth()->format('Y-m');
        
        $schedule[$currentMonth] = [
            'content_updates' => ['Updated examples', 'New case studies', 'Refreshed materials'],
            'new_modules' => 1,
            'updated_resources' => 3
        ];
        
        $schedule[$nextMonth] = [
            'content_updates' => ['Additional exercises', 'Updated statistics', 'New references'],
            'new_modules' => 2,
            'updated_resources' => 5
        ];
        
        return $schedule;
    }

    private function generateLessonContent(string $title): string
    {
        return "
# {$title}

## Overview
This lesson covers the essential concepts and practical applications of {$title}. You will learn key strategies and techniques that can be immediately applied to your personal and professional development.

## Learning Objectives
By the end of this lesson, you will be able to:
- Understand the fundamental principles of {$title}
- Apply practical strategies in real-world scenarios
- Identify common challenges and solutions
- Develop actionable plans for implementation

## Key Concepts
1. **Foundation Principles**: Understanding the core concepts
2. **Practical Applications**: How to apply what you learn
3. **Best Practices**: Proven strategies for success
4. **Common Pitfalls**: What to avoid and how to overcome challenges

## Activities
- Interactive exercises to reinforce learning
- Case study analysis
- Practical worksheets
- Self-assessment tools

## Summary
{$title} is a crucial skill for success in today's competitive environment. The strategies and techniques covered in this lesson provide a solid foundation for continued growth and development.

## Next Steps
Continue to the next lesson to build upon these concepts and develop more advanced skills.
        ";
    }
}