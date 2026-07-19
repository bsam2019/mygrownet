# Business Manager Integration

**Last Updated:** April 19, 2026  
**Status:** Production

## Overview

The CMS (Company Management System) has been integrated into the main MyGrowNet dashboard as the "Business Manager" module. Users no longer need separate login credentials - they access Business Manager through their main MyGrowNet account.

## Key Changes

### 1. Single Sign-On (SSO)
- **Before:** Separate `/cms/login` and `/cms/register` pages
- **After:** Users log in once to MyGrowNet and access Business Manager from the dashboard

### 2. Module Card on Dashboard
The Business Manager module card appears in the "Business Operations" section for all users:

**No Companies (First-time users):**
- Badge: "New"
- Description: "Create your business"
- Action: Redirects to company creation flow

**Has Companies:**
- Badge: Number of companies (e.g., "2")
- Description: "Manage your business operations"
- Action: 
  - 1 company → Direct to CMS dashboard
  - 2+ companies → Show company selector modal

### 3. Company Selector Modal
When a user has multiple companies, clicking Business Manager opens a modal to select which company to manage.

**Features:**
- Lists all companies with name, industry, and role
- "Create New Business" button at the bottom
- Smooth transitions and hover effects

### 4. Removed Routes
The following routes have been removed:
- `GET /cms/login` - Use main login instead
- `POST /cms/login` - Use main auth
- `GET /cms/register` - Use main registration
- `POST /cms/register` - Use main auth
- `GET /cms/password/change` - Use main profile settings
- `POST /cms/password/change` - Use main profile settings
- `POST /cms/logout` - Use main logout

### 5. Updated Middleware
`EnsureCmsAccess` middleware now:
- Redirects unauthenticated users to main login (`/login`) instead of `/cms/login`
- Redirects authenticated users without companies to `/cms/companies/hub`
- Works seamlessly with main authentication system

## User Flows

### First-Time Business User
1. Login to MyGrowNet
2. See "Business Manager" module with "New" badge
3. Click → Redirected to `/cms/companies/create`
4. Complete 2-step company creation wizard
5. Redirected to CMS dashboard

### Returning User (1 Company)
1. Login to MyGrowNet
2. Click "Business Manager" module
3. Automatically switched to their company
4. Redirected to CMS dashboard

### Returning User (2+ Companies)
1. Login to MyGrowNet
2. Click "Business Manager" module
3. Company selector modal appears
4. Select company or create new
5. Redirected to CMS dashboard

## Technical Implementation

### Files Modified

**Backend:**
- `app/Http/Controllers/DashboardController.php` - Added `cmsCompanies` data
- `app/Http/Middleware/EnsureCmsAccess.php` - Updated redirect logic
- `routes/cms.php` - Removed auth routes

**Frontend:**
- `resources/js/pages/Dashboard/Index.vue` - Added Business Manager card
- `resources/js/Components/CompanySelectorModal.vue` - New modal component

### Data Flow

```php
// DashboardController shares CMS companies
'cmsCompanies' => $user->cmsUsers()
    ->with('company')
    ->whereHas('company', function($q) {
        $q->where('status', 'active');
    })
    ->get()
    ->map(function($cmsUser) {
        return [
            'id' => $cmsUser->company->id,
            'name' => $cmsUser->company->name,
            'industry' => $cmsUser->company->industry,
            'role' => $cmsUser->role,
        ];
    })
    ->toArray();
```

### Company Switching

```javascript
// Dashboard handles company selection
const handleBusinessManagerClick = () => {
    if (cmsCompanies.length === 0) {
        // No companies - create
        router.visit('/cms/companies/create');
    } else if (cmsCompanies.length === 1) {
        // One company - direct access
        router.post('/cms/switch-company', { 
            company_id: cmsCompanies[0].id 
        }, {
            onSuccess: () => router.visit('/cms/dashboard')
        });
    } else {
        // Multiple companies - show selector
        showCompanySelector.value = true;
    }
};
```

## Benefits

1. **Unified Experience** - One login for all MyGrowNet features
2. **Better Discovery** - Users discover Business Manager as a feature
3. **Simplified Onboarding** - No separate registration process
4. **Consistent Navigation** - Access from main dashboard like other modules
5. **Shared User Context** - Profile, notifications, and settings are unified

## Migration Notes

### Existing Users
- Existing CMS users can still access their companies
- No data migration required
- User accounts are linked via `cms_users` table

### New Users
- Register once on MyGrowNet
- Create companies from the dashboard
- No separate CMS account needed

## Future Enhancements

1. **Quick Actions** - Add quick actions to the module card (e.g., "Create Invoice")
2. **Company Badges** - Show company status/health indicators
3. **Recent Activity** - Display recent CMS activity on the card
4. **Notifications** - Show pending approvals/tasks count

## Related Documentation

- `docs/cms/MULTI_COMPANY_ARCHITECTURE.md` - Multi-company system design
- `docs/cms/COMPANY_HUB.md` - Company hub and creation flow
- `docs/structure.md` - Overall project structure
