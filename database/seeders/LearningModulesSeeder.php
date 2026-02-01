<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LearningModulesSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            [
                'title' => 'Introduction to MyGrowNet Platform',
                'slug' => 'introduction-to-mygrownet',
                'description' => 'Learn the basics of the MyGrowNet platform and how to get started',
                'content' => $this->getIntroductionContent(),
                'content_type' => 'text',
                'estimated_minutes' => 10,
                'category' => 'Getting Started',
                'sort_order' => 1,
                'is_published' => true,
                'is_required' => true,
            ],
            [
                'title' => 'Understanding the 7-Level System',
                'slug' => 'understanding-7-level-system',
                'description' => 'Learn about professional levels from Associate to Ambassador',
                'content' => $this->get7LevelContent(),
                'content_type' => 'text',
                'estimated_minutes' => 15,
                'category' => 'Platform Basics',
                'sort_order' => 2,
                'is_published' => true,
                'is_required' => true,
            ],
            [
                'title' => 'Loyalty Growth Reward (LGR) System',
                'slug' => 'lgr-system-explained',
                'description' => 'How to earn daily K30 credits through platform activities',
                'content' => $this->getLGRContent(),
                'content_type' => 'text',
                'estimated_minutes' => 12,
                'category' => 'Rewards',
                'sort_order' => 3,
                'is_published' => true,
                'is_required' => false,
            ],
            [
                'title' => 'Building Your Network',
                'slug' => 'building-your-network',
                'description' => 'Strategies for growing your team and earning commissions',
                'content' => $this->getNetworkBuildingContent(),
                'content_type' => 'text',
                'estimated_minutes' => 20,
                'category' => 'Business Development',
                'sort_order' => 4,
                'is_published' => true,
                'is_required' => false,
            ],
            [
                'title' => 'Financial Literacy Basics',
                'slug' => 'financial-literacy-basics',
                'description' => 'Essential financial concepts every member should know',
                'content' => $this->getFinancialLiteracyContent(),
                'content_type' => 'text',
                'estimated_minutes' => 25,
                'category' => 'Finance',
                'sort_order' => 5,
                'is_published' => true,
                'is_required' => false,
            ],
        ];

        foreach ($modules as $module) {
            DB::table('learning_modules')->insert(array_merge($module, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    private function getIntroductionContent(): string
    {
        return <<<'MARKDOWN'
# Welcome to MyGrowNet!

MyGrowNet is a community empowerment platform designed to help you **Learn, Earn, and Grow**.

## What is MyGrowNet?

MyGrowNet is NOT an investment scheme. We are a **subscription-based platform** that provides:

- **Learning Resources**: Access to courses, workshops, and mentorship
- **Income Opportunities**: Earn through referrals, commissions, and profit-sharing
- **Business Support**: Tools and resources to grow your business

## How It Works

1. **Subscribe**: Pay a one-time registration fee and monthly subscription
2. **Learn**: Access educational content and skill-building resources
3. **Earn**: Build your network and earn commissions
4. **Grow**: Advance through 7 professional levels

## Your Journey Starts Here

Complete this module to earn your first LGR activity credit and start your journey to success!

**Next Steps:**
- Complete the "Understanding the 7-Level System" module
- Set up your profile
- Share your referral link
MARKDOWN;
    }

    private function get7LevelContent(): string
    {
        return <<<'MARKDOWN'
# The 7-Level Professional System

MyGrowNet uses a structured progression system with 7 professional levels.

## The Levels

### Level 1: Associate
- **Network Size**: 3 direct referrals
- **Role**: New member, learning the platform
- **Focus**: Understanding the system

### Level 2: Professional
- **Network Size**: 9 members
- **Role**: Skilled member, applying knowledge
- **Focus**: Building your first team

### Level 3: Senior
- **Network Size**: 27 members
- **Role**: Experienced member, team building
- **Focus**: Developing leadership skills

### Level 4: Manager
- **Network Size**: 81 members
- **Role**: Team leader
- **Focus**: Managing and mentoring

### Level 5: Director
- **Network Size**: 243 members
- **Role**: Strategic leader
- **Focus**: Long-term planning

### Level 6: Executive
- **Network Size**: 729 members
- **Role**: Top performer
- **Focus**: Platform advocacy

### Level 7: Ambassador
- **Network Size**: 2,187 members
- **Role**: Brand representative
- **Focus**: Community leadership

## How to Advance

Advancement is based on:
- **Lifetime Points (LP)**: Accumulated through activities
- **Network Size**: Number of active members in your team
- **Engagement**: Consistent platform participation

Complete this module to understand your growth path!
MARKDOWN;
    }

    private function getLGRContent(): string
    {
        return <<<'MARKDOWN'
# Loyalty Growth Reward (LGR) System

The LGR system rewards active members with daily credits based on their package level.

## How It Works

1. **Purchase an LGR Package**: K300, K500, K1000, or K2000
2. **Complete Daily Activities**: Earn credits by staying active
3. **Receive Daily Rewards**: Get K30-K60 daily for 365 days

## Daily Activities

To qualify for daily LGR credits, complete at least ONE of these activities:

- ✅ Complete a learning module
- ✅ Attend a live event
- ✅ Make a marketplace purchase
- ✅ Post in the community forum
- ✅ Refer a new member

## Package Levels

- **K300 Package**: K30/day × 365 days = K10,950 total
- **K500 Package**: K30/day × 365 days = K10,950 total
- **K1000 Package**: K50/day × 365 days = K18,250 total
- **K2000 Package**: K60/day × 365 days = K21,900 total

## Important Notes

- Credits are awarded ONLY on days you complete activities
- Inactive days = No credits
- Credits can be used for platform purchases or withdrawn
- System encourages consistent engagement

Start earning today by completing this module!
MARKDOWN;
    }

    private function getNetworkBuildingContent(): string
    {
        return <<<'MARKDOWN'
# Building Your Network

Learn proven strategies for growing your MyGrowNet team.

## The 3×3 Matrix System

MyGrowNet uses a **3×3 forced matrix** with spillover:

- You can refer up to 3 people directly
- Additional referrals "spill over" to your downline
- This creates a collaborative growth environment

## Effective Strategies

### 1. Share Your Story
- Explain how MyGrowNet has helped you
- Be authentic and genuine
- Focus on the learning and growth aspects

### 2. Use Social Media
- Share educational content
- Post success stories
- Engage with your audience

### 3. Host Presentations
- Organize small group meetings
- Use the platform's presentation materials
- Answer questions honestly

### 4. Follow Up
- Stay in touch with prospects
- Provide support to new members
- Build genuine relationships

## Compliance Reminder

- Never promise guaranteed returns
- Focus on products and services
- Be honest about income potential
- Follow all platform guidelines

## Your Action Plan

1. Identify 10 potential prospects
2. Share your referral link
3. Follow up within 48 hours
4. Support new members through onboarding

Complete this module to master network building!
MARKDOWN;
    }

    private function getFinancialLiteracyContent(): string
    {
        return <<<'MARKDOWN'
# Financial Literacy Basics

Essential financial concepts every MyGrowNet member should understand.

## Budgeting Fundamentals

### The 50/30/20 Rule
- **50%**: Needs (housing, food, utilities)
- **30%**: Wants (entertainment, dining out)
- **20%**: Savings and debt repayment

### Creating Your Budget
1. Track all income sources
2. List all expenses
3. Categorize spending
4. Identify areas to cut
5. Set savings goals

## Understanding Income Streams

### Active Income
- Salary from employment
- Commissions from sales
- Service-based earnings

### Passive Income
- Profit-sharing from investments
- Rental income
- Royalties

### MyGrowNet Income
- Referral commissions
- Level bonuses
- Profit-sharing distributions
- LGR daily credits

## Saving Strategies

### Emergency Fund
- Save 3-6 months of expenses
- Keep in accessible account
- Use only for true emergencies

### Investment Savings
- Set aside 10-20% of income
- Diversify investments
- Think long-term

## Debt Management

### Good Debt vs Bad Debt
- **Good**: Education, business, appreciating assets
- **Bad**: High-interest consumer debt

### Debt Repayment Strategy
1. List all debts
2. Pay minimums on all
3. Focus extra on highest interest
4. Celebrate milestones

## Building Wealth

### Key Principles
- Spend less than you earn
- Invest the difference
- Be patient and consistent
- Continuously learn

### Using MyGrowNet
- Reinvest commissions
- Upgrade your package
- Participate in profit-sharing
- Build multiple income streams

Complete this module to strengthen your financial foundation!
MARKDOWN;
    }
}
