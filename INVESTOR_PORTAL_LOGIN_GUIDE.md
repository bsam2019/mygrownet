# Investor Portal Login System

**Date:** November 24, 2025  
**Status:** ✅ Complete - Investor Portal with Authentication

---

## Overview

Investors can log into a dedicated portal to view their investment details, track performance, and access documents.

**Important:** This is an **admin-controlled system**. Admins create investor accounts after completing due diligence and receiving investment funds. This ensures regulatory compliance for a private limited company.

---

## How It Works

### Admin-Controlled Onboarding

**Why admin-controlled?**
- Private limited company requires approval
- Due diligence and KYC compliance
- Verify accredited investor status
- Legal agreement signing required
- Fund receipt confirmation needed

### For Investors

**1. Receive Access Code**
- After admin records your investment, you receive an access code via email
- Access code format: `[First 4 letters of email][Investor ID]`
- Example: `JOHN123` for john@example.com with ID 123

**2. Login**
- Visit `/investor/login`
- Enter your email address
- Enter your access code
- Click "Sign In"

**3. View Dashboard**
- See your investment amount
- View equity percentage
- Check investment status (CIU/Shareholder/Exited)
- Track fundraising round progress
- Calculate potential ROI

**4. Access Documents**
- View investment agreements
- Download quarterly reports (when available)
- Access tax documents
- Read company updates

---

## Access URLs

```
Public Page:      /investors
Investor Login:   /investor/login
Investor Dashboard: /investor/dashboard
Documents:        /investor/documents
```

---

## For Admins: Recording Investments

### Step 1: Record Investment
```
1. Go to /admin/investor-accounts
2. Click "Record Investment"
3. Fill in investor details
4. Save
```

### Step 2: Get Access Code
After saving, you'll see a success message with the access code:
```
"Investment recorded successfully. 
Investor access code: JOHN123 
(Send this to the investor)"
```

### Step 3: Send to Investor
Send the investor an email with:
- Their access code
- Login URL: `/investor/login`
- Instructions

---

## Access Code System

### Current Implementation (Simple)
```
Access Code = First 4 letters of email (uppercase) + Investor ID
```

**Examples:**
- `john@example.com` + ID 1 = `JOHN1`
- `sarah.smith@company.com` + ID 25 = `SARA25`
- `mike@startup.io` + ID 100 = `MIKE100`

### Security Note
This is a simple access code system for MVP. For production, you should:
- Implement proper password hashing
- Add password reset functionality
- Enable two-factor authentication
- Add rate limiting on login attempts

---

## Investor Dashboard Features

### Investment Overview
- **Investment Amount** - Total invested
- **Equity Percentage** - Ownership stake
- **Investment Status** - CIU/Shareholder/Exited
- **Investment Date** - When invested

### Round Details
- **Fundraising Progress** - Visual progress bar
- **Amount Raised** - Current total
- **Goal Amount** - Target amount
- **Company Valuation** - Current valuation
- **Investment Value** - Calculated equity value

### Quick Stats
- **Potential ROI** - Expected return multiple
- **Investment Age** - Time since investment
- **Documents** - Quick access to docs

### What's Next
- Conversion timeline (for CIU holders)
- Quarterly update schedule
- Advisory rights information

---

## Document Access

Investors can access:

### Available Now
- Investment Agreement (downloadable)

### Coming Soon
- Quarterly Performance Reports
- Tax Documents (K-1, etc.)
- Company Updates & News
- Board Meeting Minutes (if applicable)

---

## Session Management

### How Sessions Work
- Investor logs in with email + access code
- Session stores `investor_id` and `investor_email`
- Session persists across page visits
- Logout clears session

### Security
- Sessions are server-side
- No sensitive data in cookies
- Automatic timeout after inactivity
- Logout available on every page

---

## Routes & Controllers

### Routes
```php
// Login (guest only)
GET  /investor/login
POST /investor/login

// Dashboard (authenticated)
GET  /investor/dashboard
GET  /investor/documents
POST /investor/logout
```

### Controller
```
App\Http\Controllers\Investor\InvestorPortalController
- showLogin()      # Show login form
- login()          # Handle login
- dashboard()      # Show dashboard
- documents()      # Show documents
- logout()         # Handle logout
```

---

## Frontend Components

### Pages Created
```
resources/js/pages/Investor/
├── Login.vue          # Login form
├── Dashboard.vue      # Main dashboard
└── Documents.vue      # Document library
```

### Features
- Responsive design
- Clean, professional UI
- Real-time data
- Secure session handling
- Easy navigation

---

## Database Integration

### Investor Accounts Table
```sql
investor_accounts
- id
- user_id (optional)
- name
- email
- investment_amount
- investment_date
- investment_round_id
- status (ciu/shareholder/exited)
- equity_percentage
```

### Session Storage
```
Session Keys:
- investor_id: Investor account ID
- investor_email: Investor email
```

---

## User Workflows

### First Time Login
```
1. Investor receives email with access code
2. Visits /investor/login
3. Enters email and access code
4. Redirected to /investor/dashboard
5. Views investment details
6. Explores documents
```

### Returning Login
```
1. Visit /investor/login
2. Enter credentials
3. Access dashboard
4. Session persists until logout
```

### Viewing Documents
```
1. From dashboard, click "Documents"
2. Or click "View All" in Documents card
3. Browse available documents
4. Download as needed
```

---

## Email Template (For Admins)

When recording an investment, send this to the investor:

```
Subject: Welcome to MyGrowNet Investor Portal

Dear [Investor Name],

Thank you for your investment in MyGrowNet! Your investment of K[Amount] has been recorded.

ACCESS YOUR INVESTOR PORTAL:
URL: https://yourdomain.com/investor/login
Email: [investor@email.com]
Access Code: [CODE123]

In your portal, you can:
✓ View your investment details
✓ Track company performance
✓ Access investment documents
✓ Monitor your equity value
✓ Receive quarterly updates

If you have any questions, please contact:
investors@mygrownet.com

Best regards,
MyGrowNet Team
```

---

## Security Considerations

### Current Security
- ✅ Session-based authentication
- ✅ Email + access code verification
- ✅ Server-side session storage
- ✅ Logout functionality
- ✅ Protected routes

### Recommended Enhancements
- [ ] Password hashing (bcrypt)
- [ ] Password reset via email
- [ ] Two-factor authentication
- [ ] Login attempt rate limiting
- [ ] IP-based access logs
- [ ] Email notifications on login
- [ ] Session timeout (30 min inactivity)

---

## Testing Checklist

### Admin Side
- [ ] Record new investment
- [ ] Receive access code in success message
- [ ] Copy access code
- [ ] Send to investor

### Investor Side
- [ ] Visit /investor/login
- [ ] Enter email and access code
- [ ] Successfully log in
- [ ] View dashboard with correct data
- [ ] Check investment amount
- [ ] Verify equity percentage
- [ ] View round progress
- [ ] Access documents page
- [ ] Log out successfully
- [ ] Cannot access dashboard after logout

### Security
- [ ] Cannot access dashboard without login
- [ ] Invalid access code shows error
- [ ] Wrong email shows error
- [ ] Session persists across pages
- [ ] Logout clears session

---

## Troubleshooting

### Issue: Can't log in
**Solutions:**
- Verify email is correct (case-sensitive)
- Check access code format (uppercase)
- Ensure investor account exists in database
- Clear browser cache and try again

### Issue: Access code not working
**Solutions:**
- Regenerate access code: `UPPERCASE(SUBSTR(email, 1, 4)) + investor_id`
- Check investor ID in database
- Verify email hasn't changed

### Issue: Dashboard shows wrong data
**Solutions:**
- Check session has correct investor_id
- Verify investor account in database
- Clear session and re-login

---

## Future Enhancements

### Phase 1 (Current) ✅
- Basic login system
- Dashboard with investment details
- Document access
- Session management

### Phase 2 (Planned)
- [ ] Password-based authentication
- [ ] Password reset functionality
- [ ] Email notifications
- [ ] Two-factor authentication

### Phase 3 (Planned)
- [ ] Quarterly report uploads
- [ ] Document versioning
- [ ] Investment timeline
- [ ] ROI calculator

### Phase 4 (Planned)
- [ ] Investor messaging system
- [ ] Voting on company decisions
- [ ] Dividend distribution tracking
- [ ] Exit event management

---

## Quick Commands

```bash
# Check investor sessions
php artisan tinker
>>> session()->all();

# Find investor by email
>>> App\Infrastructure\Persistence\Eloquent\Investor\InvestorAccountModel::where('email', 'john@example.com')->first();

# Generate access code manually
>>> $investor = App\Infrastructure\Persistence\Eloquent\Investor\InvestorAccountModel::find(1);
>>> strtoupper(substr($investor->email, 0, 4)) . $investor->id;
```

---

## Summary

✅ **Investor login system** - Email + access code authentication  
✅ **Investor dashboard** - View investment details and performance  
✅ **Document access** - Download agreements and reports  
✅ **Session management** - Secure, persistent sessions  
✅ **Professional UI** - Clean, responsive design  
✅ **Real-time data** - Live investment and round data  

**Investors can now access their own portal to track their investment!** 🎉

---

## Related Documentation

- `INVESTOR_DASHBOARD_FINAL_SUMMARY.md` - Complete system overview
- `INVESTOR_ACCOUNT_MANAGEMENT_GUIDE.md` - Admin account management
- `INVESTMENT_ROUNDS_ADMIN_GUIDE.md` - Rounds management
- `INVESTOR_PORTAL_QUICK_START.md` - Quick reference
