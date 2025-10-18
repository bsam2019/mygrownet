# Points System - Quick Start Guide

Get the MyGrowNet Points System up and running in minutes!

---

## 🚀 Installation

### Step 1: Run Migrations
```bash
php artisan migrate
```

This creates all necessary tables:
- user_points
- point_transactions
- monthly_activity_status
- user_badges
- monthly_challenges

### Step 2: Verify Installation
```bash
php artisan tinker
```

```php
// Check if tables exist
Schema::hasTable('user_points'); // should return true
Schema::hasTable('point_transactions'); // should return true
```

---

## 💡 Basic Usage

### Award Points to a User

```php
use App\Services\PointService;
use App\Models\User;

$user = User::find(1);
$pointService = app(PointService::class);

// Award points for a referral
$pointService->awardPoints(
    $user,
    'direct_referral',      // source
    150,                     // LP amount
    150,                     // MAP amount
    'New referral: John Doe' // description
);
```

### Check User's Points

```php
$user = User::with('points')->find(1);

echo "Lifetime Points: " . $user->points->lifetime_points;
echo "Monthly Points: " . $user->points->monthly_points;
echo "Current Streak: " . $user->points->current_streak_months . " months";
echo "Multiplier: " . $user->points->active_multiplier . "x";
```

### Check Monthly Qualification

```php
$pointService = app(PointService::class);

if ($pointService->checkMonthlyQualification($user)) {
    echo "User is qualified for this month!";
} else {
    $required = $pointService->getRequiredMAP($user);
    $current = $user->points->monthly_points;
    echo "User needs " . ($required - $current) . " more MAP";
}
```

### Check Level Advancement

```php
use App\Services\LevelAdvancementService;

$levelService = app(LevelAdvancementService::class);

$newLevel = $levelService->checkLevelAdvancement($user);

if ($newLevel) {
    echo "User advanced to: " . $newLevel;
} else {
    $progress = $levelService->getLevelProgress($user);
    echo "Progress to next level: " . $progress['progress'] . "%";
}
```

---

## 🎯 Common Point Sources

### Network Building

```php
// Direct referral
$pointService->awardPoints($user, 'direct_referral', 150, 150, 'New referral');

// Spillover referral
$pointService->awardPoints($user, 'spillover_referral', 30, 30, 'Spillover placement');

// Downline advancement
$pointService->awardPoints($user, 'downline_advancement', 50, 50, 'Team member leveled up');
```

### Product Sales

```php
// Personal purchase (K500 = 5 units × 10 points)
$amount = 500;
$points = ($amount / 100) * 10;
$pointService->awardPoints($user, 'personal_purchase', $points, $points, "Purchase: K{$amount}");

// Direct referral sale (K500 = 5 units × 20 points)
$points = ($amount / 100) * 20;
$pointService->awardPoints($user, 'direct_sale', $points, $points, "Team sale: K{$amount}");
```

### Learning & Development

```php
// Basic course completion
$pointService->awardPoints($user, 'course_basic', 30, 30, 'Completed: Intro to Business');

// Advanced course completion
$pointService->awardPoints($user, 'course_advanced', 60, 60, 'Completed: Advanced Marketing');

// Certification
$pointService->awardPoints($user, 'certification', 100, 100, 'Earned: Business Leadership Cert');
```

### Daily Activities

```php
// Daily login (automatically checks if already awarded today)
$transaction = $pointService->awardDailyLogin($user);

if ($transaction) {
    // Check for streak bonuses
    $pointService->checkStreakBonuses($user);
}
```

---

## 🔄 Scheduled Tasks

### Manual Execution (for testing)

```bash
# Reset monthly points (normally runs on 1st of month)
php artisan points:reset-monthly

# Check qualification status (normally runs daily)
php artisan points:check-qualification

# Check level advancements (normally runs daily)
php artisan points:check-advancements
```

### Automatic Scheduling

The tasks are already scheduled in `routes/console.php`:
- Monthly reset: 1st at 00:00
- Qualification check: Daily at 09:00
- Advancement check: Daily at 10:00

Just ensure Laravel scheduler is running:
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## 📊 Querying Points Data

### Get User's Transaction History

```php
$transactions = $user->pointTransactions()
    ->latest()
    ->limit(20)
    ->get();

foreach ($transactions as $transaction) {
    echo "{$transaction->description}: +{$transaction->lp_amount} LP, +{$transaction->map_amount} MAP\n";
}
```

### Get Monthly Activity History

```php
$history = $user->monthlyActivityStatuses()
    ->orderBy('year', 'desc')
    ->orderBy('month', 'desc')
    ->limit(12)
    ->get();

foreach ($history as $month) {
    echo "{$month->formatted_period}: {$month->map_earned} MAP - ";
    echo $month->qualified ? "✓ Qualified" : "✗ Not Qualified";
    echo "\n";
}
```

### Get User's Badges

```php
$badges = $user->badges;

foreach ($badges as $badge) {
    echo "{$badge->badge_name} - Earned: {$badge->earned_at->format('M d, Y')}\n";
}
```

### Get Leaderboard

```php
// Top monthly earners
$topMonthly = User::join('user_points', 'users.id', '=', 'user_points.user_id')
    ->orderBy('user_points.monthly_points', 'desc')
    ->limit(10)
    ->get();

// Top lifetime earners
$topLifetime = User::join('user_points', 'users.id', '=', 'user_points.user_id')
    ->orderBy('user_points.lifetime_points', 'desc')
    ->limit(10)
    ->get();
```

---

## 🎮 Testing Scenarios

### Scenario 1: New User Journey

```php
$user = User::factory()->create();

// Day 1: Register and login
$pointService->awardDailyLogin($user);

// Day 2: Complete first course
$pointService->awardPoints($user, 'course_basic', 30, 30, 'First course');
$pointService->awardDailyLogin($user);

// Day 3: Make first referral
$pointService->awardPoints($user, 'direct_referral', 150, 150, 'First referral');
$pointService->awardDailyLogin($user);

// Check progress
$user->refresh();
echo "Total LP: " . $user->points->lifetime_points; // 180 LP
echo "Total MAP: " . $user->points->monthly_points; // 195 MAP
```

### Scenario 2: Level Advancement

```php
$user = User::find(1);

// Simulate earning enough points for Professional level
$user->points->update(['lifetime_points' => 500]);
$user->update([
    'referral_count' => 3,
    'created_at' => now()->subMonths(2)
]);

// Check advancement
$levelService = app(LevelAdvancementService::class);
$newLevel = $levelService->checkLevelAdvancement($user);

if ($newLevel) {
    echo "Advanced to: " . $newLevel; // "professional"
}
```

### Scenario 3: Monthly Qualification

```php
$user = User::find(1);

// Simulate earning MAP throughout the month
$pointService->awardPoints($user, 'daily_login', 0, 5, 'Login'); // 20 days = 100 MAP
$pointService->awardPoints($user, 'course_basic', 30, 30, 'Course'); // +30 MAP

// Check qualification (Associate needs 100 MAP)
$qualified = $pointService->checkMonthlyQualification($user);
echo $qualified ? "Qualified!" : "Not qualified";
```

---

## 🐛 Troubleshooting

### Issue: Points not being awarded

**Check:**
1. User has a `user_points` record
2. Multiplier is set correctly
3. Transaction is being created

```php
// Ensure user points record exists
$user->points()->firstOrCreate(['user_id' => $user->id]);

// Check recent transactions
$user->pointTransactions()->latest()->first();
```

### Issue: Level not advancing

**Check:**
1. All requirements are met
2. Account age is sufficient
3. Downline requirements are satisfied

```php
$levelService = app(LevelAdvancementService::class);
$progress = $levelService->getLevelProgress($user);

// See which requirements are not met
foreach ($progress['met'] as $key => $req) {
    if (!$req['met']) {
        echo "{$key}: {$req['current']}/{$req['required']}\n";
    }
}
```

### Issue: Monthly qualification not working

**Check:**
1. MAP is being tracked
2. Required MAP is correct for level
3. Current month activity record exists

```php
$user->load('points', 'currentMonthActivity');

echo "Current MAP: " . $user->points->monthly_points . "\n";
echo "Required MAP: " . $user->points->getRequiredMapForLevel() . "\n";
echo "Qualified: " . ($user->isQualifiedThisMonth() ? 'Yes' : 'No') . "\n";
```

---

## 📱 Frontend Integration

### Access Points Dashboard

```
URL: /points
```

### API Endpoints

```javascript
// Get qualification status
axios.get('/points/qualification')
    .then(response => {
        console.log('Qualified:', response.data.qualified);
        console.log('Current MAP:', response.data.current_map);
        console.log('Required MAP:', response.data.required_map);
    });

// Get level progress
axios.get('/points/level-progress')
    .then(response => {
        console.log('Current Level:', response.data.current_level);
        console.log('Progress:', response.data.progress + '%');
    });

// Award daily login
axios.post('/points/daily-login')
    .then(response => {
        console.log('Points awarded!', response.data);
    });
```

---

## 🎯 Quick Reference

### Point Values

| Activity | LP | MAP |
|----------|----|----|
| Direct referral | 150 | 150 |
| Daily login | 0 | 5 |
| Basic course | 30 | 30 |
| Advanced course | 60 | 60 |
| Personal purchase (per K100) | 10 | 10 |
| Direct sale (per K100) | 20 | 20 |

### Level Requirements

| Level | LP | Time | Referrals |
|-------|----|----|-----------|
| Professional | 500 | 1mo | 3 |
| Senior | 1,500 | 3mo | 3 |
| Manager | 4,000 | 6mo | 3 |
| Director | 10,000 | 12mo | 3 |
| Executive | 25,000 | 18mo | 3 |
| Ambassador | 50,000 | 24mo | 3 |

### Monthly MAP Requirements

| Level | MAP Required |
|-------|--------------|
| Associate | 100 |
| Professional | 200 |
| Senior | 300 |
| Manager | 400 |
| Director | 500 |
| Executive | 600 |
| Ambassador | 800 |

---

## 📞 Support

For issues or questions:
- Check: `docs/POINTS_SYSTEM_SPECIFICATION.md`
- Read: `docs/Points_System_Guide.md`
- Review: `docs/POINTS_SYSTEM_IMPLEMENTATION.md`

---

**Happy coding! 🚀**
