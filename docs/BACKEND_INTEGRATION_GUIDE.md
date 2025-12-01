# Backend Integration Guide

**Created:** November 23, 2025  
**Status:** Required for Production

---

## 🔍 Current State Analysis

### What's Already Integrated ✅

The mobile dashboard already receives and uses real backend data for:

1. **User Data** - `props.user`
   - Name, tier, starter kit status
   - Verification status
   - Loan eligibility

2. **Stats** - `props.stats`
   - Total earnings
   - This month earnings
   - Total deposits/withdrawals

3. **Network Data** - `props.networkData`
   - Total network size
   - Direct referrals

4. **Team Volume** - `props.teamVolumeData`
   - Current month volume
   - Personal/team breakdown

5. **Referral Stats** - `props.referralStats`
   - 7 levels with members
   - Member details (name, email, tier, status)
   - Earnings per level

6. **Wallet Balance** - `props.walletBalance`
   - Current balance

7. **Earnings Breakdown** - `props.earningsBreakdown`
   - Referral, LGR, bonuses

8. **Transactions** - `props.recentTopups`
   - Recent deposits/withdrawals

9. **Loan Summary** - `props.loanSummary`
   - Loan balance, status

10. **Assets** - `props.assetData`
    - User assets and summary

### What Needs Backend Integration ⚠️

Only **2 new data points** need backend integration:

1. **Network Growth Data** (for sparkline)
2. **Earnings Trend Data** (for chart)

Everything else is already connected!

---

## 📊 Required Backend Changes

### 1. Network Growth Data

**Location:** `app/Http/Controllers/MyGrowNet/MobileDashboardController.php`

**Add to existing data array:**

```php
public function index()
{
    // ... existing code ...
    
    // NEW: Network growth data for sparkline
    $networkGrowth = DB::table('users')
        ->where('referrer_id', auth()->id())
        ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
        ->where('created_at', '>=', now()->subMonths(6))
        ->groupBy('month')
        ->orderBy('month')
        ->get()
        ->map(function($item) {
            return [
                'month' => $item->month,
                'count' => $item->count
            ];
        });
    
    // Ensure we have 6 months of data (fill missing months with 0)
    $networkGrowth = $this->fillMissingMonths($networkGrowth, 6);
    
    return Inertia::render('MyGrowNet/MobileDashboard', [
        // ... existing props ...
        'networkGrowth' => $networkGrowth, // NEW
    ]);
}

// Helper method to fill missing months
private function fillMissingMonths($data, $months)
{
    $result = [];
    $now = now();
    
    for ($i = $months - 1; $i >= 0; $i--) {
        $date = $now->copy()->subMonths($i);
        $monthKey = $date->format('Y-m');
        
        $existing = $data->firstWhere('month', $monthKey);
        
        $result[] = [
            'month' => $monthKey,
            'count' => $existing ? $existing['count'] : 0
        ];
    }
    
    return $result;
}
```

### 2. Earnings Trend Data

**Add to same controller:**

```php
public function index()
{
    // ... existing code ...
    
    // NEW: Earnings trend data for chart
    $earningsTrend = DB::table('transactions')
        ->where('user_id', auth()->id())
        ->whereIn('type', ['referral_commission', 'lgr_profit', 'bonus', 'reward'])
        ->where('status', 'completed')
        ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(amount) as amount')
        ->where('created_at', '>=', now()->subMonths(6))
        ->groupBy('month')
        ->orderBy('month')
        ->get()
        ->map(function($item) {
            return [
                'month' => $item->month,
                'label' => date('M', strtotime($item->month . '-01')),
                'amount' => (float) $item->amount
            ];
        });
    
    // Fill missing months
    $earningsTrend = $this->fillMissingMonthsWithLabels($earningsTrend, 6);
    
    return Inertia::render('MyGrowNet/MobileDashboard', [
        // ... existing props ...
        'networkGrowth' => $networkGrowth,
        'earningsTrend' => $earningsTrend, // NEW
    ]);
}

// Helper method with labels
private function fillMissingMonthsWithLabels($data, $months)
{
    $result = [];
    $now = now();
    
    for ($i = $months - 1; $i >= 0; $i--) {
        $date = $now->copy()->subMonths($i);
        $monthKey = $date->format('Y-m');
        
        $existing = $data->firstWhere('month', $monthKey);
        
        $result[] = [
            'month' => $monthKey,
            'label' => $date->format('M'),
            'amount' => $existing ? $existing['amount'] : 0
        ];
    }
    
    return $result;
}
```

---

## 🔧 Frontend Updates

### Update Props Interface

**File:** `resources/js/pages/MyGrowNet/MobileDashboard.vue`

```typescript
const props = withDefaults(defineProps<{
  // ... existing props ...
  networkGrowth?: any[]; // NEW
  earningsTrend?: any[]; // NEW
}>(), {
  // ... existing defaults ...
  networkGrowth: () => [],
  earningsTrend: () => []
});
```

### Update Computed Properties

Replace mock data with real data:

```typescript
// Network growth data for sparkline
const networkGrowthData = computed(() => {
  // Use real backend data if available
  if (props.networkGrowth && props.networkGrowth.length > 0) {
    return props.networkGrowth.map(item => item.count);
  }
  
  // Fallback to mock data for development
  const currentSize = props.networkData?.total_network_size || 0;
  const months = 6;
  const data = [];
  
  for (let i = months - 1; i >= 0; i--) {
    const percentage = (months - i) / months;
    const value = Math.max(0, Math.floor(currentSize * percentage * (0.7 + Math.random() * 0.3)));
    data.push(value);
  }
  
  return data;
});

// Earnings trend data for chart
const earningsTrendData = computed(() => {
  // Use real backend data if available
  if (props.earningsTrend && props.earningsTrend.length > 0) {
    return props.earningsTrend;
  }
  
  // Fallback to mock data for development
  const currentEarnings = props.stats?.this_month_earnings || 0;
  const months = ['Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov'];
  const data = [];
  
  const now = new Date();
  for (let i = 5; i >= 0; i--) {
    const date = new Date(now.getFullYear(), now.getMonth() - i, 1);
    const monthLabel = months[5 - i];
    const monthKey = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
    
    const baseAmount = currentEarnings * (0.4 + Math.random() * 0.6);
    const amount = Math.floor(baseAmount * ((6 - i) / 6));
    
    data.push({
      month: monthKey,
      label: monthLabel,
      amount: amount
    });
  }
  
  return data;
});
```

---

## 🚀 Optional: Lazy Loading API Endpoints

For better performance, you can create separate endpoints for each tab:

### Routes

**File:** `routes/web.php`

```php
Route::middleware(['auth'])->group(function () {
    // Existing route
    Route::get('/mobile/dashboard', [MobileDashboardController::class, 'index'])
        ->name('mygrownet.mobile.dashboard');
    
    // NEW: Lazy loading endpoints
    Route::get('/mobile/dashboard/team', [MobileDashboardController::class, 'teamData'])
        ->name('mygrownet.mobile.dashboard.team');
    
    Route::get('/mobile/dashboard/wallet', [MobileDashboardController::class, 'walletData'])
        ->name('mygrownet.mobile.dashboard.wallet');
    
    Route::get('/mobile/dashboard/learn', [MobileDashboardController::class, 'learnData'])
        ->name('mygrownet.mobile.dashboard.learn');
});
```

### Controller Methods

```php
public function teamData()
{
    return response()->json([
        'networkData' => $this->getNetworkData(),
        'referralStats' => $this->getReferralStats(),
        'teamVolumeData' => $this->getTeamVolumeData(),
        'networkGrowth' => $this->getNetworkGrowth(),
    ]);
}

public function walletData()
{
    return response()->json([
        'walletBalance' => $this->getWalletBalance(),
        'earningsBreakdown' => $this->getEarningsBreakdown(),
        'recentTopups' => $this->getRecentTopups(),
        'earningsTrend' => $this->getEarningsTrend(),
    ]);
}

public function learnData()
{
    return response()->json([
        'starterKit' => $this->getStarterKitData(),
        'tools' => $this->getAvailableTools(),
    ]);
}
```

### Frontend Implementation

Update the `loadTabData` function:

```typescript
const loadTabData = async (tab: string) => {
  if (tabDataLoaded.value[tab as keyof typeof tabDataLoaded.value]) {
    return;
  }

  tabLoading.value = true;
  
  try {
    let response;
    
    switch(tab) {
      case 'team':
        response = await axios.get(route('mygrownet.mobile.dashboard.team'));
        // Update reactive data with response
        break;
      case 'wallet':
        response = await axios.get(route('mygrownet.mobile.dashboard.wallet'));
        // Update reactive data with response
        break;
      case 'learn':
        response = await axios.get(route('mygrownet.mobile.dashboard.learn'));
        // Update reactive data with response
        break;
    }
    
    tabDataLoaded.value[tab as keyof typeof tabDataLoaded.value] = true;
  } catch (error) {
    console.error(`Error loading ${tab} tab data:`, error);
  } finally {
    tabLoading.value = false;
  }
};
```

---

## 📋 Implementation Checklist

### Required (Minimum)
- [ ] Add `networkGrowth` data to controller
- [ ] Add `earningsTrend` data to controller
- [ ] Add helper methods for filling missing months
- [ ] Update frontend props interface
- [ ] Update computed properties to use real data
- [ ] Test with real data
- [ ] Verify charts display correctly

### Optional (Performance)
- [ ] Create lazy loading API endpoints
- [ ] Implement separate controller methods
- [ ] Update frontend to use lazy loading APIs
- [ ] Add caching for expensive queries
- [ ] Optimize database queries
- [ ] Add indexes if needed

---

## 🧪 Testing

### Test Network Growth Data

```bash
# In Tinker
php artisan tinker

# Test query
DB::table('users')
    ->where('referrer_id', 1) // Replace with actual user ID
    ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
    ->where('created_at', '>=', now()->subMonths(6))
    ->groupBy('month')
    ->orderBy('month')
    ->get();
```

### Test Earnings Trend Data

```bash
# In Tinker
DB::table('transactions')
    ->where('user_id', 1) // Replace with actual user ID
    ->whereIn('type', ['referral_commission', 'lgr_profit', 'bonus', 'reward'])
    ->where('status', 'completed')
    ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(amount) as amount')
    ->where('created_at', '>=', now()->subMonths(6))
    ->groupBy('month')
    ->orderBy('month')
    ->get();
```

---

## 🎯 Summary

### What's Already Working
✅ 95% of data is already integrated  
✅ All user, stats, network, wallet data working  
✅ All member data working  
✅ All transaction data working  

### What Needs Integration
⚠️ Only 2 data points need backend:
1. Network growth (6 months)
2. Earnings trend (6 months)

### Estimated Time
- **Required changes:** 30-60 minutes
- **Optional lazy loading:** 2-3 hours
- **Testing:** 1-2 hours

### Impact
- Charts will show real historical data
- Better insights for users
- Production-ready dashboard

---

## 🚀 Next Steps

1. **Implement required changes** (30-60 min)
   - Add 2 data queries to controller
   - Update frontend props
   - Test with real data

2. **Optional: Lazy loading** (2-3 hours)
   - Create API endpoints
   - Implement lazy loading
   - Test performance

3. **Phase 3: Polish** (optional)
   - Visual refinements
   - Animation improvements
   - Accessibility enhancements

