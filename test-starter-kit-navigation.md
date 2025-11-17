# Test Starter Kit Navigation - Step by Step

## Prerequisites

1. **User must have starter kit:**
```sql
-- Run this SQL query
UPDATE users 
SET has_starter_kit = true, 
    starter_kit_tier = 'basic',
    starter_kit_purchased_at = NOW()
WHERE email = 'your-test-email@example.com';
```

2. **Clear all caches:**
```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

3. **Rebuild frontend (if needed):**
```bash
npm run build
# Or for dev:
npm run dev
```

## Test Steps

### Test 1: Verify Section Appears
1. Log in as user with starter kit
2. Go to `/mobile-dashboard`
3. Scroll down past "Quick Stats"
4. **Expected:** See "My Learning Resources" section with 4 cards
5. **If not visible:** User doesn't have `has_starter_kit = true`

### Test 2: Test Bottom Navigation First
1. Look at bottom of screen
2. Click "Learn" tab button (should be 2nd or 3rd button)
3. **Expected:** Screen switches to Learn tab
4. **If works:** Tab switching mechanism is working

### Test 3: Test Home Tab Cards
1. Click "Home" tab in bottom navigation
2. Scroll to "My Learning Resources"
3. Click "E-Books" card
4. **Expected:** Instantly switch to Learn tab
5. **If doesn't work:** Check browser console for errors

### Test 4: Test "View All" Link
1. On Home tab, in "My Learning Resources" section
2. Click "View All" link (top right)
3. **Expected:** Switch to Learn tab
4. **If doesn't work:** Same issue as cards

## What Should Happen

### Successful Navigation:
```
1. User on Home tab
2. Clicks "E-Books" card
3. Screen smoothly transitions to Learn tab
4. Learn tab shows:
   - "Learning Center" header (purple gradient)
   - 4 category cards (E-Books, Videos, Calculator, Templates)
   - Featured content section
   - "Coming Soon" notice
```

### Visual Confirmation:
- Bottom navigation "Learn" button should be highlighted/active
- URL stays the same (no page reload)
- Smooth transition (no flash)

## If Not Working

### Check 1: Browser Console
Press F12, look for errors like:
- `activeTab is not defined`
- `Cannot read property 'value'`
- Any red errors

### Check 2: Vue DevTools
If you have Vue DevTools:
1. Open DevTools
2. Go to Vue tab
3. Find MobileDashboard component
4. Check `activeTab` value
5. Try changing it manually to 'learn'

### Check 3: Network Tab
1. Open F12 → Network tab
2. Click a card
3. **Should NOT see any network requests**
4. If you see requests, something is wrong

### Check 4: Element Inspection
1. Right-click "E-Books" card
2. Inspect element
3. Should see: `<button @click="activeTab = 'learn'"...`
4. If you see `<a href=...` or `<Link...`, code wasn't updated

## Common Problems & Solutions

### Problem 1: "My Learning Resources" Not Visible
**Cause:** User doesn't have starter kit

**Solution:**
```sql
UPDATE users SET has_starter_kit = true WHERE id = YOUR_ID;
```

### Problem 2: Buttons Don't Respond
**Cause:** JavaScript not compiled

**Solution:**
```bash
npm run build
# Hard refresh browser (Ctrl+Shift+R)
```

### Problem 3: Page Reloads Instead of Tab Switch
**Cause:** Using `<Link>` instead of `<button>`

**Solution:** Code should have `<button @click="activeTab = 'learn'">` not `<Link>`

### Problem 4: Console Error "activeTab is not defined"
**Cause:** Vue reactivity issue

**Solution:** Check that `const activeTab = ref('home')` exists in script section

## Manual Test in Console

Open browser console and run:

```javascript
// Test 1: Check if Vue app is loaded
console.log('Vue app:', document.querySelector('#app').__vue_app__);

// Test 2: Try to change tab manually
// (This won't work directly, but will show if Vue is loaded)
console.log('Trying to switch tab...');

// Test 3: Check props
console.log('User has starter kit:', 
  window.__INERTIA_DATA__?.props?.user?.has_starter_kit
);

// Test 4: Check if Learn tab content exists
console.log('Learn tab element:', 
  document.querySelector('[v-show="activeTab === \'learn\'"]')
);
```

## Expected Console Output

When clicking a card, you should see:
```
(nothing - no errors, no logs)
```

Silent is good! It means it's working.

## Design Consistency Verification

### Home Tab "My Learning Resources":
- 4 cards in 2x2 grid
- Each card has icon, title, subtitle
- Gradient backgrounds (blue, purple, green, orange)
- Rounded corners, shadows

### Learn Tab Categories:
- Same 4 cards
- Same icons, titles, subtitles
- Same gradient backgrounds
- Same styling

**They should be visually identical!**

## Success Criteria

✅ "My Learning Resources" visible on Home tab  
✅ 4 cards displayed correctly  
✅ Clicking any card switches to Learn tab  
✅ No page reload  
✅ Learn tab shows matching content  
✅ Bottom navigation "Learn" button highlighted  
✅ Can switch back to Home tab  
✅ No console errors  

## If All Tests Pass But Still Issues

Provide this info:
1. Browser and version
2. Screenshot of Home tab
3. Screenshot of Learn tab
4. Console errors (if any)
5. User ID being tested
6. Value of `has_starter_kit` from database

---

**Most likely issue:** User doesn't have `has_starter_kit = true` in database.

**Quick fix:** Run the SQL UPDATE query at the top of this document.
