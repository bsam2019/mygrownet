# PWA Offline Page - When It Shows (Visual Guide)

**Last Updated:** November 17, 2025

## Quick Answer

**Offline page shows when:**
- ❌ No internet connection
- ❌ Requested content not in cache
- ❌ Service worker can't fulfill request

**Offline page DOESN'T show when:**
- ✅ Content is cached
- ✅ Service worker can serve from cache

---

## Visual Flowcharts

### Scenario 1: First Visit (No Service Worker Yet)

```
┌─────────────────────────────────────────────────────────────┐
│ User visits MyGrowNet for the first time                    │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
            ┌────────────────┐
            │ Has Internet?  │
            └────┬───────┬───┘
                 │       │
         YES ────┘       └──── NO
         │                     │
         ▼                     ▼
┌────────────────┐    ┌──────────────────────┐
│ Page Loads     │    │ Browser Default      │
│ Service Worker │    │ "No Internet" Page   │
│ Installs       │    │ (NOT our offline.html)│
│ Assets Cached  │    └──────────────────────┘
└────────────────┘
         │
         ▼
┌────────────────┐
│ ✅ Ready for   │
│ Offline Use    │
└────────────────┘
```

### Scenario 2: Return Visit - Cached Page

```
┌─────────────────────────────────────────────────────────────┐
│ User opens dashboard (previously visited)                    │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
            ┌────────────────┐
            │ Service Worker │
            │ Intercepts     │
            └────────┬───────┘
                     │
                     ▼
            ┌────────────────┐
            │ Check Cache    │
            └────┬───────────┘
                 │
         ┌───────┴───────┐
         │               │
    FOUND│               │NOT FOUND
         │               │
         ▼               ▼
┌────────────────┐  ┌────────────────┐
│ Serve from     │  │ Try Network    │
│ Cache          │  └────┬───────────┘
│                │       │
│ ✅ Dashboard   │  ┌────┴────┐
│ Loads!         │  │         │
│ (No offline    │  │    SUCCESS│FAIL
│  page)         │  │         │
└────────────────┘  │         │
                    ▼         ▼
           ┌────────────┐  ┌──────────────┐
           │ Fresh Data │  │ ❌ OFFLINE   │
           │ Loaded     │  │ PAGE SHOWS   │
           └────────────┘  └──────────────┘
```

### Scenario 3: Navigate to New Page (Offline)

```
┌─────────────────────────────────────────────────────────────┐
│ User clicks link to page never visited before               │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
            ┌────────────────┐
            │ Service Worker │
            │ Intercepts     │
            └────────┬───────┘
                     │
                     ▼
            ┌────────────────┐
            │ Check Cache    │
            └────┬───────────┘
                 │
                 ▼
         ┌───────────────┐
         │ NOT IN CACHE  │
         │ (Never visited)│
         └───────┬───────┘
                 │
                 ▼
         ┌───────────────┐
         │ Try Network   │
         └───────┬───────┘
                 │
                 ▼
         ┌───────────────┐
         │ OFFLINE!      │
         │ Network Fails │
         └───────┬───────┘
                 │
                 ▼
         ┌───────────────┐
         │ ❌ OFFLINE    │
         │ PAGE SHOWS    │
         └───────────────┘
```

### Scenario 4: API Call (Transaction)

```
┌─────────────────────────────────────────────────────────────┐
│ User tries to withdraw money                                 │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
            ┌────────────────┐
            │ POST Request   │
            │ /withdraw      │
            └────────┬───────┘
                     │
                     ▼
            ┌────────────────┐
            │ Service Worker │
            │ Intercepts     │
            └────────┬───────┘
                     │
                     ▼
         ┌───────────────────┐
         │ POST Requests     │
         │ NOT CACHED        │
         │ (Security)        │
         └───────┬───────────┘
                 │
                 ▼
         ┌───────────────┐
         │ Try Network   │
         └───────┬───────┘
                 │
         ┌───────┴────────┐
         │                │
    ONLINE│                │OFFLINE
         │                │
         ▼                ▼
┌────────────────┐  ┌──────────────┐
│ Transaction    │  │ ❌ ERROR     │
│ Processed      │  │ "You're      │
│ ✅ Success     │  │  Offline"    │
└────────────────┘  └──────────────┘
                           │
                           ▼
                    ┌──────────────┐
                    │ Could show   │
                    │ offline.html │
                    │ OR error msg │
                    └──────────────┘
```

---

## Detailed Scenarios

### ✅ Scenario A: Works Offline (No Offline Page)

**User Action:** Opens dashboard after visiting it yesterday

**Flow:**
1. User opens app (offline)
2. Service worker checks cache
3. Dashboard HTML found in cache ✅
4. Dashboard CSS/JS found in cache ✅
5. Dashboard data found in cache ✅
6. **Result:** Dashboard loads normally
7. **Offline page:** NOT SHOWN

**What User Sees:**
```
┌─────────────────────────────────┐
│  MyGrowNet Dashboard            │
│  ────────────────────────────   │
│  Balance: K1,234.56 (cached)    │
│  Team: 45 members (cached)      │
│  Earnings: K567.89 (cached)     │
│                                 │
│  ⚠️ You're viewing cached data  │
└─────────────────────────────────┘
```

### ❌ Scenario B: Shows Offline Page

**User Action:** Clicks "Admin Panel" link (never visited before)

**Flow:**
1. User clicks link (offline)
2. Service worker intercepts request
3. Checks cache for /admin
4. NOT FOUND ❌
5. Tries network
6. Network fails (offline) ❌
7. **Result:** Can't fulfill request
8. **Offline page:** SHOWN

**What User Sees:**
```
┌─────────────────────────────────┐
│         MyGrowNet Logo          │
│                                 │
│            📡                   │
│      You're Offline             │
│                                 │
│  🔴 No Internet Connection      │
│                                 │
│  It looks like you've lost...   │
│                                 │
│     [Try Again Button]          │
└─────────────────────────────────┘
```

### ✅ Scenario C: Partial Offline (Mixed)

**User Action:** Browses cached pages, then tries to withdraw

**Flow:**
1. Dashboard loads from cache ✅
2. Team page loads from cache ✅
3. Wallet page loads from cache ✅
4. User clicks "Withdraw"
5. Service worker intercepts POST
6. POST requests never cached
7. Tries network
8. Network fails ❌
9. **Result:** Error message (not offline page)

**What User Sees:**
```
┌─────────────────────────────────┐
│  Wallet Page (cached)           │
│  Balance: K1,234.56             │
│                                 │
│  [Withdraw Button] ← clicked    │
│                                 │
│  ⚠️ Error Toast:                │
│  "You're offline. Please        │
│   connect to internet to        │
│   make transactions."           │
└─────────────────────────────────┘
```

---

## Cache Strategy by Content Type

### Static Assets (Cache-First)

```
Request → Cache → Found? → ✅ Serve (FAST!)
                → Not Found? → Network → Cache → Serve
                                      → Fail → ❌ Offline Page
```

**Examples:**
- CSS files: `/build/assets/app.css`
- JavaScript: `/build/assets/app.js`
- Images: `/logo.png`, `/images/*`
- Fonts: `/fonts/*`

**Offline Behavior:**
- ✅ Loads from cache
- ❌ Offline page only if never cached

### Dynamic Content (Network-First)

```
Request → Network → Success? → ✅ Cache → Serve
                  → Fail? → Cache → Found? → ✅ Serve (stale)
                                  → Not Found? → ❌ Offline Page
```

**Examples:**
- Dashboard: `/mobile-dashboard`
- Team page: `/mygrownet/team`
- Profile: `/mygrownet/profile`
- API calls: `/api/*`

**Offline Behavior:**
- ✅ Serves cached version if available
- ❌ Offline page if not cached

### Critical Operations (Network-Only)

```
Request → Network → Success? → ✅ Process
                  → Fail? → ❌ Error (not offline page)
```

**Examples:**
- Login: `/login`
- Logout: `/logout`
- Transactions: `/withdraw`, `/deposit`
- Updates: `/profile/update`

**Offline Behavior:**
- ❌ Shows error message
- ❌ May show offline page as fallback

---

## When Offline Page Shows - Summary Table

| Situation | Cached? | Online? | Result |
|-----------|---------|---------|--------|
| First visit | ❌ No | ✅ Yes | Loads normally, caches content |
| First visit | ❌ No | ❌ No | Browser default "No internet" |
| Return visit | ✅ Yes | ✅ Yes | Fresh content, updates cache |
| Return visit | ✅ Yes | ❌ No | ✅ Cached content loads |
| New page | ❌ No | ✅ Yes | Loads normally, caches |
| New page | ❌ No | ❌ No | ❌ **OFFLINE PAGE SHOWS** |
| Transaction | N/A | ✅ Yes | Processes normally |
| Transaction | N/A | ❌ No | Error message (not offline page) |
| Static asset | ✅ Yes | ❌ No | ✅ Loads from cache |
| Static asset | ❌ No | ❌ No | ❌ **OFFLINE PAGE SHOWS** |

---

## User Experience Examples

### Example 1: Good Offline Experience

**Setup:**
- User visited dashboard, team, wallet yesterday
- All pages cached
- User goes offline today

**Experience:**
```
✅ Opens app → Dashboard loads (cached)
✅ Clicks Team → Team page loads (cached)
✅ Clicks Wallet → Wallet loads (cached)
✅ Views transactions → History loads (cached)
❌ Clicks Withdraw → Error: "You're offline"
✅ Clicks Profile → Profile loads (cached)
```

**Offline Page Shown:** Never (all visited pages cached)

### Example 2: Poor Offline Experience

**Setup:**
- User visited only dashboard
- Only dashboard cached
- User goes offline

**Experience:**
```
✅ Opens app → Dashboard loads (cached)
❌ Clicks Team → OFFLINE PAGE (not cached)
❌ Clicks Wallet → OFFLINE PAGE (not cached)
❌ Clicks Profile → OFFLINE PAGE (not cached)
```

**Offline Page Shown:** 3 times (unvisited pages)

**Solution:** Visit all pages while online first!

### Example 3: Mixed Experience

**Setup:**
- User visited dashboard and team
- Goes offline
- Tries various actions

**Experience:**
```
✅ Opens app → Dashboard loads (cached)
✅ Clicks Team → Team loads (cached)
❌ Clicks Wallet → OFFLINE PAGE (not cached)
✅ Back to Dashboard → Loads (cached)
❌ Tries to withdraw → Error message
✅ Views team members → Loads (cached)
❌ Clicks Admin → OFFLINE PAGE (not cached)
```

**Offline Page Shown:** 2 times (unvisited pages)

---

## How to Avoid Offline Page

### For Users:

**Best Practices:**
1. ✅ Visit all important pages while online
2. ✅ Let pages fully load before going offline
3. ✅ Install the app (better caching)
4. ✅ Update when prompted

**What Gets Cached:**
- Pages you visit
- Images you see
- Data you load
- Assets you use

**What Doesn't Get Cached:**
- Pages you never visit
- Future data
- Real-time updates
- Transaction submissions

### For Developers:

**Improve Offline Experience:**
1. Add important routes to `ASSETS_TO_CACHE`
2. Pre-cache critical pages on install
3. Show offline indicators in UI
4. Disable actions that require internet
5. Queue offline actions for later sync

**Example - Pre-cache Important Pages:**
```javascript
// In public/sw.js
const ASSETS_TO_CACHE = [
  '/',
  '/mobile-dashboard',
  '/mygrownet/team',      // Add this
  '/mygrownet/wallet',    // Add this
  '/mygrownet/profile',   // Add this
  '/manifest.json',
  '/logo.png',
];
```

---

## Testing Offline Scenarios

### Test 1: Cached Content
```bash
1. Visit dashboard while online
2. Enable airplane mode
3. Refresh page
Expected: ✅ Dashboard loads from cache
```

### Test 2: Uncached Content
```bash
1. Visit dashboard while online
2. Enable airplane mode
3. Click link to page never visited
Expected: ❌ Offline page shows
```

### Test 3: Mixed Scenario
```bash
1. Visit dashboard and team while online
2. Enable airplane mode
3. Navigate between dashboard and team
Expected: ✅ Both load from cache
4. Click wallet (never visited)
Expected: ❌ Offline page shows
```

### Test 4: Transaction
```bash
1. Visit wallet while online
2. Enable airplane mode
3. Try to withdraw
Expected: ❌ Error message (not offline page)
```

---

## Summary

### Offline Page Shows When:
1. ❌ Requested page not in cache
2. ❌ No internet connection
3. ❌ Service worker can't fulfill request

### Offline Page Doesn't Show When:
1. ✅ Content is cached
2. ✅ Service worker can serve from cache
3. ✅ User visited page before while online

### Best User Experience:
- Visit all pages while online first
- Install the app for better caching
- Update when prompted
- Check connection before critical actions

### Key Takeaway:
**The offline page is a last resort when the service worker can't fulfill a request from cache or network. Most of the time, if you've visited pages before, you won't see it!**
