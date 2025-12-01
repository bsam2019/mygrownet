# Network Management - Admin User Guide

## Overview
The Network Management tool allows admins to manually reorganize the member network structure when needed, while respecting the 3x7 matrix rules.

## When to Use This Tool

### Common Scenarios:
1. **Correcting Registration Errors** - Member registered under wrong sponsor
2. **Resolving Matrix Conflicts** - When automatic placement creates undesired structure
3. **Business Reorganization** - Strategic network restructuring
4. **Fixing Data Issues** - Correcting historical placement errors

### Important Notes:
- The system **automatically assigns** new members to uplines during registration
- This tool is for **manual corrections** after registration
- Use sparingly - only when automatic placement needs adjustment
- All moves are logged for audit purposes

---

## How to Move a Member to a Different Upline

### Step-by-Step Process:

#### 1. Access Network Management
- Navigate to: **Admin Dashboard → User Management → Network Management**
- You'll see two search boxes side by side

#### 2. Search for Member to Move (Left Box)
- Type the member's name, email, or phone number
- Select the correct member from the dropdown results
- You'll see:
  - Member's name and email
  - Their current referrer/upline
  - Their current downline structure (if any)

#### 3. Search for New Upline (Right Box)
- Type the new upline's name, email, or phone number
- Select the correct upline from the dropdown results
- You'll see:
  - Upline's name and email
  - **Current downline count (X/3)** ← Important!
  - Available matrix slots

#### 4. Check Matrix Availability
Look at the new upline's stats:
- ✅ **"2/3 (1 slot available)"** - Can proceed
- ❌ **"3/3 (Matrix full)"** - Cannot add directly

**If Matrix is Full:**
You have two options:
1. Choose a different upline who has available slots
2. Move one of the upline's current downlines first to create space

#### 5. Validate the Move
- Click **"Validate Move"** button
- System checks:
  - Matrix slot availability
  - No circular references (can't move to own downline)
  - No self-referral
- You'll see:
  - ✅ Green message: "Move is valid and can proceed"
  - ❌ Red message: Reason why move cannot proceed

#### 6. Execute the Move
- Review the move details carefully
- Optional: Check **"Move entire downline tree"** if you want to move the member's downlines too
- Click **"Move Member"** button
- Confirmation message appears on success

---

## Understanding the 3x7 Matrix System

### Matrix Rules:
- Each member can have **maximum 3 direct downlines** (Level 1)
- Network extends **7 levels deep**
- Total possible network: 3,279 members

### Automatic Placement Logic:
When a new member registers with a referral code:

1. **Check Direct Slots**: Does referrer have space (< 3 direct downlines)?
   - Yes → Place as direct downline
   - No → Use spillover

2. **Spillover Placement**: Find next available slot in referrer's network
   - Searches breadth-first through levels
   - Places in first available position
   - Maintains matrix structure

### Manual Override:
This tool allows you to override automatic placement when needed.

---

## Example Scenarios

### Scenario 1: Simple Move (Matrix Has Space)

**Situation:**
- Esther registered under User A by mistake
- Should be under Esaya
- Esaya has 2/3 direct downlines (1 slot available)

**Steps:**
1. Search for "Esther" (left box)
2. Search for "Esaya" (right box)
3. Validate move → ✅ "Move is valid"
4. Click "Move Member"
5. Done! Esther is now Esaya's direct downline

**Result:**
- Esther's `referrer_id` changes from User A to Esaya
- Esther becomes Esaya's 3rd direct downline
- Esther's downlines (if any) stay with her

---

### Scenario 2: Matrix Full - Need to Reorganize

**Situation:**
- Want to move Esther to Esaya
- Esaya already has 3/3 direct downlines:
  1. Israel
  2. Titus
  3. Mumba Joe

**Solution A: Move Existing Downline First**
1. Move Israel to Esther first:
   - Search "Israel" → Search "Esther"
   - Move Israel to Esther
   - Now Esaya has 2/3 (freed up 1 slot)
2. Now move Esther to Esaya:
   - Search "Esther" → Search "Esaya"
   - Move Esther to Esaya
   - Esaya now has 3/3 again

**Final Structure:**
```
Esaya (3 direct)
├─ Esther (NEW)
│  └─ Israel (MOVED)
│     ├─ Beauty
│     ├─ Tydess
│     └─ Chataika
├─ Titus
│  └─ (his downlines)
└─ Mumba Joe
```

**Solution B: Choose Different Upline**
- Instead of Esaya, place Esther under one of Esaya's downlines
- Example: Place under Titus or Mumba Joe if they have slots

---

### Scenario 3: Moving Entire Tree

**Situation:**
- Israel has 3 downlines
- Want to move Israel AND his downlines to Esther

**Steps:**
1. Search "Israel" → Search "Esther"
2. ✅ Check "Move entire downline tree with this member"
3. Validate and move

**Result:**
- Israel moves to Esther
- All 3 of Israel's downlines move with him
- Network paths recalculated for all 4 members

---

## What Gets Updated When You Move Someone

### Database Changes:
1. **users table**
   - `referrer_id` → New upline's ID

2. **user_networks table**
   - `referrer_id` → Top-level referrer
   - `level` → Depth in network (1-7)
   - `path` → Materialized path (e.g., "1.6.7.11.135")

3. **Network Paths**
   - Automatically recalculated
   - All downlines updated if moving tree

### What Stays the Same:
- Member's account and profile
- Member's earnings history
- Member's subscriptions
- Member's points and achievements

---

## Validation Rules

The system prevents invalid moves:

### ❌ Cannot Move If:
1. **Matrix Full** - New upline already has 3 direct downlines
2. **Circular Reference** - Trying to move member to their own downline
3. **Self-Referral** - Trying to move member to themselves
4. **Invalid Users** - User or upline doesn't exist

### ✅ Can Move If:
1. New upline has available slots (< 3 direct)
2. No circular references
3. Valid user and upline IDs
4. Admin has proper permissions

---

## Best Practices

### Before Moving:
1. **Verify the Request** - Confirm with member or sponsor
2. **Check Current Structure** - Review member's current position
3. **Check Target Structure** - Ensure new upline has space
4. **Document Reason** - Note why move is needed (for audit)

### During Move:
1. **Validate First** - Always click "Validate Move" before executing
2. **Review Carefully** - Double-check member and upline names
3. **Consider Downlines** - Decide if moving tree or just member
4. **One at a Time** - Move one member at a time for safety

### After Moving:
1. **Verify Success** - Check member's new position
2. **Notify Parties** - Inform member and uplines of change
3. **Monitor Impact** - Watch for any issues in network
4. **Document** - Keep record of move for future reference

---

## Troubleshooting

### Issue: "Matrix full" Error
**Solution:** 
- Move one of the upline's current downlines first
- Or choose a different upline with available slots

### Issue: "Circular reference" Error
**Solution:**
- Cannot move member to their own downline
- Choose a different upline outside their tree

### Issue: Network tree not loading
**Solution:**
- Refresh the page
- Check if member has `referrer_id` set
- Verify `user_networks` table has records

### Issue: Move succeeded but structure looks wrong
**Solution:**
- Run: `php artisan cache:clear`
- Rebuild network: Use `TeamVolumeInitializationService`
- Check database directly for `referrer_id` values

---

## Technical Details

### Network Path Format:
```
Path: "1.6.7.11.135"
Meaning: Root(1) → Level1(6) → Level2(7) → Level3(11) → User(135)
```

### Level Calculation:
- Level 1 = Direct downline of referrer
- Level 2 = Downline of Level 1 member
- Level 3 = Downline of Level 2 member
- ... up to Level 7

### Automatic Placement Code:
Located in: `app/Models/User.php`
- Method: `findMatrixPlacement()`
- Uses breadth-first search
- Respects 3x7 matrix limits

---

## FAQ

**Q: Can I move multiple members at once?**
A: No, currently one at a time for safety. Bulk moves can be added if needed.

**Q: Will this affect commissions?**
A: Future commissions will be calculated based on new structure. Past commissions remain unchanged.

**Q: Can members see when they've been moved?**
A: Not automatically. You should notify them of the change.

**Q: Can I undo a move?**
A: Yes, just move them back to their original upline.

**Q: What if I make a mistake?**
A: Moves can be reversed. Just perform another move back to original position.

**Q: Does this affect the member's sponsor?**
A: This changes their `referrer_id` (upline in matrix). The original sponsor relationship can be tracked separately if needed.

---

## Support

For issues or questions:
1. Check this guide first
2. Review the troubleshooting section
3. Check system logs for errors
4. Contact technical support if needed

---

**Last Updated:** November 12, 2025
**Version:** 1.0
