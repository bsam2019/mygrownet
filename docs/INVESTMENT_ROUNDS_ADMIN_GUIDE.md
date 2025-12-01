# Investment Rounds Admin Management Guide

**Date:** November 23, 2025  
**Status:** ✅ Complete - Admin Can Manage Investment Opportunities

---

## Overview

Admins can now create, edit, and manage investment opportunities that appear on the investor landing page. This gives you full control over what investors see without touching code.

---

## Features

### ✅ What Admins Can Do

1. **Create Investment Rounds**
   - Set round name (e.g., "Series A - Platform Expansion")
   - Write description
   - Set goal amount
   - Define minimum investment
   - Set valuation and equity percentage
   - Specify expected ROI
   - Configure use of funds breakdown

2. **Manage Rounds**
   - Edit existing rounds
   - Activate/deactivate rounds
   - Set featured round (shows on public page)
   - Close completed rounds
   - Delete draft rounds

3. **Track Progress**
   - View raised amount
   - See progress percentage
   - Monitor goal completion

---

## Admin Interface

### Access the Admin Panel

```
URL: /admin/investment-rounds
```

**Requirements:** Admin role

### Available Actions

| Action | Description | Status Required |
|--------|-------------|-----------------|
| Create | Create new investment round | - |
| Edit | Modify round details | Any |
| Activate | Make round active | Draft |
| Set Featured | Show on public page | Active |
| Close | Mark as closed | Active |
| Delete | Remove round | Draft |

---

## Creating an Investment Round

### Step 1: Navigate to Admin Panel
```
/admin/investment-rounds
```

### Step 2: Click "Create New Round"

### Step 3: Fill in Details

**Basic Information:**
- **Name**: e.g., "Series A - Platform Expansion"
- **Description**: Brief pitch for investors
- **Goal Amount**: Total amount to raise (e.g., 500000)
- **Minimum Investment**: Smallest investment accepted (e.g., 25000)

**Valuation & Terms:**
- **Valuation**: Company valuation (e.g., 2500000)
- **Equity Percentage**: Equity offered (e.g., 20)
- **Expected ROI**: Return expectation (e.g., "3-5x")

**Use of Funds:**
Add multiple items showing how funds will be used:
- Label: "Technology & Platform Development"
- Percentage: 40
- Amount: 200000

### Step 4: Save as Draft

The round is created but not visible to investors yet.

---

## Activating an Investment Round

### Step 1: Find the Round
Go to `/admin/investment-rounds` and find your draft round.

### Step 2: Click "Activate"
This changes the status from "Draft" to "Active".

### Step 3: Set as Featured
Click "Set as Featured" to display it on the public investor page.

**Note:** Only one round can be featured at a time. Setting a new featured round automatically removes the previous one.

---

## What Investors See

When you set a round as featured, investors visiting `/investors` will see:

### Investment Opportunity Section
- Round name and description
- Progress bar showing raised amount
- Goal amount and percentage
- Minimum investment required
- Company valuation
- Equity percentage offered
- Expected ROI
- Use of funds breakdown

### Example Display
```
Series A - Platform Expansion
Seeking K500,000 to accelerate growth and expand platform capabilities

Progress: K320,000 raised (64%)
[Progress Bar]

Minimum Investment: K25,000
Valuation: K2.5M
Equity Offered: 20%
Expected ROI: 3-5x

Use of Funds:
- Technology & Platform Development: 40% (K200,000)
- Marketing & Member Acquisition: 30% (K150,000)
- Team Expansion: 20% (K100,000)
- Operations & Working Capital: 10% (K50,000)
```

---

## Database Structure

### investment_rounds Table

```sql
CREATE TABLE investment_rounds (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255),                    -- Round name
    description TEXT,                     -- Description
    goal_amount DECIMAL(15,2),           -- Target amount
    raised_amount DECIMAL(15,2),         -- Amount raised
    minimum_investment DECIMAL(15,2),    -- Minimum investment
    valuation DECIMAL(15,2),             -- Company valuation
    equity_percentage DECIMAL(5,2),      -- Equity offered
    expected_roi VARCHAR(50),            -- Expected ROI
    use_of_funds JSON,                   -- Breakdown array
    status ENUM(...),                    -- draft/active/closed/completed
    start_date DATE,                     -- When activated
    end_date DATE,                       -- Optional end date
    is_featured BOOLEAN,                 -- Show on public page
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## API Endpoints

### Admin Routes (Requires Admin Role)

```
GET    /admin/investment-rounds           # List all rounds
GET    /admin/investment-rounds/create    # Create form
POST   /admin/investment-rounds           # Store new round
GET    /admin/investment-rounds/{id}/edit # Edit form
PUT    /admin/investment-rounds/{id}      # Update round
POST   /admin/investment-rounds/{id}/activate      # Activate
POST   /admin/investment-rounds/{id}/set-featured  # Set featured
POST   /admin/investment-rounds/{id}/close         # Close
DELETE /admin/investment-rounds/{id}      # Delete
```

### Public Route

```
GET    /investors                         # Shows featured round
```

---

## Round Statuses

### Draft
- Initial status when created
- Not visible to investors
- Can be edited freely
- Can be deleted

### Active
- Round is open for investment
- Can be set as featured
- Visible to investors if featured
- Can be edited (with caution)

### Closed
- Round is no longer accepting investments
- Still visible in admin panel
- Cannot be featured
- Historical record

### Completed
- Goal reached and round finalized
- Archived status
- Cannot be modified

---

## Use of Funds Format

The use of funds is stored as JSON array:

```json
[
  {
    "label": "Technology & Platform Development",
    "percentage": 40,
    "amount": 200000
  },
  {
    "label": "Marketing & Member Acquisition",
    "percentage": 30,
    "amount": 150000
  }
]
```

**Validation:**
- Percentages should add up to 100%
- Amounts should add up to goal amount
- Each item needs label, percentage, and amount

---

## Best Practices

### 1. Keep One Active Round
- Don't confuse investors with multiple active rounds
- Close previous rounds before starting new ones
- Use featured status to highlight current opportunity

### 2. Update Progress Regularly
- Manually update raised_amount as investments come in
- Keep progress bar accurate
- Builds trust with potential investors

### 3. Clear Communication
- Write clear, compelling descriptions
- Be specific about use of funds
- Set realistic ROI expectations

### 4. Professional Presentation
- Use proper formatting
- Check spelling and grammar
- Ensure numbers are accurate

---

## Updating Raised Amount

Currently, raised amount must be updated manually by admin.

### Option 1: Direct Database Update
```sql
UPDATE investment_rounds 
SET raised_amount = 350000 
WHERE id = 1;
```

### Option 2: Admin Interface (Future Enhancement)
Add "Record Investment" button in admin panel to increment raised amount.

---

## Fallback Behavior

If no investment round is set as featured:
- Public page shows default round
- Default values are hardcoded in frontend
- Ensures page never breaks

**Default Round:**
- Name: "Series A - Platform Expansion"
- Goal: K500,000
- Raised: K320,000
- Minimum: K25,000
- Valuation: K2.5M
- Equity: 20%
- ROI: 3-5x

---

## Testing Checklist

### Admin Panel
- [ ] Can access /admin/investment-rounds
- [ ] Can create new round
- [ ] Can edit existing round
- [ ] Can activate round
- [ ] Can set as featured
- [ ] Can close round
- [ ] Can delete draft round

### Public Page
- [ ] Featured round displays correctly
- [ ] Progress bar shows accurate percentage
- [ ] All numbers format properly
- [ ] Use of funds displays correctly
- [ ] Falls back to default if no featured round

---

## Troubleshooting

### Issue: Round not showing on public page
**Solution:** 
1. Check round status is "Active"
2. Verify "is_featured" is true
3. Clear cache: `php artisan cache:clear`

### Issue: Progress bar not updating
**Solution:** Update raised_amount in database

### Issue: Use of funds not displaying
**Solution:** Ensure JSON format is correct in database

### Issue: Admin panel not accessible
**Solution:** Verify user has admin role

---

## Future Enhancements

### Phase 1 (Current)
- ✅ Create/edit rounds
- ✅ Set featured round
- ✅ Display on public page

### Phase 2 (Planned)
- [ ] Record individual investments
- [ ] Auto-update raised amount
- [ ] Investor dashboard showing their investments
- [ ] Email notifications on new investments

### Phase 3 (Planned)
- [ ] Investment analytics
- [ ] Conversion tracking
- [ ] Investor CRM integration
- [ ] Automated reporting

---

## Quick Start Guide

### For First-Time Setup

1. **Run Migration**
   ```bash
   php artisan migrate
   ```

2. **Create Your First Round**
   - Go to `/admin/investment-rounds`
   - Click "Create New Round"
   - Fill in all details
   - Save as draft

3. **Activate and Feature**
   - Click "Activate" on your round
   - Click "Set as Featured"

4. **Verify Public Display**
   - Visit `/investors`
   - Confirm your round displays correctly

5. **Share with Investors**
   - Share `/investors` URL
   - Monitor inquiries in database

---

## Support

### Documentation Files
- `INVESTOR_DASHBOARD_CONCEPT.md` - Original concept
- `INVESTOR_DASHBOARD_IMPLEMENTATION.md` - Implementation details
- `INVESTOR_DASHBOARD_DDD_COMPLETE.md` - DDD architecture
- `INVESTMENT_ROUNDS_ADMIN_GUIDE.md` - This file

### Database Tables
- `investment_rounds` - Investment opportunities
- `investor_inquiries` - Investor inquiries

### Key Files
- `app/Domain/Investor/Entities/InvestmentRound.php` - Domain entity
- `app/Http/Controllers/Admin/InvestmentRoundController.php` - Admin controller
- `resources/js/pages/Investor/PublicLanding.vue` - Public page

---

## Summary

✅ **Admin Control:** Full control over investment opportunities  
✅ **No Code Changes:** Update via admin panel  
✅ **Professional Display:** Automatic formatting on public page  
✅ **DDD Architecture:** Clean, maintainable code  
✅ **Fallback Safe:** Never breaks if no round is set  

You now have complete control over what investors see on your platform!

