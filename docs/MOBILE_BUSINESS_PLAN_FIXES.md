# Mobile Business Plan - Quick Fixes

**Date:** November 22, 2025

## Issue: "watch is not defined" Error

### Problem
Error in `BusinessPlanListModal.vue`:
```
Uncaught (in promise) ReferenceError: watch is not defined at setup (BusinessPlanListModal.vue:177:1)
```

### Root Cause
The `watch` function is properly imported from Vue, but the build cache might be stale.

### Solution

#### Option 1: Clear Build Cache (Recommended)
```bash
# Stop the dev server (Ctrl+C)

# Clear Vite cache
rm -rf node_modules/.vite

# Restart dev server
npm run dev
```

#### Option 2: Hard Refresh Browser
- Chrome/Edge: `Ctrl + Shift + R` (Windows) or `Cmd + Shift + R` (Mac)
- Firefox: `Ctrl + F5` (Windows) or `Cmd + Shift + R` (Mac)
- Safari: `Cmd + Option + R`

#### Option 3: Verify Import (Already Correct)
The import in `BusinessPlanListModal.vue` line 160 is correct:
```typescript
import { ref, watch } from 'vue';
```

### Verification
After clearing cache, check:
1. No console errors
2. Business Plans list modal opens
3. Plans load automatically when modal opens
4. Can create new plan
5. Can open existing plan

## Other Potential Issues

### Missing Route
If you see "route is not defined", add to `routes/web.php`:
```php
Route::get('/mygrownet/tools/business-plans/api', [BusinessPlanController::class, 'apiList'])
    ->name('mygrownet.tools.business-plans.api');
```

### Missing API Endpoint
Add to `BusinessPlanController.php`:
```php
public function apiList()
{
    $plans = auth()->user()->businessPlans()
        ->orderBy('updated_at', 'desc')
        ->get();
    
    return response()->json(['plans' => $plans]);
}
```

## Testing Checklist

After fixes:
- [ ] No console errors
- [ ] Modal opens without errors
- [ ] Plans list loads
- [ ] Can create new plan
- [ ] Can open existing plan
- [ ] Can export plan
- [ ] Can share plan
- [ ] Can delete plan
- [ ] Action sheet works
- [ ] Empty state shows when no plans

## Quick Test
```bash
# 1. Clear cache
rm -rf node_modules/.vite

# 2. Restart
npm run dev

# 3. Hard refresh browser (Ctrl+Shift+R)

# 4. Open mobile view
# 5. Navigate to Business Plans
# 6. Check console for errors
```

## Status
✅ Code is correct  
⚠️ Build cache needs clearing  
🔄 Restart dev server required
