# MyGrowNet Points System - Technical Specification

**Version:** 2.0  
**Date:** October 20, 2025  
**Status:** LP/BP Integration

---

## ⚠️ Important Note: Documentation Enhancement

**This document uses "Bonus Points (BP)" terminology as an enhancement to the existing "Monthly Activity Points (MAP)" system.**

- ✅ **Existing Implementation:** The points system is ALREADY IMPLEMENTED using MAP terminology
- ✅ **Backward Compatible:** All existing code continues to work unchanged
- ✅ **No Breaking Changes:** Database schema and functionality remain identical
- 📝 **Documentation Only:** BP is a clearer name for the same MAP concept

**For migration details, see:** `MIGRATION_GUIDE_MAP_TO_BP.md`  
**For implementation status, see:** `IMPLEMENTATION_STATUS.md`

---

## Executive Summary

MyGrowNet implements a dual-points system to drive member engagement, product sales, and sustainable community growth. The system uses:

- **Life Points (LP)** - For professional level progression (Associate → Ambassador)
- **Bonus Points (BP)** - For monthly earnings calculation and profit-sharing distribution
  - *Note: In current code, BP is called "Monthly Activity Points (MAP)"*
  - *Same functionality, clearer name in documentation*

This system ensures fair compensation based on contribution, encourages sustained engagement, and creates multiple income streams beyond recruitment.

---

## 1. System Architecture

### 1.1 Point Types

#### Life Points (LP)
- **Purpose**: Professional level advancement (Associate → Ambassador)
- **Persistence**: Never expire, accumulate forever
- **Earning**: Network building, sales, education, community contribution
- **Usage**: Determines professional level and long-term status
- **Impact**: Unlocks leadership benefits, bonus tiers, and enhanced multipliers

#### Bonus Points (BP)
- **Purpose**: Calculate monthly cash bonuses and profit-sharing distribution
- **Persistence**: Reset to 0 on the 1st of each month
- **Earning**: Same activities as LP, but emphasizes current engagement
- **Usage**: Determines monthly earnings share from 60% profit pool
- **Formula**: `Member Bonus = (Individual BP / Total BP) × 60% of monthly profit`

### 1.2 Core Principles

1. **Dual Qualification**: Members need both LP (for level) and BP (for earnings)
2. **Activity-Based**: Points reward multiple behaviors, not just recruitment
3. **Transparent**: Clear requirements and real-time progress tracking
4. **Sustainable**: Inactive members (0 BP) don't receive distributions
5. **Flexible**: Multiple paths to earn points
6. **Fair Distribution**: Earnings proportional to contribution (BP-based)
7. **Performance-Driven**: Rewards active participation over seniority

---

## 2. Point Earning Matrix

### 2.1 Network Building Activities

| Activity | LP Earned | BP Earned | Notes |
|----------|-----------|-----------|-------|
| Direct referral registration | 150 | 150 | When new member completes registration |
| Indirect referral (spillover) | 30 | 30 | When placed in your matrix via spillover |
| Downline advances a level | 50 | 50 | When any downline member levels up |
| Complete matrix level filled | 100 | 100 | Bonus when entire level is filled |
| Help downline make first sale | 40 | 40 | Mentorship reward |
| Help downline activate account | 25 | 25 | Onboarding support |

### 2.2 Product Sales Activities (MyGrow Shop)

| Activity | LP Earned | BP Earned | Calculation |
|----------|-----------|-----------|-------------|
| Personal product purchase | 10 per K100 | 10 per K100 | Own purchases |
| Direct referral product sale | 20 per K100 | 20 per K100 | Level 1 sales |
| Downline product sale (L2-7) | 5 per K100 | 5 per K100 | Levels 2-7 sales |
| Share partner product link | 5 | 5 | Per share (max 50 BP/month) |
| Monthly sales target achieved | 200 | 200 | Bonus for hitting target |

**Sales Targets by Level:**
- Associate: K200/month
- Professional: K400/month
- Senior: K600/month
- Manager: K1,000/month
- Director: K1,500/month
- Executive: K2,500/month
- Ambassador: K4,000/month

### 2.3 Learning & Development (MyGrow Learn)

| Activity | LP Earned | BP Earned | Notes |
|----------|-----------|-----------|-------|
| Complete basic course | 30 | 30 | 1-2 hour courses |
| Complete advanced course | 60 | 60 | 3-5 hour courses |
| Pass certification exam | 100 | 100 | Professional certifications |
| Attend live webinar | 20 | 20 | Real-time participation |
| Complete monthly training | 5 | 50 | Mandatory monthly requirement |
| Watch educational video | 2 | 2 | Max 20 BP/month |

### 2.4 Community Engagement

| Activity | LP Earned | BP Earned | Limits/Notes |
|----------|-----------|-----------|--------------|
| Daily login | 0 | 5 | Once per day |
| 7-day login streak | 0 | 50 | Weekly bonus |
| 30-day login streak | 0 | 200 | Monthly bonus |
| Vote on community project | 10 | 10 | Per vote |
| Submit approved project | 100 | 100 | One-time per project |
| Quality forum post | 0 | 5 | Max 50 BP/month (10 posts) |
| Mentor new member | 30 | 30 | Verified mentorship |

### 2.5 Subscription & Renewals

| Activity | LP Earned | BP Earned | Notes |
|----------|-----------|-----------|-------|
| Monthly subscription renewal | 25 | 25 | Automatic on renewal |
| Upgrade subscription tier | 50 | 50 | One-time per upgrade |
| 3-month consecutive renewal | 0 | 75 | Loyalty bonus |
| 6-month consecutive renewal | 0 | 200 | Extended loyalty bonus |
| Annual subscription | 300 | 300 | One-time annual payment |

### 2.6 Platform Contribution

| Activity | LP Earned | BP Earned | Notes |
|----------|-----------|-----------|-------|
| Refer business partner | 300 | 300 | B2B partnership referral |
| Create approved content | 150 | 150 | Educational materials |
| Report bug/issue | 20 | 20 | Verified reports |
| Provide testimonial | 40 | 40 | Approved testimonials |
| Promote on social media | 5 | 5 | Max 25 BP/month |

### 2.6 Subscription & Loyalty

| Activity | LP Earned | MAP Earned | Notes |
|----------|-----------|------------|-------|
| Monthly subscription renewal | 0 | 50 | Automatic on payment |
| Annual subscription (upfront) | 0 | 800 | Bonus for commitment |
| Consecutive month bonus | 0 | 10 × months | Cumulative loyalty |
| Refer premium subscriber | 100 | 100 | Higher-tier referral |

---

## 3. Professional Level Progression

### 3.1 Level Requirements

| Level | LP Required | Min. Time | Additional Requirements |
|-------|-------------|-----------|------------------------|
| **Associate** | 0 | Immediate | Registration complete |
| **Professional** | 500 | 1 month | 3 direct referrals |
| **Senior** | 1,500 | 3 months | 2 active directs, 1 course completed |
| **Manager** | 4,000 | 6 months | 1 Professional in downline, 3 courses |
| **Director** | 10,000 | 12 months | 1 Senior in downline, 5 courses |
| **Executive** | 25,000 | 18 months | 1 Manager in downline, 10 courses |
| **Ambassador** | 50,000 | 24 months | 1 Director in downline, 15 courses, 1 project |

### 3.2 Level Advancement Logic

```
IF (lifetime_points >= required_lp) 
   AND (account_age >= minimum_time)
   AND (direct_referrals >= required_referrals)
   AND (active_direct_referrals >= required_active)
   AND (courses_completed >= required_courses)
   AND (downline_level_requirement_met)
   AND (additional_requirements_met)
THEN
   PROMOTE to next_level
   AWARD milestone_bonus
   SEND promotion_notification
   LOG promotion_event
END IF
```

### 3.3 Milestone Bonuses

Upon level advancement:
- **Professional**: K500 bonus + 100 LP
- **Senior**: K1,500 bonus + 200 LP
- **Manager**: K5,000 bonus + 500 LP
- **Director**: K15,000 bonus + 1,000 LP
- **Executive**: K50,000 bonus + 2,500 LP
- **Ambassador**: K150,000 bonus + 5,000 LP

---

## 4. Monthly Activity Requirements

### 4.1 MAP Thresholds for Earnings Eligibility

| Level | MAP Required | Typical Path to Achieve |
|-------|--------------|------------------------|
| **Associate** | 100 | 20 daily logins + 1 course |
| **Professional** | 200 | Daily logins + 1 course + K200 sales |
| **Senior** | 300 | Daily logins + 2 courses + K400 sales |
| **Manager** | 400 | Daily logins + 2 courses + K600 sales + mentoring |
| **Director** | 500 | Daily logins + 3 courses + K800 sales + mentoring |
| **Executive** | 600 | Daily logins + 3 courses + K1,000 sales + engagement |
| **Ambassador** | 800 | Daily logins + 4 courses + K1,500 sales + leadership |

### 4.2 Qualification Logic

```
IF (monthly_activity_points >= level_map_requirement)
THEN
   member_status = "ACTIVE"
   eligible_for_commissions = TRUE
   eligible_for_profit_sharing = TRUE
   eligible_for_bonuses = TRUE
ELSE
   member_status = "INACTIVE"
   eligible_for_commissions = FALSE
   eligible_for_profit_sharing = FALSE
   commissions_held_in_escrow = TRUE
END IF
```

### 4.3 Consequences of Inactivity

**Month 1 (Below MAP threshold):**
- Status: Inactive
- Commissions: Held in escrow
- Profit-sharing: Not distributed
- Bonuses: Not paid
- Warning notification sent

**Month 2 (Consecutive inactivity):**
- Status: Inactive
- Level progression: Frozen (cannot advance)
- All earnings: Held in escrow
- Urgent notification sent

**Month 3 (Consecutive inactivity):**
- Status: Inactive
- Level: Downgraded by 1 level
- Earnings: Forfeited (not held)
- Final warning notification

**Month 6 (Consecutive inactivity):**
- Status: Dormant
- Level: Reset to Associate
- LP: Retained (can re-qualify)
- Network: Maintained but inactive
- Reactivation required

---

## 5. Point Multipliers & Performance Tiers

### 5.1 Streak Multipliers

Active members who consistently meet MAP requirements receive multipliers:

| Consecutive Months | Multiplier | Applied To |
|-------------------|------------|------------|
| 1-2 months | 1.0x | All points (baseline) |
| 3-5 months | 1.1x | All LP and MAP earned |
| 6-11 months | 1.25x | All LP and MAP earned |
| 12+ months | 1.5x | All LP and MAP earned |

**Example:**
- Member with 6-month streak earns 100 LP from a referral
- Actual LP credited: 100 × 1.25 = 125 LP

### 5.2 Monthly Performance Tiers

Based on MAP earned in current month:

| Tier | MAP Range | Commission Bonus | Recognition |
|------|-----------|------------------|-------------|
| **Bronze** | 100-299 | 0% | Standard |
| **Silver** | 300-599 | +10% | Silver badge |
| **Gold** | 600-999 | +20% | Gold badge |
| **Platinum** | 1,000+ | +30% | Platinum badge + spotlight |

**Commission Bonus Applied To:**
- Referral commissions
- Level commissions
- Performance bonuses
- (Not applied to profit-sharing)

### 5.3 Team Performance Bonus

**Team Synergy Bonus:**
- Condition: All 3 direct referrals meet their MAP requirement
- Reward: +100 LP + 100 MAP
- Purpose: Encourage mentoring and team support
- Frequency: Monthly

---

## 6. Gamification Features

### 6.1 Badges & Achievements

| Badge | Requirement | Reward |
|-------|-------------|--------|
| **First Sale** | Make first product sale | 50 LP |
| **Network Builder** | 100 total referrals | 500 LP |
| **Scholar** | Complete 20 courses | 300 LP |
| **Mentor Master** | Help 10 people advance | 400 LP |
| **Consistent Champion** | 12-month active streak | 1,000 LP |
| **Sales Star** | K50,000 in personal sales | 800 LP |
| **Community Leader** | 100 forum contributions | 200 LP |
| **Project Pioneer** | Launch approved project | 500 LP |

### 6.2 Leaderboards

**Monthly Leaderboards:**
- Top MAP earners (current month)
- Top sales performers
- Most helpful mentors (peer-rated)
- Most active community members

**All-Time Leaderboards:**
- Top LP earners
- Fastest level advancement
- Largest active network
- Most courses completed

**Rewards:**
- Top 10: Featured on platform
- Top 3: Special recognition + bonus points
- #1: Monthly spotlight + premium reward

### 6.3 Monthly Challenges

Themed challenges with bonus point opportunities:

**Example Challenges:**
- **Sales Sprint**: 2x points on all product sales
- **Learning Month**: 3x points on course completions
- **Team Builder**: Bonus for team referrals
- **Community Champion**: Extra points for engagement

---

## 7. Database Schema

### 7.1 Core Tables

#### `user_points`
```sql
CREATE TABLE user_points (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    lifetime_points INT DEFAULT 0,
    monthly_points INT DEFAULT 0,
    last_month_points INT DEFAULT 0,
    three_month_average DECIMAL(10,2) DEFAULT 0,
    current_streak_months INT DEFAULT 0,
    longest_streak_months INT DEFAULT 0,
    active_multiplier DECIMAL(3,2) DEFAULT 1.00,
    last_activity_date TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_user_id (user_id),
    INDEX idx_lifetime_points (lifetime_points),
    INDEX idx_monthly_points (monthly_points)
);
```

#### `point_transactions`
```sql
CREATE TABLE point_transactions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    point_type ENUM('lifetime', 'monthly', 'both') NOT NULL,
    lp_amount INT DEFAULT 0,
    map_amount INT DEFAULT 0,
    source VARCHAR(50) NOT NULL,
    description TEXT,
    reference_type VARCHAR(50),
    reference_id BIGINT,
    multiplier_applied DECIMAL(3,2) DEFAULT 1.00,
    created_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_user_id (user_id),
    INDEX idx_source (source),
    INDEX idx_created_at (created_at)
);
```

#### `monthly_activity_status`
```sql
CREATE TABLE monthly_activity_status (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    month INT NOT NULL,
    year INT NOT NULL,
    map_earned INT DEFAULT 0,
    map_required INT NOT NULL,
    qualified BOOLEAN DEFAULT FALSE,
    performance_tier ENUM('bronze', 'silver', 'gold', 'platinum'),
    commission_bonus_percent DECIMAL(5,2) DEFAULT 0,
    team_synergy_bonus BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    UNIQUE KEY unique_user_month (user_id, year, month),
    INDEX idx_qualified (qualified),
    INDEX idx_year_month (year, month)
);
```

#### `user_badges`
```sql
CREATE TABLE user_badges (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    badge_code VARCHAR(50) NOT NULL,
    badge_name VARCHAR(100) NOT NULL,
    earned_at TIMESTAMP,
    lp_reward INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id),
    UNIQUE KEY unique_user_badge (user_id, badge_code),
    INDEX idx_badge_code (badge_code)
);
```

#### `monthly_challenges`
```sql
CREATE TABLE monthly_challenges (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    month INT NOT NULL,
    year INT NOT NULL,
    challenge_name VARCHAR(100) NOT NULL,
    description TEXT,
    point_multiplier DECIMAL(3,2) DEFAULT 1.00,
    target_activity VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_year_month (year, month),
    INDEX idx_is_active (is_active)
);
```

### 7.2 Additional Fields for Existing Tables

#### Add to `users` table:
```sql
ALTER TABLE users ADD COLUMN (
    current_professional_level ENUM('associate', 'professional', 'senior', 'manager', 'director', 'executive', 'ambassador') DEFAULT 'associate',
    level_achieved_at TIMESTAMP,
    courses_completed_count INT DEFAULT 0,
    days_active_count INT DEFAULT 0,
    is_currently_active BOOLEAN DEFAULT TRUE
);
```

---

## 8. Business Logic & Calculations

### 8.1 Point Awarding Service

```php
class PointService
{
    public function awardPoints(
        User $user,
        string $source,
        int $lpAmount,
        int $mapAmount,
        string $description,
        $reference = null
    ): PointTransaction {
        // Apply multiplier
        $multiplier = $user->points->active_multiplier;
        $finalLP = round($lpAmount * $multiplier);
        $finalMAP = round($mapAmount * $multiplier);
        
        // Create transaction
        $transaction = PointTransaction::create([
            'user_id' => $user->id,
            'point_type' => 'both',
            'lp_amount' => $finalLP,
            'map_amount' => $finalMAP,
            'source' => $source,
            'description' => $description,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference?->id,
            'multiplier_applied' => $multiplier,
        ]);
        
        // Update user points
        $user->points->increment('lifetime_points', $finalLP);
        $user->points->increment('monthly_points', $finalMAP);
        $user->points->touch('last_activity_date');
        
        // Check for level advancement
        $this->checkLevelAdvancement($user);
        
        // Check for badges
        $this->checkBadgeEligibility($user, $source);
        
        // Fire event
        event(new PointsAwarded($user, $transaction));
        
        return $transaction;
    }
    
    public function checkMonthlyQualification(User $user): bool
    {
        $requiredMAP = $this->getRequiredMAP($user->current_professional_level);
        $currentMAP = $user->points->monthly_points;
        
        return $currentMAP >= $requiredMAP;
    }
    
    public function resetMonthlyPoints(): void
    {
        DB::transaction(function () {
            // Archive last month's data
            $this->archiveMonthlyActivity();
            
            // Reset monthly points
            UserPoints::query()->update([
                'last_month_points' => DB::raw('monthly_points'),
                'monthly_points' => 0,
            ]);
            
            // Update streaks
            $this->updateActivityStreaks();
            
            // Update multipliers
            $this->updateMultipliers();
        });
    }
}
```

### 8.2 Level Advancement Service

```php
class LevelAdvancementService
{
    public function checkLevelAdvancement(User $user): ?string
    {
        $currentLevel = $user->current_professional_level;
        $nextLevel = $this->getNextLevel($currentLevel);
        
        if (!$nextLevel) {
            return null; // Already at max level
        }
        
        $requirements = $this->getLevelRequirements($nextLevel);
        
        if ($this->meetsAllRequirements($user, $requirements)) {
            return $this->promoteUser($user, $nextLevel);
        }
        
        return null;
    }
    
    private function meetsAllRequirements(User $user, array $requirements): bool
    {
        return $user->points->lifetime_points >= $requirements['lp']
            && $user->account_age_days >= $requirements['min_days']
            && $user->direct_referrals_count >= $requirements['direct_referrals']
            && $user->active_direct_referrals_count >= $requirements['active_referrals']
            && $user->courses_completed_count >= $requirements['courses']
            && $this->meetsDownlineRequirement($user, $requirements['downline_level']);
    }
    
    private function promoteUser(User $user, string $newLevel): string
    {
        DB::transaction(function () use ($user, $newLevel) {
            $user->update([
                'current_professional_level' => $newLevel,
                'level_achieved_at' => now(),
            ]);
            
            // Award milestone bonus
            $bonus = $this->getMilestoneBonus($newLevel);
            $this->awardMilestoneBonus($user, $bonus);
            
            // Send notification
            $user->notify(new LevelAdvancementNotification($newLevel, $bonus));
            
            // Award upline mentorship points
            $this->awardUplinesForAdvancement($user);
            
            // Log event
            activity()
                ->performedOn($user)
                ->log("Advanced to {$newLevel}");
        });
        
        return $newLevel;
    }
}
```

### 8.3 Monthly Qualification Check (Scheduled Job)

```php
class CheckMonthlyQualification extends Command
{
    public function handle()
    {
        User::with('points')->chunk(100, function ($users) {
            foreach ($users as $user) {
                $qualified = app(PointService::class)
                    ->checkMonthlyQualification($user);
                
                MonthlyActivityStatus::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'month' => now()->month,
                        'year' => now()->year,
                    ],
                    [
                        'map_earned' => $user->points->monthly_points,
                        'map_required' => $this->getRequiredMAP($user),
                        'qualified' => $qualified,
                        'performance_tier' => $this->getPerformanceTier($user),
                        'commission_bonus_percent' => $this->getCommissionBonus($user),
                    ]
                );
                
                // Send notifications if at risk
                if (!$qualified && now()->day >= 25) {
                    $user->notify(new MonthlyQualificationAtRisk());
                }
            }
        });
    }
}
```

---

## 9. User Interface Requirements

### 9.1 Dashboard Display

**Points Overview Card:**
```
┌─────────────────────────────────────────┐
│ Your Points Summary                     │
├─────────────────────────────────────────┤
│ Lifetime Points: 2,847 LP               │
│ Current Level: Senior                   │
│ Next Level: Manager (1,153 LP to go)    │
│ Progress: ████████░░░░ 71%              │
│                                         │
│ This Month's Activity:                  │
│ Monthly Points: 287 MAP                 │
│ Required: 300 MAP                       │
│ Status: ⚠️ 13 MAP needed                │
│ Days Remaining: 4                       │
│                                         │
│ Active Streak: 6 months 🔥              │
│ Multiplier: 1.25x                       │
│ Performance Tier: Silver 🥈             │
└─────────────────────────────────────────┘
```

**Quick Actions:**
- Complete a course (+30-60 MAP)
- Make a sale (+10 MAP per K100)
- Refer a member (+150 LP/MAP)
- Login daily (+5 MAP)

### 9.2 Point History

Detailed transaction log showing:
- Date/time
- Activity description
- LP earned
- MAP earned
- Multiplier applied
- Running totals

### 9.3 Progress Tracking

**Level Advancement Checklist:**
```
Manager Requirements:
✅ 4,000 LP (You have 2,847 LP - 71%)
✅ 6 months active (You have 8 months)
✅ 3 courses completed (You have 5)
⚠️ 1 Professional in downline (0/1)
```

**Monthly Qualification Tracker:**
```
This Month's Progress:
✅ Daily logins: 24/20 days (120 MAP)
✅ Courses: 2 completed (60 MAP)
⚠️ Sales: K700/K600 target (70 MAP)
❌ Mentoring: 0/1 session (0 MAP)

Total: 287/300 MAP
Status: 13 MAP short - Complete 1 more course!
```

### 9.4 Notifications

**Real-time Alerts:**
- Points earned: "+150 LP for new referral!"
- Level advancement: "Congratulations! You're now a Manager!"
- Monthly qualification: "You've qualified for this month's earnings!"
- At-risk warning: "Only 4 days left to earn 13 MAP"
- Streak milestone: "6-month streak! Multiplier increased to 1.25x"

---

## 10. Implementation Phases

### Phase 1: Core Points System (Weeks 1-2)
- Database schema implementation
- Point awarding service
- Basic transaction logging
- Dashboard display

### Phase 2: Level Progression (Weeks 3-4)
- Level advancement logic
- Requirement checking
- Milestone bonuses
- Promotion notifications

### Phase 3: Monthly Qualification (Weeks 5-6)
- MAP tracking
- Monthly reset job
- Qualification checking
- Earnings eligibility logic

### Phase 4: Multipliers & Tiers (Weeks 7-8)
- Streak tracking
- Multiplier calculations
- Performance tier system
- Bonus calculations

### Phase 5: Gamification (Weeks 9-10)
- Badge system
- Leaderboards
- Monthly challenges
- Achievement notifications

### Phase 6: Optimization & Testing (Weeks 11-12)
- Performance optimization
- Edge case testing
- User acceptance testing
- Documentation finalization

---

## 11. Success Metrics

### Key Performance Indicators (KPIs)

**Engagement Metrics:**
- Daily active users (DAU)
- Monthly active users (MAU)
- Average MAP earned per user
- Qualification rate (% meeting MAP requirement)

**Growth Metrics:**
- New member registrations
- Referral conversion rate
- Level advancement rate
- Network growth rate

**Revenue Metrics:**
- Product sales volume
- Average order value
- Sales per active member
- Course completion rate

**Retention Metrics:**
- Active streak distribution
- Churn rate
- Reactivation rate
- Long-term retention (12+ months)

### Target Benchmarks (Year 1)

- 70% monthly qualification rate
- 40% of members with 3+ month streaks
- 25% level advancement rate annually
- 80% course completion rate
- 60% daily login rate

---

## 12. Risk Mitigation

### Potential Issues & Solutions

**Issue: Point Inflation**
- Solution: Regular review of point values, cap on certain activities

**Issue: Gaming the System**
- Solution: Manual review of high-value transactions, fraud detection

**Issue: Member Frustration**
- Solution: Clear communication, achievable targets, multiple earning paths

**Issue: Technical Performance**
- Solution: Database optimization, caching, async processing

**Issue: Complexity Overload**
- Solution: Progressive disclosure, tooltips, onboarding tutorials

---

## 13. Compliance & Legal

### Regulatory Considerations

1. **Transparency**: All point values and requirements publicly disclosed
2. **Fair Practice**: No retroactive changes to earned points
3. **Data Privacy**: Point history is personal data, GDPR/POPIA compliant
4. **Anti-Fraud**: Monitoring for suspicious point accumulation
5. **Terms of Service**: Clear terms on point expiration and forfeiture

### Audit Trail

All point transactions logged with:
- User ID
- Timestamp
- Source activity
- Amount awarded
- Multiplier applied
- Reference data

Immutable audit log retained for 7 years.

---

## 14. Future Enhancements

### Potential Additions (Phase 2)

1. **Point Gifting**: Transfer points to downline members
2. **Point Marketplace**: Redeem points for products/services
3. **Bonus Point Events**: Limited-time double/triple point periods
4. **Team Challenges**: Collaborative point earning
5. **Seasonal Campaigns**: Holiday-themed point bonuses
6. **VIP Tiers**: Ultra-high achievers with special benefits
7. **Point Predictions**: AI-powered progress forecasting
8. **Social Sharing**: Earn points for platform promotion

---

## Appendix A: Point Earning Quick Reference

| Activity | LP | MAP | Frequency |
|----------|----|----|-----------|
| Direct referral | 150 | 150 | Unlimited |
| Spillover referral | 30 | 30 | Unlimited |
| Personal purchase (per K100) | 10 | 10 | Unlimited |
| Direct sale (per K100) | 20 | 20 | Unlimited |
| Downline sale (per K100) | 5 | 5 | Unlimited |
| Basic course | 30 | 30 | Unlimited |
| Advanced course | 60 | 60 | Unlimited |
| Certification | 100 | 100 | Unlimited |
| Daily login | 0 | 5 | Daily |
| 7-day streak | 0 | 50 | Weekly |
| 30-day streak | 0 | 200 | Monthly |
| Monthly subscription | 0 | 50 | Monthly |
| Mentor session | 30 | 30 | Unlimited |
| Forum post | 0 | 5 | Max 10/month |
| Project vote | 10 | 10 | Per project |

---

## Appendix B: Level Requirements Summary

| Level | LP | Time | Referrals | Courses | Downline |
|-------|----|----|-----------|---------|----------|
| Associate | 0 | 0 | 0 | 0 | - |
| Professional | 500 | 1mo | 3 | 0 | - |
| Senior | 1,500 | 3mo | 3 | 1 | 2 active |
| Manager | 4,000 | 6mo | 3 | 3 | 1 Professional |
| Director | 10,000 | 12mo | 3 | 5 | 1 Senior |
| Executive | 25,000 | 18mo | 3 | 10 | 1 Manager |
| Ambassador | 50,000 | 24mo | 3 | 15 | 1 Director |

---

## Appendix C: Monthly MAP Requirements

| Level | MAP Required | Typical Activities |
|-------|--------------|-------------------|
| Associate | 100 | 20 logins + 1 course |
| Professional | 200 | 25 logins + 1 course + K200 sales |
| Senior | 300 | 28 logins + 2 courses + K400 sales |
| Manager | 400 | 30 logins + 2 courses + K600 sales + mentor |
| Director | 500 | 30 logins + 3 courses + K800 sales + mentor |
| Executive | 600 | 30 logins + 3 courses + K1000 sales + engage |
| Ambassador | 800 | 30 logins + 4 courses + K1500 sales + lead |

---

**Document Control:**
- **Author**: MyGrowNet Development Team
- **Reviewers**: Product, Engineering, Legal, Finance
- **Approval**: Pending
- **Next Review**: Q2 2026

---

*End of Technical Specification*


---

## 15. Monthly Bonus Pool Distribution (BP System)

### 15.1 Revenue Generation & Pool Creation

**Monthly Revenue Sources:**
1. MyGrow Investments returns
2. MyGrow Shop sales commissions
3. Subscription fees (40% allocated)
4. Partnership revenues
5. Service fees

**Bonus Pool Calculation:**
```
Total Monthly Profit = Sum of all revenue sources - Operating costs
Bonus Pool = Total Monthly Profit × 60%
Company Retention = Total Monthly Profit × 40%
```

### 15.2 BP-Based Distribution Formula

**Individual Member Bonus:**
```
Member Bonus = (Member BP / Total BP) × Bonus Pool
```

**Where:**
- **Member BP** = Total Bonus Points earned by the member in the current month
- **Total BP** = Sum of all BP earned by all members in the current month
- **Bonus Pool** = 60% of monthly profit

### 15.3 Distribution Example

**Scenario:**
- Monthly profit: K100,000
- Bonus pool (60%): K60,000
- Total BP earned by all members: 50,000 BP

**Member A:**
- BP earned this month: 250 BP
- Bonus calculation: (250 / 50,000) × K60,000 = K300

**Member B:**
- BP earned this month: 500 BP
- Bonus calculation: (500 / 50,000) × K60,000 = K600

**Member C:**
- BP earned this month: 1,000 BP
- Bonus calculation: (1,000 / 50,000) × K60,000 = K1,200

### 15.4 Minimum BP Requirement

**Active Member Threshold:**
- Minimum BP to qualify: 50 BP
- Members with < 50 BP receive no bonus that month
- Encourages minimum engagement

**Rationale:**
- Prevents inactive members from receiving distributions
- Ensures fair distribution to active participants
- Maintains platform sustainability

### 15.5 Monthly Distribution Process

**Timeline:**
```
Day 1-28/30/31: Members earn BP through activities
Day 1 (next month): BP reset to 0, previous month archived
Day 1-5: Finance team calculates total profit
Day 5-7: Bonus pool calculated and distributed
Day 7: Members receive bonus in MyGrow Wallet
Day 7-10: Withdrawal window opens
```

**Automated Process:**
1. Archive previous month's BP data
2. Calculate total monthly profit
3. Determine bonus pool (60%)
4. Sum all member BP
5. Calculate individual bonuses
6. Credit MyGrow Wallets
7. Send notification to members
8. Generate distribution report

### 15.6 Transparency & Reporting

**Monthly Report Includes:**
- Total revenue generated
- Operating costs
- Net profit
- Bonus pool amount (60%)
- Company retention (40%)
- Total BP earned by all members
- Average bonus per active member
- Distribution statistics

**Member Dashboard Shows:**
- Your BP this month
- Total BP platform-wide
- Your percentage share
- Estimated bonus (real-time)
- Final bonus received
- Historical bonus data

### 15.7 BP Earning Strategies

**High-Value Activities:**
1. **Referrals** (150 BP each)
2. **Product Sales** (10-20 BP per K100)
3. **Monthly Subscription** (25 BP)
4. **Course Completion** (30-100 BP)
5. **Daily Engagement** (5-200 BP via streaks)

**Optimization Tips:**
- Maintain daily login streak (200 BP/month)
- Complete 2-3 courses monthly (60-180 BP)
- Make personal purchases (variable BP)
- Refer 1-2 new members (150-300 BP)
- Help downline activate (25 BP each)

**Realistic Monthly BP Targets:**
- **Minimum Active**: 50-100 BP
- **Average Active**: 200-400 BP
- **High Performer**: 500-1,000 BP
- **Top Achiever**: 1,000+ BP

### 15.8 Bonus Pool Growth Projections

**Year 1 Projections:**

| Month | Members | Avg BP/Member | Total BP | Profit | Bonus Pool | Avg Bonus |
|-------|---------|---------------|----------|--------|------------|-----------|
| 1 | 100 | 150 | 15,000 | K20,000 | K12,000 | K120 |
| 3 | 300 | 200 | 60,000 | K50,000 | K30,000 | K100 |
| 6 | 800 | 250 | 200,000 | K120,000 | K72,000 | K90 |
| 12 | 2,000 | 300 | 600,000 | K300,000 | K180,000 | K90 |

**Key Insights:**
- As platform grows, bonus pool increases
- Average bonus stabilizes due to more members
- High performers earn significantly more
- Consistent engagement rewarded monthly

### 15.9 Comparison: BP vs Traditional Commission

**Traditional MLM:**
- Fixed commission rates (e.g., 10% of sales)
- Limited to direct network activity
- Capped earning potential
- No benefit from platform growth

**BP System:**
- Dynamic earnings based on contribution
- Multiple earning paths (not just sales)
- Share in total platform success
- Scales with company growth
- Fair distribution based on effort

**Example:**
- Traditional: Refer 3 members = K300 commission (fixed)
- BP System: Earn 450 BP + share of K60,000 pool = K540 (variable, can be higher)

### 15.10 Risk Management

**Safeguards:**
1. **Minimum Profit Threshold**: Bonus pool only distributed if profit > K10,000
2. **Reserve Fund**: 10% of bonus pool held in reserve for stability
3. **Cap on Individual Share**: No single member can receive > 5% of bonus pool
4. **Audit Trail**: All calculations logged and auditable
5. **Dispute Resolution**: 30-day window for bonus disputes

**Sustainability Measures:**
- 40% profit retention ensures company growth
- Minimum BP requirement prevents exploitation
- Multiple revenue streams reduce dependency
- Transparent reporting builds trust

---

## 16. Integration with Existing Systems

### 16.1 Commission System Integration

**Hybrid Model:**
- **Direct Commissions**: Paid immediately on referral/sale
- **BP Bonuses**: Accumulated and paid monthly from pool

**Example Member Earnings:**
```
Direct Referral Commission: K50 (immediate)
+ BP Earned: 150 BP
+ Monthly Bonus: K300 (from BP share)
= Total Earnings: K350
```

### 16.2 Profit-Sharing Integration

**Quarterly Profit-Sharing (Separate):**
- Based on LP level (Associate 1.0x → Ambassador 4.0x)
- Equal + weighted distribution
- From empowerment project returns

**Monthly BP Bonuses (New):**
- Based on BP earned
- Proportional distribution
- From all platform revenues

**Both Systems Coexist:**
- LP determines quarterly profit-sharing weight
- BP determines monthly bonus share
- Members benefit from both systems

### 16.3 Wallet Integration

**MyGrow Wallet Features:**
- Receive monthly BP bonuses
- Receive quarterly profit-sharing
- Receive direct commissions
- Make purchases (earn BP)
- Transfer to other members
- Withdraw to mobile money/bank

**Transaction Types:**
```
+ Monthly BP Bonus: K300
+ Quarterly Profit Share: K150
+ Direct Commission: K50
- Purchase from Shop: -K100 (earns 10 BP)
- Withdrawal: -K200
= Balance: K200
```

---

## 17. Member Education & Onboarding

### 17.1 Onboarding Flow

**New Member Journey:**
1. **Registration** → Earn 100 LP (welcome bonus)
2. **Tutorial** → Learn about LP and BP systems
3. **First Activity** → Complete course, earn first BP
4. **Dashboard Tour** → See real-time BP tracking
5. **Goal Setting** → Set monthly BP target
6. **First Month** → Earn BP, see bonus calculation
7. **First Payout** → Receive bonus, understand distribution

### 17.2 Educational Resources

**Video Tutorials:**
- "Understanding Life Points (LP)"
- "How Bonus Points (BP) Work"
- "Maximizing Your Monthly Bonus"
- "BP Earning Strategies"
- "Reading Your Dashboard"

**Interactive Calculators:**
- BP Earnings Calculator
- Bonus Projection Tool
- Level Advancement Tracker
- Monthly Goal Planner

**FAQs:**
- What's the difference between LP and BP?
- How is my monthly bonus calculated?
- When do I receive my bonus?
- What happens if I don't earn enough BP?
- Can I see how much others earn?

### 17.3 Ongoing Support

**Monthly Webinars:**
- "This Month's Results" (transparency session)
- "Top Earner Strategies" (learn from best)
- "New Features & Updates"
- "Q&A with Leadership"

**Community Forums:**
- Share BP earning tips
- Discuss strategies
- Celebrate milestones
- Support each other

---

## Conclusion

The **Life Points (LP)** and **Bonus Points (BP)** system creates a fair, transparent, and sustainable reward mechanism that:

✅ **Rewards Active Participation** - BP system ensures only active members benefit  
✅ **Encourages Long-Term Growth** - LP system promotes sustained engagement  
✅ **Fair Distribution** - Earnings proportional to contribution  
✅ **Multiple Income Streams** - Beyond just recruitment  
✅ **Transparent & Auditable** - Clear calculations and reporting  
✅ **Scalable** - Grows with platform success  
✅ **Legally Compliant** - Performance-based, not investment-based  

This dual-point system positions MyGrowNet as a modern, ethical, and sustainable platform for community empowerment and wealth creation.

---

*For implementation details, see: `UNIFIED_PRODUCTS_SERVICES.md`*  
*For member guide, see: `Points_System_Guide.md`*  
*For admin operations, see: `POINTS_SYSTEM_ADMIN_GUIDE.md`*

---

**End of Specification - Version 2.0**
