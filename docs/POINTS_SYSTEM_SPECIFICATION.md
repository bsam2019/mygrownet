# MyGrowNet Points System - Technical Specification

**Version:** 1.0  
**Date:** October 17, 2025  
**Status:** Draft for Implementation

---

## Executive Summary

MyGrowNet implements a dual-points system to drive member engagement, product sales, and sustainable community growth. The system uses **Lifetime Points (LP)** for professional level progression and **Monthly Activity Points (MAP)** to ensure active participation for earnings eligibility.

---

## 1. System Architecture

### 1.1 Point Types

#### Lifetime Points (LP)
- **Purpose**: Professional level advancement (Associate → Ambassador)
- **Persistence**: Never expire, accumulate forever
- **Earning**: Network building, sales, education, community contribution
- **Usage**: Determines professional level and long-term status

#### Monthly Activity Points (MAP)
- **Purpose**: Qualify for monthly commissions, bonuses, and profit-sharing
- **Persistence**: Reset to 0 on the 1st of each month
- **Earning**: Same activities as LP, but emphasizes current engagement
- **Usage**: Determines active status and earnings eligibility

### 1.2 Core Principles

1. **Dual Qualification**: Members need both LP (for level) and MAP (for earnings)
2. **Activity-Based**: Points reward multiple behaviors, not just recruitment
3. **Transparent**: Clear requirements and real-time progress tracking
4. **Sustainable**: Inactive members don't receive distributions
5. **Flexible**: Multiple paths to earn points

---

## 2. Point Earning Matrix

### 2.1 Network Building Activities

| Activity | LP Earned | MAP Earned | Notes |
|----------|-----------|------------|-------|
| Direct referral registration | 150 | 150 | When new member completes registration |
| Indirect referral (spillover) | 30 | 30 | When placed in your matrix via spillover |
| Downline advances a level | 50 | 50 | When any downline member levels up |
| Complete matrix level filled | 100 | 100 | Bonus when entire level is filled |
| Help downline make first sale | 40 | 40 | Mentorship reward |

### 2.2 Product Sales Activities

| Activity | LP Earned | MAP Earned | Calculation |
|----------|-----------|------------|-------------|
| Personal product purchase | 10 per K100 | 10 per K100 | Own purchases |
| Direct referral product sale | 20 per K100 | 20 per K100 | Level 1 sales |
| Downline product sale (L2-7) | 5 per K100 | 5 per K100 | Levels 2-7 sales |
| Monthly sales target achieved | 200 | 200 | Bonus for hitting target |

**Sales Targets by Level:**
- Associate: K200/month
- Professional: K400/month
- Senior: K600/month
- Manager: K1,000/month
- Director: K1,500/month
- Executive: K2,500/month
- Ambassador: K4,000/month

### 2.3 Learning & Development

| Activity | LP Earned | MAP Earned | Notes |
|----------|-----------|------------|-------|
| Complete basic course | 30 | 30 | 1-2 hour courses |
| Complete advanced course | 60 | 60 | 3-5 hour courses |
| Pass certification exam | 100 | 100 | Professional certifications |
| Attend live webinar | 20 | 20 | Real-time participation |
| Complete monthly training | 0 | 50 | Mandatory monthly requirement |

### 2.4 Community Engagement

| Activity | LP Earned | MAP Earned | Limits/Notes |
|----------|-----------|------------|--------------|
| Daily login | 0 | 5 | Once per day |
| 7-day login streak | 0 | 50 | Weekly bonus |
| 30-day login streak | 0 | 200 | Monthly bonus |
| Vote on community project | 10 | 10 | Per vote |
| Submit approved project | 100 | 100 | One-time per project |
| Quality forum post | 0 | 5 | Max 50 MAP/month (10 posts) |
| Mentor new member | 30 | 30 | Verified mentorship |

### 2.5 Platform Contribution

| Activity | LP Earned | MAP Earned | Notes |
|----------|-----------|------------|-------|
| Refer business partner | 300 | 300 | B2B partnership referral |
| Create approved content | 150 | 150 | Educational materials |
| Report bug/issue | 20 | 20 | Verified reports |
| Provide testimonial | 40 | 40 | Approved testimonials |

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
