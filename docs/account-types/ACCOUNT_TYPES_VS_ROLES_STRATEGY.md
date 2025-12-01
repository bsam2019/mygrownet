# Account Types vs Spatie Roles - Strategy

**Last Updated:** December 1, 2025

## The Problem

We have two overlapping concepts:
1. **Account Types** - Business classification (MEMBER, CLIENT, BUSINESS, INVESTOR, EMPLOYEE)
2. **Spatie Roles** - Permission system (Member, Admin, Super Admin, etc.)

This creates confusion and potential conflicts.

---

## The Solution: Clear Separation

### Account Types = "WHO you are" (Business Identity)
- Determines which **portal** you access
- Determines which **modules** are available
- Determines your **billing model**
- Stored in: `users.account_type` (can be JSON array for multiple types)

### Spatie Roles = "WHAT you can do" (Permissions)
- Determines which **actions** you can perform
- Controls **authorization** (view, create, edit, delete)
- Independent of account type
- Stored in: `model_has_roles` table (Spatie)

---

## Recommended Role Structure

### Core Roles (Spatie)

#### 1. **User** (Default)
- Basic authenticated user
- Can view own profile
- Can access purchased features
- **Assigned to:** Everyone by default

#### 2. **Member** (MLM Participant)
- All User permissions +
- Can view MLM dashboard
- Can manage referrals
- Can view commissions
- **Assigned to:** Users with MEMBER account type

#### 3. **Investor** (Venture Builder)
- All User permissions +
- Can view investor portal
- Can access investment documents
- Can vote on projects
- **Assigned to:** Users with INVESTOR account type

#### 4. **Business Owner** (SME)
- All User permissions +
- Can access business tools
- Can manage employees
- Can view business reports
- **Assigned to:** Users with BUSINESS account type

#### 5. **Support Agent** (Employee)
- Can view support tickets
- Can respond to live chat
- Can view customer profiles (limited)
- **Assigned to:** EMPLOYEE account type with support role

#### 6. **Manager** (Employee)
- All Support Agent permissions +
- Can assign tasks
- Can view team performance
- Can approve certain actions
- **Assigned to:** EMPLOYEE account type with manager role

#### 7. **Admin** (Employee)
- All Manager permissions +
- Can manage users
- Can configure system settings
- Can view all data
- **Assigned to:** EMPLOYEE account type with admin role

#### 8. **Super Admin** (Employee)
- All Admin permissions +
- Can manage roles and permissions
- Can access sensitive data
- Can perform destructive actions
- **Assigned to:** EMPLOYEE account type with super admin role

---

## Account Type + Role Matrix

| Account Type | Default Role(s) | Can Also Have |
|--------------|----------------|---------------|
| MEMBER | User, Member | - |
| CLIENT | User | - |
| BUSINESS | User, Business Owner | - |
| INVESTOR | User, Investor | Member (if also MLM) |
| EMPLOYEE | User, Support Agent | Manager, Admin, Super Admin |

---

## Implementation Strategy

### 1. Account Type Determines Portal Access

```php
// Middleware: CheckAccountType
public function handle($request, Closure $next, ...$types)
{
    $user = $request->user();
    
    foreach ($types as $type) {
        if ($user->hasAccountType(AccountType::from($type))) {
            return $next($request);
        }
    }
    
    abort(403, 'Access denied to this portal');
}

// Usage in routes
Route::middleware(['auth', 'account.type:member'])->group(function () {
    // MLM portal routes
});
```

### 2. Spatie Roles Determine Permissions

```php
// Use Spatie's built-in middleware
Route::middleware(['auth', 'role:admin'])->group(function () {
    // Admin actions
});

Route::middleware(['auth', 'permission:edit-users'])->group(function () {
    // Specific permission
});
```

### 3. Combined Checks When Needed

```php
// Check both account type AND role
Route::middleware(['auth', 'account.type:employee', 'role:admin'])->group(function () {
    // Employee portal admin section
});
```

---

## Auto-Assignment Logic

### On User Creation

```php
protected static function boot()
{
    parent::boot();
    
    static::created(function ($user) {
        // ALWAYS assign base User role
        $user->assignRole('User');
        
        // Assign additional roles based on account type
        if ($user->hasAccountType(AccountType::MEMBER)) {
            $user->assignRole('Member');
        }
        
        if ($user->hasAccountType(AccountType::INVESTOR)) {
            $user->assignRole('Investor');
        }
        
        if ($user->hasAccountType(AccountType::BUSINESS)) {
            $user->assignRole('Business Owner');
        }
        
        if ($user->hasAccountType(AccountType::EMPLOYEE)) {
            // Don't auto-assign employee roles
            // Admin must manually assign Support Agent, Manager, Admin, etc.
        }
    });
}
```

### On Account Type Change

```php
// When user upgrades from CLIENT to MEMBER
public function upgradeToMember()
{
    $this->addAccountType(AccountType::MEMBER);
    $this->assignRole('Member');
}

// When user becomes investor
public function becomeInvestor()
{
    $this->addAccountType(AccountType::INVESTOR);
    $this->assignRole('Investor');
}
```

---

## Permission Structure

### Define Permissions (Not Roles)

```php
// In PermissionSeeder
$permissions = [
    // MLM Permissions
    'view-mlm-dashboard',
    'view-network',
    'view-commissions',
    'manage-referrals',
    
    // Investor Permissions
    'view-investor-portal',
    'view-investments',
    'vote-on-projects',
    'download-documents',
    
    // Business Permissions
    'view-business-tools',
    'manage-employees',
    'view-business-reports',
    'manage-inventory',
    
    // Admin Permissions
    'manage-users',
    'manage-settings',
    'view-all-data',
    'approve-withdrawals',
    
    // Support Permissions
    'view-tickets',
    'respond-to-chat',
    'view-customer-profiles',
];

// Assign permissions to roles
$memberRole->givePermissionTo([
    'view-mlm-dashboard',
    'view-network',
    'view-commissions',
    'manage-referrals',
]);

$investorRole->givePermissionTo([
    'view-investor-portal',
    'view-investments',
    'vote-on-projects',
    'download-documents',
]);
```

---

## Blade/Vue Usage

### Check Account Type (Portal Access)

```php
// Blade
@if($user->hasAccountType(AccountType::MEMBER))
    <a href="/mygrownet/dashboard">MLM Dashboard</a>
@endif

// Vue (via Inertia props)
<template>
    <div v-if="$page.props.auth.user.account_types.includes('member')">
        <Link href="/mygrownet/dashboard">MLM Dashboard</Link>
    </div>
</template>
```

### Check Role/Permission (Authorization)

```php
// Blade
@can('manage-users')
    <button>Edit User</button>
@endcan

@role('admin')
    <a href="/admin">Admin Panel</a>
@endrole

// Vue (via Inertia props)
<template>
    <button v-if="$page.props.auth.user.permissions.includes('manage-users')">
        Edit User
    </button>
</template>
```

---

## Migration Path

### Phase 1: Add Base User Role
1. Create "User" role
2. Assign to all existing users
3. Keep existing roles intact

### Phase 2: Rename Conflicting Roles
1. Rename "Member" role to "MLM Member" (if needed)
2. Rename "Client" role to "App User" (if needed)
3. Update role assignments

### Phase 3: Implement Account Type Checks
1. Add account type middleware
2. Update routes to use account type checks
3. Keep role checks for permissions

### Phase 4: Clean Up
1. Remove auto-assignment of roles based on account type
2. Implement proper role assignment logic
3. Update documentation

---

## Key Principles

### 1. Account Types = Business Logic
- Use for: Portal routing, feature access, billing
- Check with: `$user->hasAccountType(AccountType::MEMBER)`
- Middleware: `account.type:member`

### 2. Roles = Authorization
- Use for: Permission checks, action authorization
- Check with: `$user->hasRole('admin')` or `@can('permission')`
- Middleware: `role:admin` or `permission:manage-users`

### 3. Don't Mix Them
- ❌ Don't use roles to determine portal access
- ❌ Don't use account types for permission checks
- ✅ Use account types for "what features"
- ✅ Use roles/permissions for "what actions"

### 4. Multiple Account Types OK
- User can be MEMBER + INVESTOR
- Gets both Member and Investor roles
- Can access both portals

### 5. Employee Roles Are Special
- EMPLOYEE account type is required
- Then assign specific role: Support Agent, Manager, Admin, Super Admin
- This allows hierarchy within employees

---

## Example Scenarios

### Scenario 1: Regular MLM Member
- **Account Type:** MEMBER
- **Roles:** User, Member
- **Can Access:** MLM dashboard, training, marketplace
- **Can Do:** View network, manage referrals, view commissions

### Scenario 2: Member Who Becomes Investor
- **Account Types:** MEMBER, INVESTOR
- **Roles:** User, Member, Investor
- **Can Access:** MLM dashboard + Investor portal
- **Can Do:** All member actions + view investments, vote on projects

### Scenario 3: App-Only Client
- **Account Type:** CLIENT
- **Roles:** User
- **Can Access:** Purchased modules, marketplace
- **Can Do:** Use purchased apps, shop

### Scenario 4: Support Agent (Employee)
- **Account Type:** EMPLOYEE
- **Roles:** User, Support Agent
- **Can Access:** Employee portal, live chat
- **Can Do:** View tickets, respond to chat, view customer profiles

### Scenario 5: Admin (Employee)
- **Account Type:** EMPLOYEE
- **Roles:** User, Support Agent, Manager, Admin
- **Can Access:** Employee portal, admin panel
- **Can Do:** All support actions + manage users, configure settings

---

## Summary

**Account Types** and **Spatie Roles** serve different purposes:

- **Account Types** = Business classification → Portal access
- **Spatie Roles** = Permission system → Action authorization

Keep them separate, use them together, and you'll have a clean, flexible system.

**Rule of Thumb:**
- Routing/Portal access → Check Account Type
- Button visibility/Action authorization → Check Role/Permission
