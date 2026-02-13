# CMS Landing Page & Authentication System

**Last Updated:** February 11, 2026  
**Status:** Production Ready ✅

## Changelog

### February 11, 2026
- Fixed import statement in AuthController (changed `UserModel as CmsUserModel` to `CmsUserModel`)
- Simplified registration form to only collect name, email, and password
- Company details now collected in onboarding wizard instead of registration
- Improved form field visibility with proper contrast colors
- Updated login page to match register page styling
- Applied consistent input border styling (`border-gray-300`) across all CMS forms
- Updated FormInput and FormSelect components with new styling standards

## Overview

The CMS now has its own dedicated landing page and authentication system, separate from the main MyGrowNet platform. This provides a professional, business-focused identity for the CMS product.

## Features

### Landing Page (`/cms`)

**Hero Section:**
- Compelling headline: "Manage Your Business with Confidence"
- Subheading explaining the value proposition
- Two CTA buttons: "Get Started" and "Sign In"
- Professional gradient background

**Features Showcase:**
- 6 key features with icons:
  - Job Management
  - Customer Tracking
  - Invoice & Payments
  - Financial Reports
  - Inventory Control
  - Team Collaboration

**Industry Benefits:**
- Targeted messaging for different industries
- Printing & Branding
- Construction
- Retail & Services
- Professional Services

**Pricing Preview:**
- Simple pricing structure
- Clear value proposition
- Call-to-action

### Authentication System

**Login Page (`/cms/login`):**
- Business-focused design
- Email and password fields
- Remember me checkbox
- Link to registration
- Professional branding

**Register Page (`/cms/register`):**
- Simplified registration form:
  - Owner name
  - Email
  - Password (with confirmation)
- Automatic company setup with placeholder details
- Owner role assignment
- CMS user record creation
- Automatic login after registration
- Redirect to onboarding wizard for company setup

**Design Features:**
- Professional gradient background
- Clean, minimal form design
- Proper field visibility with high contrast
- Clear validation error messages
- Loading states during submission
- Link to login page for existing users

**Logout:**
- Secure logout with session invalidation
- Redirect to landing page

## Technical Implementation

### Routes

```php
// Public routes
GET  /cms                  - Landing page
GET  /cms/login           - Login page
POST /cms/login           - Login handler
GET  /cms/register        - Register page
POST /cms/register        - Register handler

// Authenticated routes
POST /cms/logout          - Logout handler
```

### AuthController Methods

**showLogin()**
- Displays login page
- Guest middleware applied

**login(Request $request)**
- Validates credentials
- Checks CMS access
- Regenerates session
- Redirects to dashboard

**showRegister()**
- Displays registration page
- Guest middleware applied

**register(Request $request)**
- Validates registration data (name, email, password)
- Creates user account with `account_type = 'cms'`
- Creates placeholder company (name: "[User]'s Company", industry: "Not Set")
- Creates owner role with full permissions
- Creates CMS user record linking user to company
- Logs user in automatically
- Redirects to onboarding wizard

**Note:** Company details (actual name, industry, phone, etc.) are collected in the onboarding wizard, not during registration. This keeps the registration form simple and reduces friction.

**logout(Request $request)**
- Logs user out
- Invalidates session
- Redirects to landing page

### Middleware

**EnsureCmsAccess**
- Checks user authentication
- Verifies CMS access
- Checks user is active
- Checks company is active
- Shares CMS data with Inertia:
  - `cmsUser` - Current CMS user with role
  - `company` - Current company

### Database Integration

**Registration Flow:**
1. Create `User` record (main users table)
   - Sets `account_type = 'cms'`
   - Stores name, email, hashed password
2. Create `CompanyModel` record
   - Placeholder name: "[User]'s Company"
   - Industry: "Not Set"
   - Settings: `onboarding_completed = false`
3. Create `RoleModel` record (Owner role)
   - Full permissions (`['*']`)
4. Create `CmsUserModel` record
   - Links user to company
   - Assigns owner role
   - Sets `is_active = true`
5. Log user in automatically
6. Redirect to onboarding wizard

**Onboarding Wizard:**
- First step collects actual company details
- Updates company name, industry, phone
- Configures business settings
- Marks onboarding as complete

## Benefits

### Business Benefits
- **Professional Identity:** CMS feels like a standalone product
- **Clear Branding:** Separate from MyGrowNet community platform
- **B2B Focus:** Business-oriented messaging and design
- **Trust Building:** Professional appearance builds credibility

### Technical Benefits
- **Separation of Concerns:** CMS auth separate from platform auth
- **Security:** Dedicated auth flow with CMS-specific validation
- **Flexibility:** Easy to customize or white-label
- **Scalability:** Can implement different security policies

### User Experience Benefits
- **Clarity:** Users know they're accessing business software
- **Onboarding:** Smooth flow from landing → register → onboarding
- **Context:** No confusion about which system they're using
- **Professional:** Business-focused design and copy

## Usage

### For New Users

1. Visit `/cms` landing page
2. Click "Get Started"
3. Fill simplified registration form:
   - Full name
   - Email address
   - Password (and confirmation)
4. Submit registration
5. Automatically logged in
6. Redirected to onboarding wizard
7. Complete company setup in onboarding:
   - Company name
   - Industry
   - Phone number
   - Business settings

### For Existing Users

1. Visit `/cms` landing page
2. Click "Sign In"
3. Enter email and password
4. Click "Sign In"
5. Redirected to dashboard

### For Administrators

**Adding New Companies:**
- Users self-register via `/cms/register`
- Company automatically created
- Owner role automatically assigned
- Onboarding wizard guides setup

**Managing Access:**
- Use existing CMS user management
- Assign roles and permissions
- Activate/deactivate users

## Testing Checklist

### Landing Page
- [ ] Visit `/cms` - page loads correctly
- [ ] Click "Get Started" - redirects to register
- [ ] Click "Sign In" - redirects to login
- [ ] Responsive design works on mobile
- [ ] All feature icons display correctly

### Registration
- [ ] Visit `/cms/register`
- [ ] Form displays correctly with proper field visibility
- [ ] Fill name, email, password fields
- [ ] Password confirmation works
- [ ] Submit form
- [ ] User account created with `account_type = 'cms'`
- [ ] Placeholder company created
- [ ] Owner role assigned with full permissions
- [ ] CMS user record created
- [ ] Logged in automatically
- [ ] Redirected to onboarding wizard (`/cms/onboarding`)
- [ ] Validation errors display clearly
- [ ] Email uniqueness validated

### Login
- [ ] Visit `/cms/login`
- [ ] Enter valid credentials
- [ ] Click "Sign In"
- [ ] Logged in successfully
- [ ] Redirected to dashboard
- [ ] Invalid credentials show error
- [ ] Non-CMS users denied access

### Logout
- [ ] Click logout button
- [ ] Session invalidated
- [ ] Redirected to landing page
- [ ] Cannot access protected routes

### Middleware
- [ ] CMS data shared with all pages
- [ ] `cmsUser` available in Inertia
- [ ] `company` available in Inertia
- [ ] Inactive users denied access
- [ ] Suspended companies denied access

## Future Enhancements

### Short-term
- Email verification after registration
- Password reset functionality
- Social login (Google, Microsoft)
- Company logo upload during registration

### Medium-term
- Two-factor authentication
- IP whitelisting for companies
- Session management dashboard
- Login activity tracking

### Long-term
- White-label customization
- Custom domain support
- SSO integration
- Multi-factor authentication

## Files

### Vue Pages
- `resources/js/Pages/CMS/Landing.vue`
- `resources/js/Pages/CMS/Auth/Login.vue`
- `resources/js/Pages/CMS/Auth/Register.vue`

### Controllers
- `app/Http/Controllers/CMS/AuthController.php`

### Middleware
- `app/Http/Middleware/EnsureCmsAccess.php`

### Routes
- `routes/cms.php`

## Troubleshooting

### Class Not Found Errors

**Issue:** `Target class [App\Http\Controllers\CMS\AuthController] does not exist`

**Cause:** Laravel's autoload cache is stale after creating new files.

**Solution:**
```bash
# Windows - use the provided script
clear-cache.bat

# Or manually:
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
composer dump-autoload
```

Then restart your development server.

### Registration Form Fields Not Visible

**Issue:** Input fields appear invisible or hard to read.

**Cause:** Poor contrast or missing background colors.

**Solution:** The registration form now uses:
- `bg-gray-50` for input backgrounds
- `text-gray-900` for labels and input text
- `placeholder-gray-500` for placeholder text
- Proper focus states with `focus:ring-2 focus:ring-blue-500`

### Registration Fails
- Check database connection
- Verify all migrations ran
- Check validation errors in response
- Ensure email is unique

### Login Fails
- Verify credentials are correct
- Check user has CMS access (cms_users record)
- Verify user is active
- Check company is active

### Redirect Issues
- Clear browser cache
- Check route names are correct
- Verify middleware is applied
- Check session configuration

### CMS Data Not Available
- Verify middleware is applied to routes
- Check Inertia share is working
- Verify relationships are loaded
- Check user has cms_users record

## Support

For issues or questions:
1. Check this documentation
2. Review `docs/cms/IMPLEMENTATION_PROGRESS.md`
3. Check `docs/cms/README.md`
4. Review error logs
