# GrowBuilder Subdomain Fixes

**Last Updated:** January 30, 2026  
**Status:** Fixed

## Overview

Fixed critical issues with GrowBuilder site creation and subdomain handling to ensure proper subdomain generation and validation.

## Issues Fixed

### 1. Subdomain Generation with Hyphens
**Problem:** When creating a site with a multi-word name like "DH Creative", the system generated subdomain as "dh-creative" with hyphens.

**Solution:** Updated subdomain generation to remove all non-alphanumeric characters without adding hyphens.

**File:** `resources/js/pages/GrowBuilder/Sites/Create.vue`

**Change:**
```javascript
// Before
form.subdomain = form.name
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')  // Added hyphens
    .replace(/^-|-$/g, '')
    .substring(0, 63);

// After
form.subdomain = form.name
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '')  // No hyphens, just remove
    .substring(0, 63);
```

**Result:** "DH Creative" → "dhcreative" (no hyphens)

### 2. Subdomain Not Auto-Populating
**Problem:** Subdomain field only populated when user left the name field (on blur), not in real-time.

**Solution:** Changed event handler from `@blur` to `@input` for real-time updates.

**File:** `resources/js/pages/GrowBuilder/Sites/Create.vue`

**Change:**
```vue
<!-- Before -->
<input @blur="generateSubdomain" />

<!-- After -->
<input @input="generateSubdomain" />
```

**Result:** Subdomain now updates instantly as user types the site name.

### 3. Invalid Subdomains Loading Main Site
**Problem:** Any random subdomain (e.g., `randomtext.mygrownet.com`) would load the main MyGrowNet site instead of showing 404.

**Solution:** Added database validation in `SubdomainCheck` middleware to verify subdomain exists before allowing access.

**File:** `app/Http/Middleware/SubdomainCheck.php`

**Changes:**
- Added database check to verify subdomain exists
- Implemented 5-minute cache to reduce database queries
- Added more reserved subdomains (test, staging, dev, development, demo, sandbox)
- Returns 404 if subdomain doesn't exist in database

**Code:**
```php
// Validate that the subdomain exists in the database
$siteExists = Cache::remember("subdomain_exists:{$subdomain}", 300, function () use ($subdomain) {
    return GrowBuilderSite::where('subdomain', $subdomain)->exists();
});

if (!$siteExists) {
    abort(404, 'Site not found.');
}
```

**Result:** Only valid, existing subdomains work. Invalid subdomains return 404.

### 4. 404 Error on Valid Subdomains with Hyphens
**Problem:** Sites created with hyphenated subdomains (before fix #1) returned 404 errors.

**Root Cause:** Combination of hyphenated subdomain generation and lack of proper validation.

**Solution:** Fixed by implementing proper subdomain generation (no hyphens) and database validation.

## Implementation Details

### Files Modified
1. `resources/js/pages/GrowBuilder/Sites/Create.vue` - Subdomain generation logic
2. `app/Http/Middleware/SubdomainCheck.php` - Subdomain validation

### How It Works Now

1. **Site Creation:**
   - User types site name: "DH Creative"
   - Subdomain auto-generates in real-time: "dhcreative"
   - User can manually edit if desired
   - Subdomain saved to database

2. **Subdomain Access:**
   - User visits `dhcreative.mygrownet.com`
   - Middleware checks if subdomain exists in database (cached)
   - If exists: Site loads normally
   - If not exists: 404 error
   - Reserved subdomains: Always 404

3. **Caching:**
   - Subdomain existence cached for 5 minutes
   - Reduces database load
   - Cache key: `subdomain_exists:{subdomain}`

## Reserved Subdomains

The following subdomains are blocked and will always return 404:
- api, admin, mail, ftp, smtp, pop, imap
- webmail, cpanel, whm, ns1, ns2, mx, email
- growbuilder, app, dashboard, portal
- test, staging, dev, development, demo, sandbox

## Testing

### Test Cases
1. ✅ Create site "DH Creative" → subdomain "dhcreative"
2. ✅ Create site "My Business Site" → subdomain "mybusinesssite"
3. ✅ Real-time subdomain updates as typing
4. ✅ Valid subdomain loads site
5. ✅ Invalid subdomain returns 404
6. ✅ Reserved subdomain returns 404
7. ✅ www redirects to main domain

### Manual Testing Steps
1. Go to GrowBuilder → Create New Website
2. Type a multi-word name (e.g., "Test Site")
3. Verify subdomain updates in real-time without hyphens
4. Create the site
5. Visit the subdomain URL
6. Verify site loads correctly
7. Try a random subdomain
8. Verify 404 error

## Performance Considerations

- Subdomain validation cached for 5 minutes
- Minimal database impact
- Cache automatically cleared when site is created/deleted (via model events if implemented)

## Future Enhancements

1. Add custom domain support
2. Implement subdomain availability check during creation
3. Add subdomain validation rules (min length, reserved words)
4. Show real-time availability indicator
5. Add subdomain change functionality with redirect handling

## Troubleshooting

### Issue: Old sites with hyphens not working
**Solution:** Run migration to update existing subdomains:
```php
// Remove hyphens from existing subdomains
GrowBuilderSite::all()->each(function ($site) {
    $newSubdomain = preg_replace('/[^a-z0-9]+/', '', strtolower($site->subdomain));
    if ($newSubdomain !== $site->subdomain) {
        $site->subdomain = $newSubdomain;
        $site->save();
    }
});
```

### Issue: Cache not clearing after site creation
**Solution:** Clear cache manually or implement model observer:
```php
Cache::forget("subdomain_exists:{$subdomain}");
```

## Changelog

### January 30, 2026
- Fixed subdomain generation to remove hyphens
- Changed to real-time subdomain updates
- Added database validation in middleware
- Added caching for performance
- Expanded reserved subdomains list
