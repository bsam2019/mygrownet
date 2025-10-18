# Matrix Management - Admin Actions Guide

**For**: MyGrowNet Administrators  
**Purpose**: Understanding and managing the 7-level matrix system

---

## Available Admin Actions

### 1. ✅ View Matrix Overview
**Location**: `/admin/matrix`

**What You See**:
- Total positions and filled positions
- Users in matrix count
- 7-level distribution table with:
  - Professional names (Associate → Ambassador)
  - Fill percentage for each level
  - Visual progress bars
  - Capacity vs filled count
- Commission statistics (paid and pending)
- Recent activity (placements today/week/month)

**Purpose**: Get a bird's-eye view of the entire matrix system health

---

### 2. ✅ Process Spillover Queue
**Location**: Matrix Overview page → Spillover Queue section

**What It Does**:
- Shows users waiting to be placed in the matrix
- Users who couldn't be placed directly under their sponsor
- Allows bulk or individual processing

**How to Use**:
1. Check the "Spillover Queue" section
2. Select users using checkboxes
3. Click "Process Selected" to place them automatically
4. Or click "Process Now" for individual placement

**When to Use**:
- When you see pending spillovers count > 0
- After bulk user imports
- When fixing matrix issues

---

### 3. ✅ View User Matrix Details
**Location**: Click "View Matrix" on any user in recent placements

**What You See**:
- User's complete matrix tree (up to 3 levels deep)
- Downline counts by level
- Position details (level, position number, sponsor)
- Commission history
- Performance metrics

**Purpose**: 
- Investigate specific user's network
- Verify correct placement
- Check commission calculations
- Troubleshoot user issues

---

### 4. ✅ View Recent Placements
**Location**: Matrix Overview page → Recent Matrix Placements table

**What You See**:
- Last 20 matrix placements
- User and sponsor information
- Position number (1, 2, or 3)
- Professional level name
- Active/Inactive status
- Placement timestamp

**Purpose**: Monitor recent matrix activity and verify correct placements

---

### 5. ⚠️ Reassign Matrix Position
**Location**: User matrix details page (when viewing specific user)

**What It Does**:
- Move a user to a different sponsor
- Change their position in the matrix
- Fix incorrect placements

**When to Use**:
- User was placed under wrong sponsor
- Correcting placement errors
- Resolving disputes
- Fixing orphaned positions

**How It Works**:
1. Go to user's matrix details
2. Click "Reassign Position"
3. Select new sponsor
4. Choose position (1, 2, or 3)
5. Provide reason for reassignment
6. Confirm action

**⚠️ Warning**: This affects downline commissions and should be done carefully

---

### 6. ✅ View Matrix Issues
**Location**: Matrix Overview page → Matrix Issues section

**What You See**:
- Orphaned positions (positions without proper sponsor linkage)
- Users without matrix positions
- Severity levels (high/medium/low)
- Issue counts

**Purpose**: Identify and fix matrix integrity problems

**Common Issues**:
- **Orphaned Positions**: Level > 1 positions without sponsor
  - **Fix**: Reassign to proper sponsor
- **Users Without Positions**: Users who should be in matrix but aren't
  - **Fix**: Process through spillover queue

---

### 7. ✅ View Matrix Analytics
**Location**: `/admin/matrix/analytics`

**What You See**:
- Matrix growth trends over time
- Level distribution analytics
- Commission distribution by level
- Performance metrics
- Period filters (day/week/month/year)

**Purpose**: 
- Track matrix growth
- Analyze commission patterns
- Make strategic decisions
- Generate reports

---

## Common Admin Tasks

### Task 1: Onboard New Users
**Steps**:
1. User registers with referral code
2. System automatically places them in matrix
3. If spillover occurs, check spillover queue
4. Process spillover if needed
5. Verify placement in recent placements

### Task 2: Fix Incorrect Placement
**Steps**:
1. Go to Matrix Overview
2. Find user in recent placements or search
3. Click "View Matrix"
4. Review their position
5. If incorrect, use "Reassign Position"
6. Document reason for change

### Task 3: Resolve Matrix Issues
**Steps**:
1. Check "Matrix Issues" section
2. Identify issue type and severity
3. For orphaned positions:
   - Find the position details
   - Reassign to correct sponsor
4. For users without positions:
   - Add to spillover queue
   - Process placement

### Task 4: Monitor Matrix Health
**Daily**:
- Check total positions vs filled
- Review recent placements
- Process any pending spillovers
- Check for new issues

**Weekly**:
- Review 7-level distribution
- Check fill percentages
- Analyze commission stats
- Review analytics trends

**Monthly**:
- Generate comprehensive reports
- Analyze growth patterns
- Plan capacity expansions
- Review commission distribution

---

## Understanding the 7-Level System

### Level Capacity
| Level | Name | Capacity | Cumulative |
|-------|------|----------|------------|
| 1 | Associate | 3 | 3 |
| 2 | Professional | 9 | 12 |
| 3 | Senior | 27 | 39 |
| 4 | Manager | 81 | 120 |
| 5 | Director | 243 | 363 |
| 6 | Executive | 729 | 1,092 |
| 7 | Ambassador | 2,187 | 3,279 |

**Total Network Capacity**: 3,279 members per person

### Commission Rates
| Level | Name | Rate |
|-------|------|------|
| 1 | Associate | 15% |
| 2 | Professional | 10% |
| 3 | Senior | 8% |
| 4 | Manager | 6% |
| 5 | Director | 4% |
| 6 | Executive | 3% |
| 7 | Ambassador | 2% |

**Total**: 48% distributed across 7 levels

---

## Best Practices

### DO:
✅ Process spillovers promptly (within 24 hours)  
✅ Document all position reassignments  
✅ Monitor matrix issues daily  
✅ Verify placements after bulk imports  
✅ Keep users informed of their matrix position  
✅ Review analytics regularly  

### DON'T:
❌ Reassign positions without valid reason  
❌ Ignore orphaned positions  
❌ Let spillover queue grow too large  
❌ Make changes without documenting  
❌ Forget to notify affected users  
❌ Ignore matrix integrity issues  

---

## Troubleshooting

### Issue: User Not Showing in Matrix
**Possible Causes**:
- Not yet processed from spillover queue
- Registration incomplete
- No referral code used

**Solution**:
1. Check spillover queue
2. Verify user registration
3. Manually process if needed

### Issue: Incorrect Sponsor
**Possible Causes**:
- Wrong referral code used
- System error during placement
- Manual error

**Solution**:
1. Verify correct sponsor
2. Use "Reassign Position"
3. Document the change
4. Notify user

### Issue: Orphaned Position
**Possible Causes**:
- Sponsor removed from system
- Data corruption
- Migration error

**Solution**:
1. Identify correct sponsor
2. Reassign position
3. Verify downline integrity

---

## Quick Reference

### Key Metrics to Monitor
- **Fill Percentage**: Should be growing steadily
- **Spillover Queue**: Should be < 10 at any time
- **Matrix Issues**: Should be 0 or minimal
- **Recent Activity**: Should show consistent placements

### Warning Signs
🚨 Spillover queue > 50 users  
🚨 Matrix issues with "high" severity  
🚨 Fill percentage declining  
🚨 No recent placements for extended period  
🚨 Large number of orphaned positions  

---

## Support

For technical issues or questions:
1. Check this guide first
2. Review the Matrix System Update documentation
3. Contact development team
4. Document the issue with screenshots

---

**Last Updated**: January 18, 2025  
**Version**: 1.0 - 7-Level System
