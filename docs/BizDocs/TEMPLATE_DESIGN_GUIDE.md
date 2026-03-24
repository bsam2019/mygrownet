# BizDocs Template Design Guide

**Last Updated:** March 23, 2026  
**Status:** In Progress - PDF Compatibility Fix Needed

## Overview

The BizDocs template system provides a flexible, dynamic way to create and manage document templates with completely different layouts and designs. **Templates are generic and work for ANY document type** - the same template can be used for invoices, receipts, quotations, delivery notes, etc.

### CRITICAL: PDF Compatibility Status

**Templates Fixed (1/14):** 
- ✅ **blue-professional.blade.php** - Fully working with conditional layouts

**Templates Pending Fix (13):**
All remaining templates need the same conditional layout pattern applied.

### Fix Pattern (Based on blue-professional)

Each template needs:

1. **Add `$isPdf` flag checks in CSS:**
```blade
@if(!isset($isPdf) || !$isPdf)
/* Modern CSS for HTML preview (flexbox, grid) */
@else
/* Table-based CSS for PDF */
@endif
```

2. **Add conditional HTML layouts:**
```blade
@if(!isset($isPdf) || !$isPdf)
{{-- Modern layout with flex/grid --}}
@else
{{-- Table-based layout for PDF --}}
@endif
```

3. **Logo centering technique (PDF-compatible):**
```blade
<div style="width: 80px; height: 80px; background: white; border-radius: 8px; position: relative;">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
        <img src="{{ $logoPath }}" style="max-width: 70px; max-height: 70px; width: auto; height: auto;">
    </div>
</div>
```

4. **Signature centering:**
```blade
<div style="text-align: center; margin-bottom: 10px;">
    <img src="{{ $signaturePath }}" style="max-width:180px;max-height:60px;width:auto;height:auto;">
</div>
```

### Remaining Templates to Fix

1. green-modern.blade.php - Uses `display: grid` and `display: flex`
2. purple-elegant.blade.php - Uses `display: flex` and `display: grid`
3. modern-professional.blade.php - Uses `display: flex`
4. compact-receipt.blade.php - Uses `display: flex`
5. corporate-formal.blade.php - Uses `display: flex`
6. minimal-center.blade.php - Uses `display: flex`
7. colorful-modern.blade.php - Uses `display: flex`
8. elegant-two-column.blade.php - Uses `display: flex`
9. classic-left.blade.php - Uses `display: flex`
10. bold-modern.blade.php - Uses `display: flex`
11. blue-lines-professional.blade.php - Uses `display: flex`
12. orange-creative.blade.php - Uses `display: flex`
13. red-bold.blade.php - Uses `display: flex`

### How Generic Templates Work

**Templates are layout/design templates, NOT document-type specific:**
- A user creating an invoice can select ANY template (Blue Professional, Purple Elegant, etc.)
- A user creating a receipt can select ANY template
- A user creating a quotation can select ANY template
- The document type only affects the content (numbering, labels, fields shown), not which templates are available

**Dynamic Content Based on Document Type:**
- Template displays: `{{ strtoupper($document->type()->value()) }}` - shows "INVOICE", "RECEIPT", "QUOTATION", etc.
- Document numbering: Uses the appropriate sequence (INV-001, RCT-001, QUO-001, etc.)
- Fields shown: Due date for invoices, validity date for quotations, etc.
- Labels adapt: "Bill To" for invoices, "Received From" for receipts, "Quotation For" for quotations

This design ensures:
- Maximum flexibility - users can use their favorite template design for any document
- Consistent branding - same visual style across all document types
- Simplified template management - one template works for everything

## Current Templates (15 Total)

All templates are generic and work for any document type (invoice, receipt, quotation, delivery note, etc.)

### Professional & Modern
1. **Blue Professional** - `blue-professional.blade.php` - Clean blue header design
2. **Green Modern** - `green-modern.blade.php` - Fresh green color scheme
3. **Professional** (Default) - `modern-professional.blade.php` - Classic professional layout
4. **Purple Elegant** - `purple-elegant.blade.php` - Elegant purple design
5. **Corporate Formal** - `corporate-formal.blade.php` - Formal business style

### Industry-Optimized (but work for all types)
6. **Colorful Modern (Retail)** - `colorful-modern.blade.php` - Bright, retail-friendly
7. **Elegant Two Column (Healthcare)** - `elegant-two-column.blade.php` - Clean two-column layout
8. **Compact (Education)** - `compact-receipt.blade.php` - Compact, space-efficient
9. **Simple Compact** - `compact-receipt.blade.php` - Minimalist design
10. **Classic Left** - `classic-left.blade.php` - Traditional left-aligned layout

### Bold & Creative
11. **Bold Modern (Construction)** - `bold-modern.blade.php` - Strong, bold design
12. **Blue Lines Professional** - `blue-lines-professional.blade.php` - Professional with blue accents
13. **Orange Creative** - `orange-creative.blade.php` - Creative orange theme
14. **Red Bold** - `red-bold.blade.php` - Bold red design
15. **Minimal Center** - `minimal-center.blade.php` - Centered minimalist layout

## Architecture

### Key Components

1. **Template Blade Files** (`resources/views/bizdocs/pdf/templates/*.blade.php`)
   - Self-contained HTML/CSS templates
   - Each template has completely different layout and positioning
   - Uses inline CSS for PDF compatibility

2. **Database Table** (`bizdocs_document_templates`)
   - Stores template metadata
   - `layout_file` column links to Blade file (without .blade.php extension)
   - Supports industry categories and visibility settings

3. **Template Data Wrapper** (`app/Application/BizDocs/Services/TemplateDataWrapper.php`)
   - Allows both property and method access: `$obj->property` and `$obj->property()`
   - Implements ArrayAccess for array-style access: `$obj['property']`
   - Ensures template compatibility with different data access patterns

4. **PDF Generation Service** (`app/Application/BizDocs/Services/PdfGenerationService.php`)
   - Dynamically loads templates based on `layout_file`
   - Wraps data in TemplateDataWrapper for flexible access
   - Falls back to default template if layout_file is missing

5. **Document Controller** (`app/Http/Controllers/BizDocs/DocumentController.php`)
   - Handles template selection and PDF generation
   - Provides template preview functionality
   - Manages user template preferences

## Data Structure Available to Templates

Templates receive the following data wrapped in `TemplateDataWrapper`:

```php
// Business Profile
$businessProfile->businessName()  // or $businessProfile->businessName
$businessProfile->address()
$businessProfile->phone()
$businessProfile->email()
$businessProfile->tpin()
$businessProfile->defaultCurrency()
$businessProfile->logo()
$businessProfile->signatureImage()

// Customer
$customer->name()  // or $customer->name
$customer->address()
$customer->phone()
$customer->email()
$customer->tpin()

// Document
$document->number  // or $document->number()
$document->type
$document->issueDate
$document->dueDate
$document->validityDate

// Items (array of wrapped objects)
foreach($items as $item) {
    $item->description()  // or $item->description
    $item->quantity()
    $item->unitPrice()  // in cents
    $item->taxRate()
    $item->discountAmount()  // in cents
}

// Totals (wrapped object with array access)
$totals['subtotal']  // in cents
$totals['taxTotal']  // in cents
$totals['discountTotal']  // in cents
$totals['grandTotal']  // in cents

// Image paths (base64 encoded for PDF)
$logoPath  // base64 data URI or null
$signaturePath  // base64 data URI or null
```

## Creating a New Template

### Step 1: Create the Blade File

Create a new file in `resources/views/bizdocs/pdf/templates/` with a descriptive name (e.g., `my-new-template.blade.php`):

```blade
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>My New Template</title>
    <style>
        /* All CSS must be inline or in <style> tags */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; color: #333; }
        /* Your custom styles here */
    </style>
</head>
<body>
    <!-- Your unique layout here -->
    <div class="header">
        <h1>{{ strtoupper($document->type) }}</h1>
        <p>{{ $businessProfile->businessName() }}</p>
    </div>
    
    <div class="customer">
        <strong>Bill To:</strong>
        <p>{{ $customer->name() }}</p>
    </div>
    
    <table class="items">
        <thead>
            <tr>
                <th>Description</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>{{ $item->description() }}</td>
                <td>{{ $item->quantity() }}</td>
                <td>{{ $businessProfile->defaultCurrency() }} {{ number_format($item->unitPrice() / 100, 2) }}</td>
                <td>{{ $businessProfile->defaultCurrency() }} {{ number_format(($item->quantity() * $item->unitPrice()) / 100, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="totals">
        <div>Subtotal: {{ $businessProfile->defaultCurrency() }} {{ number_format($totals['subtotal'] / 100, 2) }}</div>
        <div>Tax: {{ $businessProfile->defaultCurrency() }} {{ number_format($totals['taxTotal'] / 100, 2) }}</div>
        <div>Total: {{ $businessProfile->defaultCurrency() }} {{ number_format($totals['grandTotal'] / 100, 2) }}</div>
    </div>
</body>
</html>
```

### Step 2: Add to Database Seeder

Add your template to `database/seeders/BizDocsTemplateSeeder.php`:

```php
[
    'name' => 'My New Template',
    'document_type' => 'invoice',  // invoice, receipt, quotation, etc.
    'visibility' => 'industry',
    'owner_id' => null,
    'industry_category' => 'general_business',
    'layout_file' => 'my-new-template',  // WITHOUT .blade.php extension
    'template_structure' => json_encode([
        // Template configuration metadata
    ]),
    'is_default' => false,
],
```

### Step 3: Run the Seeder

```bash
php artisan db:seed --class=BizDocsTemplateSeeder
```

## Design Guidelines

### Layout Diversity
Each template should have a **completely different layout**, not just color variations:

- **Header positioning**: Top, side, centered, split
- **Customer block**: Left, right, boxed, inline
- **Table styling**: Bordered, borderless, striped, cards
- **Totals placement**: Right-aligned, centered, boxed, sidebar
- **Footer layout**: Centered, split, minimal, detailed

### CSS Best Practices

1. **Use inline styles or `<style>` tags** - External CSS won't work in PDFs
2. **Avoid flexbox/grid** - Use tables and absolute positioning instead
3. **Use web-safe fonts** - Arial, Verdana, Times New Roman, Courier
4. **Test with DomPDF** - Not all CSS properties are supported
5. **Use `px` units** - Avoid `rem`, `em`, `vh`, `vw`

### Color Schemes

Use distinct color palettes for each template:
- Blue: `#1e40af`, `#2563eb`, `#3b82f6`
- Green: `#059669`, `#10b981`, `#34d399`
- Purple: `#7c3aed`, `#8b5cf6`, `#a78bfa`
- Orange: `#ea580c`, `#f97316`, `#fb923c`
- Red: `#dc2626`, `#ef4444`, `#f87171`

### Responsive to Missing Data

Always check for optional fields:

```blade
@if($customer->address())
    <p>{{ $customer->address() }}</p>
@endif

@if($document->dueDate)
    <p>Due: {{ $document->dueDate->format('d M Y') }}</p>
@endif

@if($logoPath)
    <img src="{{ $logoPath }}" alt="Logo" style="width: 100px;">
@endif
```

## Template Preview System

Users can preview templates before selecting them:

1. **Gallery View** - `/bizdocs/templates/gallery?type=invoice`
2. **Preview Endpoint** - `/bizdocs/templates/{id}/preview`
3. **Sample Data** - Automatically generated for previews

## User Template Preferences

Users can set default templates per document type:
- Stored in `bizdocs_user_template_preferences` table
- Automatically selected when creating new documents
- Can be changed at any time

## Troubleshooting

### PDF Generation Fails with "Call to undefined method"

**Problem:** Template calls methods like `$businessProfile->businessName()` but data is stdClass.

**Solution:** Ensure `TemplateDataWrapper` is used in both `DocumentController.downloadPdf()` and `PdfGenerationService.generatePdf()`. The wrapper allows both property and method access.

### Template Not Showing in Gallery

**Problem:** Template exists in database but doesn't appear in gallery.

**Causes:**
1. `layout_file` column is empty or null
2. Blade file doesn't exist at the specified path
3. Template visibility is set to 'business' for a different owner

**Solution:**
```bash
# Check database
php artisan tinker --execute="DB::table('bizdocs_document_templates')->select('id', 'name', 'layout_file')->get()"

# Verify Blade file exists
ls resources/views/bizdocs/pdf/templates/
```

### PDF Shows Wrong Template

**Problem:** PDF uses default template instead of selected template.

**Causes:**
1. Document's `template_id` is null
2. Template's `layout_file` is empty
3. Blade file doesn't exist

**Solution:** Check document's template_id and verify the template's layout_file points to an existing Blade file.

### Images Not Showing in PDF

**Problem:** Logo or signature doesn't appear in generated PDF.

**Solution:** Images are converted to base64 data URIs in `DocumentController.downloadPdf()`. Check:
1. File exists in storage
2. Storage disk is accessible
3. Base64 conversion succeeds

### Only Seeing 9 Templates Instead of 15

**Problem:** Gallery shows fewer templates than expected.

**Causes:**
1. Seeder not run after adding new templates
2. Database query filtering by document_type
3. Frontend pagination or filtering

**Solution:**
```bash
# Re-run seeder
php artisan db:seed --class=BizDocsTemplateSeeder

# Check total count
php artisan tinker --execute="echo DB::table('bizdocs_document_templates')->count();"
```

## File Locations

```
app/
├── Application/BizDocs/Services/
│   ├── PdfGenerationService.php          # PDF generation logic
│   └── TemplateDataWrapper.php           # Data wrapper for templates
├── Http/Controllers/BizDocs/
│   └── DocumentController.php            # Template selection & PDF download
└── Infrastructure/BizDocs/Persistence/Eloquent/
    └── DocumentTemplateModel.php         # Template model

resources/views/bizdocs/pdf/
├── templates/                            # All template Blade files
│   ├── blue-professional.blade.php
│   ├── green-modern.blade.php
│   ├── purple-elegant.blade.php
│   ├── modern-professional.blade.php
│   ├── compact-receipt.blade.php
│   ├── corporate-formal.blade.php
│   ├── minimal-center.blade.php
│   ├── colorful-modern.blade.php
│   ├── elegant-two-column.blade.php
│   ├── bold-modern.blade.php
│   ├── classic-left.blade.php
│   ├── blue-lines-professional.blade.php
│   ├── orange-creative.blade.php
│   └── red-bold.blade.php
└── document.blade.php                    # Default fallback template

database/
├── migrations/
│   └── 2026_03_21_180000_add_layout_file_to_bizdocs_document_templates_table.php
└── seeders/
    └── BizDocsTemplateSeeder.php         # Template seeder

resources/js/pages/BizDocs/
├── Templates/
│   └── Gallery.vue                       # Template gallery component
└── Documents/
    └── Create.vue                        # Document creation with template selection
```

## Changelog

### March 22, 2026 (Update 3) - ALL TEMPLATES COMPLETE ✅
- **COMPLETED**: All 15 templates now have full logo and signature support
- Removed all emoji placeholders (🏢, 🎨, 📄, 👑, ⚡, etc.) from all templates
- All templates now use actual logo images or business initials fallback
- All templates now display digital signatures when available
- All templates support email and TPIN fields
- Templates use table-based layouts for PDF compatibility
- **Templates completed in this update:**
  - green-modern: Added logo and signature support
  - purple-elegant: Added logo and signature support
  - red-bold: Added logo and signature support, removed emojis
- **Status**: Production-ready, all 15 templates fully functional

### March 22, 2026 (Update 2)
- **CRITICAL FIX NEEDED**: All 15 templates have hardcoded emoji placeholders for logos
- Templates need to be updated to use `$logoPath` and `$signaturePath` variables
- Fallback should show business initials when logo is not available
- Signature section should display actual signature image when available

### March 22, 2026
- Fixed PDF generation error with TemplateDataWrapper
- Added ArrayAccess interface to TemplateDataWrapper for array-style access
- Updated both DocumentController and PdfGenerationService to use wrapper
- Verified all 15 templates are properly seeded
- Added comprehensive troubleshooting section
- Documented data access patterns (property, method, and array access)

### March 21, 2026
- Created 15 unique template Blade files with completely different layouts
- Added `layout_file` column to database via migration
- Updated seeder to map templates to layout files
- Modified controllers to dynamically load templates
- Created comprehensive template design guide
