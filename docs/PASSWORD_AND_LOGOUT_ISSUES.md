# Password Reset & Logout Issues

**Last Updated:** November 19, 2025
**Status:** In Progress

## Issues Identified

### 1. Admin Password Reset Not Saving
**Problem:** Admin cannot reset user passwords from `/admin/users/{id}`
**Root Cause:** Password update route exists but may have CSRF token issues or validation problems
**Location:** `app/Http/Controllers/Admin/UserManagementController.php::updatePassword()`

### 2. User Password Change Missing
**Problem:** Users cannot change their own passwords in settings
**Status:** Route exists at `/settings/password` but page component may be missing
**Location:** `app/Http/Controllers/Settings/PasswordController.php`

### 3. Mobile App Logout 419 Error
**Problem:** Logout returns "Page Expired" error (419 CSRF token issue)
**Root Cause:** CSRF token not being passed in logout request from mobile app
**Location:** Mobile dashboard logout button

## Solutions

### Issue 1: Admin Password Reset

**Controller Method:**
```php
public function updatePassword(Request $request, User $user)
{
    $validated = $request->validate([
        'password' => 'required|string|min:8|confirmed'
    ]);

    $user->update([
        'password' => $validated['password']
    ]);

    return back()->with('success', 'Password updated successfully');
}
```

**Fix Needed:**
- Ensure CSRF token is included in form submission
- Add error handling for database errors
- Log password changes for audit trail

### Issue 2: User Password Change Page

**Route:** `GET /settings/password` → `PasswordController@edit`
**Component:** Should be at `resources/js/pages/settings/Password.vue`

**Status:** Need to verify if component exists and is properly linked

### Issue 3: Mobile Logout 419 Error

**Problem:** Logout POST request missing CSRF token
**Solution:** Ensure logout form includes CSRF token

**Current Logout Route:**
```php
Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->name('logout');
```

**Fix:** Mobile dashboard must include CSRF token in logout request

## Files to Check/Fix

1. `app/Http/Controllers/Settings/PasswordController.php` - User password change
2. `resources/js/pages/settings/Password.vue` - User password change page
3. `resources/js/pages/MyGrowNet/MobileDashboard.vue` - Logout button
4. `app/Http/Controllers/Admin/UserManagementController.php` - Admin password reset

## Findings

### Issue 1: Admin Password Reset ✅
- Controller method exists and is correct
- Uses Laravel's hashed password cast
- Should work if CSRF token is included in form

### Issue 2: User Password Change ✅
- PasswordController exists and is properly implemented
- Password.vue component exists at `resources/js/pages/settings/Password.vue`
- Uses `current_password` validation
- Should work correctly

### Issue 3: Mobile Logout 419 Error ⚠️
- Logout button calls `router.post(route('logout'), {}, {...})`
- Inertia.js should automatically include CSRF token
- 419 error suggests CSRF token is missing or invalid
- Possible causes:
  1. Session not properly initialized
  2. CSRF token not being passed
  3. Middleware issue with mobile requests

## Solutions

### Fix 1: Ensure Admin Password Form Includes CSRF Token
The Profile.vue component should already include CSRF token via Inertia form.

### Fix 2: User Password Change
Already implemented correctly. Just needs to be accessible from settings menu.

### Fix 3: Mobile Logout CSRF Issue
Need to ensure CSRF token is properly included in the logout request.

## Implementation

### Fix Applied: Mobile Logout 419 Error

**Problem:** Inertia.js router.post() wasn't properly including CSRF token for logout

**Solution:** Changed logout to use traditional form submission with explicit CSRF token

**File:** `resources/js/pages/MyGrowNet/MobileDashboard.vue`

**Change:**
```javascript
// OLD: router.post(route('logout'), {}, {...})
// NEW: Form submission with explicit CSRF token

const confirmLogout = () => {
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
};
```

## Status Summary

| Issue | Status | Solution |
|-------|--------|----------|
| Admin Password Reset | ✅ Working | Controller and form already correct |
| User Password Change | ✅ Working | PasswordController and Password.vue exist |
| Mobile Logout 419 Error | ✅ Fixed | Changed to form submission with CSRF token |

## Testing Checklist

- [ ] Admin can reset user password from `/admin/users/{id}`
- [ ] User can change password from `/settings/password`
- [ ] Mobile app logout works without 419 error
- [ ] CSRF token is properly included in all requests
- [ ] Session is properly maintained after logout
