<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LifePlusSeeder extends Seeder
{
    public function run(): void
    {
        // Default Expense Categories
        $categories = [
            ['name' => 'Food', 'icon' => '🍔', 'color' => '#ef4444', 'is_default' => true],
            ['name' => 'Transport', 'icon' => '🚌', 'color' => '#f59e0b', 'is_default' => true],
            ['name' => 'Airtime', 'icon' => '📱', 'color' => '#3b82f6', 'is_default' => true],
            ['name' => 'Rent', 'icon' => '🏠', 'color' => '#8b5cf6', 'is_default' => true],
            ['name' => 'School', 'icon' => '📚', 'color' => '#10b981', 'is_default' => true],
            ['name' => 'Health', 'icon' => '💊', 'color' => '#ec4899', 'is_default' => true],
            ['name' => 'Utilities', 'icon' => '💡', 'color' => '#06b6d4', 'is_default' => true],
            ['name' => 'Entertainment', 'icon' => '🎬', 'color' => '#f97316', 'is_default' => true],
            ['name' => 'Shopping', 'icon' => '🛒', 'color' => '#84cc16', 'is_default' => true],
            ['name' => 'Other', 'icon' => '💰', 'color' => '#6b7280', 'is_default' => true],
        ];

        foreach ($categories as $category) {
            DB::table('lifeplus_expense_categories')->updateOrInsert(
                ['name' => $category['name'], 'user_id' => null],
                array_merge($category, [
                    'user_id' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        // Sample Knowledge Items (Daily Tips)
        $knowledgeItems = [
            [
                'title' => 'Start Small with Savings',
                'content' => 'Save small amounts every day to build financial security for your family. Even K5 a day adds up to K150 per month!',
                'category' => 'finance',
                'type' => 'article',
                'is_featured' => true,
                'is_daily_tip' => true,
            ],
            [
                'title' => 'Track Every Expense',
                'content' => 'Recording every expense, no matter how small, helps you understand where your money goes. This awareness is the first step to better financial management.',
                'category' => 'finance',
                'type' => 'article',
                'is_featured' => true,
                'is_daily_tip' => true,
            ],
            [
                'title' => 'Set Clear Goals',
                'content' => 'Write down your goals and review them daily. People who write their goals are 42% more likely to achieve them.',
                'category' => 'motivation',
                'type' => 'article',
                'is_featured' => true,
                'is_daily_tip' => true,
            ],
            [
                'title' => 'Build One Habit at a Time',
                'content' => 'Focus on building one new habit before adding another. It takes about 21 days to form a new habit.',
                'category' => 'motivation',
                'type' => 'article',
                'is_featured' => true,
                'is_daily_tip' => true,
            ],
            [
                'title' => 'Emergency Fund First',
                'content' => 'Before investing, build an emergency fund that covers 3-6 months of expenses. This protects you from unexpected financial shocks.',
                'category' => 'finance',
                'type' => 'article',
                'is_featured' => true,
                'is_daily_tip' => true,
            ],
            [
                'title' => 'Learn a New Skill',
                'content' => 'Invest in yourself by learning a new skill. Skills like tailoring, cooking, or digital marketing can create additional income streams.',
                'category' => 'skills',
                'type' => 'article',
                'is_featured' => true,
                'is_daily_tip' => true,
            ],
            [
                'title' => 'Network Actively',
                'content' => 'Your network is your net worth. Attend community events and connect with people who can help you grow personally and professionally.',
                'category' => 'business',
                'type' => 'article',
                'is_featured' => true,
                'is_daily_tip' => true,
            ],
            [
                'title' => 'Healthy Body, Healthy Mind',
                'content' => 'Exercise for at least 30 minutes daily. Physical activity improves mental clarity and productivity.',
                'category' => 'health',
                'type' => 'article',
                'is_featured' => true,
                'is_daily_tip' => true,
            ],
            [
                'title' => 'Budget Before You Spend',
                'content' => 'Create a budget at the start of each month. Allocate money for needs first, then wants, and always include savings.',
                'category' => 'finance',
                'type' => 'article',
                'is_featured' => true,
                'is_daily_tip' => true,
            ],
            [
                'title' => 'Teach Your Children About Money',
                'content' => 'Start teaching children about money early. Give them small amounts to manage and let them learn from their decisions.',
                'category' => 'parenting',
                'type' => 'article',
                'is_featured' => true,
                'is_daily_tip' => true,
            ],
        ];

        foreach ($knowledgeItems as $item) {
            DB::table('lifeplus_knowledge_items')->updateOrInsert(
                ['title' => $item['title']],
                array_merge($item, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $this->command->info('LifePlus default data seeded successfully!');
    }
}
