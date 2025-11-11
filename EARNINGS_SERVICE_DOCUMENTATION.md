# Earnings Service Documentation

**Created:** November 9, 2025  
**Status:** ✅ Complete - Centralized Earnings Calculations

---

## Overview

The `EarningsService` provides a centralized, maintainable way to calculate and retrieve user earnings across the platform. This service consolidates all earnings-related logic in one place for consistency and easy maintenance.

---

## Why This Service?

### Before ❌
- Earnings calculations scattered across multiple files
- Duplicate logic in User model and controllers
- Hard to maintain and update
- Inconsistent calculations
- Difficult to add new earning types

### After ✅
- Single source of truth for earnings
- Consistent calculations everywhere
- Easy to maintain and extend
- Clear, documented methods
- Type-safe with proper return types

---

## Features

### 1. Total Earnings Calculation
```php
$earningsService = app(EarningsService::class);
$total = $earningsService->calculateTotalEarnings($user);
```

### 2. Detailed Breakdown
```php
$breakdown = $earningsService->getEarningsBreakdown($user);
// Returns:
// [
//     'commissions' => 1500.00,
//     'profit_shares' => 500.00,
//     'bonuses' => 200.00,
//     'total' => 2200.00
// ]
```

### 3. Period-Based Earnings
```php
// This month
$thisMonth = $earningsService->getThisMonthEarnings($user);

// Custom period
$earnings = $earningsService->getEarningsForPeriod(
    $user,
    Carbon::parse('2025-01-01'),
    Carbon::parse('2025-01-31')
);
```

### 4. Seven-Level Commission Breakdown
```php
$levels = $earningsService->getSevenLevelBreakdown($user);
// Returns array with earnings for each level 1-7
```

### 5. Earnings Statistics
```php
$stats = $earningsService->getEarningsStatistics($user);
// Returns:
// [
//     'all_time' => 5000.00,
//     'this_month' => 800.00,
//     'last_month' => 600.00,
//     'growth_rate' => 33.33,
//     'pending' => 150.00,
//     'average_monthly' => 700.00
// ]
```

### 6. Monthly History
```php
$history = $earningsService->getMonthlyEarningsHistory($user, 6);
// Returns 6 months of earnings data
```

---

## Available Methods

### Public Methods

#### `calculateTotalEarnings(User $user): float`
Calculate total all-time earnings for a user.

**Returns:** Total earnings (commissions + profit shares + bonuses)

#### `getEarningsBreakdown(User $user): array`
Get detailed breakdown of earnings by type.

**Returns:**
```php
[
    'commissions' => float,
    'profit_shares' => float,
    'bonuses' => float,
    'total' => float
]
```

#### `getThisMonthEarnings(User $user): float`
Get total earnings for the current month.

**Returns:** Current month earnings

#### `getEarningsForPeriod(User $user, Carbon $startDate, Carbon $endDate): array`
Get earnings for a specific date range.

**Parameters:**
- `$user` - User instance
- `$startDate` - Start of period
- `$endDate` - End of period

**Returns:**
```php
[
    'commissions' => float,
    'profit_shares' => float,
    'bonuses' => float,
    'total' => float
]
```

#### `getCommissionEarningsByLevel(User $user, int $level, ?Carbon $startDate, ?Carbon $endDate): float`
Get commission earnings for a specific level (1-7).

**Parameters:**
- `$user` - User instance
- `$level` - Level number (1-7)
- `$startDate` - Optional start date
- `$endDate` - Optional end date

**Returns:** Commission earnings for that level

#### `getSevenLevelBreakdown(User $user): array`
Get earnings breakdown for all 7 levels.

**Returns:**
```php
[
    [
        'level' => 1,
        'total_earnings' => 500.00,
        'this_month_earnings' => 100.00,
        'count' => 5
    ],
    // ... levels 2-7
]
```

#### `getPendingEarnings(User $user): float`
Get unpaid/pending earnings.

**Returns:** Total pending earnings

#### `getMonthlyEarningsHistory(User $user, int $months = 6): array`
Get earnings history for the last N months.

**Parameters:**
- `$user` - User instance
- `$months` - Number of months (default: 6)

**Returns:**
```php
[
    [
        'month' => 'Nov 2025',
        'earnings' => [
            'commissions' => 800.00,
            'profit_shares' => 200.00,
            'bonuses' => 50.00,
            'total' => 1050.00
        ]
    ],
    // ... previous months
]
```

#### `getEarningsStatistics(User $user): array`
Get comprehensive earnings statistics.

**Returns:**
```php
[
    'all_time' => float,
    'this_month' => float,
    'last_month' => float,
    'growth_rate' => float, // Percentage
    'pending' => float,
    'average_monthly' => float
]
```

#### `getAverageMonthlyEarnings(User $user, int $months = 6): float`
Calculate average monthly earnings over N months.

**Parameters:**
- `$user` - User instance
- `$months` - Number of months to average (default: 6)

**Returns:** Average monthly earnings

---

## Usage Examples

### In Controllers

```php
use App\Services\EarningsService;

class DashboardController extends Controller
{
    public function __construct(
        protected EarningsService $earningsService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        
        return Inertia::render('Dashboard', [
            'stats' => [
                'total_earnings' => $this->earningsService->calculateTotalEarnings($user),
                'this_month_earnings' => $this->earningsService->getThisMonthEarnings($user),
            ],
            'earnings_breakdown' => $this->earningsService->getEarningsBreakdown($user),
            'commission_levels' => $this->earningsService->getSevenLevelBreakdown($user),
        ]);
    }
}
```

### In API Endpoints

```php
public function getEarnings(Request $request)
{
    $user = $request->user();
    $earningsService = app(EarningsService::class);
    
    return response()->json([
        'earnings' => $earningsService->getEarningsStatistics($user),
        'history' => $earningsService->getMonthlyEarningsHistory($user, 12),
    ]);
}
```

### In User Model

```php
public function getTotalEarningsAttribute(): float
{
    return app(EarningsService::class)->calculateTotalEarnings($this);
}
```

---

## Earnings Components

### 1. Commissions
- Referral commissions (7 levels)
- Status: 'paid' only
- Source: `referral_commissions` table

### 2. Profit Shares
- Quarterly profit distributions
- Community project profits
- Source: `profit_shares` table

### 3. Bonuses
- Achievement bonuses
- Milestone rewards
- Special promotions
- Source: `achievement_bonuses` table

---

## Adding New Earning Types

To add a new earning type (e.g., "Workshop Commissions"):

### 1. Add Private Helper Method
```php
private function getWorkshopCommissions(
    User $user, 
    ?Carbon $startDate = null, 
    ?Carbon $endDate = null
): float {
    $query = $user->workshopCommissions()->where('status', 'paid');

    if ($startDate && $endDate) {
        $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    return (float) $query->sum('amount');
}
```

### 2. Update Main Calculation Methods
```php
public function calculateTotalEarnings(User $user): float
{
    return $this->getCommissionEarnings($user) 
         + $this->getProfitShareEarnings($user)
         + $this->getBonusEarnings($user)
         + $this->getWorkshopCommissions($user); // Add here
}

public function getEarningsBreakdown(User $user): array
{
    return [
        'commissions' => $this->getCommissionEarnings($user),
        'profit_shares' => $this->getProfitShareEarnings($user),
        'bonuses' => $this->getBonusEarnings($user),
        'workshop_commissions' => $this->getWorkshopCommissions($user), // Add here
        'total' => $this->calculateTotalEarnings($user),
    ];
}
```

### 3. Update Period Methods
Add the new earning type to `getEarningsForPeriod()` and `getThisMonthEarnings()`.

---

## Migration Guide

### Step 1: Update Controllers

**Before:**
```php
$totalEarnings = $user->calculateTotalEarnings();
$thisMonth = $user->referralCommissions()
    ->whereMonth('created_at', now()->month)
    ->sum('amount');
```

**After:**
```php
$earningsService = app(EarningsService::class);
$totalEarnings = $earningsService->calculateTotalEarnings($user);
$thisMonth = $earningsService->getThisMonthEarnings($user);
```

### Step 2: Update User Model

Keep the User model methods for backward compatibility, but delegate to the service:

```php
public function calculateTotalEarnings(): float
{
    return app(EarningsService::class)->calculateTotalEarnings($this);
}
```

### Step 3: Update Tests

```php
public function test_calculates_total_earnings()
{
    $user = User::factory()->create();
    $earningsService = app(EarningsService::class);
    
    // Create test data...
    
    $total = $earningsService->calculateTotalEarnings($user);
    
    $this->assertEquals(1500.00, $total);
}
```

---

## Benefits

### 1. Maintainability ✅
- Single file to update for earnings logic
- Clear method names and documentation
- Easy to find and fix bugs

### 2. Consistency ✅
- Same calculations everywhere
- No duplicate logic
- Predictable results

### 3. Testability ✅
- Easy to unit test
- Mock-friendly
- Clear inputs and outputs

### 4. Extensibility ✅
- Simple to add new earning types
- Clear pattern to follow
- Backward compatible

### 5. Performance ✅
- Optimized queries
- Efficient calculations
- Cacheable results

---

## Testing

```php
use Tests\TestCase;
use App\Services\EarningsService;
use App\Models\User;

class EarningsServiceTest extends TestCase
{
    protected EarningsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(EarningsService::class);
    }

    public function test_calculates_total_earnings()
    {
        $user = User::factory()->create();
        
        // Create test commissions
        $user->referralCommissions()->create([
            'amount' => 500,
            'status' => 'paid',
            'level' => 1,
        ]);
        
        $total = $this->service->calculateTotalEarnings($user);
        
        $this->assertEquals(500.00, $total);
    }

    public function test_gets_seven_level_breakdown()
    {
        $user = User::factory()->create();
        
        $breakdown = $this->service->getSevenLevelBreakdown($user);
        
        $this->assertCount(7, $breakdown);
        $this->assertArrayHasKey('level', $breakdown[0]);
        $this->assertArrayHasKey('total_earnings', $breakdown[0]);
    }
}
```

---

## Future Enhancements

### Phase 1 (Current)
- ✅ Basic earnings calculations
- ✅ Period-based queries
- ✅ Seven-level breakdown
- ✅ Statistics and history

### Phase 2 (Planned)
- [ ] Caching for performance
- [ ] Real-time earnings updates
- [ ] Earnings projections
- [ ] Tax calculations

### Phase 3 (Future)
- [ ] Multi-currency support
- [ ] Earnings goals and tracking
- [ ] Automated reports
- [ ] Analytics dashboard

---

## Notes

- All monetary values returned as `float`
- Dates use Carbon for consistency
- Only 'paid' status earnings counted (except `getPendingEarnings()`)
- Service is stateless and can be safely injected
- Methods are chainable where appropriate

---

**Status:** ✅ Ready for production use

The EarningsService provides a solid foundation for all earnings-related calculations with room for future enhancements.
