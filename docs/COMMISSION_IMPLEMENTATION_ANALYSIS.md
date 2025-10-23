# Commission Implementation Analysis

**Date:** October 23, 2025  
**Status:** ⚠️ DISCREPANCY FOUND

---

## Executive Summary

There is a **critical discrepancy** between the documented compensation plan and the actual implementation in the codebase.

### Documented (Compensation Plan Presentation)
- **7 levels** of commissions
- Rates: 15%, 10%, 8%, 6%, 4%, 3%, 2%

### Actually Implemented (Code)
- **5 levels** of commissions (in Domain service)
- **7 levels** defined (in Model constants)
- Mixed implementation across different services

---

## Detailed Analysis

### 1. ReferralCommission Model (`app/Models/ReferralCommission.php`)

**Status:** ✅ **7 LEVELS DEFINED**

```php
public const COMMISSION_RATES = [
    1 => 15.0, // Level 1 (Associate): 15%
    2 => 10.0, // Level 2 (Professional): 10%
    3 => 8.0,  // Level 3 (Senior): 8%
    4 => 6.0,  // Level 4 (Manager): 6%
    5 => 4.0,  // Level 5 (Director): 4%
    6 => 3.0,  // Level 6 (Executive): 3%
    7 => 2.0,  // Level 7 (Ambassador): 2%
];

public const MAX_COMMISSION_LEVELS = 7;
```

**Assessment:** ✅ Correctly implements 7-level structure with proper rates

---

### 2. MLMCommissionService (`app/Services/MLMCommissionService.php`)

**Status:** ⚠️ **ONLY PROCESSES 5 LEVELS**

```php
public function processMLMCommissions(
    User $purchaser, 
    float $packageAmount, 
    string $packageType = 'subscription'
): array {
    // Get upline referrers up to 5 levels
    $uplineReferrers = $this->getUplineReferrers($purchaser, 5);  // ⚠️ HARDCODED TO 5
    
    foreach ($uplineReferrers as $referrerData) {
        $referrer = User::find($referrerData['user_id']);
        $level = $referrerData['level'];
        
        // Calculate commission amount for this level
        $commissionRate = ReferralCommission::getCommissionRate($level);  // ✅ Uses model rates
        $commissionAmount = $packageAmount * ($commissionRate / 100);
        
        // Create commission record...
    }
}
```

**Issues:**
1. ❌ Hardcoded to process only 5 levels
2. ✅ Uses correct commission rates from model
3. ⚠️ Will never create Level 6 or Level 7 commissions

---

### 3. MLMCommissionCalculationService (`app/Domain/MLM/Services/MLMCommissionCalculationService.php`)

**Status:** ❌ **WRONG RATES, ONLY 5 LEVELS**

```php
public const COMMISSION_RATES = [
    1 => 12.0, // Level 1: 12%  ❌ Should be 15%
    2 => 6.0,  // Level 2: 6%   ❌ Should be 10%
    3 => 4.0,  // Level 3: 4%   ❌ Should be 8%
    4 => 2.0,  // Level 4: 2%   ❌ Should be 6%
    5 => 1.0,  // Level 5: 1%   ❌ Should be 4%
    // ❌ Missing Level 6: 3%
    // ❌ Missing Level 7: 2%
];
```

**Issues:**
1. ❌ Completely different rates from model
2. ❌ Only defines 5 levels
3. ❌ Not aligned with MyGrowNet specification
4. ⚠️ This service appears to be from old VBIF system

---

### 4. Database Schema

**Status:** ✅ **SUPPORTS 7 LEVELS**

```sql
-- referral_commissions table
level INT DEFAULT 1  -- No constraint, can store 1-7
```

**Assessment:** ✅ Database can handle 7 levels without modification

---

## Impact Assessment

### Current Behavior

When a member makes a purchase:

1. **MLMCommissionService.processMLMCommissions()** is called
2. It fetches only **5 upline levels**
3. It uses **correct rates** from ReferralCommission model (15%, 10%, 8%, 6%, 4%)
4. **Levels 6 and 7 never receive commissions** ❌

### What Members Experience

| Level | Expected Commission | Actual Commission | Status |
|-------|-------------------|-------------------|--------|
| 1 (Associate) | 15% | 15% | ✅ Working |
| 2 (Professional) | 10% | 10% | ✅ Working |
| 3 (Senior) | 8% | 8% | ✅ Working |
| 4 (Manager) | 6% | 6% | ✅ Working |
| 5 (Director) | 4% | 4% | ✅ Working |
| 6 (Executive) | 3% | **0%** | ❌ NOT WORKING |
| 7 (Ambassador) | 2% | **0%** | ❌ NOT WORKING |

---

## Root Cause

The issue stems from **hardcoded level limit** in the commission processing service:

```php
// app/Services/MLMCommissionService.php:18
$uplineReferrers = $this->getUplineReferrers($purchaser, 5);  // ⚠️ Should be 7
```

This was likely carried over from the old VBIF system which only had 5 levels.

---

## Required Fixes

### Priority 1: Fix MLMCommissionService (CRITICAL)

**File:** `app/Services/MLMCommissionService.php`

**Change Line 18:**
```php
// BEFORE
$uplineReferrers = $this->getUplineReferrers($purchaser, 5);

// AFTER
$uplineReferrers = $this->getUplineReferrers($purchaser, ReferralCommission::MAX_COMMISSION_LEVELS);
```

**Change Line 95:**
```php
// BEFORE
protected function getUplineReferrers(User $user, int $maxLevels = 5): array

// AFTER
protected function getUplineReferrers(User $user, int $maxLevels = 7): array
```

---

### Priority 2: Fix MLMCommissionCalculationService (HIGH)

**File:** `app/Domain/MLM/Services/MLMCommissionCalculationService.php`

**Replace Lines 16-22:**
```php
// BEFORE
public const COMMISSION_RATES = [
    1 => 12.0,
    2 => 6.0,
    3 => 4.0,
    4 => 2.0,
    5 => 1.0,
];

// AFTER
public const COMMISSION_RATES = [
    1 => 15.0, // Level 1 (Associate): 15%
    2 => 10.0, // Level 2 (Professional): 10%
    3 => 8.0,  // Level 3 (Senior): 8%
    4 => 6.0,  // Level 4 (Manager): 6%
    5 => 4.0,  // Level 5 (Director): 4%
    6 => 3.0,  // Level 6 (Executive): 3%
    7 => 2.0,  // Level 7 (Ambassador): 2%
];
```

**Change Line 31:**
```php
// BEFORE
$uplineReferrers = UserNetwork::getUplineReferrers($purchaser->id, 5);

// AFTER
$uplineReferrers = UserNetwork::getUplineReferrers($purchaser->id, 7);
```

---

### Priority 3: Update UserNetwork Model (MEDIUM)

**File:** `app/Models/UserNetwork.php`

Ensure `getUplineReferrers()` method supports 7 levels:

```php
public static function getUplineReferrers(int $userId, int $maxLevels = 7): array
```

---

### Priority 4: Update Tests (MEDIUM)

Update all commission-related tests to verify 7 levels:

**Files to update:**
- `tests/Unit/Services/MLMCommissionServiceTest.php`
- `tests/Unit/Domain/MLM/Services/MLMCommissionCalculationServiceTest.php`
- `tests/Unit/Domain/Reward/Services/ReferralMatrixServiceTest.php`

---

## Testing Plan

### 1. Unit Tests

Create test case with 7-level network:
```php
public function test_processes_seven_level_commissions(): void
{
    // Create 7-level network
    $level1 = User::factory()->create();
    $level2 = User::factory()->create(['referrer_id' => $level1->id]);
    $level3 = User::factory()->create(['referrer_id' => $level2->id]);
    $level4 = User::factory()->create(['referrer_id' => $level3->id]);
    $level5 = User::factory()->create(['referrer_id' => $level4->id]);
    $level6 = User::factory()->create(['referrer_id' => $level5->id]);
    $level7 = User::factory()->create(['referrer_id' => $level6->id]);
    $purchaser = User::factory()->create(['referrer_id' => $level7->id]);
    
    // Process commission
    $service = new MLMCommissionService();
    $commissions = $service->processMLMCommissions($purchaser, 1000);
    
    // Assert 7 commissions created
    $this->assertCount(7, $commissions);
    
    // Verify amounts
    $this->assertEquals(150, $commissions[0]->amount); // 15% of 1000
    $this->assertEquals(100, $commissions[1]->amount); // 10% of 1000
    $this->assertEquals(80, $commissions[2]->amount);  // 8% of 1000
    $this->assertEquals(60, $commissions[3]->amount);  // 6% of 1000
    $this->assertEquals(40, $commissions[4]->amount);  // 4% of 1000
    $this->assertEquals(30, $commissions[5]->amount);  // 3% of 1000
    $this->assertEquals(20, $commissions[6]->amount);  // 2% of 1000
}
```

### 2. Integration Tests

Test with real database:
1. Create 7-level network
2. Make purchase
3. Verify all 7 levels receive commissions
4. Check commission amounts are correct

### 3. Manual Testing

1. Create test accounts at each level
2. Make subscription purchase
3. Check each account's commission balance
4. Verify notifications sent to all 7 levels

---

## Migration Strategy

### Phase 1: Code Fix (Immediate)
1. Update MLMCommissionService
2. Update MLMCommissionCalculationService
3. Update UserNetwork model
4. Deploy to staging

### Phase 2: Testing (1-2 days)
1. Run unit tests
2. Run integration tests
3. Manual testing on staging
4. Verify no regressions

### Phase 3: Production Deployment (After testing)
1. Deploy to production
2. Monitor commission processing
3. Verify Level 6 and 7 members receive commissions

### Phase 4: Backfill (Optional)
If there are existing Level 6 and 7 members who missed commissions:
1. Identify affected transactions
2. Calculate missing commissions
3. Create manual commission records
4. Process payments

---

## Financial Impact

### Current Loss

If there are members at Level 6 and 7 who should be receiving commissions:

**Example Scenario:**
- 10 Level 6 members (Executive)
- 5 Level 7 members (Ambassador)
- Average monthly network purchases: K50,000 per member

**Monthly Lost Commissions:**
- Level 6: 10 members × K50,000 × 3% = K15,000
- Level 7: 5 members × K50,000 × 2% = K5,000
- **Total: K20,000/month**

### After Fix

All 7 levels will receive proper commissions as documented.

---

## Recommendations

### Immediate Actions

1. ✅ **Fix the code** - Update hardcoded 5 to 7
2. ✅ **Test thoroughly** - Ensure no regressions
3. ✅ **Deploy to production** - As soon as testing passes
4. ⚠️ **Communicate with members** - Inform about the fix

### Long-term Improvements

1. **Use constants** - Replace all hardcoded numbers with `ReferralCommission::MAX_COMMISSION_LEVELS`
2. **Centralize configuration** - Move commission rates to config file
3. **Add validation** - Ensure consistency across services
4. **Improve documentation** - Keep code and docs in sync
5. **Add monitoring** - Alert if commission levels don't match expected

---

## Conclusion

The MyGrowNet platform has a **7-level commission structure properly defined** in the model, but the **processing service only handles 5 levels**. This is a critical bug that prevents Level 6 (Executive) and Level 7 (Ambassador) members from receiving their entitled commissions.

**The fix is straightforward** - change hardcoded `5` to `7` in two key locations. However, thorough testing is required before deployment to ensure no unintended consequences.

**Estimated Fix Time:** 2-4 hours (including testing)  
**Risk Level:** Low (simple change, well-defined scope)  
**Business Impact:** High (affects top-tier members)

---

**Prepared by:** System Analysis  
**Date:** October 23, 2025  
**Status:** Ready for Implementation
