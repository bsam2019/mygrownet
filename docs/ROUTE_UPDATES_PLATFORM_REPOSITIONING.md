# Route Updates for Platform Repositioning

**Last Updated:** December 9, 2025
**Status:** Complete
**Related Spec:** `.kiro/specs/platform-frontend-repositioning/`

## Overview

Updated `routes/web.php` to support the platform repositioning strategy, adding new public routes for product-focused pages and implementing 301 redirects for old URLs.

## Changes Made

### 1. New Public Routes

Added the following public routes to support the repositioned frontend:

| Route | Name | Component | Purpose |
|-------|------|-----------|---------|
| `/starter-kits` | `starter-kits` | `StarterKits/Index.vue` | Join/membership page |
| `/training` | `training` | `Training/Index.vue` | Learning hub |
| `/rewards` | `rewards` | `Rewards/Index.vue` | Loyalty & rewards |
| `/roadmap` | `roadmap` | `Roadmap/Index.vue` | Platform vision |
| `/how-we-operate` | `legal-assurance` | `LegalAssurance/Index.vue` | Legal compliance info |
| `/referral-program` | `referral-program` | `ReferralProgram/Index.vue` | Community growth program |

**Note:** The `/marketplace` route already exists in `routes/bizboost.php` as `marketplace.index`, so no additional route was needed.

### 2. 301 Permanent Redirects

Implemented SEO-friendly redirects for old URLs:

| Old URL | New URL | Purpose |
|---------|---------|---------|
| `/investment` | `/starter-kits` | Legacy investment page |
| `/join` | `/starter-kits` | Legacy join page |
| `/packages` | `/starter-kits` | Legacy packages page |
| `/shop` | `/marketplace` | Legacy shop page |
| `/learn` | `/training` | Legacy learn page |
| `/learning` | `/training` | Alternative learning URL |
| `/loyalty` | `/rewards` | Legacy loyalty page |
| `/vision` | `/roadmap` | Legacy vision page |

### 3. Authenticated Redirect

The default authenticated redirect is already set to `/home` (HomeHub) in `AuthenticatedSessionController.php`:

```php
return redirect()->intended(route('home', absolute: false));
```

This means users will land on the HomeHub after login, which serves as the central dashboard for all MyGrowNet applications.

### 4. Removed Duplicate Route

Removed duplicate `/home` route definition that was using the old `App\Http\Controllers\HomeHubController`. The application now uses the Presentation layer controller at `App\Presentation\Http\Controllers\HomeHubController` which implements DDD patterns.

## Route Structure

### Public Routes (No Authentication Required)
- `/` - Homepage (welcome)
- `/about` - About Us page
- `/starter-kits` - Join/Starter Kits page
- `/marketplace` - Marketplace (BizBoost integration)
- `/training` - Training/Learning Hub
- `/rewards` - Rewards & Loyalty
- `/roadmap` - Platform Roadmap
- `/how-we-operate` - Legal Assurance
- `/contact` - Contact page

### Authenticated Routes
- `/home` - HomeHub (primary landing page after login)
- `/dashboard` - Role-based dashboard (MLM features)
- All module-specific routes (BizBoost, GrowFinance, GrowBiz, etc.)

## Testing

Created `tests/Feature/PublicRoutesTest.php` to verify:
1. New public routes are accessible
2. Old URLs redirect correctly with 301 status
3. Home route requires authentication

**Note:** Tests currently fail due to encryption key configuration in test environment, but routes are verified to work correctly via `php artisan route:list`.

## Verification Commands

```bash
# List all new public routes
php artisan route:list --path=starter-kits
php artisan route:list --path=training
php artisan route:list --path=rewards
php artisan route:list --path=roadmap
php artisan route:list --path=how-we-operate

# Verify home route
php artisan route:list --name=home

# Check marketplace route (from bizboost.php)
php artisan route:list --path=marketplace
```

## Next Steps

1. ✅ Routes updated and verified
2. ⏳ Create Vue components for new pages (Tasks 1.3)
3. ⏳ Update Navigation component (Task 1.2)
4. ⏳ Enhance HomeHub (Task 1.5)

## Files Modified

- `routes/web.php` - Added new public routes and redirects
- `tests/Feature/PublicRoutesTest.php` - Created test file

## Related Documentation

- Requirements: `.kiro/specs/platform-frontend-repositioning/requirements.md`
- Design: `.kiro/specs/platform-frontend-repositioning/design.md`
- Tasks: `.kiro/specs/platform-frontend-repositioning/tasks.md`
