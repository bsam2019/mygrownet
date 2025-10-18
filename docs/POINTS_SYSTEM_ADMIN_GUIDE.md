# Points System - Admin Management Guide

## Overview

The Points System Admin Management interface allows administrators to monitor, manage, and adjust user points, levels, and qualifications.

## Accessing Admin Points Management

1. Log in as an admin user
2. Navigate to **User Management → Points Management** in the sidebar
3. You'll see the Points System Dashboard

## Admin Dashboard Features

### Statistics Overview

The dashboard displays key metrics:
- **Total LP Awarded**: All lifetime points awarded across the platform
- **Total MAP Awarded**: All monthly activity points awarded
- **Qualified This Month**: Number of users who met their MAP requirement
- **Users with Points**: Total users participating in the points system
- **Average LP**: Average lifetime points per user
- **Average MAP**: Average monthly points per user

### Level Distribution

Visual breakdown showing how many users are at each professional level:
- Associate
- Professional
- Senior
- Manager
- Director
- Executive
- Ambassador

### Recent Transactions

Real-time feed of the latest point transactions across the platform.

## Managing User Points

### Viewing All Users

1. Click **"Manage User Points"** or navigate to `/admin/points/users`
2. Use filters to find specific users:
   - **Search**: Filter by name or email
   - **Level**: Filter by professional level
   - **Qualification**: Show only qualified or unqualified users

### Viewing User Details

1. Click **"View Details"** next to any user
2. You'll see:
   - Current points (LP and MAP)
   - Active multiplier and streak
   - Qualification status
   - Level progress
   - Transaction history
   - Badges earned

## Admin Actions

### 1. Award Points

**When to use**: Reward users for special achievements, contests, or corrections

**Steps**:
1. Click **"Award Points"** button
2. Enter:
   - **LP Amount**: Lifetime points to award
   - **MAP Amount**: Monthly points to award
   - **Reason**: Required explanation for audit trail
3. Click **"Award Points"**

**Example uses**:
- Contest winner bonus
- Community contribution reward
- Correction for system error

### 2. Deduct Points

**When to use**: Remove points for violations, corrections, or penalties

**Steps**:
1. Click **"Deduct Points"** button
2. Enter:
   - **LP Amount**: Lifetime points to deduct
   - **MAP Amount**: Monthly points to deduct
   - **Reason**: Required explanation for audit trail
3. Click **"Deduct Points"**

**Example uses**:
- Policy violation penalty
- Correction for duplicate award
- Fraud prevention

### 3. Set Points

**When to use**: Manually set exact point values (overrides current values)

**Steps**:
1. Click **"Set Points"** button
2. Enter:
   - **Lifetime Points**: New LP total
   - **Monthly Points**: New MAP total
   - **Reason**: Required explanation for audit trail
3. Click **"Set Points"**

**Example uses**:
- Data migration
- Major correction
- Account reset

### 4. Advance Level

**When to use**: Manually promote user to a higher professional level

**Steps**:
1. Click **"Advance Level"** button
2. Select:
   - **New Level**: Target professional level
   - **Reason**: Required explanation for audit trail
3. Click **"Advance Level"**

**Example uses**:
- Special recognition
- Override requirements for exceptional cases
- Correction for system error

**Note**: This bypasses normal level requirements!

## Bulk Operations

### Bulk Award Points

**When to use**: Award points to multiple users at once

**Steps**:
1. Click **"Bulk Award Points"** from dashboard
2. Select users (or use filters)
3. Enter:
   - **LP Amount**: Points for each user
   - **MAP Amount**: Points for each user
   - **Reason**: Explanation for all awards
4. Confirm bulk operation

**Example uses**:
- Platform-wide bonus
- Event participation rewards
- Monthly incentives

## Best Practices

### 1. Always Provide Reasons

Every admin action requires a reason. This creates an audit trail and helps with:
- Accountability
- Dispute resolution
- System audits
- Compliance

### 2. Use Appropriate Actions

- **Award/Deduct**: For adjustments to existing points
- **Set**: Only for major corrections or migrations
- **Advance Level**: Use sparingly, only for special cases

### 3. Verify Before Acting

- Check user's current points
- Review transaction history
- Confirm the correct user
- Double-check amounts

### 4. Monitor Impact

After making changes:
- Verify the transaction appears in history
- Check user's new totals
- Confirm qualification status if relevant

## Understanding Point Sources

Common point sources you'll see:

| Source | Description |
|--------|-------------|
| `referral` | Direct referral registration |
| `spillover_referral` | Indirect referral via spillover |
| `product_sale` | Product purchase points |
| `course_completion` | Educational course completed |
| `daily_login` | Daily login bonus |
| `login_streak_7` | 7-day login streak bonus |
| `login_streak_30` | 30-day login streak bonus |
| `downline_advancement` | Downline member leveled up |
| `level_advancement` | User's own level advancement bonus |
| `badge_earned` | Badge achievement reward |
| `admin_award` | Admin manual award |
| `admin_deduction` | Admin manual deduction |
| `admin_adjustment` | Admin set points |

## Monitoring & Reports

### Statistics API

Access detailed statistics:
```
GET /admin/points/statistics?period=month
```

Periods available:
- `day`: Last 24 hours
- `week`: Last 7 days
- `month`: Current month
- `year`: Current year

Returns:
- Total LP/MAP awarded
- Transaction count
- Unique users
- Breakdown by source

## Troubleshooting

### User Not Qualifying

**Check**:
1. Current MAP vs required MAP for their level
2. Recent transaction history
3. Last activity date
4. Any deductions or penalties

**Solutions**:
- Award missing points if system error
- Verify user completed required activities
- Check for any holds or flags

### Points Not Updating

**Check**:
1. Transaction was created successfully
2. User points record exists
3. No database errors in logs

**Solutions**:
- Refresh the page
- Check application logs
- Use "Set Points" to correct if needed

### Level Not Advancing

**Check**:
1. All requirements met (LP, time, referrals, courses, downline)
2. Level progress indicators
3. Recent level check job execution

**Solutions**:
- Run manual level check: `php artisan points:check-advancements`
- Use "Advance Level" if requirements are met but system didn't trigger

## Security & Compliance

### Audit Trail

All admin actions are logged with:
- Admin user ID
- Timestamp
- Action type
- User affected
- Amounts changed
- Reason provided

### Access Control

Only users with `admin` role can:
- View points management
- Award/deduct points
- Set points manually
- Advance levels

### Data Privacy

When viewing user points:
- Only show necessary information
- Follow data protection policies
- Don't share point details publicly

## Scheduled Tasks

The system runs automated tasks:

### Daily
- `points:check-qualification` - Check monthly qualification status
- `points:check-advancements` - Check for level advancements

### Monthly (1st of month)
- `points:reset-monthly` - Reset all monthly points

**Note**: These run automatically via Laravel scheduler. No manual intervention needed.

## Support & Questions

For technical issues:
- Check application logs: `storage/logs/laravel.log`
- Review point transactions table
- Contact development team

For policy questions:
- Refer to Points System Specification
- Consult with management
- Review platform policies

---

**Remember**: With great power comes great responsibility. Admin actions directly impact user experience and platform integrity. Always act with care and transparency.
