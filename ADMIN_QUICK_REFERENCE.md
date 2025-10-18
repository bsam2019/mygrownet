# Admin System - Quick Reference Guide

## Admin Login Credentials

```
Email:    admin@vbif.com
Password: vbif@2025!
Role:     Administrator
```

---

## Admin Features Access

### ✅ Working Features

#### Dashboard & Analytics
- `/admin/dashboard` - Main admin dashboard
- `/admin/activities` - Activity logs
- `/admin/metrics` - System metrics

#### User Management
- `/admin/users` - User list and management
- `/admin/role-management/roles` - Role management
- `/admin/role-management/permissions` - Permission management
- `/admin/role-management/users` - User role assignment

#### Investment Management
- `/admin/investments` - Investment overview
- `/admin/investment-categories` - Category management
- `/admin/investment-tiers` - Tier management
- `/admin/investments/pending` - Pending approvals

#### Financial Management
- `/admin/withdrawals` - Withdrawal requests
- `/admin/profit-distribution` - Profit distribution
- `/admin/financial/reports` - Financial reports
- `/admin/transactions` - Transaction history

#### MLM & Referrals
- `/admin/referrals` - Referral management
- `/admin/mlm` - MLM administration
- `/admin/matrix` - Matrix management
- `/admin/reward-analytics` - Reward analytics

#### Points System
- `/admin/points` - Points overview
- `/admin/points/users` - User points management
- `/admin/points/transactions` - Points transactions
- `/admin/points/badges` - Badge management

#### System Management
- `/admin/settings` - System settings
- `/admin/security` - Security settings
- `/admin/applications` - Application management
- `/admin/assets` - Asset management

---

## Authorization Patterns

### Pattern 1: Controller-Level Check (is_admin)
```php
// In any admin controller
if (!auth()->user()->is_admin) {
    abort(403, 'Unauthorized. Administrator access required.');
}
```

### Pattern 2: Role-Based Check (hasRole)
```php
// Check for specific admin role
if (auth()->user()->hasRole('Administrator')) {
    // Admin action
}

// Check for any admin role
if (auth()->user()->hasRole(['Administrator', 'admin', 'superadmin'])) {
    // Admin action
}
```

### Pattern 3: Middleware Protection
```php
// In routes/admin.php
Route::middleware(['auth', 'admin'])->group(function () {
    // Protected admin routes
});
```

### Pattern 4: Policy-Based Authorization
```php
// In policies
public function viewAny(User $user)
{
    return $user->hasRole('Administrator') || $user->hasRole('admin');
}
```

---

## Role Hierarchy

### Admin Roles (Equivalent Access)
1. **superadmin** - Highest level
2. **Administrator** - Your current role
3. **admin** - Alternative name

All three have full admin access.

### System Roles (Protected)
Cannot be edited or deleted:
- `superadmin`
- `Administrator`
- `admin`
- `employee`
- `member`

### Custom Roles
Can be created, edited, and deleted:
- `Investment Manager`
- `Support Agent`
- `Investor`
- `manager`
- Any custom roles you create

---

## Common Admin Tasks

### Create a New Role
1. Navigate to `/admin/role-management/roles`
2. Click "Create Role" button
3. Enter role name and description
4. Click "Create"

### Assign Permissions to Role
1. Go to `/admin/role-management/roles`
2. Click "View" on the role
3. Select permissions to assign
4. Click "Update Permissions"

### Assign Role to User
1. Go to `/admin/role-management/users`
2. Find the user
3. Select role from dropdown
4. Click "Assign Role"

### Approve Investment
1. Navigate to `/admin/investments`
2. Filter by "Pending" status
3. Click "Approve" or "Reject"

### Process Withdrawal
1. Go to `/admin/withdrawals`
2. Review withdrawal request
3. Click "Approve" or "Reject"

### View Activity Logs
1. Navigate to `/admin/activities`
2. Filter by type, status, or date range
3. View detailed activity information

---

## Middleware Reference

### Available Middleware
- `auth` - Requires authentication
- `admin` - Requires admin role (Administrator, admin, or superadmin)
- `admin.or.role:{role}` - Requires admin OR specific role
- `role.dashboard` - Role-based dashboard redirect

### Middleware Locations
- `app/Http/Middleware/AdminMiddleware.php` - Main admin check
- `app/Http/Middleware/Admin.php` - Alternative admin check
- `app/Http/Middleware/AdminOrRoleMiddleware.php` - Flexible admin check
- `app/Http/Middleware/RoleBasedDashboard.php` - Dashboard routing

---

## API Endpoints

### Admin API Routes
All require `auth:sanctum` and `admin` middleware:

```
GET  /api/admin/referral/stats
GET  /api/admin/referral/top-referrers
GET  /api/admin/referral/analytics
POST /api/admin/referral/process-pending
GET  /api/admin/referral/monthly-stats
GET  /api/admin/referral/export
```

---

## Troubleshooting

### Issue: 403 Unauthorized Error
**Cause:** User doesn't have admin role  
**Solution:** Verify user has Administrator role
```bash
php artisan tinker
User::where('email', 'admin@vbif.com')->first()->roles->pluck('name');
```

### Issue: CRUD Buttons Not Showing
**Cause:** Authorization check failing  
**Solution:** Clear cache and refresh browser
```bash
php artisan optimize:clear
```
Then press Ctrl+F5 in browser

### Issue: Changes Not Reflecting
**Cause:** Cached data  
**Solution:** Clear all caches
```bash
php artisan optimize:clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Issue: Cannot Access Admin Routes
**Cause:** Not logged in or wrong role  
**Solution:** 
1. Logout completely
2. Clear browser cookies
3. Login with admin credentials
4. Verify redirect to admin dashboard

---

## Security Best Practices

### 1. Role Assignment
- Only assign admin roles to trusted users
- Use specific roles (Investment Manager, Support Agent) when possible
- Avoid giving everyone Administrator role

### 2. Permission Management
- Follow principle of least privilege
- Assign only necessary permissions to roles
- Regularly audit role permissions

### 3. Activity Monitoring
- Regularly check activity logs
- Monitor admin actions
- Investigate suspicious activities

### 4. Password Security
- Use strong passwords for admin accounts
- Change default passwords immediately
- Enable 2FA if available

---

## Database Tables

### Roles & Permissions (Spatie)
- `roles` - Role definitions
- `permissions` - Permission definitions
- `model_has_roles` - User-role assignments
- `model_has_permissions` - Direct user permissions
- `role_has_permissions` - Role-permission assignments

### Users
- `users` - User accounts
- `user_profiles` - Extended user information

### Activity Tracking
- `activity_logs` - System activity logs
- `audit_logs` - Audit trail

---

## Useful Artisan Commands

### Role Management
```bash
# Create a role
php artisan permission:create-role "Role Name"

# Create a permission
php artisan permission:create-permission "permission-name"

# Assign permission to role
php artisan tinker
Role::findByName('Administrator')->givePermissionTo('permission-name');
```

### User Management
```bash
# Assign role to user
php artisan tinker
$user = User::find(1);
$user->assignRole('Administrator');

# Remove role from user
$user->removeRole('Administrator');

# Check user roles
$user->roles->pluck('name');
```

### Cache Management
```bash
# Clear all caches
php artisan optimize:clear

# Clear specific caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## File Locations

### Controllers
```
app/Http/Controllers/Admin/
├── AdminDashboardController.php
├── RolePermissionController.php
├── UserManagementController.php
├── InvestmentController.php
└── ... (other admin controllers)
```

### Middleware
```
app/Http/Middleware/
├── AdminMiddleware.php
├── Admin.php
├── AdminOrRoleMiddleware.php
└── RoleBasedDashboard.php
```

### Routes
```
routes/
├── admin.php          # Admin web routes
├── api.php            # API routes (includes admin API)
└── web.php            # General web routes
```

### Views (Inertia/Vue)
```
resources/js/pages/Admin/
├── Dashboard/
├── Roles/
├── Permissions/
├── Users/
└── ... (other admin pages)
```

### Models
```
app/Models/
├── User.php           # User model (with is_admin accessor)
├── Role.php           # Custom role model
└── Permission.php     # Custom permission model
```

---

## Testing

### Test Admin Access
```bash
php artisan tinker
```
```php
$admin = User::where('email', 'admin@vbif.com')->first();

// Test is_admin accessor
$admin->is_admin; // Should return true

// Test hasRole
$admin->hasRole('Administrator'); // Should return true
$admin->hasRole(['Administrator', 'admin']); // Should return true

// Test permissions
$admin->getAllPermissions();
```

### Test Role Authorization
```bash
php artisan tinker
```
```php
// Test role CRUD authorization
$admin = User::where('email', 'admin@vbif.com')->first();
$admin->hasRole(['superadmin', 'Administrator', 'admin']); // Should return true
```

---

## Support Resources

### Documentation Files
- `ADMIN_403_FIX.md` - Fix for 403 errors
- `ROLES_PERMISSIONS_BUTTONS_FIX.md` - Fix for missing CRUD buttons
- `ADMIN_AUTHORIZATION_COMPLETE_FIX.md` - Comprehensive fix guide
- `ADMIN_QUICK_REFERENCE.md` - This file

### Log Files
- `storage/logs/laravel.log` - Application logs
- Check for errors and warnings

### Database
- Use phpMyAdmin or database client
- Check `users`, `roles`, `model_has_roles` tables

---

## Quick Commands Cheat Sheet

```bash
# Login to admin panel
# URL: http://your-domain/login
# Email: admin@vbif.com
# Password: vbif@2025!

# Clear all caches
php artisan optimize:clear

# Check admin user
php artisan tinker
User::where('email', 'admin@vbif.com')->first()->roles->pluck('name');

# Assign admin role
php artisan tinker
User::find(1)->assignRole('Administrator');

# View all roles
php artisan tinker
Role::all()->pluck('name');

# View all permissions
php artisan tinker
Permission::all()->pluck('name');
```

---

**Last Updated:** 2025-10-18  
**Version:** 1.0  
**Status:** ✅ Production Ready
