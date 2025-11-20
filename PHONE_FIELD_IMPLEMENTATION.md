# Phone Field Implementation

**Last Updated:** November 18, 2025
**Status:** Complete

## Overview

The phone field is properly implemented across registration and admin user management. This document explains how phone is stored, normalized, and displayed.

## Registration Flow

### 1. Registration Form (`resources/js/pages/auth/Register.vue`)
- Phone field is **optional** if email is provided
- At least one identifier (email OR phone) is required
- Placeholder: `0977123456 or +260977123456`
- Accepts Zambian phone numbers

### 2. Registration Controller (`app/Http/Controllers/Auth/RegisteredUserController.php`)

**Phone Normalization:**
```php
$normalizedPhone = $request->phone ? User::normalizePhone($request->phone) : null;
```

**Normalization Rules:**
- Removes all non-digit characters except `+`
- Converts `0977123456` → `+260977123456`
- Converts `260977123456` → `+260977123456`
- Keeps `+260977123456` as is

**Validation:**
- Phone is optional but must be unique if provided
- Either email or phone (or both) required
- Max 20 characters

**Storage:**
```php
$user = User::create([
    'name' => $request->name,
    'email' => $request->email,
    'phone' => $normalizedPhone,  // Stored in normalized format
    'password' => Hash::make($request->password),
    'referrer_id' => $actualReferrerId,
]);
```

## Admin User Management

### 1. User List Page (`app/Http/Controllers/Admin/UserManagementController.php::index()`)
- Phone field is selected and displayed in the users list
- Searchable by phone number
- Shows normalized phone format

### 2. User Profile/Show Page (`app/Http/Controllers/Admin/UserManagementController.php::show()`)

**Fixed Issue:**
The phone field now displays correctly by:
1. Using profile phone_number if it exists: `$user->profile->phone_number ?? $user->phone`
2. Falling back to main users table phone field
3. Creating default profile object if no profile exists

**Data Returned:**
```php
'profile' => $user->profile ? [
    'phone_number' => $user->profile->phone_number ?? $user->phone,
    'address' => $user->profile->address,
    // ... other fields
] : [
    'phone_number' => $user->phone,  // Fallback to main phone field
    'address' => null,
    // ... other fields
]
```

### 3. User Profile Form (`resources/js/pages/Admin/Users/Profile.vue`)
- Phone field is editable
- Stored in form as `phone_number`
- Submitted to `admin.users.update` route

### 4. User Update (`app/Http/Controllers/Admin/UserManagementController.php::update()`)
- Validates phone_number (optional, max 20 chars)
- Updates user profile with phone_number
- Falls back to creating profile if doesn't exist

## Database Schema

### Users Table
- `phone` column: VARCHAR(20), nullable, unique
- Stores normalized phone numbers
- Example: `+260977123456`

### User Profiles Table
- `phone_number` column: VARCHAR(20), nullable
- Optional secondary phone storage
- Used for profile-specific phone information

## Phone Normalization Examples

| Input | Output |
|-------|--------|
| `0977123456` | `+260977123456` |
| `977123456` | `977123456` (unchanged) |
| `260977123456` | `+260977123456` |
| `+260977123456` | `+260977123456` |
| `+27 977 123 456` | `+27977123456` |

## Current Status

✅ **Registration:** Phone field working correctly
✅ **Admin List:** Phone field displaying and searchable
✅ **Admin Profile:** Phone field now displays (FIXED)
✅ **Phone Normalization:** Working as expected
✅ **Unique Constraint:** Enforced on users.phone

## Files Modified

- `app/Http/Controllers/Admin/UserManagementController.php` - Fixed show() method to include phone field

## Testing

To verify phone field is working:

1. **Registration:** Register with phone number `0977123456`
2. **Admin List:** Search for the phone number
3. **Admin Profile:** Visit `/admin/users/{id}` and verify phone displays
4. **Edit:** Update phone number and save
5. **Verify:** Check that phone is stored in normalized format

## Notes

- Phone is normalized on registration to ensure consistency
- Both users.phone and user_profiles.phone_number can store phone data
- Admin form prioritizes profile phone_number but falls back to users.phone
- Phone field is optional during registration but must be unique if provided
