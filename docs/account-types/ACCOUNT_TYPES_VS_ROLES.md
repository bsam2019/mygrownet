# Account Types vs Roles - Complete Guide

**Last Updated:** December 1, 2025

## Critical Distinction

MyGrowNet uses TWO separate systems for user classification:

1. **Account Types** (Enum) - Business/billing classification
2. **Roles** (Spatie Permission) - Access control and permissions

These work TOGETHER but serve different purposes.

---

## Account Types (Business Classification)

**Purpose:** Determine billing model, MLM participation, and business relationship

**Stored in:** `users.account_type` (enum field)

**Values:**
- `member` - MLM participant
- `client` - App/shop user (no MLM)
- `business` - SME owner
- `investor` - Venture Builder co-investor
- `employee` - Internal staff

**Controls:**
- Billing/subscription model
- MLM participation (yes/no)
- Available modules/features
- Dashboard routing

---

## Roles (Access Control)

**Purpose:** Control permissions and access to specific features

**Stored in:** `model_has_roles` table (Spatie Permission)

**System Roles:**
- `superadmin` - Super administrator (highest access)
- `admin` - Platform administrators
- `employee` - Company employees
- `member` - Regular platform users
- `Client` - Non-MLM users (marketplace, apps)
- `Business` - SME users (business tools)

**Legacy Roles (Backward Compatibility):**
- `investor` - Use "member" instead
- `manager` - Deprecated

**Controls:**
- Feature access (via permissions)
- UI visibility
- API endpoints
- Admin panel sections

---

## How They Work Together

### Example 1: MLM Member
```php
$user->account_type = 'member';  // Business classification
$user->assignRole('member');      // Access control

// Result:
// - Pays MLM subscription
// - Subject to MLM rules
// - Has member dashboard permissions
// - Can access network, commissions, training
```

### Example 2: App-Only Client
```php
$user->account_type = 'client';  // Business classification
$user->assignRole('Client');      // Access control

// Result:
// - Pays per-module subscription
// - NOT subject to MLM rules
// - Has marketplace/app permissions
// - Cannot access MLM features
```

### Example 3: SME Business Owner
```php
$user->account_type = 'business';  // Business classification
$user->assignRole('Business');      // Access control

// Result:
// - Pays business tools subscription
// - NOT subject to MLM rules
// - Has accounting/staff management permissions
// - Cannot access MLM features
```

### Example 4: Internal Employee
```php
$user->account_type = 'employee';  // Business classification
$user->assignRole('employee');      // Access control

// Result:
// - No billing (internal account)
// - NOT subject to MLM rules
// - Has employee dashboard permissions
// - Can access admin tools based on role
```

### Example 5: Admin Who Is Also a Member
```php
$user->account_type = 'member';    // Business classification
$user->assignRole(['member', 'admin']); // Multiple roles

// Result:
// - Pays MLM subscription
// - Subject to MLM rules
// - Has both member AND admin permissions
// - Can access everything
```

---

## Mapping: Account Type → Default Role

When a user is created, they get a default role based on account type:

| Account Type | Default Role | Auto-Assigned |
|--------------|--------------|---------------|
| `member` | `member` | ✅ Yes |
| `client` | `Client` | ✅ Yes |
| `business` | `Business` | ✅ Yes |
| `investor` | `member` | ✅ Yes (investors are also members) |
| `employee` | `employee` | ✅ Yes |

**Implementation:**
```php
// In User model boot() method
static::created(function ($user) {
    $roleMap = [
        'member' => 'member',
        'client' => 'Client',
        'business' => 'Business',
        'investor' => 'member',
        'employee' => 'employee',
    ];
    
    $roleName = $roleMap[$user->account_type] ?? 'Client';
    
    if (Role::where('name', $roleName)->exists()) {
        $user->assignRole($roleName);
    }
});
```

---

## Permission Structure

### Member Role Permissions
```php
[
    'view_member_dashboard',
    'create_subscription',
    'cancel_subscription',
    'view_personal_reports',
    'view_personal_matrix',
    'view_personal_commissions',
    'view_personal_points',
]
```

### Client Role Permissions
```php
[
    'view marketplace',
    'purchase products',
    'view venture builder',
    'invest in ventures',
    'manage profile',
    'view wallet',
]
```

### Business Role Permissions
```php
[
    'view marketplace',
    'purchase products',
    'manage profile',
    'view wallet',
    'access accounting',
    'manage staff',
    'manage tasks',
    'view reports',
]
```

### Employee Role Permissions
```php
[
    'view_employee_dashboard',
    'view_team_users',
    'view_team_members',
    'view_team_subscriptions',
    'view_team_reports',
    'view_team_matrix',
    'view_team_commissions',
    'view_team_points',
    'manage_courses',
    'manage_learning_packs',
]
```

### Admin Role Permissions
```php
// All permissions EXCEPT:
// - manage_roles
// - manage_permissions
// - assign_roles
```

### Superadmin Role Permissions
```php
// ALL permissions (no restrictions)
```

---

## Multi-Account Type Support

Users can have multiple account types but typically ONE primary role:

### Example: Member + Investor
```php
$user->account_types = ['member', 'investor']; // JSON array
$user->assignRole('member'); // Single role

// Access check:
$user->hasAccountType('member');   // true
$user->hasAccountType('investor'); // true
$user->hasRole('member');          // true

// MLM participation:
$user->isMLMParticipant(); // true (because has 'member' account type)
```

### Example: Client + Investor
```php
$user->account_types = ['client', 'investor']; // JSON array
$user->assignRole('Client'); // Single role

// Access check:
$user->hasAccountType('client');   // true
$user->hasAccountType('investor'); // true
$user->hasRole('Client');          // true

// MLM participation:
$user->isMLMParticipant(); // false (no 'member' account type)
```

---

## Access Control Patterns

### Middleware: Account Type Check
```php
// Check if user has specific account type
Route::middleware(['auth', 'account.type:member'])->group(function () {
    // MLM routes - only for members
});

Route::middleware(['auth', 'account.type:client,member'])->group(function () {
    // Module routes - for clients and members
});
```

### Middleware: Role Check (Spatie)
```php
// Check if user has specific role
Route::middleware(['auth', 'role:admin'])->group(function () {
    // Admin routes
});

Route::middleware(['auth', 'role:member|Client'])->group(function () {
    // Routes for members OR clients
});
```

### Middleware: Permission Check (Spatie)
```php
// Check if user has specific permission
Route::middleware(['auth', 'permission:manage_users'])->group(function () {
    // User management routes
});
```

### Blade Directives
```blade
{{-- Check account type --}}
@if($user->account_type === 'member')
    <div>MLM Dashboard</div>
@endif

{{-- Check role --}}
@role('admin')
    <div>Admin Panel</div>
@endrole

{{-- Check permission --}}
@can('manage_users')
    <button>Manage Users</button>
@endcan
```

### Vue/Inertia
```vue
<template>
  <!-- Check account type -->
  <div v-if="$page.props.auth.user.account_type === 'member'">
    MLM Dashboard
  </div>
  
  <!-- Check role -->
  <div v-if="$page.props.auth.user.roles.includes('admin')">
    Admin Panel
  </div>
  
  <!-- Check permission -->
  <button v-if="$page.props.auth.user.permissions.includes('manage_users')">
    Manage Users
  </button>
</template>
```

---

## Decision Tree: When to Use What?

### Use Account Type When:
- ✅ Determining billing/subscription model
- ✅ Checking MLM participation
- ✅ Routing to correct dashboard
- ✅ Showing/hiding modules based on purchase
- ✅ Applying business rules (commissions, points, etc.)

### Use Role When:
- ✅ Controlling access to features
- ✅ Showing/hiding UI elements
- ✅ Protecting routes and endpoints
- ✅ Checking admin/employee access
- ✅ Implementing fine-grained permissions

### Use Both When:
- ✅ Complex access control scenarios
- ✅ Multi-tenant features
- ✅ Hybrid user types (e.g., admin who is also a member)

---

## Common Patterns

### Pattern 1: MLM Feature Access
```php
// Check if user participates in MLM
if ($user->account_type === 'member' && $user->hasRole('member')) {
    // Show MLM features
}

// Or use helper method
if ($user->isMLMParticipant()) {
    // Show MLM features
}
```

### Pattern 2: Module Access
```php
// Check if user can access a module
if (in_array($user->account_type, ['member', 'client']) && 
    $user->hasPermissionTo('purchase products')) {
    // Allow module access
}
```

### Pattern 3: Admin Access
```php
// Check if user is admin (regardless of account type)
if ($user->hasRole(['admin', 'superadmin'])) {
    // Show admin panel
}
```

### Pattern 4: Employee Operations
```php
// Check if user is internal staff
if ($user->account_type === 'employee' && $user->hasRole('employee')) {
    // Show employee portal
}
```

---

## Database Schema

### Account Type (Single Field)
```sql
-- users table
account_type ENUM('member', 'client', 'business', 'investor', 'employee') 
    DEFAULT 'client'
```

### Roles (Spatie Permission Tables)
```sql
-- roles table
id, name, guard_name, slug, description

-- model_has_roles table (pivot)
role_id, model_type, model_id

-- permissions table
id, name, guard_name, slug, description

-- role_has_permissions table (pivot)
permission_id, role_id
```

---

## Migration Path

### Current State
- Account types defined in enum
- Roles defined via Spatie Permission
- Some overlap/confusion between the two

### Recommended Approach
1. **Keep both systems** - they serve different purposes
2. **Account Type** = Business classification (billing, MLM participation)
3. **Role** = Access control (permissions, features)
4. **Auto-assign roles** based on account type on user creation
5. **Use account type** for business logic
6. **Use roles/permissions** for access control

### Code Example
```php
// Business logic - use account type
if ($user->account_type === 'member') {
    $this->calculateCommissions($user);
}

// Access control - use role/permission
if ($user->can('view_admin_dashboard')) {
    return view('admin.dashboard');
}
```

---

## Summary

| Aspect | Account Type | Role |
|--------|--------------|------|
| **Purpose** | Business classification | Access control |
| **Storage** | `users.account_type` | `model_has_roles` table |
| **System** | Custom enum | Spatie Permission |
| **Controls** | Billing, MLM rules, modules | Permissions, features |
| **Multiple?** | Can have multiple (JSON) | Can have multiple |
| **Examples** | member, client, business | admin, employee, member |
| **Use For** | Business logic | Access control |

**Key Principle:** Account Type determines WHAT you are (business relationship), Role determines WHAT you can do (permissions).

---

## Related Documentation

- `docs/USER_TYPES_AND_ACCESS_MODEL.md` - Complete user types guide
- `USER_TYPES_QUICK_SUMMARY.md` - Quick reference
- `database/seeders/RoleSeeder.php` - Role definitions
- `database/seeders/ClientRoleSeeder.php` - Client/Business roles
- `app/Enums/AccountType.php` - Account type enum

---

**Remember:** Account Types and Roles are complementary systems. Use account types for business logic and billing, use roles for access control and permissions.
