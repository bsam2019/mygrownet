# CRITICAL: Roles vs Professional Levels

## The Confusion

There was confusion between **System Roles** (access control) and **Professional Levels** (progression).

---

## ❌ WRONG Understanding

Thinking that the 7 professional levels are roles:
- ❌ Associate role
- ❌ Professional role
- ❌ Senior role
- ❌ Manager role
- ❌ Director role
- ❌ Executive role
- ❌ Ambassador role

**This is INCORRECT!**

---

## ✅ CORRECT Understanding

### System Roles (Access Control)

**Only 2 main roles**:

1. **admin** - Platform administrators
   - Full system access
   - Manage users, content, settings
   - View all reports

2. **member** - Regular platform users
   - Subscribe to packages
   - Access learning materials
   - Participate in network
   - Earn commissions

**Plus 1 legacy role**:
- **investor** - Legacy role (same as member, kept for backward compatibility)

---

### Professional Levels (Progression)

**Stored in**: `users.professional_level` field (integer 1-7)

**NOT roles, but progression levels**:

| Level | Name | Description |
|-------|------|-------------|
| 1 | Associate | New member, learning |
| 2 | Professional | Skilled member, applying |
| 3 | Senior | Experienced, team building |
| 4 | Manager | Team leader |
| 5 | Director | Strategic leader |
| 6 | Executive | Top performer |
| 7 | Ambassador | Brand representative |

---

## How They Work Together

### Example User

```php
$user = User::find(1);

// ROLE (Access Control)
$user->hasRole('member'); // true
// Determines: What can they ACCESS?

// PROFESSIONAL LEVEL (Progression)
$user->professional_level; // 4 (Manager)
// Determines: What's their PROGRESSION status?
```

### In Practice

```php
// Check if user can access member dashboard (ROLE)
if ($user->hasRole('member')) {
    // Show member dashboard
}

// Check commission rate based on level (PROGRESSION)
if ($user->professional_level >= 4) {
    // Apply Manager-level commission rate (6%)
}
```

---

## Database Structure

### Roles Table
```sql
roles
- id
- name (admin, member, investor)
- slug
- description
```

### Users Table
```sql
users
- id
- name
- email
- professional_level (1-7)  ← This is the progression level!
- ...
```

### Model Has Roles (Pivot)
```sql
model_has_roles
- role_id
- model_id (user_id)
- model_type (User)
```

---

## Why This Matters

### 1. Access Control (Roles)

**Question**: Can this user access the admin panel?

**Answer**: Check their **role**
```php
if ($user->hasRole('admin')) {
    // Yes, they can access admin panel
}
```

### 2. Commission Calculation (Levels)

**Question**: What commission rate should this user get?

**Answer**: Check their **professional level**
```php
$rate = match($user->professional_level) {
    1 => 15%, // Associate
    2 => 10%, // Professional
    3 => 8%,  // Senior
    4 => 6%,  // Manager
    5 => 4%,  // Director
    6 => 3%,  // Executive
    7 => 2%,  // Ambassador
};
```

### 3. Qualification Requirements (Levels)

**Question**: What MAP does this user need to qualify?

**Answer**: Check their **professional level**
```php
$requiredMap = match($user->professional_level) {
    1 => 100,  // Associate
    2 => 200,  // Professional
    3 => 300,  // Senior
    4 => 400,  // Manager
    5 => 500,  // Director
    6 => 600,  // Executive
    7 => 800,  // Ambassador
};
```

---

## Common Mistakes to Avoid

### ❌ Mistake 1: Creating Roles for Levels

```php
// WRONG!
Role::create(['name' => 'Associate']);
Role::create(['name' => 'Professional']);
Role::create(['name' => 'Senior']);
// etc.
```

**Why wrong**: These are progression levels, not access control roles.

### ❌ Mistake 2: Checking Role for Commission

```php
// WRONG!
if ($user->hasRole('Manager')) {
    $rate = 6%;
}
```

**Why wrong**: "Manager" is a level, not a role. Check `professional_level` instead.

### ❌ Mistake 3: Using Level for Access Control

```php
// WRONG!
if ($user->professional_level >= 4) {
    // Allow access to admin panel
}
```

**Why wrong**: Professional level is for progression, not access control. Use roles instead.

---

## Correct Implementation

### User Registration

```php
// Create new user
$user = User::create([
    'name' => $request->name,
    'email' => $request->email,
    'password' => Hash::make($request->password),
    'professional_level' => 1, // Start as Associate
]);

// Assign member role (access control)
$user->assignRole('member');
```

### Level Advancement

```php
// User advances from Associate (1) to Professional (2)
$user->professional_level = 2;
$user->save();

// Role stays the same (still 'member')
// Only progression level changes
```

### Access Control

```php
// Check if user can access feature
Route::middleware(['auth', 'role:member'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});
```

### Commission Calculation

```php
// Calculate commission based on professional level
$commissionRate = ReferralCommission::getCommissionRate($user->professional_level);
```

---

## Summary

### Roles (Access Control)
- **Purpose**: Determine what users can ACCESS
- **Count**: 2 main roles (admin, member)
- **Storage**: `roles` table + `model_has_roles` pivot
- **Check**: `$user->hasRole('member')`
- **Used for**: Permissions, middleware, guards

### Professional Levels (Progression)
- **Purpose**: Track user's PROGRESSION status
- **Count**: 7 levels (Associate → Ambassador)
- **Storage**: `users.professional_level` field (1-7)
- **Check**: `$user->professional_level`
- **Used for**: Commission rates, qualification requirements, benefits

---

## Updated RoleSeeder

The RoleSeeder now correctly creates only:
- ✅ `admin` role
- ✅ `member` role
- ✅ `investor` role (legacy, for backward compatibility)

**It does NOT create**:
- ❌ Associate role
- ❌ Professional role
- ❌ Senior role
- ❌ Manager role
- ❌ Director role
- ❌ Executive role
- ❌ Ambassador role

**Because these are progression levels, not roles!**

---

## Key Takeaway

**Roles** = What you can ACCESS (admin, member)  
**Levels** = Where you are in PROGRESSION (1-7)

**Never confuse the two!**

---

**Date**: October 18, 2025  
**Status**: ✅ Clarified and Corrected
