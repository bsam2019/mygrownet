# Logout, Password Reset, and CSRF Token Issues - Complete Fix

**Last Updated:** November 19, 2025
**Status:** In Progress

## Issues Identified

### 1. 419 Page Expired Error on Logout
**Problem:** Users getting "419 Page Expired" when trying to logout
**Root Cause:** CSRF token expiration or mismatch between frontend and backend

### 2. Password Update Not Saving
**Problem:** Admin password reset for users not saving to database
**Root Cause:** Likely CSRF token validation failure or form submission issue

### 3. User Password Update Functionality Broken
**Problem:** Users unable to update their own passwords
**Root Cause:** Same CSRF token or form submission issues

## Current Implementation Status

### CSRF Token Refresh Middleware
**File:** `app/Http/Middleware/RefreshCsrfToken.php`

✅ **Already Implemented:**
- Regenerates CSRF token on every request for authenticated users
- Adds X-CSRF-Token header to all responses
- Adds X-CSRF-Token-Meta header for Vue to pick up
- Designed specifically for PWA where sessions persist longer

### Logout Controller
**File:** `app/Http/Controllers/Auth/AuthenticatedSessionController.php`

✅ **Correct Implementation:**
```php
public function destroy(Request $request): RedirectResponse
{
    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
}
```

### Password Update Controller
**File:** `app/Http/Controllers/Admin/UserManagementController.php`

✅ **Logging Added:**
- Logs password update request received
- Logs validation passed
- Logs successful password update
- Includes user ID and name for tracking

## Solutions

### Solution 1: Fix CSRF Token Meta Tag

**Issue:** The CSRF token meta tag might not exist or be properly set

**Current Implementation in MobileDashboard.vue:**
```javascript
csrfInput.value = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
```

**Problem:** If meta tag doesn't exist, it defaults to empty string, causing 419 error

**Fix:** Update the logout function to use Inertia's built-in CSRF handling:

```javascript
import { router, usePage } from '@inertiajs/vue3'

const confirmLogout = () => {
  // Use Inertia's router which automatically handles CSRF
  router.post(route('logout'))
}
```

### Solution 2: Ensure CSRF Token Meta Tag Exists

**File:** `resources/views/app.blade.php` or main layout

Add this to the `<head>` section:
```html
<meta name="csrf-token" content="{{ csrf_token() }}">
```

### Solution 3: Use Inertia Link Component for Logout

**Current (problematic):**
```javascript
const logout = () => router.post('/logout');
```

**Better:**
```javascript
import { Link } from '@inertiajs/vue3'

// In template:
<Link method="post" :href="route('logout')" as="button">
  Logout
</Link>
```

### Solution 4: Fix Password Update Form Submission

**File:** `resources/js/pages/Admin/Users/Profile.vue`

The password form is using `router.post()` which should work, but ensure:

```javascript
const updatePassword = () => {
  // Inertia automatically includes CSRF token
  router.post(route('admin.users.update-password', props.user.id), passwordForm.value, {
    preserveScroll: true,
    onSuccess: () => {
      closePasswordModal()
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: 'Password updated successfully',
        timer: 2000,
        showConfirmButton: false
      })
    },
    onError: (errors) => {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: Object.values(errors).flat().join(', '),
      })
    }
  })
}
```

### Solution 3: Verify Session Configuration

**File:** `config/session.php`

Check these settings:
```php
'lifetime' => env('SESSION_LIFETIME', 120), // 120 minutes
'expire_on_close' => false,
'encrypt' => false,
'http_only' => true,
'same_site' => 'lax', // Important for CSRF
```

### Solution 4: Clear Stale Sessions

Run on production:
```bash
php artisan session:table
php artisan migrate
php artisan cache:clear
php artisan config:clear
```

## Fixes Applied

### ✅ Fix 1: Logout CSRF Issue
**File:** `resources/js/pages/MyGrowNet/MobileDashboard.vue`

**Changed from:**
```javascript
// Manual form creation with CSRF token lookup
const form = document.createElement('form');
form.method = 'POST';
form.action = route('logout');
const csrfInput = document.createElement('input');
csrfInput.type = 'hidden';
csrfInput.name = '_token';
csrfInput.value = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
form.appendChild(csrfInput);
document.body.appendChild(form);
form.submit();
```

**Changed to:**
```javascript
// Use Inertia's router which automatically handles CSRF tokens
router.post(route('logout'));
```

**Why:** Inertia.js automatically includes CSRF tokens in all POST requests, eliminating the need for manual token handling and preventing 419 errors.

## Testing Checklist

- [ ] Test logout from mobile dashboard
- [ ] Test logout from desktop dashboard
- [ ] Test admin password reset for user
- [ ] Test user password change
- [ ] Verify no 419 errors in browser console
- [ ] Check network tab shows successful logout
- [ ] Verify user is redirected to home page after logout

## Debugging Steps (if issues persist)

### Check CSRF Token in Meta Tag:
```bash
# In browser console
console.log(document.querySelector('meta[name="csrf-token"]')?.content)
```

### Check Session Configuration:
```bash
ssh sammy@138.197.187.134 "cd /var/www/mygrownet.com && cat config/session.php | grep -A 5 'lifetime\|same_site'"
```

### Check Laravel Logs:
```bash
ssh sammy@138.197.187.134 "tail -50 /var/www/mygrownet.com/storage/logs/laravel.log | grep -i 'csrf\|419\|token'"
```

## Files Modified

1. ✅ `resources/js/pages/MyGrowNet/MobileDashboard.vue` - Fixed logout to use Inertia router
2. ✅ `app/Http/Controllers/Admin/UserManagementController.php` - Already has logging for password updates
3. ✅ `app/Http/Middleware/RefreshCsrfToken.php` - Already refreshing tokens on every request

## Files to Review

1. `resources/js/pages/Admin/Users/Profile.vue` - Password form (uses Inertia router, should work)
2. `resources/js/components/AppSidebarHeader.vue` - Logout button (uses Inertia router, should work)
3. `resources/js/components/UserMenuContent.vue` - Logout link (uses Inertia Link component, should work)
4. `app/Http/Controllers/Auth/AuthenticatedSessionController.php` - Logout controller (correct implementation)

## Expected Results

After applying these fixes:
- ✅ Users can logout without 419 errors
- ✅ Admin can reset user passwords
- ✅ Users can update their own passwords
- ✅ CSRF tokens are automatically handled by Inertia
- ✅ No manual token management needed
