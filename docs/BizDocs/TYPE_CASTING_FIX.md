# Type Casting Fix for Receipt Generation

**Date:** April 1, 2026  
**Status:** Implemented - Testing Required

---

## Problem

Even after clearing caches, receipts were still generating only 2 per page instead of the selected layout (4, 6, 8, etc.). This indicated the type casting wasn't being preserved through the entire chain.

---

## Root Cause Analysis

PHP 8's `match()` expression uses strict type comparison (`===`). If `$documentsPerPage` arrives as a string at any point in the chain, the match will fail and fall through to the default case (2).

The type can be lost at several points:
1. Controller → DTO
2. DTO → UseCase  
3. UseCase → Service
4. Service → View (Blade template)

---

## Fixes Applied

### 1. Controller - Explicit Casting

**File:** `app/Http/Controllers/BizDocs/StationeryController.php`

```php
// In generate() method
$dto = new GenerateStationeryDTO(
    businessId: $user->id,
    documentType: $validated['document_type'],
    templateId: (int) $validated['template_id'],
    quantity: (int) $validated['quantity'],
    documentsPerPage: (int) $validated['documents_per_page'], // ← Explicit cast
    startingNumber: $validated['starting_number'],
    pageSize: $validated['page_size'] ?? 'A4',
    rowCount: $validated['row_count'] ? (int) $validated['row_count'] : null,
);

// In preview() method
$data = [
    // ...
    'documentsPerPage' => (int) $validated['documents_per_page'], // ← Explicit cast
    // ...
];
```

### 2. Service - Debug Logging + Explicit Casting

**File:** `app/Application/BizDocs/Services/StationeryGeneratorService.php`

Added debug logging at the start of `generate()`:
```php
\Log::debug('StationeryGeneratorService::generate - Type check', [
    'documentsPerPage_value' => $documentsPerPage,
    'documentsPerPage_type' => gettype($documentsPerPage),
    'quantity_value' => $quantity,
    'quantity_type' => gettype($quantity),
]);
```

Added explicit cast when preparing data for view:
```php
$data = [
    // ...
    'documentsPerPage' => (int) $documentsPerPage, // ← Explicit cast
    // ...
];

\Log::debug('Data prepared for PDF', [
    'documentsPerPage' => $data['documentsPerPage'],
    'documentsPerPage_type' => gettype($data['documentsPerPage']),
]);
```

### 3. Template - Force Type Cast

**File:** `resources/views/bizdocs/stationery/receipt-simple.blade.php`

Added explicit cast at the very start of the PHP block:
```php
@php
    // Force type to integer to ensure match() works correctly
    $documentsPerPage = (int) $documentsPerPage;
    
    $colsPerRow = match($documentsPerPage) {
        1 => 1,
        2 => 1,
        4 => 2,
        6 => 3,
        8 => 2,
        10 => 2,
        default => 2
    };
    // ...
@endphp
```

Added type to debug info:
```php
$debugInfo = [
    'documentsPerPage' => $documentsPerPage,
    'documentsPerPage_type' => gettype($documentsPerPage), // ← Added
    // ...
];
```

### 4. Security Fix - Disabled enable_php

**File:** `app/Application/BizDocs/Services/StationeryGeneratorService.php`

```php
$pdf = Pdf::loadView($viewPath, $data)
    ->setOptions([
        'isHtml5ParserEnabled' => true,
        'isRemoteEnabled' => true,
        'defaultFont' => 'Arial',
        'enable_php' => false, // ← Changed from true to false
        'enable_javascript' => false,
        'enable_css_float' => true,
        'enable_automatic_breaks' => true,
    ]);
```

**Reason:** Blade has already rendered all PHP. Allowing DomPDF to execute PHP tags is redundant and potentially risky if any PHP code leaks into the rendered HTML.

---

## Testing Steps

### 1. Generate Receipt with 4 per page

1. Go to Stationery Generator
2. Select "Receipt" as document type
3. Select "4 per page - Quarter-page (Recommended for receipts)"
4. Enter quantity: 8 (should generate 2 pages)
5. Click "Generate Stationery"

**Expected Result:**
- PDF should have 2 pages
- Each page should have 4 receipts in a 2×2 grid
- Total of 8 receipts

### 2. Check Debug Logs

After generating, check the Laravel log:

```bash
tail -100 storage/logs/laravel.log | grep -E "(Type check|Data prepared)"
```

**Expected Output:**
```
[2026-04-01 ...] local.DEBUG: StationeryGeneratorService::generate - Type check
{"documentsPerPage_value":4,"documentsPerPage_type":"integer","quantity_value":8,"quantity_type":"integer"}

[2026-04-01 ...] local.DEBUG: Data prepared for PDF
{"documentsPerPage":4,"documentsPerPage_type":"integer"}
```

All types should show as `"integer"`, not `"string"`.

### 3. Test Other Layouts

Test each layout to ensure they all work:

| Layout | Expected Grid | Test Quantity | Expected Pages |
|--------|---------------|---------------|----------------|
| 1 per page | 1×1 | 3 | 3 pages |
| 2 per page | 1×2 (vertical) | 6 | 3 pages |
| 4 per page | 2×2 | 8 | 2 pages |
| 6 per page | 3×2 | 12 | 2 pages |
| 8 per page | 2×4 | 16 | 2 pages |
| 10 per page | 2×5 | 20 | 2 pages |

### 4. Verify Debug Comment in PDF

Open the generated PDF in a text editor and look for the debug comment near the top:

```html
<!-- DEBUG: {"documentsPerPage":4,"documentsPerPage_type":"integer",...} -->
```

This confirms the type is correct when the template renders.

---

## If Still Getting 2 Per Page

If you're still getting only 2 receipts per page after all these fixes:

### Check 1: Verify Caches Cleared
```bash
php artisan view:clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

### Check 2: Check the Debug Comment in PDF

Open the PDF in a text editor (not PDF viewer) and search for the debug comment. It will show:
- What value `documentsPerPage` has
- What type it is
- What `colsPerRow` was calculated as

### Check 3: Check Laravel Logs

The debug logs will show if the type is being lost somewhere in the chain.

### Check 4: Verify DTO Type Hints

Check `app/Application/BizDocs/DTOs/GenerateStationeryDTO.php`:
```php
public readonly int $documentsPerPage, // ← Should have 'int' type hint
```

---

## Files Modified

1. `app/Http/Controllers/BizDocs/StationeryController.php`
   - Added explicit casts in both `preview()` and `generate()` methods

2. `app/Application/BizDocs/Services/StationeryGeneratorService.php`
   - Added debug logging at method start
   - Added explicit cast when preparing data array
   - Added debug logging after data preparation
   - Changed `enable_php` from `true` to `false`

3. `resources/views/bizdocs/stationery/receipt-simple.blade.php`
   - Added explicit cast at start of PHP block
   - Added type to debug info output

---

## Next Steps

1. ✅ Clear all caches (done)
2. ⏳ Generate a test PDF with 4 per page
3. ⏳ Check the debug logs
4. ⏳ Verify the PDF has 4 receipts per page
5. ⏳ Test other layouts

---

## Related Documentation

- See `RECEIPT_GENERATION_BUGS_FIXED.md` for the original bug fixes
- See `STATIONERY_GENERATOR_STATUS.md` for overall feature status
