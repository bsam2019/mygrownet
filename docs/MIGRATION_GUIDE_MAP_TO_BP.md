# Migration Guide: MAP to BP Terminology

**Version:** 1.0  
**Date:** October 20, 2025  
**Status:** Backward Compatible Enhancement

---

## Overview

This guide explains how the new **Bonus Points (BP)** terminology integrates with the existing **Monthly Activity Points (MAP)** system. **No breaking changes** - this is purely a terminology enhancement for better clarity.

---

## Key Point: Backward Compatibility

### ✅ What Stays the Same

**Database Schema:**
- ✅ `user_points` table unchanged
- ✅ `point_transactions` table unchanged
- ✅ `monthly_activity_status` table unchanged
- ✅ All column names remain the same
- ✅ All relationships intact

**Code Implementation:**
- ✅ `UserPoints` model unchanged
- ✅ `PointTransaction` model unchanged
- ✅ All existing methods work as before
- ✅ All calculations remain identical

**Functionality:**
- ✅ Point earning logic unchanged
- ✅ Monthly qualification logic unchanged
- ✅ Multipliers and streaks unchanged
- ✅ Performance tiers unchanged

---

## What Changed: Documentation Only

### Terminology Enhancement

| Old Term (Code) | New Term (Docs) | Reason |
|-----------------|-----------------|--------|
| MAP (Monthly Activity Points) | BP (Bonus Points) | More descriptive of purpose |
| `monthly_points` | Still `monthly_points` in code | Database unchanged |
| `map_amount` | Still `map_amount` in code | Database unchanged |
| `map_earned` | Still `map_earned` in code | Database unchanged |

### Why the Change?

**Old:** "Monthly Activity Points (MAP)"
- Generic name
- Doesn't clearly indicate purpose
- Could be confused with other activity metrics

**New:** "Bonus Points (BP)"
- Clearly indicates purpose: calculating bonuses
- Better member understanding
- More intuitive for new users

---

## Implementation Strategy

### Phase 1: Documentation (COMPLETE ✅)
- Updated all documentation to use BP terminology
- Added comprehensive product ecosystem docs
- Created member-friendly guides
- **No code changes required**

### Phase 2: UI/UX Updates (OPTIONAL)
When updating the frontend, you can gradually introduce BP terminology:

```vue
<!-- Old (still works) -->
<div>Monthly Activity Points: {{ user.points.monthly_points }}</div>

<!-- New (enhanced clarity) -->
<div>Bonus Points (BP): {{ user.points.monthly_points }}</div>
<small>Earn BP to qualify for monthly bonuses</small>
```

### Phase 3: Code Refactoring (FUTURE)
If desired, you can add aliases without breaking existing code:

```php
// In UserPoints model - ADD, don't replace
public function getBonusPointsAttribute(): int
{
    return $this->monthly_points; // Alias
}

// Both work:
$user->points->monthly_points; // Old way (still works)
$user->points->bonus_points;   // New way (alias)
```

---

## Database Compatibility

### Current Schema (Unchanged)

```sql
-- user_points table
CREATE TABLE user_points (
    id BIGINT PRIMARY KEY,
    user_id BIGINT,
    lifetime_points INT DEFAULT 0,      -- LP (unchanged)
    monthly_points INT DEFAULT 0,       -- MAP/BP (unchanged)
    last_month_points INT DEFAULT 0,    -- (unchanged)
    -- ... other fields
);

-- point_transactions table
CREATE TABLE point_transactions (
    id BIGINT PRIMARY KEY,
    user_id BIGINT,
    lp_amount INT DEFAULT 0,            -- LP (unchanged)
    map_amount INT DEFAULT 0,           -- MAP/BP (unchanged)
    source VARCHAR(50),
    -- ... other fields
);
```

### Future Enhancement (Optional)

If you want to add BP columns for clarity:

```sql
-- OPTIONAL: Add alias columns (not required)
ALTER TABLE user_points 
    ADD COLUMN bonus_points INT GENERATED ALWAYS AS (monthly_points) VIRTUAL;

ALTER TABLE point_transactions
    ADD COLUMN bp_amount INT GENERATED ALWAYS AS (map_amount) VIRTUAL;
```

This creates virtual columns that mirror the existing data without duplication.

---

## Code Examples: Backward Compatible

### Existing Code (Still Works)

```php
// Award points - existing method
$user->points->increment('monthly_points', 150);

// Check qualification - existing method
$qualified = $user->points->meetsMonthlyQualification();

// Get required MAP - existing method
$required = $user->points->getRequiredMapForLevel();

// Create transaction - existing method
PointTransaction::create([
    'user_id' => $user->id,
    'lp_amount' => 150,
    'map_amount' => 150,  // Still called map_amount
    'source' => 'referral',
]);
```

### Enhanced Code (Optional Additions)

```php
// Add helper methods without breaking existing code
class UserPoints extends Model
{
    // Existing methods unchanged...
    
    // NEW: Add BP-friendly aliases
    public function getBonusPoints(): int
    {
        return $this->monthly_points;
    }
    
    public function awardBonusPoints(int $amount): void
    {
        $this->increment('monthly_points', $amount);
    }
    
    public function getMonthlyBonusShare(float $bonusPool, int $totalBP): float
    {
        if ($totalBP === 0) return 0;
        return ($this->monthly_points / $totalBP) * $bonusPool;
    }
}

// Both old and new ways work:
$user->points->monthly_points;        // Old way
$user->points->getBonusPoints();      // New way
```

---

## Documentation Alignment

### In Code Comments

```php
/**
 * Monthly Activity Points (MAP) / Bonus Points (BP)
 * 
 * These points reset monthly and determine the member's
 * share of the monthly bonus pool.
 * 
 * Formula: Member Bonus = (Member BP / Total BP) × Bonus Pool
 * 
 * @var int
 */
protected int $monthly_points;
```

### In API Responses

```json
{
  "points": {
    "lifetime_points": 2500,
    "monthly_points": 350,
    "bonus_points": 350,  // Optional: Add as alias
    "monthly_points_label": "Bonus Points (BP)",
    "required_for_qualification": 300,
    "qualified": true
  }
}
```

---

## Member-Facing Changes

### Dashboard Display

**Before:**
```
Monthly Activity Points: 350
Required: 300
Status: Qualified ✓
```

**After (Enhanced):**
```
Bonus Points (BP): 350
Required: 300 BP
Status: Qualified for monthly bonus ✓

Your BP determines your share of this month's bonus pool.
```

### Notifications

**Before:**
```
You earned 150 MAP for referring a new member!
```

**After (Enhanced):**
```
You earned 150 BP for referring a new member!
This increases your monthly bonus share.
```

---

## Testing Strategy

### Verify Backward Compatibility

```php
// Test existing functionality
public function test_existing_map_functionality_unchanged()
{
    $user = User::factory()->create();
    $user->points()->create(['monthly_points' => 350]);
    
    // Old methods still work
    $this->assertEquals(350, $user->points->monthly_points);
    $this->assertTrue($user->points->meetsMonthlyQualification());
    
    // Increment still works
    $user->points->increment('monthly_points', 50);
    $this->assertEquals(400, $user->points->monthly_points);
}

// Test new terminology (if added)
public function test_new_bp_terminology_works()
{
    $user = User::factory()->create();
    $user->points()->create(['monthly_points' => 350]);
    
    // New methods work (if implemented)
    $this->assertEquals(350, $user->points->getBonusPoints());
    
    // Both ways access same data
    $this->assertEquals(
        $user->points->monthly_points,
        $user->points->getBonusPoints()
    );
}
```

---

## Migration Checklist

### Immediate (No Code Changes)
- [x] Update documentation to use BP terminology
- [x] Create member education materials
- [x] Update marketing materials
- [ ] Train support team on new terminology

### Short-Term (UI Updates)
- [ ] Update dashboard labels (MAP → BP)
- [ ] Update notification messages
- [ ] Update help text and tooltips
- [ ] Update onboarding flow

### Medium-Term (Optional Enhancements)
- [ ] Add BP alias methods to models
- [ ] Update API response labels
- [ ] Add virtual columns (optional)
- [ ] Update admin panel terminology

### Long-Term (Future Consideration)
- [ ] Consider renaming columns in major version
- [ ] Full codebase terminology alignment
- [ ] Database schema optimization

---

## FAQ

### Q: Do I need to change my database?
**A:** No! The database schema remains unchanged. BP is just a clearer name for the same `monthly_points` field.

### Q: Will existing code break?
**A:** No! All existing code continues to work exactly as before. This is purely a documentation enhancement.

### Q: Do I need to migrate data?
**A:** No! There's no data migration needed. The data structure is identical.

### Q: When should I update the UI?
**A:** At your convenience. You can update labels gradually without any rush. The functionality works the same either way.

### Q: Can I use both MAP and BP terminology?
**A:** Yes! During transition, you can use both. For example: "Bonus Points (BP) / Monthly Activity Points"

### Q: What about existing members who know MAP?
**A:** Communicate the change: "We're renaming Monthly Activity Points to Bonus Points for clarity. Everything works the same, just a clearer name!"

---

## Communication Template

### For Members

**Subject:** Introducing Bonus Points (BP) - Clearer Name, Same System

Hi [Member Name],

We're making MyGrowNet easier to understand! We're renaming **Monthly Activity Points (MAP)** to **Bonus Points (BP)**.

**What's changing:**
- The name: MAP → BP (Bonus Points)
- The purpose is clearer: BP shows how your monthly bonus is calculated

**What's NOT changing:**
- How you earn points (same activities)
- How bonuses are calculated (same formula)
- Your current points (all preserved)
- Any functionality (everything works the same)

**Why the change:**
"Bonus Points" better describes what these points do - they determine your share of the monthly bonus pool!

Your current BP: [X]
Required for bonus: [Y]

Questions? Contact support@mygrownet.com

---

## Conclusion

The MAP → BP terminology change is a **documentation enhancement only**. It provides:

✅ **Better clarity** for members  
✅ **No breaking changes** to code  
✅ **Backward compatibility** maintained  
✅ **Gradual adoption** possible  
✅ **Improved understanding** of the system  

You can adopt the new terminology at your own pace without any technical pressure or risk.

---

**Next Steps:**
1. Review this guide
2. Update UI labels when convenient
3. Communicate change to members
4. Update training materials
5. Consider adding alias methods (optional)

---

*For questions, refer to:*
- *UNIFIED_PRODUCTS_SERVICES.md - Complete BP system*
- *POINTS_SYSTEM_SPECIFICATION.md - Technical details*
- *LP_BP_SYSTEM_SUMMARY.md - Member guide*

---

**Prepared by:** Kiro AI Assistant  
**Date:** October 20, 2025  
**Status:** Backward Compatible
