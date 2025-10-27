<?php

namespace Database\Seeders;

use App\Models\LibraryResource;
use Illuminate\Database\Seeder;

class LibraryResourceSeeder extends Seeder
{
    public function run(): void
    {
        $resources = [
            // Business Fundamentals
            [
                'title' => 'Business Model Canvas Explained',
                'description' => 'Learn how to create and use a business model canvas for your venture.',
                'type' => 'video',
                'category' => 'business',
                'resource_url' => 'https://www.youtube.com/watch?v=QoAOzMTLP5s',
                'author' => 'Strategyzer',
                'duration_minutes' => 15,
                'difficulty' => 'beginner',
                'is_external' => true,
                'is_featured' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Starting a Business: The Complete Guide',
                'description' => 'Comprehensive guide covering everything from idea validation to launch.',
                'type' => 'article',
                'category' => 'business',
                'resource_url' => 'https://www.entrepreneur.com/starting-a-business',
                'author' => 'Entrepreneur.com',
                'difficulty' => 'beginner',
                'is_external' => true,
                'sort_order' => 2,
            ],

            // Marketing & Sales
            [
                'title' => 'Digital Marketing Fundamentals',
                'description' => 'Free course covering SEO, social media, email marketing, and analytics.',
                'type' => 'course',
                'category' => 'marketing',
                'resource_url' => 'https://www.google.com/digitalgarage/courses',
                'author' => 'Google Digital Garage',
                'duration_minutes' => 240,
                'difficulty' => 'beginner',
                'is_external' => true,
                'is_featured' => true,
                'sort_order' => 3,
            ],
            [
                'title' => 'Social Media Marketing Strategy',
                'description' => 'Learn how to create an effective social media marketing strategy.',
                'type' => 'video',
                'category' => 'marketing',
                'resource_url' => 'https://www.youtube.com/watch?v=BodGrSHfhGQ',
                'author' => 'HubSpot',
                'duration_minutes' => 45,
                'difficulty' => 'intermediate',
                'is_external' => true,
                'sort_order' => 4,
            ],
            [
                'title' => 'Content Marketing Templates',
                'description' => 'Ready-to-use templates for blog posts, social media, and email campaigns.',
                'type' => 'template',
                'category' => 'marketing',
                'resource_url' => 'https://blog.hubspot.com/marketing/templates',
                'author' => 'HubSpot',
                'difficulty' => 'beginner',
                'is_external' => true,
                'sort_order' => 5,
            ],

            // Financial Management
            [
                'title' => 'Personal Finance Basics',
                'description' => 'Learn budgeting, saving, and investing fundamentals.',
                'type' => 'course',
                'category' => 'finance',
                'resource_url' => 'https://www.khanacademy.org/college-careers-more/personal-finance',
                'author' => 'Khan Academy',
                'duration_minutes' => 180,
                'difficulty' => 'beginner',
                'is_external' => true,
                'is_featured' => true,
                'sort_order' => 6,
            ],
            [
                'title' => 'Financial Planning Spreadsheet',
                'description' => 'Track income, expenses, and savings goals with this free template.',
                'type' => 'template',
                'category' => 'finance',
                'resource_url' => 'https://www.vertex42.com/ExcelTemplates/personal-budget-spreadsheet.html',
                'author' => 'Vertex42',
                'difficulty' => 'beginner',
                'is_external' => true,
                'sort_order' => 7,
            ],
            [
                'title' => 'Understanding Cash Flow',
                'description' => 'Master cash flow management for your business.',
                'type' => 'article',
                'category' => 'finance',
                'resource_url' => 'https://www.investopedia.com/terms/c/cashflow.asp',
                'author' => 'Investopedia',
                'difficulty' => 'intermediate',
                'is_external' => true,
                'sort_order' => 8,
            ],

            // Leadership
            [
                'title' => 'Leadership Principles',
                'description' => 'Learn the fundamentals of effective leadership.',
                'type' => 'video',
                'category' => 'leadership',
                'resource_url' => 'https://www.youtube.com/watch?v=fW8amMCVAJQ',
                'author' => 'Simon Sinek',
                'duration_minutes' => 18,
                'difficulty' => 'beginner',
                'is_external' => true,
                'is_featured' => true,
                'sort_order' => 9,
            ],
            [
                'title' => 'Team Building Strategies',
                'description' => 'Practical strategies for building and managing effective teams.',
                'type' => 'article',
                'category' => 'leadership',
                'resource_url' => 'https://www.mindtools.com/pages/article/newTMM_84.htm',
                'author' => 'MindTools',
                'difficulty' => 'intermediate',
                'is_external' => true,
                'sort_order' => 10,
            ],

            // Personal Development
            [
                'title' => 'Goal Setting Workshop',
                'description' => 'Learn how to set and achieve your personal and professional goals.',
                'type' => 'video',
                'category' => 'personal_development',
                'resource_url' => 'https://www.youtube.com/watch?v=L4N1q4RNi9I',
                'author' => 'Brian Tracy',
                'duration_minutes' => 30,
                'difficulty' => 'beginner',
                'is_external' => true,
                'sort_order' => 11,
            ],
            [
                'title' => 'Time Management Mastery',
                'description' => 'Proven techniques to boost productivity and manage your time effectively.',
                'type' => 'course',
                'category' => 'personal_development',
                'resource_url' => 'https://www.coursera.org/learn/work-smarter-not-harder',
                'author' => 'Coursera',
                'duration_minutes' => 120,
                'difficulty' => 'beginner',
                'is_external' => true,
                'is_featured' => true,
                'sort_order' => 12,
            ],
            [
                'title' => 'Daily Productivity Planner',
                'description' => 'Printable planner to organize your daily tasks and priorities.',
                'type' => 'template',
                'category' => 'personal_development',
                'resource_url' => 'https://www.vertex42.com/ExcelTemplates/daily-planner.html',
                'author' => 'Vertex42',
                'difficulty' => 'beginner',
                'is_external' => true,
                'sort_order' => 13,
            ],

            // Network Building
            [
                'title' => 'Networking for Success',
                'description' => 'Master the art of professional networking and relationship building.',
                'type' => 'video',
                'category' => 'network_building',
                'resource_url' => 'https://www.youtube.com/watch?v=YRjdQqzYHY4',
                'author' => 'TEDx',
                'duration_minutes' => 20,
                'difficulty' => 'beginner',
                'is_external' => true,
                'is_featured' => true,
                'sort_order' => 14,
            ],
            [
                'title' => 'LinkedIn Profile Optimization',
                'description' => 'Step-by-step guide to creating a powerful LinkedIn profile.',
                'type' => 'article',
                'category' => 'network_building',
                'resource_url' => 'https://www.linkedin.com/help/linkedin/answer/112133',
                'author' => 'LinkedIn',
                'difficulty' => 'beginner',
                'is_external' => true,
                'sort_order' => 15,
            ],
            [
                'title' => 'Referral Program Template',
                'description' => 'Ready-to-use template for creating your own referral program.',
                'type' => 'template',
                'category' => 'network_building',
                'resource_url' => 'https://www.referralcandy.com/blog/referral-program-templates/',
                'author' => 'ReferralCandy',
                'difficulty' => 'intermediate',
                'is_external' => true,
                'sort_order' => 16,
            ],
        ];

        foreach ($resources as $resource) {
            LibraryResource::create($resource);
        }
    }
}
