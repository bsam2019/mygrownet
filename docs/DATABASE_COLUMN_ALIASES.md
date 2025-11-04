# Database Column Aliases

## LGR (Loyalty Growth Reward) Fields

### The Problem
The database uses legacy column names that don't clearly reflect their purpose:
- `loyalty_points` - Sounds like a points system, but actually stores monetary amounts (Kwacha)
- `loyalty_points_awarded_total` - Long and unclear
- `loyalty_points_withdrawn_total` - Long and unclear

### The Solution
We've added **accessor methods** in the User model that provide clearer aliases:

| Database Column | Accessor Alias | Description |
|----------------|----------------|-------------|
| `loyalty_points` | `lgr_balance` | Current LGR balance in Kwacha |
| `loyalty_points_awarded_total` | `lgr_awarded_total` | Total LGR ever awarded |
| `loyalty_points_withdrawn_total` | `lgr_withdrawn_total` | Total LGR withdrawn/transferred |

### Usage

**OLD WAY (Still works, but confusing):**
```php
$balance = $user->loyalty_points;
$awarded = $user->loyalty_points_awarded_total;
$withdrawn = $user->loyalty_points_withdrawn_total;
```

**NEW WAY (Recommended - Clear and readable):**
```php
$balance = $user->lgr_balance;
$awarded = $user->lgr_awarded_total;
$withdrawn = $user->lgr_withdrawn_total;
```

### Database Operations

When writing to the database, you still use the actual column names:

```php
// Updating via Eloquent
$user->update([
    'loyalty_points' => 1000,
    'loyalty_points_awarded_total' => 1000
]);

// Or via increment/decrement
$user->increment('loyalty_points', 100);
$user->decrement('loyalty_points', 50);
```

### Why Not Rename the Columns?

Renaming database columns would require:
1. Creating a migration
2. Updating ALL existing code that references these columns
3. Risk of breaking production if we miss any references
4. Downtime during migration on large tables

Using accessors gives us:
- ✅ Clear, readable code
- ✅ No breaking changes
- ✅ No database migration needed
- ✅ Both old and new names work
- ✅ Easy to adopt gradually

### Best Practices

1. **New Code**: Use the accessor aliases (`lgr_balance`, etc.)
2. **Existing Code**: Can stay as-is or be gradually updated
3. **Database Queries**: Use actual column names in raw queries
4. **Documentation**: Always refer to LGR as "amounts" or "balance", not "points"

### Example: Before and After

**Before (Confusing):**
```php
// What are loyalty points? Are they redeemable? Can I spend them?
if ($user->loyalty_points > 100) {
    $user->decrement('loyalty_points', 100);
}
```

**After (Clear):**
```php
// Ah, it's the LGR balance in Kwacha!
if ($user->lgr_balance > 100) {
    $user->decrement('loyalty_points', 100); // Still use column name for updates
}
```

## Future Considerations

If we ever need to rename the columns (e.g., during a major version upgrade), we can:
1. Create migration to rename columns
2. Remove the accessor methods
3. Update code to use new column names directly

But for now, the accessors provide the best balance of clarity and safety.
