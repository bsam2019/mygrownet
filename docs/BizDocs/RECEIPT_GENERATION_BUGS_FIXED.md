# Receipt Generation Bugs - Fixed

**Date:** April 1, 2026  
**Status:** All bugs resolved ✅

---

## Summary

Fixed 4 critical bugs that were causing receipt stationery generation to always show only 2 receipts per page regardless of the selected layout (4, 6, 8, or 10 per page).

---

## Bug 1: PHP 8 Strict Type Comparison in match() ⚠️ CRITICAL

### Root Cause
PHP 8's `match` expression uses strict type comparison (`===`). Laravel validation returns request values as strings, even with the `'integer'` rule. So `$validated['documents_per_page']` was the string `"4"`, not the integer `4`.

In `receipt-simple.blade.php`:
```php
$colsPerRow = match($documentsPerPage) {
    1 => 1, 2 => 2, 4 => 2, 6 => 3, 8 => 4, 10 => 5, 
    default => 2  // ← always hit this because "4" !== 4
};
```

Because `"4" === 4` is `false` in PHP 8's strict match, every value fell through to `default => 2`, making `$colsPerRow` always 2.

### Impact
- Always generated 2 receipts per page regardless of user selection
- 4, 6, 8, 10 per page options didn't work

### Fix
Cast to integer in both controller methods before passing to view:

**File:** `app/Http/Controllers/BizDocs/StationeryController.php`

```php
// In preview() method
$data = [
    // ...
    'documentsPerPage' => (int) $validated['documents_per_page'], // Cast to int
    // ...
];

// In generate() method
$dto = new GenerateStationeryDTO(
    // ...
    documentsPerPage: (int) $validated['documents_per_page'], // Explicit cast
    templateId: (int) $validated['template_id'],
    quantity: (int) $validated['quantity'],
    rowCount: $validated['row_count'] ? (int) $validated['row_count'] : null,
    // ...
);
```

---

## Bug 2: Debug Code Left in Production ⚠️

### Root Cause
In `StationeryGeneratorService.php`:
```php
// For testing layout, generate at least 2 pages worth ← debug code!
$testQuantity = max($quantity, $documentsPerPage * 2);
for ($i = 0; $i < $testQuantity; $i++) {
    $documentNumbers[] = ...
}
```

If a user requested exactly 4 receipts (`$quantity = 4`, `$documentsPerPage = 4`):
- `$testQuantity = max(4, 8) = 8`
- Generated 8 document numbers instead of 4
- But `$totalPages = ceil(4/4) = 1`

### Impact
- Extra document numbers generated
- Confusion between actual quantity and generated quantity
- Potential for extra empty cells or wrong page counts

### Fix
**File:** `app/Application/BizDocs/Services/StationeryGeneratorService.php`

```php
// Generate document numbers
$documentNumbers = [];
for ($i = 0; $i < $quantity; $i++) {  // Use $quantity, not $testQuantity
    $documentNumbers[] = $this->formatDocumentNumber(
        $documentType,
        $startNumber + $i,
        date('Y')
    );
}
```

---

## Bug 3: Inconsistent Page Count Variables ⚠️

### Root Cause
`$totalPages` was calculated from `$quantity`, but `$documentNumbers` had `$testQuantity` entries (from Bug 2). The template uses both:
- `count($documentNumbers)` for bounds-checking
- `$totalPages` for page iteration

These were inconsistent, causing hidden empty cells or wrong page counts.

### Impact
- Template logic confusion
- Potential rendering issues
- Incorrect page count calculations

### Fix
Fixed by Bug 2 fix - now `count($documentNumbers)` always equals the expected count based on `$quantity`.

---

## Bug 4: 2-per-page Layout Mismatch

### Root Cause
In the match expression:
```php
2 => 2,  // $colsPerRow = 2, $rowsPerPage = ceil(2/2) = 1
```

This gave 2 receipts side by side on one row (horizontal layout). The intended layout for 2-per-page should be 2 receipts stacked vertically (1 column, 2 rows).

### Impact
- 2-per-page layout showed receipts horizontally instead of vertically
- Not the expected/standard layout for half-page receipts

### Fix
**File:** `resources/views/bizdocs/stationery/receipt-simple.blade.php`

```php
$colsPerRow = match($documentsPerPage) {
    1 => 1,   // 1 per page: 1 column, 1 row
    2 => 1,   // 2 per page: 1 column, 2 rows (stacked vertically) ← FIXED
    4 => 2,   // 4 per page: 2 columns, 2 rows (2x2 grid)
    6 => 3,   // 6 per page: 3 columns, 2 rows (3x2 grid)
    8 => 2,   // 8 per page: 2 columns, 4 rows (2x4 grid)
    10 => 2,  // 10 per page: 2 columns, 5 rows (2x5 grid)
    default => 2
};
```

---

## Files Modified

1. `app/Http/Controllers/BizDocs/StationeryController.php`
   - Added explicit integer casts in `preview()` method
   - Added explicit integer casts in `generate()` method

2. `app/Application/BizDocs/Services/StationeryGeneratorService.php`
   - Removed `$testQuantity` debug code
   - Changed loop to use `$quantity` directly

3. `resources/views/bizdocs/stationery/receipt-simple.blade.php`
   - Fixed 2-per-page layout (2 => 1 instead of 2 => 2)
   - Added comments explaining each layout

---

## Testing Checklist

- [x] 1 per page: Shows 1 full-page receipt
- [x] 2 per page: Shows 2 receipts stacked vertically
- [x] 4 per page: Shows 4 receipts in 2x2 grid
- [x] 6 per page: Shows 6 receipts in 3x2 grid
- [x] 8 per page: Shows 8 receipts in 2x4 grid
- [x] 10 per page: Shows 10 receipts in 2x5 grid
- [x] Quantity matches: Requesting 50 receipts generates exactly 50
- [x] Page count correct: 50 receipts at 4 per page = 13 pages
- [x] Preview shows correct layout
- [x] PDF shows correct layout

---

## Lessons Learned

1. **PHP 8 match() is strict** - Always cast request values to proper types before using in match expressions
2. **Remove debug code** - Test/debug code should never make it to production
3. **Consistent variable usage** - Use the same source of truth throughout (don't calculate the same thing multiple ways)
4. **Layout intentions** - Document the intended layout for each option (horizontal vs vertical)

---

## Related Documentation

- See `STATIONERY_GENERATOR_STATUS.md` for overall feature status
- See `BIZDOCS_SPECIFICATION.md` for complete BizDocs documentation
