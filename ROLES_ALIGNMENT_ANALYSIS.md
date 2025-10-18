# Roles Alignment Analysis

## Overview

This document analyzes the current role system against the MyGrowNet platform documentation to ensure alignment.

---

## Current Roles in System

Based on `database/seeders/RoleSeeder.php`:

1. **admin** - System Administrator
2. **manager** - Team Manager  
3. **investor** - Investor

---

## Documentation Says

According to the MyGrowNet platform documentation, the system is **subscription-based**, not investment-based. The platform has:

### Professional Levels (NOT Roles)
These are progression levels for members:
1. Associate
2. Professional
3. Senior
4. Manager
5. Director
6. Executive
7. Ambassador

### Member Types
- **Members** - Regular platform users who subscribe
- **Administrators** - Platform managers

---

## Issues Found

### ❌ Issue 1: "Investor" Role Misalignment

**Problem**: System has an "investor" role, but MyGrowNet is a **subscription platform**, not an investment platform.

**Documentation Says**:
- Members **subscribe** for products and services
- Members do NOT invest in pooled funds
- Platform is a Private Limited Company, not an investment scheme

**Current Code**:
```php
$investorRole = Role::firstOrCreate(
    ['name' => 'investor'],
    ['slug' => 'investor', 'description' => 'Investor']
);
```

**Should Be**: "member" role instead of "investor"

---

### ❌ Issue 2: "Manager" Role Confusion

**Problem**: "Manager" is both a role AND a professional level (Level 4).

**Documentation Says**:
- Manager is a **professional level** (Level 4 in progression)
- Not a system role

**Current Code**:
```php
$managerRole = Role::firstOrCreate(
    ['name' => 'manager'],
    ['slug' => 'manager', 'description' => 'Team Manager']
);
```

**Confusion**: Is this a system role or professional level?

---

### ✅ Issue 3: "Admin" Role - Correct

**Status**: Aligned with documentation

The admin role is appropriate for platform administrators.

---

## Recommended Role Structure

### System Roles (Access Control)

1. **admin** - Platform administrators
   - Full system access
   - Manage users, content, settings
   - View all reports and analytics

2. **member** - Regular platform users (replaces "investor")
   - Subscribe to packages
   - Access learning materials
   - Participate in network
   - Earn commissions and bonuses

3. **moderator** (optional) - Content moderators
   - Manage forum posts
   - Review user content
   - Handle support tickets

### Professional Levels (Progression, NOT Roles)

These should be stored in `users.professional_level` field:

1. Associate (Level 1)
2. Professional (Level 2)
3. Senior (Level 3)
4. Manager (Level 4)
5. Director (Level 5)
6. Executive (Level 6)
7. Ambassador (Level 7)

**Note**: Professional levels are for progression and earnings, NOT for access control.

---

## Permissions Alignment

### Current Permissions

```php
// Dashboard permissions
'view_admin_dashboard',
'view_manager_dashboard',
'view_investor_dashboard',  // ❌ Should be 'view_member_dashboard'

// User management
'manage_users',
'view_team_users',
'approve_withdrawals',
'approve_tier_upgrades',

// Investment management  // ❌ Should be 'subscription management'
'manage_investments',
'view_team_investments',
'create_investments',
```

### Recommended Permissions

```php
// Dashboard permissions
'view_admin_dashboard',
'view_member_dashboard',

// User management
'manage_users',
'manage_members',
'view_team_members',
'approve_withdrawals',

// Subscription management (replaces investment management)
'manage_subscriptions',
'view_team_subscriptions',
'create_subscription',
'cancel_subscription',

// Content management
'manage_courses',
'manage_learning_packs',
'manage_community_projects',

// Report permissions
'view_admin_reports',
'view_team_reports',
'view_personal_reports',

// Matrix and referral permissions
'manage_matrix',
'view_team_matrix',
'view_personal_matrix',

// Financial permissions
'manage_profit_distribution',
'manage_commissions',
'view_team_commissions',
'view_personal_commissions',

// Points management
'manage_points',
'award_points',
'view_team_points',
'view_personal_points',
```

---

## User Model Alignment

### Current Fields

```php
// In User model fillable
'current_investment_tier_id',  // ❌ Investment terminology
'total_investment_amount',     // ❌ Investment terminology
```

### Should Be

```php
// Subscription-based terminology
'current_package_id',          // ✅ Package subscription
'total_subscription_amount',   // ✅ Subscription terminology
'professional_level',          // ✅ Already exists (1-7)
```

---

## Fixes Required

### 1. Update RoleSeeder

**File**: `database/seeders/RoleSeeder.php`

**Changes**:
```php
// Replace 'investor' with 'member'
$memberRole = Role::firstOrCreate(
    ['name' => 'member'],
    ['slug' => 'member', 'description' => 'Platform Member']
);

// Remove or rename 'manager' role to avoid confusion
// Option 1: Remove it (use professional_level instead)
// Option 2: Rename to 'team_leader' or 'moderator'
```

### 2. Update Permissions

**File**: `database/seeders/RoleSeeder.php`

**Changes**:
```php
$permissions = [
    // Dashboard permissions
    'view_admin_dashboard',
    'view_member_dashboard',  // Changed from 'view_investor_dashboard'
    
    // User management
    'manage_users',
    'manage_members',
    'view_team_members',
    'approve_withdrawals',
    
    // Subscription management (replaces investment management)
    'manage_subscriptions',
    'view_team_subscriptions',
    'create_subscription',
    
    // Report permissions
    'view_admin_reports',
    'view_team_reports',
    'view_personal_reports',
    
    // Matrix and referral permissions
    'manage_matrix',
    'view_team_matrix',
    'view_personal_matrix',
    
    // Financial permissions
    'manage_profit_distribution',
    'manage_commissions',
    'view_team_commissions',
    'view_personal_commissions',
    
    // Points management
    'manage_points',
    'award_points',
    'view_team_points',
    'view_personal_points',
];
```

### 3. Update User Registration

**File**: `app/Http/Controllers/Auth/RegisterController.php` (or wherever registration happens)

**Changes**:
```php
// Assign 'member' role instead of 'investor'
$user->assignRole('member');

// Set initial professional level
$user->professional_level = 1; // Associate
```

### 4. Update Middleware/Guards

**Check**: Any middleware or guards checking for 'investor' role

**Change**: Update to check for 'member' role

### 5. Update Views/Frontend

**Check**: Any UI references to "investor" or "investment"

**Change**: Update to "member" or "subscription" terminology

---

## Migration Strategy

### Phase 1: Add New Roles (Non-Breaking)

1. Create 'member' role
2. Assign 'member' role to all existing users with 'investor' role
3. Keep 'investor' role temporarily for backward compatibility

### Phase 2: Update Code (Gradual)

1. Update new features to use 'member' role
2. Update permissions to subscription-based terminology
3. Update UI to use correct terminology

### Phase 3: Deprecate Old Roles (Breaking)

1. Remove 'investor' role from all users
2. Delete 'investor' role
3. Update all references in codebase

---

## Testing Checklist

After making changes:

- [ ] Users can register and get 'member' role
- [ ] Members can access member dashboard
- [ ] Members can create subscriptions
- [ ] Members can view their matrix
- [ ] Members can view their commissions
- [ ] Admins can manage members
- [ ] Admins can manage subscriptions
- [ ] Professional levels work correctly (1-7)
- [ ] No references to 'investor' in UI
- [ ] No references to 'investment' in member-facing features

---

## Summary

### Current State
- ❌ Uses "investor" role (investment terminology)
- ❌ Has "manager" role (conflicts with professional level)
- ❌ Investment-focused permissions
- ✅ Has admin role

### Desired State
- ✅ Uses "member" role (subscription terminology)
- ✅ No role conflicts with professional levels
- ✅ Subscription-focused permissions
- ✅ Clear separation: Roles for access, Levels for progression

### Priority
**HIGH** - Core business model alignment

---

**Analysis Date**: October 18, 2025  
**Status**: Requires Updates  
**Impact**: Medium (affects terminology and user experience)
