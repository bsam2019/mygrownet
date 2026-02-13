# GrowBuilder Premium Template Access Management

**Last Updated:** February 5, 2026  
**Status:** Production Ready

## Overview

Admins can now grant premium template access to specific users without requiring them to purchase a GrowBuilder subscription. Access is **tier-based**, matching the GrowBuilder subscription tiers:

- **Starter** (K120/month equivalent) - Basic premium templates
- **Business** (K350/month equivalent) - All premium templates + features
- **Agency** (K900/month equivalent) - Full access + white-label options

This allows for promotional access, beta testing, partnerships, or special cases where users should have premium template access at specific tier levels.

## Features

- ✅ Admin interface to manage premium template access
- ✅ Grant/revoke access for individual users
- ✅ Bulk grant/revoke for multiple users
- ✅ Track who granted access and when
- ✅ Add notes for each grant
- ✅ Search and filter users
- ✅ Automatic integration with existing template access logic

## Implementation

### Database Changes

**Migration:** `2026_02_05_044932_add_premium_access_to_users_table.php`

Initial fields added to `users` table:
- `has_premium_template_access` (boolean) - Deprecated, replaced by tier-based system
- `premium_access_granted_at` (timestamp) - When access was granted
- `premium_access_granted_by` (foreign key) - Admin who granted access
- `premium_access_notes` (text) - Optional notes about the grant

**Migration:** `2026_02_05_054000_update_premium_access_to_tier_based.php`

Updated to tier-based system:
- Removed `has_premium_template_access` (boolean)
- Added `premium_template_tier` (string: 'starter', 'business', 'agency', or null)
- Migrated existing data: `true` → 'starter' tier
- Preserved `premium_access_granted_at`, `premium_access_granted_by`, `premium_access_notes`

### Backend Files

**Controller:** `app/Http/Controllers/Admin/PremiumAccessController.php`
- `index()` - List users with search/filter
- `grant()` - Grant access to a user
- `revoke()` - Revoke access from a user
- `bulkGrant()` - Grant access to multiple users
- `bulkRevoke()` - Revoke access from multiple users

**Model Updates:** `app/Models/User.php`
- Added `premiumAccessGrantedBy()` relationship
- Added `premium_template_tier` to `$fillable` array
- Added `hasPremiumTemplateAccess(?string $minimumTier = 'starter'): bool` helper method
  - Checks if user has admin-granted access at specified tier level or higher
  - Tier hierarchy: starter (1) < business (2) < agency (3)

**Routes:** `routes/admin.php`
```php
Route::prefix('premium-access')->name('premium-access.')->group(function () {
    Route::get('/', [PremiumAccessController::class, 'index'])->name('index');
    Route::post('/{user}/grant', [PremiumAccessController::class, 'grant'])->name('grant');
    Route::post('/{user}/revoke', [PremiumAccessController::class, 'revoke'])->name('revoke');
    Route::post('/bulk-grant', [PremiumAccessController::class, 'bulkGrant'])->name('bulk-grant');
    Route::post('/bulk-revoke', [PremiumAccessController::class, 'bulkRevoke'])->name('bulk-revoke');
});
```

**Access Logic Update:** `app/Domain/Module/Services/ModuleAccessService.php`

Added special case for GrowBuilder admin-granted premium access in `getAccessLevel()` method:
```php
// Special case: GrowBuilder admin-granted premium access
if ($moduleId->value() === 'growbuilder' && !empty($user->premium_template_tier)) {
    // User has admin-granted premium access, return their tier
    return $user->premium_template_tier;
}
```

This integrates seamlessly with the existing subscription system. The tier returned by `getAccessLevel()` is used throughout GrowBuilder to:
- Determine available templates (premium vs free)
- Apply tier-specific restrictions (sites limit, storage, features)
- Control access to tier-specific features (custom domains, e-commerce, etc.)

**SiteController Update:** `app/Http/Controllers/GrowBuilder/SiteController.php`

Updated premium template validation:
```php
$hasPremiumAccess = $currentTier !== 'free' || $request->user()->hasPremiumTemplateAccess();
```

### Frontend Files

**Admin Page:** `resources/js/Pages/Admin/PremiumAccess/Index.vue`
- User list with search and filters
- Grant/revoke buttons
- Bulk actions
- Statistics dashboard
- Modal for adding notes

## Usage

### For Admins

**Access the Management Page:**
1. Go to Admin Dashboard
2. Navigate to "Premium Template Access" (add to admin sidebar)
3. Or visit: `/admin/premium-access`

**Grant Access to a User:**
1. Search for the user by name or email
2. Click "Grant Access" button
3. **Select the tier level:**
   - **Starter (K120)** - Basic premium templates, custom domain, up to 20 products
   - **Business (K350)** - All premium templates, unlimited products, payment integrations, marketing tools
   - **Agency (K900)** - Multi-site management, white-label, all business features
4. Optionally add notes (e.g., "Beta tester", "Partnership agreement")
5. Confirm

**Revoke Access:**
1. Find the user with premium access
2. Click "Revoke Access"
3. Confirm the action

**Bulk Actions:**
1. Select multiple users using checkboxes
2. Click "Bulk Actions" button
3. **Select the tier level** for all selected users
4. Choose "Grant Access" or "Revoke Access"
5. Optionally add notes for bulk grants
6. Confirm

**Filter Users:**
- **All Users** - Show everyone
- **Premium Access Granted** - Show only users with admin-granted access
- **No Premium Access** - Show users without admin-granted access

### For Users

Users with admin-granted premium access can:
- Create sites using premium templates (based on their tier level)
- Access tier-specific features:
  - **Starter**: Custom domain, basic e-commerce (20 products), email support
  - **Business**: Unlimited products, payment integrations, marketing tools, priority support
  - **Agency**: Multi-site management, white-label options, all business features
- No subscription payment required

The system automatically checks for premium access when:
- Creating a new site with a premium template
- Editing site templates
- Accessing tier-specific features

## How It Works

### Integration with Subscription System

The admin-granted premium access integrates seamlessly with GrowBuilder's existing subscription system:

1. **Tier Detection**: `ModuleAccessService->getAccessLevel()` checks for admin-granted tier BEFORE checking database subscriptions
2. **Priority**: Admin-granted access takes precedence over free tier (but paid subscriptions can coexist)
3. **Feature Access**: The returned tier ('starter', 'business', 'agency') is used throughout GrowBuilder to:
   - Filter available templates
   - Apply storage limits
   - Control feature access (custom domains, e-commerce, etc.)
   - Determine sites limit
   - Enable/disable marketing tools

### Access Flow

```
User creates site
    ↓
SiteController checks tier
    ↓
TierRestrictionService->getUserTier()
    ↓
SubscriptionService->getUserTier()
    ↓
ModuleAccessService->getAccessLevel()
    ↓
Checks: premium_template_tier → database subscription → free tier
    ↓
Returns: 'starter' | 'business' | 'agency' | 'free'
    ↓
Tier-specific features applied
```

## Use Cases

### 1. Beta Testing
Grant premium access to beta testers at appropriate tier levels:
```
User: john@example.com
Tier: Starter (K120)
Notes: "Beta tester for new premium templates - expires March 2026"
```

### 2. Partnerships
Provide premium access to partner organizations at business tier:
```
Users: [partner1@org.com, partner2@org.com, partner3@org.com]
Tier: Business (K350)
Notes: "Partnership agreement with XYZ Organization"
```

### 3. Promotional Access
Grant temporary premium access for contest winners:
```
User: winner@example.com
Tier: Starter (K120)
Notes: "Contest winner - 6 months premium access"
```

### 4. Special Cases
Handle special requests with appropriate tier:
```
User: nonprofit@example.com
Tier: Business (K350)
Notes: "Non-profit organization - complimentary premium access"
```

### 5. Agency Partners
Grant full access to agency partners:
```
User: agency@example.com
Tier: Agency (K900)
Notes: "Agency partner - white-label access for client sites"
```

## Admin Interface Features

### Statistics Dashboard
- Total users count
- Users with premium access count

### Search & Filter
- Search by name or email
- Filter by access status
- Real-time search with debouncing

### User Table
- Checkbox selection for bulk actions
- User name and email
- Access status badge (green for granted, gray for none)
- Granted by admin name
- Grant date
- Quick action buttons

### Modals
- **Grant Modal**: Add optional notes when granting access
- **Bulk Modal**: Perform actions on multiple users at once

## Security

- Only admins can access the premium access management page
- All actions are logged with admin ID and timestamp
- Confirmation required for revoke actions
- Bulk actions require explicit confirmation

## Database Queries

**Find users with premium access:**
```php
User::whereNotNull('premium_template_tier')->get();
```

**Find users with specific tier:**
```php
User::where('premium_template_tier', 'business')->get();
```

**Check if user has premium access:**
```php
if ($user->hasPremiumTemplateAccess()) {
    // User has admin-granted premium access (any tier)
}

if ($user->hasPremiumTemplateAccess('business')) {
    // User has business tier or higher
}
```

**Get admin who granted access:**
```php
$grantedBy = $user->premiumAccessGrantedBy;
```

## Future Enhancements

- [ ] Expiration dates for premium access grants
- [ ] Email notifications when access is granted/revoked
- [ ] Activity log for premium access changes
- [ ] Export list of users with premium access
- [ ] Automatic revocation after X days
- [ ] Usage analytics for admin-granted access

## Troubleshooting

### Search/filter not working

**Symptoms:**
- Search input doesn't filter results
- Filter dropdown doesn't change displayed users
- Console error: "Cannot read properties of null (reading 'toString')"

**Solution:**
- Ensure backend uses `!empty($search)` instead of just `if ($search)`
- Add `withQueryString()` to pagination in controller
- Use `replace: true` in Inertia router options
- Add conditional rendering for pagination (`v-if="users.links && users.links.length > 0"`)
- Pass `undefined` instead of `null` for href in pagination links

**Fixed in:** February 5, 2026 Update 2

### User can't access premium templates after grant

**Check:**
1. Verify `premium_template_tier` is set (not null) in database
2. Ensure tier is valid: 'starter', 'business', or 'agency'
3. Clear user session/cache
4. Check if template is actually marked as premium
5. Verify SiteController logic uses `hasPremiumTemplateAccess()` method

### Wrong tier level granted

**Check:**
1. Verify tier was selected correctly in admin interface
2. Check database value matches expected tier
3. Ensure tier hierarchy is working: starter < business < agency
4. Test with `$user->hasPremiumTemplateAccess('business')` to check tier level

### Bulk actions not working

**Check:**
1. Ensure user IDs are valid
2. Check for database constraints
3. Verify admin permissions
4. Check browser console for errors

### Access not showing in admin panel

**Check:**
1. Migration ran successfully
2. User model has relationship defined
3. Controller is returning correct data
4. Frontend is receiving props correctly

## Changelog

### February 5, 2026 - Update 5 (Frontend Integration Fix)
- **CRITICAL FIX**: Connected frontend template locking to subscription tier
- Added `hasGrowBuilderSubscription` computed property in GrowBuilder Dashboard
- Passes subscription status to CreateSiteWizard component
- Templates now properly unlock when user has admin-granted access
- Frontend checks: `subscription.tier !== 'free'` (includes starter, business, agency)

### February 5, 2026 - Update 4 (Complete Integration)
- **CRITICAL FIX**: Integrated admin-granted access with GrowBuilder subscription system
- Updated `ModuleAccessService->getAccessLevel()` to check `premium_template_tier` for GrowBuilder
- Admin-granted tier now properly applies ALL tier-specific features and restrictions
- Users with admin-granted access now have full access to:
  - Premium templates (based on tier)
  - Tier-specific storage limits
  - Custom domain features
  - E-commerce capabilities
  - Marketing tools (business/agency tiers)
  - Multi-site management (agency tier)
- Access flow: premium_template_tier → database subscription → free tier

### February 5, 2026 - Update 3 (Tier-Based System)
- **BREAKING CHANGE**: Converted from boolean to tier-based access system
- Added `premium_template_tier` field (starter, business, agency)
- Removed `has_premium_template_access` boolean field
- Migration automatically converts existing grants to 'starter' tier
- Added tier selection dropdown in grant modal
- Added tier selection in bulk actions
- Updated User model with `hasPremiumTemplateAccess(?string $minimumTier)` helper
- Tier hierarchy: starter (1) < business (2) < agency (3)
- Color-coded tier badges in admin interface (blue=starter, green=business, purple=agency)
- Updated all documentation to reflect tier-based system

### February 5, 2026 - Update 2
- Fixed search and filter functionality
- Added proper query string preservation for pagination
- Improved empty search handling with `!empty()` check
- Added `withQueryString()` to pagination for filter persistence
- Fixed pagination null URL error by adding conditional rendering
- Added `replace: true` option to prevent history pollution

### February 5, 2026 - Initial Release
- Initial implementation
- Database migration for premium access fields
- Admin controller with CRUD operations
- Frontend admin interface
- Integration with GrowBuilder template access logic
- Bulk grant/revoke functionality
- Search and filter capabilities
