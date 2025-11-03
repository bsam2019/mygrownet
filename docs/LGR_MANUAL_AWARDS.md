# LGR Manual Awards System

**Last Updated:** November 3, 2025  
**Status:** ✅ Production Ready - Fully Tested

## Overview

The LGR Manual Awards system allows administrators to award Loyalty Growth Reward bonuses directly to premium members, bypassing the normal qualification requirements. This is particularly useful during the early stages of platform launch to incentivize active members and drive engagement.

## Purpose

- **Early Stage Marketing**: Reward early adopters and active premium members
- **Performance Recognition**: Acknowledge exceptional platform engagement
- **Flexibility**: Award bonuses without waiting for 70-day cycle completion
- **Motivation**: Encourage premium tier adoption and platform activity

## How It Works

### Eligibility
- Only **Premium Starter Kit** members are eligible
- Member must have `starter_kit_tier = 'premium'`
- Member must have `status = 'active'`

### Award Process
1. Admin selects eligible premium member
2. Enters award amount (K10 - K2,100)
3. Selects award type and provides reason
4. System credits member's LGC balance immediately
5. Creates transaction record for audit trail
6. **Sends email and in-app notification to member**

### Award Types

1. **Early Adopter** - For members who joined during launch phase
2. **Performance** - For exceptional platform engagement
3. **Marketing** - For promotional campaigns and referral drives
4. **Special** - For unique circumstances or recognition

### Award Limits

- **Minimum**: K10
- **Maximum**: K2,100 (equivalent to full 70-day cycle)
- **Recommended**: K500-K1,000 for early stage incentives

## Admin Interface

### Access
Navigate to: **Admin Dashboard → LGR → Manual Awards**

Routes:
- List awards: `/admin/lgr/awards`
- Create award: `/admin/lgr/awards/create`

### Awarding Process

1. **Select Member**
   - Choose from dropdown of eligible premium members
   - View member's current referral count and LGC balance

2. **Enter Amount**
   - Specify award amount (K10 - K2,100)
   - System validates against limits

3. **Choose Award Type**
   - Select appropriate category
   - Helps with reporting and tracking

4. **Provide Reason**
   - Required field (10-500 characters)
   - Document why member is receiving award
   - Creates audit trail

5. **Confirm & Award**
   - Preview shows current and new balance
   - Confirmation required before processing

### What Happens

When an award is processed:

1. **Award Record Created** - Stored in `lgr_manual_awards` table
2. **Wallet Credited** - Amount added to user's `loyalty_points`
3. **Transaction Logged** - Entry created in `transactions` table
4. **Audit Trail** - Records admin who awarded and timestamp

## Database Structure

### Table: `lgr_manual_awards`

```sql
- id (primary key)
- user_id (foreign key to users)
- awarded_by (foreign key to users - admin)
- amount (decimal 10,2)
- award_type (enum: early_adopter, performance, special, marketing)
- reason (text)
- credited (boolean)
- credited_at (timestamp)
- created_at, updated_at
```

### Transaction Record

Each award creates a transaction with:
- Type: `lgr_manual_award`
- Description: "LGR Manual Award: {reason}"
- Metadata: Award ID, type, and admin name

## Usage Guidelines

### When to Use

✅ **Good Use Cases:**
- Rewarding first 50 premium members
- Recognizing members who refer 5+ people
- Compensating for technical issues
- Marketing campaign incentives
- Community leadership recognition

❌ **Avoid:**
- Regular, ongoing awards (use normal LGR cycle)
- Excessive amounts that create unfair advantage
- Awards without clear justification
- Favoritism or bias

### Best Practices

1. **Document Clearly** - Always provide specific reason
2. **Be Consistent** - Apply similar criteria across members
3. **Track Spending** - Monitor monthly award totals
4. **Set Budgets** - Establish monthly/quarterly limits
5. **Review Regularly** - Audit award history for patterns

### Recommended Amounts

- **Early Adopter Bonus**: K500 (one-time)
- **Referral Milestone**: K250 per milestone
- **Performance Recognition**: K300-K500
- **Marketing Campaign**: K200-K400
- **Special Recognition**: K100-K300

## Reporting & Analytics

### Available Stats

Dashboard shows:
- **Total Awarded** - Lifetime sum of all awards
- **Total Recipients** - Unique members who received awards
- **This Month** - Current month's award total

### Award History

Table displays:
- Date of award
- Recipient details
- Amount awarded
- Award type (with color coding)
- Admin who awarded
- Reason provided

### Filtering & Export

- Paginated view (20 per page)
- Filter by date, type, or recipient
- Export capability for reporting

## Integration with LGR System

### Relationship to Normal LGR

- Manual awards are **separate** from 70-day cycles
- Do not affect qualification status
- Do not count toward cycle completion
- Credited immediately to wallet

### Wallet Integration

- Awards go to `loyalty_points` balance
- Subject to same usage rules:
  - 100% usable on platform
  - Up to 40% convertible to cash
  - Minimum withdrawal: K100

### Transaction History

- Visible in member's transaction history
- Labeled as "LGR Manual Award"
- Includes reason in description

## Security & Compliance

### Access Control

- Only users with `admin` role can award
- All actions logged with admin ID
- Cannot be deleted (audit trail)

### Validation

- Verifies member is premium tier
- Checks amount within limits
- Requires reason (prevents accidental awards)
- Confirmation dialog before processing

### Audit Trail

Complete record includes:
- Who awarded (admin user)
- When awarded (timestamp)
- To whom (recipient user)
- How much (amount)
- Why (reason)
- Transaction ID (for reconciliation)

## Technical Implementation

### Files Created

**Backend:**
- `database/migrations/2025_11_03_000000_create_lgr_manual_awards_table.php`
- `app/Models/LgrManualAward.php`
- `app/Http/Controllers/Admin/LgrManualAwardController.php`
- `app/Notifications/LgrManualAwardNotification.php`

**Frontend:**
- `resources/js/pages/Admin/LGR/ManualAwards.vue`
- `resources/js/pages/Admin/LGR/AwardBonus.vue`
- `resources/js/components/Admin/LGR/AwardModal.vue`

**Routes:**
- `GET /admin/lgr/awards` - List awards
- `GET /admin/lgr/awards/create` - Award form
- `POST /admin/lgr/awards` - Process award
- `GET /admin/lgr/awards/{award}` - View details

### Key Methods

**Controller:**
- `index()` - List all awards with stats
- `create()` - Show award form with eligible members
- `store()` - Process and credit award
- `show()` - View award details

**Model:**
- Relationships: `user()`, `awardedBy()`
- Casts: amount, credited, timestamps

**Notification:**
- Email notification with award details
- In-app notification (database channel)
- Queued for async processing
- Includes direct link to wallet

### Notification Features

When an award is granted, the member receives:
- **In-app notification** visible in their notification center (same as wallet topups)
- Award amount, type, and reason
- Direct link to view their wallet balance
- Notification respects user's wallet notification preferences

The notification is created immediately and appears in the member's notification center, just like deposit/topup notifications that admins see.

## Future Enhancements

Potential additions:
- Bulk award capability (award multiple members at once)
- Award templates (predefined amounts/reasons)
- SMS notifications for high-value awards
- Award scheduling (future-dated awards)
- Approval workflow (require senior admin approval for large amounts)
- Budget limits (enforce monthly/quarterly caps)
- Scheduled awards (award on specific date)
- Recurring awards (monthly recognition)

## Support & Questions

For technical issues or questions:
- Check transaction logs in database
- Review award history in admin panel
- Verify member's wallet balance
- Contact development team if issues persist

---

## Quick Reference

**Access:** Admin Dashboard → LGR → Manual Awards  
**Eligibility:** Premium members only  
**Range:** K10 - K2,100  
**Types:** Early Adopter, Performance, Marketing, Special  
**Processing:** Immediate wallet credit  
**Audit:** Full trail maintained


## Troubleshooting

### Issue: Awards Not Saving

**Symptom:** Form shows success but no record in database

**Solution:** This was caused by incorrect column name in transactions table. Fixed in production.
- Changed `'type'` to `'transaction_type'`
- Changed `'reference'` to `'reference_number'`

**Verification:**
```bash
php artisan tinker --execute="echo 'Total awards: ' . \App\Models\LgrManualAward::count();"
```

### Issue: Member Not in Dropdown

**Possible Causes:**
1. Member is not premium tier
2. Member status is not 'active'
3. Database query issue

**Check:**
```sql
SELECT id, name, starter_kit_tier, status 
FROM users 
WHERE starter_kit_tier = 'premium' AND status = 'active';
```

### Issue: Transaction Not Created

**Check transactions table:**
```sql
SELECT * FROM transactions 
WHERE transaction_type = 'lgr_manual_award' 
ORDER BY created_at DESC LIMIT 5;
```

### Issue: Balance Not Updated

**Verify user's loyalty_points:**
```sql
SELECT id, name, loyalty_points 
FROM users 
WHERE id = [USER_ID];
```

## Related Documentation

- **Quick Admin Guide**: `docs/LGR_ADMIN_QUICK_GUIDE.md` - Quick reference for admins
- **Quickstart**: `docs/LGR_MANUAL_AWARDS_QUICKSTART.md` - Step-by-step walkthrough
- **Testing**: `docs/LGR_MANUAL_AWARDS_TESTING.md` - Testing results and verification

## Changelog

### November 3, 2025
- ✅ Fixed transaction table column name issue (`type` → `transaction_type`)
- ✅ Added comprehensive logging for debugging
- ✅ Improved error handling with try-catch blocks
- ✅ Enhanced user feedback with SweetAlert2 notifications
- ✅ Implemented searchable member selection modal
- ✅ Added balance preview in confirmation dialog
- ✅ Created email and in-app notifications for award recipients
- ✅ Created admin quick guide for easy reference
- ✅ Full testing completed - system production ready
