# Investor Account Management Guide

**Date:** November 24, 2025  
**Status:** ✅ Complete - DDD Implementation

---

## Overview

The Investor Account Management system allows you to track actual investments, manage investor status (CIU → Shareholder → Exited), and automatically update investment round progress.

---

## Key Features

### ✅ Investment Tracking
- Record investor details and investment amounts
- Link investments to specific rounds
- Track equity percentages
- Optional link to user accounts

### ✅ Status Management
- **CIU (Convertible Investment Unit)** - Initial investment status
- **Shareholder** - Converted to equity shares
- **Exited** - Investor has exited

### ✅ Automatic Updates
- Investment rounds automatically update raised_amount
- Progress bars reflect real investment data
- Total invested and investor count tracked

---

## Admin Interface

### Access
```
URL: /admin/investor-accounts
Role: Admin
```

### Dashboard View
Shows:
- Total Investors count
- Total Invested amount
- List of all investor accounts
- Status badges (CIU/Shareholder/Exited)
- Quick actions (Edit/Convert/Exit)

---

## Recording a New Investment

### Step 1: Navigate to Admin Panel
```
/admin/investor-accounts → Click "Record Investment"
```

### Step 2: Fill Investor Details
```
Name: John Doe
Email: john@example.com
User ID: (optional - if investor is also a member)
```

### Step 3: Fill Investment Details
```
Investment Round: Select from active rounds
Investment Amount: K50,000
Equity Percentage: 2.5
Investment Date: 2025-11-24
```

### Step 4: Save
- System creates investor account
- Automatically updates round's raised_amount
- Investor appears in accounts list with "CIU" status

---

## Managing Investor Status

### Converting CIU to Shareholder

**When to use:**
- Milestone reached (e.g., revenue target)
- Funding round closed
- Company valuation event

**How to convert:**
1. Go to `/admin/investor-accounts`
2. Find investor with "CIU" status
3. Click "Convert" button
4. Confirm action
5. Status changes to "Shareholder"

**What it means:**
- Investor's CIU converts to actual equity shares
- Represents formal conversion event
- Tracks conversion date

### Marking Investor as Exited

**When to use:**
- Investor sells their shares
- Buyback completed
- Exit event occurs

**How to mark:**
1. Go to `/admin/investor-accounts`
2. Find investor (any status)
3. Click "Exit" button
4. Confirm action
5. Status changes to "Exited"

**What it means:**
- Investor no longer holds position
- Historical record maintained
- Tracks exit date

---

## Editing Investor Details

### What Can Be Edited
- ✅ Name
- ✅ Email
- ✅ Equity percentage

### What Cannot Be Edited
- ❌ Investment amount (data integrity)
- ❌ Investment date (historical record)
- ❌ Investment round (linked record)
- ❌ Status (use Convert/Exit actions)

**Why?**
To maintain accurate historical records and prevent data inconsistencies.

**If you need to change these:**
Create a new investment record or adjust in database directly.

---

## Domain-Driven Design Architecture

### Domain Layer
```
app/Domain/Investor/
├── Entities/
│   └── InvestorAccount.php          # Rich domain entity
├── ValueObjects/
│   └── InvestorStatus.php           # Status value object
├── Repositories/
│   └── InvestorAccountRepositoryInterface.php
└── Services/
    └── (Future: InvestorAccountService.php)
```

### Infrastructure Layer
```
app/Infrastructure/Persistence/
├── Eloquent/Investor/
│   └── InvestorAccountModel.php     # Data model
└── Repositories/Investor/
    └── EloquentInvestorAccountRepository.php
```

### Presentation Layer
```
app/Http/Controllers/Admin/
└── InvestorAccountController.php    # Admin controller

resources/js/pages/Admin/Investor/Accounts/
├── Index.vue                        # List view
├── Create.vue                       # Record investment
└── Edit.vue                         # Edit details
```

---

## Database Schema

### investor_accounts Table
```sql
CREATE TABLE investor_accounts (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NULLABLE,              -- Optional link to user
    name VARCHAR(255),                    -- Investor name
    email VARCHAR(255),                   -- Contact email
    investment_amount DECIMAL(15,2),      -- Amount invested
    investment_date DATE,                 -- When invested
    investment_round_id BIGINT,           -- Which round
    status ENUM('ciu', 'shareholder', 'exited'),
    equity_percentage DECIMAL(5,4),       -- Equity owned
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (investment_round_id) REFERENCES investment_rounds(id)
);
```

---

## Business Logic (Domain Entity)

### InvestorAccount Entity Methods

```php
// Create new investor account
InvestorAccount::create(
    userId: 123,
    name: 'John Doe',
    email: 'john@example.com',
    investmentAmount: 50000.00,
    investmentDate: new DateTimeImmutable('2025-11-24'),
    investmentRoundId: 1,
    equityPercentage: 2.5
);

// Convert to shareholder
$account->convertToShareholder();

// Mark as exited
$account->exit();

// Check status
$account->isCIU();          // true/false
$account->isShareholder();  // true/false
```

---

## Integration with Investment Rounds

### Automatic Updates

When you record an investment:
```
1. Investor account created
2. Investment round's raised_amount += investment_amount
3. Progress bar updates automatically
4. Public page shows new progress
```

### Example Flow
```
Round: "Seed Round"
Goal: K500,000
Raised: K100,000 (20%)

Record Investment: K50,000
↓
Raised: K150,000 (30%)
↓
Public page updates automatically
```

---

## API Endpoints

### List All Accounts
```
GET /admin/investor-accounts
Returns: accounts[], totalInvested, investorCount
```

### Record Investment
```
POST /admin/investor-accounts
Body: {
    name, email, user_id?,
    investment_amount, investment_date,
    investment_round_id, equity_percentage
}
```

### Update Account
```
PUT /admin/investor-accounts/{id}
Body: { name, email, equity_percentage }
```

### Convert to Shareholder
```
POST /admin/investor-accounts/{id}/convert
```

### Mark as Exited
```
POST /admin/investor-accounts/{id}/exit
```

### Delete Account
```
DELETE /admin/investor-accounts/{id}
```

---

## Use Cases

### Use Case 1: Recording First Investment
```
Scenario: Investor commits K50,000 to Seed Round

Steps:
1. Admin receives investment confirmation
2. Navigate to /admin/investor-accounts
3. Click "Record Investment"
4. Fill in investor details
5. Select "Seed Round"
6. Enter K50,000 amount
7. Calculate equity: (50,000 / 3,000,000) * 25% = 0.4167%
8. Save
9. Seed Round raised_amount updates
10. Investor appears with "CIU" status
```

### Use Case 2: Converting to Shareholder
```
Scenario: Company reaches K1M revenue milestone

Steps:
1. Navigate to /admin/investor-accounts
2. Filter for "CIU" status investors
3. Select all eligible investors
4. Click "Convert" for each
5. All CIU investors become Shareholders
6. Conversion date recorded
7. Cap table reflects new shareholders
```

### Use Case 3: Investor Exit
```
Scenario: Investor sells shares to new investor

Steps:
1. Navigate to /admin/investor-accounts
2. Find exiting investor
3. Click "Exit"
4. Status changes to "Exited"
5. Record new investor separately
6. Historical record maintained
```

---

## Reporting & Analytics

### Available Metrics
- Total investors count
- Total invested amount
- Average investment size
- Status distribution (CIU/Shareholder/Exited)
- Investment by round
- Equity distribution

### Future Enhancements
- Cap table generation
- Investor ROI tracking
- Dividend distribution
- Quarterly reports
- Investment timeline
- Exit analysis

---

## Best Practices

### 1. Accurate Record Keeping
- Record investments promptly
- Verify amounts before saving
- Double-check equity calculations
- Keep investor contact info updated

### 2. Status Management
- Convert CIUs only at defined milestones
- Document conversion reasons
- Communicate with investors before conversion
- Track conversion dates

### 3. Data Integrity
- Don't edit investment amounts after creation
- Create new records for additional investments
- Maintain historical accuracy
- Regular data backups

### 4. Communication
- Notify investors when status changes
- Send quarterly updates
- Provide access to investment details
- Maintain transparency

---

## Troubleshooting

### Issue: Investment not updating round progress
**Solution:** Check that investment_round_id is correct and round exists

### Issue: Cannot convert investor
**Solution:** Verify investor status is "CIU" before converting

### Issue: Equity percentage seems wrong
**Solution:** Recalculate: (investment / valuation) * investor_pool_percentage

### Issue: Investor not appearing in list
**Solution:** Check database, verify migration ran successfully

---

## Security Considerations

### Access Control
- Only admins can access investor accounts
- Middleware protection on all routes
- Audit log for status changes (future)

### Data Privacy
- Investor details are sensitive
- Email addresses protected
- Investment amounts confidential
- Secure database storage

---

## Testing Checklist

- [ ] Record investment with all fields
- [ ] Record investment without user_id
- [ ] Verify round raised_amount updates
- [ ] Convert CIU to shareholder
- [ ] Mark investor as exited
- [ ] Edit investor details
- [ ] View total invested and count
- [ ] Check status badges display correctly
- [ ] Verify equity percentage calculations
- [ ] Test with multiple rounds

---

## Quick Commands

```bash
# Run migration
php artisan migrate

# Check investor accounts
php artisan tinker
>>> App\Infrastructure\Persistence\Eloquent\Investor\InvestorAccountModel::all();

# Check total invested
>>> App\Infrastructure\Persistence\Eloquent\Investor\InvestorAccountModel::sum('investment_amount');

# Count investors
>>> App\Infrastructure\Persistence\Eloquent\Investor\InvestorAccountModel::count();

# Find by status
>>> App\Infrastructure\Persistence\Eloquent\Investor\InvestorAccountModel::where('status', 'ciu')->get();
```

---

## Summary

✅ **Complete investor lifecycle tracking**  
✅ **DDD architecture for maintainability**  
✅ **Automatic round updates**  
✅ **Status conversion management**  
✅ **Clean admin interface**  
✅ **Data integrity protection**  

**You can now track your entire investor base from initial CIU investment through shareholder conversion to exit!** 🎉

---

## Related Documentation

- `INVESTOR_DASHBOARD_FINAL_SUMMARY.md` - Complete system overview
- `INVESTMENT_ROUNDS_ADMIN_GUIDE.md` - Investment rounds management
- `INVESTOR_DASHBOARD_CONCEPT.md` - Original concept
- `INVESTOR_DASHBOARD_DDD_COMPLETE.md` - DDD architecture details
