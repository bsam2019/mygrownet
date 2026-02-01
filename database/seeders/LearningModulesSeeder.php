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

Master the essential financial concepts that will transform your relationship with money and set you on the path to financial freedom.

## Introduction: Why Financial Literacy Matters

Financial literacy is not just about understanding money—it's about taking control of your future. In today's world, the ability to manage your finances effectively can mean the difference between living paycheck to paycheck and building lasting wealth.

**What you'll learn in this module:**
- How to create and stick to a realistic budget
- Understanding different types of income streams
- Smart saving and investment strategies
- Effective debt management techniques
- Building long-term wealth through MyGrowNet

---

## Part 1: Budgeting Fundamentals

### The 50/30/20 Rule Explained

This simple yet powerful budgeting framework helps you allocate your income effectively:

**50% - Needs (Essential Expenses)**
- Housing (rent/mortgage)
- Utilities (electricity, water, internet)
- Food and groceries
- Transportation
- Insurance
- Minimum debt payments

**30% - Wants (Lifestyle Choices)**
- Entertainment and hobbies
- Dining out and takeaways
- Shopping and personal items
- Subscriptions (Netflix, Spotify, etc.)
- Vacations and travel

**20% - Savings & Debt Repayment**
- Emergency fund
- Retirement savings
- Investment accounts
- Extra debt payments
- Future goals (house, car, education)

### Creating Your Personal Budget: Step-by-Step

**Step 1: Track Your Income**
- List all sources of income (salary, side hustles, MyGrowNet commissions)
- Calculate your total monthly income after taxes
- Include irregular income (bonuses, seasonal work)

**Step 2: List All Expenses**
- Fixed expenses (same amount each month)
- Variable expenses (change month to month)
- Periodic expenses (quarterly, annually)
- Don't forget small purchases—they add up!

**Step 3: Categorize Your Spending**
- Use the 50/30/20 framework
- Be honest about needs vs wants
- Look for patterns in your spending

**Step 4: Identify Areas to Cut**
- Find subscriptions you don't use
- Reduce dining out frequency
- Shop smarter (bulk buying, sales)
- Negotiate bills (internet, insurance)

**Step 5: Set Realistic Savings Goals**
- Start small if needed (even K50/month helps)
- Automate your savings
- Increase gradually as income grows
- Celebrate milestones

### Practical Budgeting Tools

**Free Tools You Can Use:**
- Spreadsheets (Excel, Google Sheets)
- Mobile apps (Money Manager, Wallet)
- Envelope system (cash-based budgeting)
- MyGrowNet earnings tracker

**Pro Tip:** Review your budget weekly for the first month, then monthly once you're comfortable.

---

## Part 2: Understanding Income Streams

### Active Income

**Definition:** Money earned through direct effort and time investment.

**Examples:**
- Salary from employment
- Hourly wages
- Freelance work
- Consulting fees
- Service-based businesses

**Characteristics:**
- Requires your active participation
- Limited by time available
- Immediate income
- Stops when you stop working

### Passive Income

**Definition:** Money earned with minimal ongoing effort after initial setup.

**Examples:**
- Rental income from property
- Dividend payments from stocks
- Royalties from creative work
- Affiliate marketing income
- Interest from savings/investments

**Characteristics:**
- Continues earning while you sleep
- Requires upfront investment (time or money)
- Builds over time
- Provides financial security

### MyGrowNet Income Opportunities

**1. Referral Commissions**
- Earn when you refer new members
- Multiple levels of commissions
- Recurring income potential
- Build your network strategically

**2. Level Bonuses**
- Advance through 7 professional levels
- Higher levels = higher earning potential
- Recognition and status benefits
- Leadership opportunities

**3. Profit-Sharing Distributions**
- Quarterly distributions from company projects
- All active members qualify
- Weighted by professional level
- Passive income component

**4. LGR Daily Credits**
- Earn K30-K60 daily through activities
- Complete learning modules
- Attend events
- Engage with platform
- Consistent daily income

**5. Product Sales Commissions**
- Sell digital products
- Earn on marketplace transactions
- Build your own product portfolio
- Leverage platform tools

### Building Multiple Income Streams

**Why It Matters:**
- Reduces financial risk
- Increases total income
- Provides security during changes
- Accelerates wealth building

**How to Start:**
1. Master your primary income source
2. Add one new stream at a time
3. Reinvest earnings into growth
4. Diversify across active and passive
5. Use MyGrowNet as a foundation

---

## Part 3: Smart Saving Strategies

### The Emergency Fund: Your Financial Safety Net

**What It Is:**
A dedicated savings account for unexpected expenses only.

**How Much to Save:**
- **Minimum:** 3 months of essential expenses
- **Ideal:** 6 months of expenses
- **Self-employed:** 9-12 months recommended

**What Qualifies as an Emergency:**
- Job loss or income reduction
- Medical emergencies
- Urgent home/car repairs
- Family emergencies

**What's NOT an Emergency:**
- Vacations
- New gadgets
- Sales and shopping
- Planned expenses

### Building Your Emergency Fund

**Phase 1: Quick Start (K1,000)**
- Save K50-K100 per week
- Reach K1,000 in 10-20 weeks
- Covers most small emergencies
- Builds saving habit

**Phase 2: Three Months (K5,000-K10,000)**
- Calculate 3 months of expenses
- Set monthly savings target
- Automate transfers
- Don't touch unless true emergency

**Phase 3: Six Months (K10,000-K20,000)**
- Full financial security
- Peace of mind
- Career flexibility
- Investment opportunities

### Investment Savings: Growing Your Wealth

**Short-term Savings (1-3 years)**
- High-yield savings accounts
- Money market accounts
- Short-term bonds
- Keep liquid and accessible

**Medium-term Savings (3-10 years)**
- Balanced investment portfolios
- Index funds
- Real estate down payment
- Business capital

**Long-term Savings (10+ years)**
- Retirement accounts
- Stock market investments
- Real estate investments
- Business ownership

### The Power of Compound Interest

**Example:** Saving K500/month at 10% annual return

- After 5 years: K38,600
- After 10 years: K102,000
- After 20 years: K382,000
- After 30 years: K1,130,000

**Key Lesson:** Start early, stay consistent, let time work for you.

---

## Part 4: Debt Management Mastery

### Understanding Good Debt vs Bad Debt

**Good Debt (Appreciating Assets)**
- Education loans (increase earning potential)
- Business loans (generate income)
- Mortgage (builds equity)
- Strategic investments

**Characteristics:**
- Low interest rates
- Tax deductible (sometimes)
- Increases net worth
- Generates future income

**Bad Debt (Depreciating Assets)**
- High-interest credit cards
- Payday loans
- Consumer loans for wants
- Car loans (depreciating asset)

**Characteristics:**
- High interest rates
- No tax benefits
- Decreases net worth
- No income generation

### Debt Repayment Strategies

**Strategy 1: Debt Snowball Method**

1. List all debts smallest to largest
2. Pay minimums on all debts
3. Put extra money toward smallest debt
4. When paid off, roll payment to next smallest
5. Repeat until debt-free

**Pros:**
- Quick wins build motivation
- Psychological boost
- Simple to follow

**Cons:**
- May pay more interest overall
- Takes longer mathematically

**Strategy 2: Debt Avalanche Method**

1. List all debts by interest rate (highest first)
2. Pay minimums on all debts
3. Put extra money toward highest interest debt
4. When paid off, move to next highest rate
5. Repeat until debt-free

**Pros:**
- Saves most money on interest
- Fastest mathematically
- Most efficient

**Cons:**
- Slower initial progress
- Requires discipline

**Strategy 3: Debt Consolidation**

Combine multiple debts into one loan with lower interest rate.

**When It Makes Sense:**
- Multiple high-interest debts
- Good credit score
- Lower rate available
- Committed to not adding new debt

**Risks:**
- May extend repayment period
- Fees and costs
- Temptation to use freed-up credit

### Creating Your Debt Repayment Plan

**Step 1: List All Debts**
- Creditor name
- Total amount owed
- Interest rate
- Minimum payment
- Due date

**Step 2: Calculate Total Debt**
- Face the reality
- Don't panic—you can do this
- Knowledge is power

**Step 3: Choose Your Strategy**
- Snowball for motivation
- Avalanche for efficiency
- Hybrid approach possible

**Step 4: Find Extra Money**
- Cut unnecessary expenses
- Increase income (MyGrowNet!)
- Sell unused items
- Temporary sacrifices

**Step 5: Automate Payments**
- Never miss a payment
- Avoid late fees
- Build credit score
- Stay on track

**Step 6: Track Progress**
- Monthly debt reduction
- Celebrate milestones
- Adjust as needed
- Stay motivated

---

## Part 5: Building Long-Term Wealth

### The Wealth-Building Mindset

**Key Principles:**

1. **Spend Less Than You Earn**
   - Live below your means
   - Avoid lifestyle inflation
   - Save the difference

2. **Invest the Difference**
   - Don't just save—invest
   - Let money work for you
   - Start small, grow consistently

3. **Be Patient and Consistent**
   - Wealth builds slowly
   - No get-rich-quick schemes
   - Compound growth takes time

4. **Continuously Learn**
   - Financial education never stops
   - Stay informed
   - Adapt to changes

### Leveraging MyGrowNet for Wealth Building

**Phase 1: Foundation (Months 1-3)**
- Complete all learning modules
- Build your first 3 referrals
- Earn first LGR credits
- Establish consistent activity

**Phase 2: Growth (Months 4-12)**
- Reach Professional level (9 members)
- Earn regular commissions
- Participate in profit-sharing
- Reinvest 50% of earnings

**Phase 3: Expansion (Year 2)**
- Advance to Senior/Manager level
- Build leadership team
- Multiple income streams active
- Significant monthly earnings

**Phase 4: Mastery (Year 3+)**
- Director/Executive/Ambassador level
- Passive income established
- Mentor others
- Financial freedom achieved

### The Reinvestment Strategy

**Rule:** Reinvest 50-70% of MyGrowNet earnings for first 2 years

**Where to Reinvest:**
- Upgrade your package
- Help team members succeed
- Invest in personal development
- Build emergency fund
- Start other businesses

**Why It Works:**
- Accelerates growth
- Compounds earnings
- Builds sustainable income
- Creates long-term wealth

---

## Part 6: Practical Action Steps

### This Week's Assignments

**Day 1-2: Financial Assessment**
- Calculate total monthly income
- List all expenses
- Identify spending patterns
- Calculate net worth

**Day 3-4: Create Your Budget**
- Apply 50/30/20 rule
- Set up tracking system
- Identify areas to cut
- Set savings goals

**Day 5-6: Debt Analysis**
- List all debts
- Choose repayment strategy
- Calculate extra payment amount
- Set up automatic payments

**Day 7: MyGrowNet Strategy**
- Review earning opportunities
- Set income goals
- Plan referral strategy
- Schedule daily activities

### Monthly Financial Checklist

- [ ] Review budget vs actual spending
- [ ] Track net worth changes
- [ ] Review debt progress
- [ ] Check savings goals
- [ ] Analyze MyGrowNet earnings
- [ ] Adjust strategies as needed
- [ ] Celebrate wins!

### Resources and Tools

**Recommended Reading:**
- "Rich Dad Poor Dad" by Robert Kiyosaki
- "The Total Money Makeover" by Dave Ramsey
- "Your Money or Your Life" by Vicki Robin

**Free Tools:**
- MyGrowNet earnings dashboard
- Google Sheets budget templates
- Mint.com (expense tracking)
- Personal Capital (net worth tracking)

**MyGrowNet Support:**
- Financial literacy workshops
- One-on-one mentorship
- Community support groups
- Success stories and case studies

---

## Conclusion: Your Financial Freedom Journey

Financial literacy is a journey, not a destination. Every step you take today brings you closer to financial freedom tomorrow.

**Remember:**
- Start where you are
- Use what you have
- Do what you can
- Stay consistent
- Never stop learning

**Your Next Steps:**
1. Complete this module
2. Create your first budget
3. Set up emergency fund
4. Start your MyGrowNet journey
5. Help others learn

You have the knowledge. Now take action!

---

**Congratulations on completing Financial Literacy Basics!**

You've learned the fundamentals of budgeting, income streams, saving, debt management, and wealth building. Apply these principles consistently, and you'll transform your financial future.

**Ready to continue learning?** Check out our Time Management & Productivity module next!
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
