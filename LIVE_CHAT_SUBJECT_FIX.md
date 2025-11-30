# Live Chat Subject Display Fix

**Issue:** Members and investors were seeing "[Member] Sammy - General Inquiry" in their conversation list instead of just "General Inquiry".

## Problem

The backend stores ticket subjects with prefixes like:
- `[Member] John Doe - General Inquiry`
- `[Investor] Jane Smith - Investment Question`

This is useful for admins to quickly identify who the ticket is from, but when members/investors view their own tickets, they shouldn't see this prefix - it looks technical and confusing.

## Solution

Added a `cleanSubjectForUser()` method to both controllers that strips the prefix when returning tickets to the user:

### Files Modified

1. **app/Http/Controllers/MyGrowNet/SupportTicketController.php**
   - Added `cleanSubjectForUser()` method
   - Updated `listJson()` to clean subjects
   - Updated `showJson()` to clean subjects

2. **app/Http/Controllers/Investor/SupportController.php**
   - Added `cleanSubjectForUser()` method
   - Updated `listJson()` to clean subjects
   - Updated `showJson()` to clean subjects

### Implementation

```php
/**
 * Remove the [Member] Name prefix from subject when showing to the member
 */
private function cleanSubjectForUser(string $subject): string
{
    // Remove patterns like "[Member] John Doe - " from the beginning
    return preg_replace('/^\[Member\]\s+[^-]+-\s*/', '', $subject);
}
```

For investors:
```php
/**
 * Remove the [Investor] Name prefix from subject when showing to the investor
 */
private function cleanSubjectForUser(string $subject): string
{
    // Remove patterns like "[Investor] John Doe - " from the beginning
    return preg_replace('/^\[Investor\]\s+[^-]+-\s*/', '', $subject);
}
```

## Result

**Before:**
- Conversation list showed: "[Member] Sammy - General Inquiry"
- Looked technical and confusing

**After:**
- Conversation list shows: "General Inquiry"
- Clean, user-friendly display

**Admin View (unchanged):**
- Admins still see the full subject with prefix: "[Member] Sammy - General Inquiry"
- This helps them quickly identify who the ticket is from

## Testing

Test the fix by:
1. Creating a new support ticket as a member
2. Opening the live chat widget
3. Checking "Your Open Conversations" section
4. Subject should show only the category (e.g., "General Inquiry")
5. Repeat for investor portal

## Notes

- Employee tickets don't have this issue - they never had the prefix
- The prefix is still stored in the database (unchanged)
- Only the display to users is cleaned
- Admin views are unaffected
