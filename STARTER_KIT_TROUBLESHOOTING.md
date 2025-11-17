# Starter Kit Links Not Working - Troubleshooting

## Issue
Links in "My Learning Resources" on Home tab are not switching to Learn tab.

## Quick Fixes to Try

### 1. Clear Browser Cache
```bash
# Clear Laravel caches
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Then in browser:
# - Hard refresh (Ctrl+Shift+R or Cmd+Shift+R)
# - Or clear browser cache
```

### 2. Check Console for Errors
Open browser console (F12) and look for:
- JavaScript errors
- Vue warnings
- Network errors

### 3. Verify activeTab is Reactive
The buttons use `@click="activeTab = 'learn'"` which should work.

Check in browser console:
```javascript
// Should show current tab
console.log(document.querySelector('[data-active-tab]'))
```

### 4. Test Bottom Navigation
Click the "Learn" tab button in the bottom navigation. If that works but the cards don't, there's a specific issue with the card buttons.

## Debugging Steps

### Step 1: Add Console Logs
Temporarily add this to test:

```vue
<button
  @click="() => { console.log('Button clicked!'); activeTab = 'learn'; }"
  class="flex flex-col items-center p-4..."
>
```

### Step 2: Check if v-if is Hiding Content
The section has `v-if="user?.has_starter_kit"`. Verify:
1. User is logged in
2. User has `has_starter_kit = true` in database

Check in browser console:
```javascript
// Should show user object
console.log(window.__INERTIA_DATA__)
```

### Step 3: Verify Learn Tab Content Exists
Click bottom navigation "Learn" button. If Learn tab shows content, the tab switching works.

## Common Issues

### Issue 1: User Doesn't Have Starter Kit
**Symptom:** "My Learning Resources" section not visible on Home tab

**Fix:** 
```sql
-- Check user's starter kit status
SELECT id, name, has_starter_kit, starter_kit_tier 
FROM users 
WHERE id = YOUR_USER_ID;

-- If false, update:
UPDATE users 
SET has_starter_kit = true, starter_kit_tier = 'basic' 
WHERE id = YOUR_USER_ID;
```

### Issue 2: JavaScript Not Compiled
**Symptom:** Buttons don't respond to clicks

**Fix:**
```bash
# Rebuild frontend
npm run build

# Or for development
npm run dev
```

### Issue 3: Vue Reactivity Issue
**Symptom:** activeTab changes but UI doesn't update

**Fix:** Check if `activeTab` is properly defined as `ref`:
```typescript
const activeTab = ref('home'); // Should be ref, not just a variable
```

## Expected Behavior

### When Working Correctly:

1. **Home Tab:**
   - Shows "My Learning Resources" section (if has starter kit)
   - Shows 4 cards: E-Books, Videos, Calculator, Templates
   - Each card is clickable

2. **Click Any Card:**
   - Tab instantly switches to "Learn"
   - No page reload
   - Learn tab shows content

3. **Learn Tab:**
   - Shows "Learning Center" header
   - Shows 4 category cards (matching Home tab design)
   - Shows featured content section
   - Shows "Coming Soon" notice

## Design Consistency Check

### Home Tab Cards:
```
┌─────────────┐  ┌─────────────┐
│ 📄 E-Books  │  │ 🎥 Videos   │
│ Digital lib │  │ Training    │
└─────────────┘  └─────────────┘
```

### Learn Tab Cards (Should Match):
```
┌─────────────┐  ┌─────────────┐
│ 📄 E-Books  │  │ 🎥 Videos   │
│ Digital lib │  │ Training    │
└─────────────┘  └─────────────┘
```

**They should look identical!**

## Test Script

Run this in browser console to test:

```javascript
// Test 1: Check if activeTab exists
console.log('Active tab:', document.querySelector('[data-active-tab]'));

// Test 2: Manually change tab
// (This should work if Vue is loaded)
window.activeTab = 'learn';

// Test 3: Check user data
console.log('User data:', window.__INERTIA_DATA__?.props?.user);

// Test 4: Check if has starter kit
console.log('Has starter kit:', window.__INERTIA_DATA__?.props?.user?.has_starter_kit);
```

## If Still Not Working

### Option 1: Use Bottom Navigation
As a workaround, users can click the "Learn" tab in the bottom navigation to access content.

### Option 2: Direct Links
Change buttons to use Inertia links:

```vue
<Link
  :href="route('mygrownet.content.index')"
  class="flex flex-col items-center p-4..."
>
  E-Books
</Link>
```

This will navigate to a separate page (not SPA style, but will work).

### Option 3: Check BottomNavigation Component
The bottom navigation might have a different tab name. Check:

```vue
<!-- In BottomNavigation.vue -->
<!-- Make sure it emits 'learn' not 'learning' or something else -->
<button @click="$emit('navigate', 'learn')">
  Learn
</button>
```

## Quick Test

1. Open mobile dashboard
2. Open browser console (F12)
3. Type: `activeTab.value = 'learn'`
4. Press Enter
5. If Learn tab appears, buttons should work too

## Contact Info

If none of these work, provide:
1. Browser console errors (screenshot)
2. Network tab errors (screenshot)
3. User ID being tested
4. Database value of `has_starter_kit` for that user

---

**Most likely cause:** Browser cache or user doesn't have `has_starter_kit = true`
