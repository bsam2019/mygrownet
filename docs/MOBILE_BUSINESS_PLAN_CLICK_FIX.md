# Mobile Business Plan - Action Click Fix

**Status:** ✅ FIXED  
**Date:** November 22, 2025

## Issue

When clicking "View & Edit Plan" (or any action) in the action sheet modal, the modal would disappear without performing the action.

## Root Cause

**Event Timing Issue:** The component was emitting `close` event before `open-plan` event:

```typescript
// BEFORE (Broken)
const openPlan = (plan: any) => {
  emit('close');        // ❌ Closes modal immediately
  emit('open-plan', plan);  // ⚠️ May not be received
};
```

When `emit('close')` was called first, it would:
1. Close the list modal immediately
2. The parent component might not receive the `open-plan` event
3. The business plan editor modal never opens
4. User sees nothing happen

## Solution

**Reverse Event Order + Use nextTick:**

```typescript
// AFTER (Fixed)
const openPlan = (plan: any) => {
  console.log('openPlan called, emitting open-plan event with:', plan);
  emit('open-plan', plan);  // ✅ Emit action first
  nextTick(() => {
    emit('close');          // ✅ Close after event is processed
  });
};
```

This ensures:
1. `open-plan` event is emitted first
2. Parent component receives and processes the event
3. `existingBusinessPlan` is set
4. Business plan modal opens
5. List modal closes after everything is set up

## Changes Made

### File: `resources/js/components/Mobile/Tools/BusinessPlanListModal.vue`

#### 1. Import nextTick
```typescript
import { ref, watch, nextTick } from 'vue';
```

#### 2. Fix openPlan Function
```typescript
const openPlan = (plan: any) => {
  console.log('openPlan called, emitting open-plan event with:', plan);
  // Emit open-plan first, then close
  emit('open-plan', plan);
  // Use nextTick to ensure the event is processed before closing
  nextTick(() => {
    emit('close');
  });
};
```

#### 3. Fix createNew Function
```typescript
const createNew = () => {
  emit('open-plan', null);
  nextTick(() => {
    emit('close');
  });
};
```

## How It Works Now

### User Flow
1. User taps plan in list
2. Action sheet slides up
3. User taps "View & Edit Plan"
4. `editPlan()` is called
5. `openPlan(plan)` is called
6. `open-plan` event is emitted with plan data
7. Parent receives event and calls `handleOpenPlan(plan)`
8. `existingBusinessPlan` is set to the plan
9. Business plan modal opens with the plan loaded
10. After Vue processes the event, list modal closes
11. User sees the business plan editor with their data

### Event Flow
```
User Click
    ↓
editPlan(plan)
    ↓
openPlan(plan)
    ↓
emit('open-plan', plan)  ← Parent receives this
    ↓
handleOpenPlan(plan)     ← Parent processes
    ↓
existingBusinessPlan = plan
    ↓
showBusinessPlanModal = true
    ↓
nextTick()               ← Wait for Vue to update
    ↓
emit('close')            ← Now close list modal
    ↓
showBusinessPlanListModal = false
```

## Why nextTick?

`nextTick()` is a Vue utility that waits for the next DOM update cycle. This ensures:

1. **Event Processing:** The `open-plan` event is fully processed by the parent
2. **State Updates:** All reactive state changes are applied
3. **Modal Transition:** The business plan modal starts opening
4. **Clean Close:** The list modal closes after everything is ready

Without `nextTick()`, the close event might fire before the parent finishes processing the open event, causing race conditions.

## Testing Checklist

- [x] Click "View & Edit Plan" - Opens editor with plan data
- [x] Click "Continue Editing" - Opens editor with plan data
- [x] Click "Create New" - Opens editor with empty form
- [x] Click "Export as PDF" - Downloads PDF
- [x] Click "Share Plan" - Opens share sheet
- [x] Click "Delete Plan" - Shows confirmation
- [x] Click "Cancel" - Closes action sheet
- [x] All actions work without modal disappearing

## Related Issues Fixed

This fix also resolves:
- ✅ "Create New" button not working
- ✅ Plans not loading when clicked from list
- ✅ Action sheet closing without action
- ✅ Race conditions between modals

## Technical Notes

### Vue Event System
Vue's event system is synchronous, but component updates are batched and asynchronous. When you emit multiple events in sequence:

```typescript
emit('event1');  // Queued
emit('event2');  // Queued
// Both processed in next tick
```

If `event1` causes a component to unmount, `event2` might not be received. Using `nextTick()` ensures proper sequencing.

### Alternative Solutions Considered

1. **Delay with setTimeout:** ❌ Unreliable, arbitrary timing
2. **Promise-based:** ❌ Overcomplicated for this use case
3. **Callback prop:** ❌ More code, less idiomatic
4. **nextTick:** ✅ Vue's built-in solution, perfect fit

## Summary

The mobile business plan action sheet now works correctly:
- ✅ All actions execute properly
- ✅ Modals transition smoothly
- ✅ No race conditions
- ✅ Clean event flow
- ✅ Better user experience

The fix was simple but critical: emit the action event before closing the modal, and use `nextTick()` to ensure proper timing.
