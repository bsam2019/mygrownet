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
                'title' => 'Financial Literacy Basics',
                'slug' => 'financial-literacy-basics',
                'description' => 'Essential financial concepts everyone should know',
                'content' => $this->getFinancialLiteracyContent(),
                'content_type' => 'text',
                'estimated_minutes' => 25,
                'category' => 'Life Skills',
                'sort_order' => 2,
                'is_published' => true,
                'is_required' => false,
            ],
            [
                'title' => 'Time Management & Productivity',
                'slug' => 'time-management-productivity',
                'description' => 'Master your time and boost your productivity',
                'content' => $this->getTimeManagementContent(),
                'content_type' => 'text',
                'estimated_minutes' => 20,
                'category' => 'Life Skills',
                'sort_order' => 3,
                'is_published' => true,
                'is_required' => false,
            ],
            [
                'title' => 'Effective Communication Skills',
                'slug' => 'effective-communication-skills',
                'description' => 'Learn to communicate clearly and confidently',
                'content' => $this->getCommunicationSkillsContent(),
                'content_type' => 'text',
                'estimated_minutes' => 18,
                'category' => 'Life Skills',
                'sort_order' => 4,
                'is_published' => true,
                'is_required' => false,
            ],
            [
                'title' => 'Goal Setting & Achievement',
                'slug' => 'goal-setting-achievement',
                'description' => 'Set and achieve your personal and professional goals',
                'content' => $this->getGoalSettingContent(),
                'content_type' => 'text',
                'estimated_minutes' => 15,
                'category' => 'Life Skills',
                'sort_order' => 5,
                'is_published' => true,
                'is_required' => false,
            ],
            [
                'title' => 'Understanding the 7-Level System',
                'slug' => 'understanding-7-level-system',
                'description' => 'Learn about professional levels from Associate to Ambassador (GrowNet Members)',
                'content' => $this->get7LevelContent(),
                'content_type' => 'text',
                'estimated_minutes' => 15,
                'category' => 'GrowNet',
                'sort_order' => 6,
                'is_published' => true,
                'is_required' => false,
            ],
            [
                'title' => 'Loyalty Growth Reward (LGR) System',
                'slug' => 'lgr-system-explained',
                'description' => 'How to earn daily credits through platform activities (GrowNet Members)',
                'content' => $this->getLGRContent(),
                'content_type' => 'text',
                'estimated_minutes' => 12,
                'category' => 'GrowNet',
                'sort_order' => 7,
                'is_published' => true,
                'is_required' => false,
            ],
            [
                'title' => 'Building Your Network',
                'slug' => 'building-your-network',
                'description' => 'Strategies for growing your team and earning commissions (GrowNet Members)',
                'content' => $this->getNetworkBuildingContent(),
                'content_type' => 'text',
                'estimated_minutes' => 20,
                'category' => 'GrowNet',
                'sort_order' => 8,
                'is_published' => true,
                'is_required' => false,
            ],
        ];

        foreach ($modules as $module) {
            DB::table('learning_modules')->updateOrInsert(
                ['slug' => $module['slug']], // Match on slug
                array_merge($module, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
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

    private function getTimeManagementContent(): string
    {
        return <<<'MARKDOWN'
# Time Management & Productivity

Master your time and boost your productivity with proven strategies.

## Understanding Time Management

Time is your most valuable resource. Unlike money, you can't earn more time - you can only use it wisely.

### The Time Management Matrix

**Urgent & Important**: Do immediately
- Crises and emergencies
- Pressing deadlines
- Critical meetings

**Important, Not Urgent**: Schedule and prioritize
- Planning and strategy
- Skill development
- Relationship building
- Health and wellness

**Urgent, Not Important**: Delegate or minimize
- Interruptions
- Some emails and calls
- Other people's priorities

**Not Urgent, Not Important**: Eliminate
- Time wasters
- Excessive social media
- Busy work

## Productivity Techniques

### The Pomodoro Technique
1. Choose a task
2. Work for 25 minutes (1 Pomodoro)
3. Take a 5-minute break
4. After 4 Pomodoros, take a 15-30 minute break

### Time Blocking
- Dedicate specific time blocks to specific activities
- Protect your focus time
- Schedule breaks and buffer time
- Review and adjust weekly

### The 2-Minute Rule
If a task takes less than 2 minutes, do it immediately rather than adding it to your to-do list.

## Daily Planning

### Morning Routine
1. Review your goals
2. Identify top 3 priorities
3. Schedule focused work time
4. Prepare for meetings

### Evening Review
1. Celebrate wins
2. Note incomplete tasks
3. Plan tomorrow
4. Disconnect and rest

## Overcoming Procrastination

### Common Causes
- Task feels overwhelming
- Fear of failure
- Perfectionism
- Lack of clarity

### Solutions
- Break tasks into smaller steps
- Start with just 5 minutes
- Remove distractions
- Use accountability partners

## Tools & Systems

### Essential Tools
- Calendar for time blocking
- Task manager for to-do lists
- Note-taking app for ideas
- Timer for focused work

### MyGrowNet Schedule Feature
Use the Day Plan feature to:
- Plan your daily activities
- Track time spent
- Build productive habits
- Earn LGR credits

## Work-Life Balance

### Setting Boundaries
- Define work hours
- Protect family time
- Schedule self-care
- Learn to say no

### Energy Management
- Identify your peak hours
- Schedule important work during peak energy
- Take regular breaks
- Maintain healthy habits

Complete this module to take control of your time!
MARKDOWN;
    }

    private function getCommunicationSkillsContent(): string
    {
        return <<<'MARKDOWN'
# Effective Communication Skills

Learn to communicate clearly, confidently, and persuasively in all situations.

## The Foundation of Communication

Communication is more than just talking - it's about understanding and being understood.

### The Communication Process
1. **Sender**: You formulate a message
2. **Message**: The information you want to convey
3. **Channel**: How you deliver it (verbal, written, visual)
4. **Receiver**: The person receiving your message
5. **Feedback**: Their response

## Verbal Communication

### Speaking Clearly
- Use simple, direct language
- Organize your thoughts before speaking
- Speak at a moderate pace
- Vary your tone for emphasis

### Active Listening
- Give full attention
- Don't interrupt
- Ask clarifying questions
- Summarize what you heard

### The Power of Questions
- **Open questions**: "What do you think about...?"
- **Closed questions**: "Did you complete...?"
- **Probing questions**: "Can you tell me more about...?"

## Non-Verbal Communication

### Body Language
- Maintain appropriate eye contact
- Use open posture
- Mirror the other person subtly
- Be aware of personal space

### Facial Expressions
- Smile genuinely
- Show interest and engagement
- Match expressions to your message
- Be authentic

## Written Communication

### Email Best Practices
- Clear subject lines
- Professional greeting
- Concise message
- Specific call-to-action
- Professional closing

### Business Writing
- Know your audience
- Use active voice
- Be concise and clear
- Proofread before sending

## Difficult Conversations

### Preparation
1. Clarify your objective
2. Consider the other perspective
3. Choose the right time and place
4. Plan your opening

### During the Conversation
- Stay calm and respectful
- Focus on facts, not emotions
- Listen actively
- Seek mutual understanding

### Conflict Resolution
- Acknowledge emotions
- Find common ground
- Focus on solutions
- Follow up

## Communication in Business

### Networking Conversations
- Prepare your introduction
- Ask about others first
- Share your value proposition
- Exchange contact information
- Follow up within 48 hours

### Presenting Your Business
- Know your audience
- Start with a hook
- Use stories and examples
- Address objections
- End with clear next steps

## Digital Communication

### Social Media
- Be professional and authentic
- Engage with your audience
- Share valuable content
- Respond promptly to messages

### Video Calls
- Test technology beforehand
- Choose a professional background
- Look at the camera
- Mute when not speaking

## Building Relationships

### Trust Building
- Be consistent and reliable
- Keep your promises
- Admit mistakes
- Show genuine interest

### Empathy
- Try to understand others' perspectives
- Validate feelings
- Show compassion
- Offer support

## Communication for MyGrowNet

### Sharing the Opportunity
- Focus on benefits, not features
- Use testimonials and stories
- Be honest about expectations
- Answer questions thoroughly

### Supporting Your Team
- Regular check-ins
- Clear instructions
- Positive reinforcement
- Constructive feedback

Complete this module to become a confident communicator!
MARKDOWN;
    }

    private function getGoalSettingContent(): string
    {
        return <<<'MARKDOWN'
# Goal Setting & Achievement

Set and achieve your personal and professional goals with proven strategies.

## Why Goals Matter

Goals give you direction, motivation, and a way to measure progress. Without goals, you're drifting - with goals, you're navigating.

### Benefits of Goal Setting
- Provides clarity and focus
- Increases motivation
- Helps prioritize actions
- Builds confidence through achievement
- Creates accountability

## SMART Goals Framework

Make your goals SMART:

### Specific
- **Vague**: "I want to earn more money"
- **Specific**: "I want to earn K5,000 per month from MyGrowNet"

### Measurable
- Define how you'll track progress
- Set milestones
- Use numbers and dates

### Achievable
- Challenging but realistic
- Consider your resources
- Build on past successes

### Relevant
- Aligns with your values
- Supports bigger objectives
- Matters to you personally

### Time-bound
- Set a deadline
- Create urgency
- Enable planning

## Types of Goals

### Short-term Goals (1-3 months)
- Complete all learning modules
- Refer 3 new members
- Attend 2 live events

### Medium-term Goals (3-12 months)
- Reach Professional level
- Build a team of 9 active members
- Earn K10,000 in commissions

### Long-term Goals (1-5 years)
- Achieve Ambassador level
- Build a network of 2,000+ members
- Create multiple income streams

## The Goal-Setting Process

### Step 1: Dream Big
- What do you really want?
- Don't limit yourself
- Consider all areas of life

### Step 2: Choose Your Focus
- Select 3-5 major goals
- Ensure they're aligned
- Balance different life areas

### Step 3: Make Them SMART
- Apply the SMART framework
- Write them down
- Make them visible

### Step 4: Create Action Plans
- Break goals into steps
- Assign deadlines
- Identify resources needed

### Step 5: Take Action
- Start immediately
- Build momentum
- Track progress

### Step 6: Review and Adjust
- Weekly progress checks
- Monthly reviews
- Adjust as needed

## Overcoming Obstacles

### Common Challenges
- **Lack of clarity**: Refine your goals
- **Overwhelm**: Break into smaller steps
- **Distractions**: Protect your focus time
- **Fear**: Start small, build confidence
- **Setbacks**: Learn and adjust

### Staying Motivated
- Visualize success
- Celebrate small wins
- Find an accountability partner
- Review your "why" regularly

## Goal Categories

### Financial Goals
- Monthly income target
- Savings goals
- Debt reduction
- Investment targets

### Career/Business Goals
- Skill development
- Professional level advancement
- Network growth
- Business launch

### Personal Development Goals
- Learning new skills
- Health and fitness
- Relationships
- Hobbies and interests

## MyGrowNet Goal Examples

### Beginner Goals
- Complete all required learning modules
- Refer first 3 members
- Earn first LGR credit
- Attend first live event

### Intermediate Goals
- Reach Professional level (9 members)
- Earn K5,000 in monthly commissions
- Complete all life skills modules
- Help 3 team members succeed

### Advanced Goals
- Achieve Director level (243 members)
- Earn K20,000+ monthly
- Mentor 10+ active leaders
- Participate in profit-sharing

## Tracking Your Progress

### Daily Actions
- Review your top 3 priorities
- Complete at least one goal-related task
- Track activities in Day Plan
- Reflect on progress

### Weekly Review
- Assess progress on each goal
- Celebrate wins
- Identify obstacles
- Plan next week's actions

### Monthly Assessment
- Measure results against targets
- Adjust strategies if needed
- Set new milestones
- Reward yourself

## The Power of Habits

### Building Success Habits
- Start small
- Be consistent
- Stack habits
- Track streaks

### MyGrowNet Success Habits
- Daily platform login
- Complete one learning activity
- Engage with your team
- Share valuable content
- Track your progress

## Accountability Systems

### Self-Accountability
- Written goals
- Progress journal
- Regular reviews
- Reward system

### External Accountability
- Share goals with mentor
- Join accountability group
- Regular check-ins
- Public commitments

## Celebrating Success

### Why Celebration Matters
- Reinforces positive behavior
- Builds confidence
- Maintains motivation
- Creates positive associations

### How to Celebrate
- Acknowledge the win
- Share with supporters
- Reward yourself
- Reflect on lessons learned

Complete this module and set your first SMART goal today!
MARKDOWN;
    }
}
