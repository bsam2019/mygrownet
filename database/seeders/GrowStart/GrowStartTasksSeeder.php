<?php

namespace Database\Seeders\GrowStart;

use Illuminate\Database\Seeder;
use App\Models\GrowStart\Task;
use App\Models\GrowStart\Stage;
use App\Models\GrowStart\Country;

class GrowStartTasksSeeder extends Seeder
{
    public function run(): void
    {
        $stages = Stage::all()->keyBy('slug');
        $zambia = Country::where('code', 'ZMB')->first();

        // Idea Stage Tasks
        $this->seedStageTasks($stages['idea']->id, [
            ['title' => 'Define your business idea clearly', 'description' => 'Write a clear one-sentence description of what your business will do', 'order' => 1, 'estimated_hours' => 1],
            ['title' => 'Identify your target customers', 'description' => 'Define who will buy your product or service', 'order' => 2, 'estimated_hours' => 2],
            ['title' => 'Research your competition', 'description' => 'Identify at least 3 competitors and analyze their strengths and weaknesses', 'order' => 3, 'estimated_hours' => 3],
            ['title' => 'Define your unique value proposition', 'description' => 'What makes your business different from competitors?', 'order' => 4, 'estimated_hours' => 2],
            ['title' => 'Estimate initial startup costs', 'description' => 'Create a rough estimate of how much money you need to start', 'order' => 5, 'estimated_hours' => 2],
        ]);

        // Validation Stage Tasks
        $this->seedStageTasks($stages['validation']->id, [
            ['title' => 'Talk to 10 potential customers', 'description' => 'Interview potential customers to validate your idea', 'order' => 1, 'estimated_hours' => 5],
            ['title' => 'Create a simple prototype or sample', 'description' => 'Build a basic version of your product or service offering', 'order' => 2, 'estimated_hours' => 8],
            ['title' => 'Test pricing with potential customers', 'description' => 'Ask customers what they would pay for your product/service', 'order' => 3, 'estimated_hours' => 2],
            ['title' => 'Refine your business concept', 'description' => 'Update your business idea based on customer feedback', 'order' => 4, 'estimated_hours' => 2],
            ['title' => 'Identify potential suppliers', 'description' => 'Research and list potential suppliers for your business', 'order' => 5, 'estimated_hours' => 3],
        ]);

        // Planning Stage Tasks
        $this->seedStageTasks($stages['planning']->id, [
            ['title' => 'Write your business plan', 'description' => 'Create a comprehensive business plan document', 'order' => 1, 'estimated_hours' => 10],
            ['title' => 'Create financial projections', 'description' => 'Project your income, expenses, and profits for the first year', 'order' => 2, 'estimated_hours' => 5],
            ['title' => 'Plan your operations', 'description' => 'Define how your business will operate day-to-day', 'order' => 3, 'estimated_hours' => 3],
            ['title' => 'Identify funding sources', 'description' => 'Determine how you will fund your startup', 'order' => 4, 'estimated_hours' => 2],
            ['title' => 'Create a launch timeline', 'description' => 'Set milestones and deadlines for your launch', 'order' => 5, 'estimated_hours' => 2],
        ]);

        // Registration Stage Tasks (Zambia-specific)
        $this->seedStageTasks($stages['registration']->id, [
            ['title' => 'Register business with PACRA', 'description' => 'Register your business name and company with Patents and Companies Registration Agency', 'order' => 1, 'estimated_hours' => 4, 'country_id' => $zambia->id, 'external_link' => 'https://www.pacra.org.zm', 'instructions' => "1. Visit PACRA office or online portal\n2. Search for available business names\n3. Complete registration form\n4. Pay registration fees\n5. Receive certificate"],
            ['title' => 'Obtain TPIN from ZRA', 'description' => 'Register for Tax Payer Identification Number with Zambia Revenue Authority', 'order' => 2, 'estimated_hours' => 3, 'country_id' => $zambia->id, 'external_link' => 'https://www.zra.org.zm', 'instructions' => "1. Visit ZRA office or e-services portal\n2. Complete TPIN application form\n3. Submit required documents\n4. Receive TPIN certificate"],
            ['title' => 'Register for NAPSA', 'description' => 'Register with National Pension Scheme Authority for employee contributions', 'order' => 3, 'estimated_hours' => 2, 'country_id' => $zambia->id, 'external_link' => 'https://www.napsa.co.zm'],
            ['title' => 'Open business bank account', 'description' => 'Open a dedicated bank account for your business', 'order' => 4, 'estimated_hours' => 4],
            ['title' => 'Obtain industry-specific licenses', 'description' => 'Apply for any licenses required for your specific industry', 'order' => 5, 'estimated_hours' => 8, 'is_required' => false],
            ['title' => 'Register domain name', 'description' => 'Register a domain name for your business website', 'order' => 6, 'estimated_hours' => 1, 'is_required' => false],
        ]);

        // Launch Stage Tasks
        $this->seedStageTasks($stages['launch']->id, [
            ['title' => 'Set up your business location', 'description' => 'Prepare your physical or virtual business location', 'order' => 1, 'estimated_hours' => 16],
            ['title' => 'Purchase initial inventory/equipment', 'description' => 'Buy the supplies and equipment needed to start', 'order' => 2, 'estimated_hours' => 8],
            ['title' => 'Set up payment systems', 'description' => 'Configure how customers will pay you (cash, mobile money, etc.)', 'order' => 3, 'estimated_hours' => 2],
            ['title' => 'Create your first product/service offering', 'description' => 'Finalize what you will sell and at what price', 'order' => 4, 'estimated_hours' => 4],
            ['title' => 'Make your first sale', 'description' => 'Complete your first customer transaction', 'order' => 5, 'estimated_hours' => 2],
        ]);

        // Accounting Stage Tasks
        $this->seedStageTasks($stages['accounting']->id, [
            ['title' => 'Set up bookkeeping system', 'description' => 'Choose and set up a system to track income and expenses', 'order' => 1, 'estimated_hours' => 3],
            ['title' => 'Create invoice template', 'description' => 'Design a professional invoice for your business', 'order' => 2, 'estimated_hours' => 1],
            ['title' => 'Set up expense tracking', 'description' => 'Create a system to track all business expenses', 'order' => 3, 'estimated_hours' => 2],
            ['title' => 'Understand tax obligations', 'description' => 'Learn about your tax filing requirements', 'order' => 4, 'estimated_hours' => 2, 'country_id' => $zambia->id],
            ['title' => 'Connect to GrowFinance', 'description' => 'Set up GrowFinance for automated bookkeeping', 'order' => 5, 'estimated_hours' => 1, 'is_required' => false],
        ]);

        // Marketing Stage Tasks
        $this->seedStageTasks($stages['marketing']->id, [
            ['title' => 'Create your brand identity', 'description' => 'Design your logo, colors, and brand guidelines', 'order' => 1, 'estimated_hours' => 5],
            ['title' => 'Set up social media profiles', 'description' => 'Create business profiles on Facebook, WhatsApp Business, etc.', 'order' => 2, 'estimated_hours' => 2],
            ['title' => 'Create marketing materials', 'description' => 'Design flyers, business cards, and promotional content', 'order' => 3, 'estimated_hours' => 4],
            ['title' => 'Plan your first marketing campaign', 'description' => 'Create a plan to attract your first customers', 'order' => 4, 'estimated_hours' => 3],
            ['title' => 'Connect to BizBoost', 'description' => 'Set up BizBoost for marketing automation', 'order' => 5, 'estimated_hours' => 1, 'is_required' => false],
        ]);

        // Growth Stage Tasks
        $this->seedStageTasks($stages['growth']->id, [
            ['title' => 'Analyze your first month performance', 'description' => 'Review sales, expenses, and customer feedback', 'order' => 1, 'estimated_hours' => 3],
            ['title' => 'Identify growth opportunities', 'description' => 'Find ways to increase sales and expand your business', 'order' => 2, 'estimated_hours' => 4],
            ['title' => 'Plan for hiring', 'description' => 'Determine when and who you need to hire', 'order' => 3, 'estimated_hours' => 2, 'is_required' => false],
            ['title' => 'Explore new products/services', 'description' => 'Consider expanding your offerings', 'order' => 4, 'estimated_hours' => 3, 'is_required' => false],
            ['title' => 'Set 6-month growth goals', 'description' => 'Define specific targets for the next 6 months', 'order' => 5, 'estimated_hours' => 2],
        ]);
    }

    private function seedStageTasks(int $stageId, array $tasks): void
    {
        foreach ($tasks as $task) {
            Task::updateOrCreate(
                [
                    'stage_id' => $stageId,
                    'title' => $task['title'],
                ],
                array_merge([
                    'stage_id' => $stageId,
                    'industry_id' => $task['industry_id'] ?? null,
                    'country_id' => $task['country_id'] ?? null,
                    'description' => $task['description'] ?? null,
                    'instructions' => $task['instructions'] ?? null,
                    'external_link' => $task['external_link'] ?? null,
                    'estimated_hours' => $task['estimated_hours'] ?? 1,
                    'order' => $task['order'] ?? 0,
                    'is_required' => $task['is_required'] ?? true,
                    'is_premium' => $task['is_premium'] ?? false,
                ], $task)
            );
        }
    }
}
