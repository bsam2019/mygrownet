# GrowSuite Complimentary Access System

**Last Updated:** February 21, 2026  
**Status:** Implemented

## Overview

The Complimentary Access System allows MyGrowNet to provide free GrowSuite access to select clients under special arrangements without requiring direct subscription payments.

## Subscription Types

### 1. **Paid** (Default)
- Standard subscription model
- Monthly/annual billing
- Full access based on plan tier

### 2. **Sponsored**
- Access sponsored by MyGrowNet or partner organization
- No billing to the client
- Full access to specified plan features
- Tracked via `sponsor_reference` field

### 3. **Complimentary**
- Free access granted for specific reasons:
  - Beta testing
  - Strategic partnerships
  - Community support
  - Marketing/promotional purposes
- Can be time-limited via `complimentary_until` field
- Converts to paid or expires after period

### 4. **Partner**
- Special access for business partners
- May have custom feature sets
- Tracked separately for partnership agreements

## Database Schema

```php
// cms_companies table additions
subscription_type: string (paid|sponsored|complimentary|partner)
sponsor_reference: string|null (e.g., "MyGrowNet HQ", "Partner: ABC Corp")
subscription_notes: text|null (internal notes)
complimentary_until: timestamp|null (expiration date for complimentary access)
```

## Implementation

### Setting Up Complimentary Access

**Via Admin Panel (Recommended):**
1. Navigate to CMS Admin → Companies
2. Select company
3. Edit subscription settings
4. Set `subscription_type` to desired type
5. Add sponsor reference and notes
6. Set expiration date if applicable

**Via Database:**
```sql
UPDATE cms_companies 
SET subscription_type = 'complimentary',
    sponsor_reference = 'MyGrowNet - Beta Tester',
    subscription_notes = 'Complimentary access for beta testing period',
    complimentary_until = '2026-12-31 23:59:59'
WHERE id = 123;
```

### Access Control Logic

```php
// In EnsureCmsAccess middleware or similar
public function hasActiveSubscription(Company $company): bool
{
    switch ($company->subscription_type) {
        case 'paid':
            return $company->subscription_status === 'active';
            
        case 'sponsored':
        case 'partner':
            return $company->status === 'active';
            
        case 'complimentary':
            if ($company->complimentary_until) {
                return now()->lte($company->complimentary_until) 
                    && $company->status === 'active';
            }
            return $company->status === 'active';
            
        default:
            return false;
    }
}
```

## Use Cases

### 1. Beta Testing Program
```php
$company->update([
    'subscription_type' => 'complimentary',
    'sponsor_reference' => 'MyGrowNet Beta Program',
    'subscription_notes' => 'Beta tester - Professional plan features',
    'complimentary_until' => now()->addMonths(3),
]);
```

### 2. Strategic Partnership
```php
$company->update([
    'subscription_type' => 'partner',
    'sponsor_reference' => 'Partnership Agreement #2026-001',
    'subscription_notes' => 'Full Enterprise access as per partnership agreement',
    'complimentary_until' => null, // No expiration
]);
```

### 3. Community Support
```php
$company->update([
    'subscription_type' => 'sponsored',
    'sponsor_reference' => 'MyGrowNet Community Initiative',
    'subscription_notes' => 'Non-profit organization - sponsored access',
    'complimentary_until' => null,
]);
```

### 4. Promotional Campaign
```php
$company->update([
    'subscription_type' => 'complimentary',
    'sponsor_reference' => 'Launch Promotion 2026',
    'subscription_notes' => '6 months free Professional plan',
    'complimentary_until' => now()->addMonths(6),
]);
```

## Expiration Handling

### Automated Expiration Check
Create a scheduled command to check and handle expired complimentary access:

```php
// app/Console/Commands/CheckExpiredComplimentaryAccess.php
public function handle()
{
    $expired = Company::where('subscription_type', 'complimentary')
        ->whereNotNull('complimentary_until')
        ->where('complimentary_until', '<', now())
        ->get();
        
    foreach ($expired as $company) {
        // Notify company admin
        $company->owner->notify(new ComplimentaryAccessExpired($company));
        
        // Optionally suspend or convert to trial
        $company->update([
            'status' => 'suspended',
            'subscription_notes' => $company->subscription_notes . 
                ' | Expired on ' . $company->complimentary_until->format('Y-m-d'),
        ]);
    }
    
    $this->info("Processed {$expired->count()} expired complimentary accounts");
}
```

Schedule in `app/Console/Kernel.php`:
```php
$schedule->command('cms:check-expired-complimentary')->daily();
```

## Reporting

### Track Complimentary Access
```php
// Get all complimentary/sponsored accounts
$complimentaryAccounts = Company::whereIn('subscription_type', [
    'complimentary', 'sponsored', 'partner'
])->get();

// Revenue impact analysis
$potentialRevenue = $complimentaryAccounts->sum(function ($company) {
    return $company->plan->price ?? 0;
});
```

## Admin Interface

### Accessing CMS Companies Management

1. Log in to MyGrowNet as a super admin
2. Navigate to Admin Dashboard
3. Go to **Admin → CMS Companies** (route: `/admin/cms-companies`)

### Managing Company Access

**View All Companies:**
- See all GrowSuite companies with their subscription status
- Filter by subscription type, status, or expiring soon
- Search by company name, email, or registration number
- View stats: total companies, active, complimentary, expiring soon

**Edit Company Subscription:**
1. Click "Edit Access" on any company
2. Select subscription type:
   - **Paid**: Standard subscription (default)
   - **Sponsored**: Sponsored by MyGrowNet or partner
   - **Complimentary**: Free access (beta, promo, etc.)
   - **Partner**: Special partner access
3. Add sponsor reference (who's sponsoring)
4. Set expiration date (required for complimentary)
5. Add internal notes documenting the arrangement
6. Set account status (active/suspended)
7. Click "Save Changes"

### Quick Actions

**Grant Complimentary Access:**
```
1. Go to Admin → CMS Companies
2. Find the company
3. Click "Edit Access"
4. Set Subscription Type: Complimentary
5. Set Sponsor Reference: "MyGrowNet Beta Program"
6. Set Expiration Date: Select future date
7. Add Notes: "Beta tester - 3 months free access"
8. Save
```

**Suspend Expired Access:**
```
1. Filter by "Expiring Soon" to see companies about to expire
2. Click "Edit Access" on expired company
3. Set Status: Suspended
4. Add Notes: Document expiration
5. Save
```

## Security Considerations

1. **Access Control**: Only super admins should be able to set complimentary access
2. **Audit Trail**: Log all changes to subscription_type
3. **Notifications**: Notify relevant parties when complimentary access is granted or expires
4. **Validation**: Ensure complimentary_until is in the future when set

## Best Practices

1. **Always add notes**: Document why complimentary access was granted
2. **Set expiration dates**: For temporary arrangements, always set `complimentary_until`
3. **Regular review**: Monthly review of all non-paid accounts
4. **Clear communication**: Inform clients about their access type and any expiration dates
5. **Conversion strategy**: Have a plan to convert complimentary users to paid

## Migration Path

Run the migration:
```bash
php artisan migrate
```

Existing companies will default to `subscription_type = 'paid'`.

## Implementation Status

### ✅ Completed
1. **Database Migration**: `2026_02_21_000001_add_subscription_type_to_cms_companies_table.php`
   - Added `subscription_type` field (paid|sponsored|complimentary|partner)
   - Added `sponsor_reference` field
   - Added `subscription_notes` field
   - Added `complimentary_until` timestamp field
   - Migration executed successfully

2. **Model Updates**: `app/Infrastructure/Persistence/Eloquent/CMS/CompanyModel.php`
   - Added new fields to `$fillable` array
   - Added `complimentary_until` to `$casts`
   - Implemented `hasValidAccess()` method
   - Implemented `isComplimentaryExpiringSoon()` method
   - Implemented `daysUntilComplimentaryExpires()` method

3. **Access Control**: `app/Http/Middleware/EnsureCmsAccess.php`
   - Updated to use `hasValidAccess()` method
   - Added specific error messages for expired complimentary access
   - Validates subscription type and expiration dates

4. **Scheduled Command**: `app/Console/Commands/CheckExpiredComplimentaryAccess.php`
   - Checks for expired complimentary access daily
   - Suspends expired accounts automatically
   - Warns about accounts expiring within 7 days
   - Scheduled to run daily at 1:00 AM

5. **Scheduling**: `routes/console.php`
   - Added `cms:check-expired-complimentary` to daily schedule

6. **Admin Interface**: Complete management UI in MyGrowNet admin dashboard
   - **Controller**: `app/Http/Controllers/Admin/CmsCompanyController.php`
   - **Routes**: Added to `routes/web.php` under `/admin/cms-companies`
   - **List Page**: `resources/js/pages/Admin/CMS/Companies/Index.vue`
     - View all companies with stats dashboard
     - Filter by subscription type, status, expiring soon
     - Search by name, email, registration number
     - Pagination support
   - **Edit Page**: `resources/js/pages/Admin/CMS/Companies/Edit.vue`
     - Manage subscription type
     - Set sponsor reference and notes
     - Set expiration dates for complimentary access
     - Update account status

### 🔄 Pending
- Email notifications for expiring/expired access
- Reporting dashboard for complimentary accounts

## Access Control Flow

Access is granted through `app/Http/Middleware/EnsureCmsAccess.php`:

1. Check if user is authenticated
2. Check if user has CMS user record
3. Check if CMS user is active
4. **Check if company has valid access** (NEW)
   - Calls `$company->hasValidAccess()`
   - Validates based on subscription type
   - Checks expiration dates for complimentary access

## Changelog

### February 21, 2026
- ✅ Initial implementation complete
- ✅ Database migration created and executed
- ✅ Model methods implemented
- ✅ Middleware updated with access validation
- ✅ Scheduled command created for expiration handling
- ✅ Command added to daily schedule
- ✅ Admin interface implemented
  - Companies list page with filtering and stats
  - Edit page for managing subscription types
  - Routes added to web.php
  - Controller created with CRUD operations
- 🔄 Email notifications pending
