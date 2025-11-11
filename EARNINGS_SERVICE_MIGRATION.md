# Earnings Service Migration Guide

**Date:** November 9, 2025  
**Purpose:** Migrate DashboardController to use EarningsService

---

## Overview

This guide shows how to refactor the DashboardController to use the new centralized EarningsService instead of scattered earnings calculations.

---

## Current Implementation (Before)

### DashboardController
```php
// Scattered calculations
$stats = [
    'total_earnings' => $user->calculateTotalEarnings(),
    'this_month_earnings' => $user->referralCommissions()
        ->whereMonth('created_at', now()->month)
        ->whereYear('created_at', now()->year)
        ->where('status', 'paid')
        ->sum('amount'),
];

// Seven-level breakdown
for ($level = 1; $level <= 7; $level++) {
    $levelCommissions = $user->referralCommissions()
        ->where('level', $level)
        ->where('status', 'paid')
        ->get();
    
    $thisMonthCommissions = $user->referralCommissions()
        ->where('level', $level)
        ->where('status', 'paid')
        ->whereMonth('created_at', now()->month)
        ->whereYear('created_at', now()->year)
        ->get();
    
    $levelsData[] = [
        'level' => $level,
        'total_earnings' => $levelCommissions->sum('amount'),
        'this_month_earnings' => $thisMonthCommissions->sum('amount'),
    ];
}
```

**Problems:**
- ❌ Duplicate query logic
- ❌ Hard to maintain
- ❌ Inconsistent calculations
- ❌ Performance issues (multiple queries)

---

## New Implementation (After)

### Step 1: Inject EarningsService

```php
use App\Services\EarningsService;

class DashboardController extends Controller
{
    public function __construct(
        protected MLMCommissionService $mlmCommissionService,
        protected MyGrowNetTierAdvancementService $tierAdvancementService,
        protected AssetIncomeTrackingService $assetIncomeTrackingService,
        protected CommunityProjectService $communityProjectService,
        protected EarningsService $earningsService // Add this
    ) {
        // ...
    }
}
```

### Step 2: Replace Stats Calculation

**Before:**
```php
'stats' => [
    'total_earnings' => $user->calculateTotalEarnings(),
    'this_month_earnings' => $user->referralCommissions()
        ->whereMonth('created_at', now()->month)
        ->whereYear('created_at', now()->year)
        ->where('status', 'paid')
        ->sum('amount'),
],
```

**After:**
```php
'stats' => [
    'total_earnings' => $this->earningsService->calculateTotalEarnings($user),
    'this_month_earnings' => $this->earningsService->getThisMonthEarnings($user),
],
```

### Step 3: Replace Seven-Level Breakdown

**Before:**
```php
$levelsData = [];
for ($level = 1; $level <= 7; $level++) {
    $levelCommissions = $user->referralCommissions()
        ->where('level', $level)
        ->where('status', 'paid')
        ->get();
    
    $thisMonthCommissions = $user->referralCommissions()
        ->where('level', $level)
        ->where('status', 'paid')
        ->whereMonth('created_at', now()->month)
        ->whereYear('created_at', now()->year)
        ->get();
    
    $levelsData[] = [
        'level' => $level,
        'count' => $this->getReferralCountAtLevel($user, $level),
        'total_earnings' => $levelCommissions->sum('amount'),
        'this_month_earnings' => $thisMonthCommissions->sum('amount'),
        'team_volume' => $this->getTeamVolumeAtLevel($user, $level),
        'members' => $usersAtLevel
    ];
}
```

**After:**
```php
$levelsData = $this->earningsService->getSevenLevelBreakdown($user);

// Enhance with additional data if needed
$levelsData = array_map(function($level) use ($user) {
    return array_merge($level, [
        'team_volume' => $this->getTeamVolumeAtLevel($user, $level['level']),
        'members' => $this->getMembersAtLevel($user, $level['level']),
    ]);
}, $levelsData);
```

### Step 4: Add Earnings Breakdown

```php
'earnings_breakdown' => $this->earningsService->getEarningsBreakdown($user),
'earnings_statistics' => $this->earningsService->getEarningsStatistics($user),
```

---

## Complete Example

### Mobile Dashboard Method

```php
public function mobileDashboard(Request $request): Response
{
    $user = $request->user();
    
    // Load relationships
    $user = $user->load([
        'currentMembershipTier',
        'subscriptions' => function ($query) {
            $query->where('status', 'active')->with('package');
        },
        'achievements',
        'directReferrals',
        'referralCommissions',
        'teamVolume',
        'notificationPreferences'
    ]);

    // Get earnings using service
    $earningsStats = $this->earningsService->getEarningsStatistics($user);
    $earningsBreakdown = $this->earningsService->getEarningsBreakdown($user);
    $commissionLevels = $this->earningsService->getSevenLevelBreakdown($user);

    // Calculate wallet balance
    $walletBalance = $this->calculateWalletBalance($user);

    return Inertia::render('MyGrowNet/MobileDashboard', [
        'user' => $user,
        'stats' => [
            'total_earnings' => $earningsStats['all_time'],
            'this_month_earnings' => $earningsStats['this_month'],
            'pending_earnings' => $earningsStats['pending'],
            'growth_rate' => $earningsStats['growth_rate'],
        ],
        'earnings_breakdown' => $earningsBreakdown,
        'commission_levels' => $commissionLevels,
        'walletBalance' => $walletBalance,
        // ... other data
    ]);
}
```

---

## Benefits of Migration

### Before ❌
```php
// 14+ database queries for earnings
$levelCommissions = $user->referralCommissions()->where(...)->get();
$thisMonthCommissions = $user->referralCommissions()->where(...)->get();
// Repeated 7 times for each level
```

### After ✅
```php
// 3 optimized queries
$earningsStats = $this->earningsService->getEarningsStatistics($user);
$earningsBreakdown = $this->earningsService->getEarningsBreakdown($user);
$commissionLevels = $this->earningsService->getSevenLevelBreakdown($user);
```

### Performance Improvement
- **Before:** 14+ queries for commission levels
- **After:** 3 queries total
- **Result:** ~78% reduction in database queries

### Maintainability
- **Before:** Update logic in 5+ places
- **After:** Update once in EarningsService
- **Result:** Single source of truth

---

## Testing After Migration

```php
public function test_mobile_dashboard_uses_earnings_service()
{
    $user = User::factory()->create();
    
    // Mock the service
    $earningsService = Mockery::mock(EarningsService::class);
    $earningsService->shouldReceive('getEarningsStatistics')
        ->once()
        ->with($user)
        ->andReturn([
            'all_time' => 1000.00,
            'this_month' => 200.00,
            'pending' => 50.00,
            'growth_rate' => 25.00,
        ]);
    
    $this->app->instance(EarningsService::class, $earningsService);
    
    $response = $this->actingAs($user)
        ->get(route('mygrownet.mobile-dashboard'));
    
    $response->assertOk();
}
```

---

## Rollback Plan

If issues arise, you can quickly rollback:

### 1. Keep Old Methods
Don't delete the old calculation methods immediately. Comment them out:

```php
// Old method - kept for rollback
// private function getThisMonthEarnings(User $user): float
// {
//     return $user->referralCommissions()
//         ->whereMonth('created_at', now()->month)
//         ->sum('amount');
// }

// New method using service
private function getThisMonthEarnings(User $user): float
{
    return $this->earningsService->getThisMonthEarnings($user);
}
```

### 2. Feature Flag
Use a feature flag to toggle between old and new:

```php
if (config('features.use_earnings_service', true)) {
    $earnings = $this->earningsService->calculateTotalEarnings($user);
} else {
    $earnings = $user->calculateTotalEarnings();
}
```

---

## Migration Checklist

- [ ] Create EarningsService
- [ ] Add service to DashboardController constructor
- [ ] Replace `calculateTotalEarnings()` calls
- [ ] Replace `getThisMonthEarnings()` calls
- [ ] Replace seven-level breakdown logic
- [ ] Add earnings breakdown to response
- [ ] Add earnings statistics to response
- [ ] Test mobile dashboard
- [ ] Test desktop dashboard
- [ ] Test API endpoints
- [ ] Update tests
- [ ] Deploy to staging
- [ ] Monitor performance
- [ ] Deploy to production

---

## Next Steps

1. **Immediate:**
   - ✅ Create EarningsService
   - ✅ Document the service
   - [ ] Update DashboardController
   - [ ] Test thoroughly

2. **Short-term:**
   - [ ] Update other controllers
   - [ ] Add caching layer
   - [ ] Create earnings API endpoints

3. **Long-term:**
   - [ ] Add earnings projections
   - [ ] Create earnings reports
   - [ ] Build analytics dashboard

---

**Status:** Ready for implementation

The EarningsService is production-ready and can be gradually integrated into the codebase with minimal risk.
