# Mobile Business Plan - Bottom Navigation Fix

**Issue:** Next and Previous buttons not showing  
**Cause:** Bottom navigation hidden behind mobile tab bar  
**Status:** ✅ FIXED  
**Date:** November 22, 2025

## Problem

The bottom navigation buttons (Previous/Next) were positioned at `bottom: 0` which placed them behind the mobile app's bottom tab bar (Home, Team, Wallet, Learn, Profile).

## Solution

### Changed Bottom Navigation Position
```vue
<!-- Before -->
<div class="fixed bottom-0 left-0 right-0 ...">

<!-- After -->
<div class="fixed left-0 right-0 ..." style="bottom: 60px;">
```

This positions the buttons 60px from the bottom, which is above the mobile tab bar.

### Increased Content Padding
```vue
<!-- Before -->
<div class="flex-1 overflow-y-auto p-4 pb-32">

<!-- After -->
<div class="flex-1 overflow-y-auto p-4" style="padding-bottom: 140px;">
```

This ensures content doesn't get hidden behind the navigation buttons.

## Changes Made

**File:** `resources/js/components/Mobile/Tools/BusinessPlanModal.vue`

1. **Bottom Navigation** (line ~593)
   - Changed from `bottom-0` to `style="bottom: 60px;"`
   - Removed `pb-safe` class (not needed)
   - Kept `z-50` for proper layering

2. **Content Area** (line ~33)
   - Changed from `pb-32` to `style="padding-bottom: 140px;"`
   - Ensures scrollable content clears both navigation and tab bar

## Visual Layout

```
┌─────────────────────────┐
│  Header (Back, Save)    │ ← Sticky top
├─────────────────────────┤
│  Progress Bar           │ ← Shows step X of 10
├─────────────────────────┤
│                         │
│  Content Area           │ ← Scrollable
│  (Forms, inputs)        │
│                         │
│  [140px padding]        │ ← Clears navigation
├─────────────────────────┤
│  ← Previous | Next →    │ ← 60px from bottom
├─────────────────────────┤
│  [60px space]           │ ← Mobile tab bar area
│  Home Team Wallet...    │ ← System tab bar
└─────────────────────────┘
```

## Testing

- [x] Buttons visible on all steps
- [x] Previous button shows on steps 2-10
- [x] Next button shows on all steps
- [x] Content scrolls without hiding behind buttons
- [x] Buttons stay fixed while scrolling
- [x] Works on iOS and Android
- [x] No overlap with tab bar

## Result

✅ Bottom navigation buttons now clearly visible above the mobile tab bar  
✅ Users can navigate between all 10 steps  
✅ Content properly scrolls without being cut off  
✅ Professional mobile UX maintained
