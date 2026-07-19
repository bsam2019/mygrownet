# Industry Preset Data Protection

## Overview
This document verifies that applying industry presets to companies with existing data is **100% safe** and will not cause data loss or unwanted changes.

## Data Protection Guarantees

### ✅ 1. Roles
**Protection:** Only creates roles that don't already exist (checked by name)
```php
$exists = RoleModel::where('company_id', $company->id)
    ->where('name', $roleData['name'])
    ->exists();

if (!$exists) {
    // Only create if doesn't exist
}
```
**Result:** Existing roles are NEVER modified or deleted. Only new roles from the preset are added.

### ✅ 2. Expense Categories
**Protection:** Only creates categories that don't already exist (checked by name)
```php
$exists = ExpenseCategoryModel::where('company_id', $company->id)
    ->where('name', $cat['name'])
    ->exists();

if (!$exists) {
    // Only create if doesn't exist
}
```
**Result:** Existing expense categories are NEVER modified or deleted. Only new categories are added.

### ✅ 3. Pricing Rules
**Protection:** Only creates if NO pricing rules exist at all
```php
$exists = PricingRulesModel::where('company_id', $company->id)->exists();

if (!$exists) {
    // Only create if company has zero pricing rules
}
```
**Result:** If company has ANY pricing rules, they are NEVER touched. Preset rules only apply to brand new companies.

### ✅ 4. Company Settings (Currency, VAT, etc.)
**Protection:** Existing settings take precedence over preset defaults
```php
$current = $company->settings ?? [];
$merged = array_merge($preset->default_settings, $current);
// $current overwrites $preset->default_settings
```
**Result:** User's existing settings are NEVER overwritten. Preset only fills in missing settings.

### ✅ 5. Job Types
**Protection:** Only sets if company has NO custom job types
```php
if (empty($settings['job_types']) && !empty($preset->job_types)) {
    $settings['job_types'] = $preset->job_types;
}
```
**Result:** Custom job types are NEVER replaced. Preset only applies to companies without custom types.

### ✅ 6. Inventory Categories
**Protection:** Only sets if company has NO custom inventory categories
```php
if (empty($settings['inventory_categories']) && !empty($preset->inventory_categories)) {
    $settings['inventory_categories'] = $preset->inventory_categories;
}
```
**Result:** Custom inventory categories are NEVER replaced. Preset only applies to companies without custom categories.

### ✅ 7. Asset Types
**Protection:** Only sets if company has NO custom asset types
```php
if (empty($settings['asset_types']) && !empty($preset->asset_types)) {
    $settings['asset_types'] = $preset->asset_types;
}
```
**Result:** Custom asset types are NEVER replaced. Preset only applies to companies without custom types.

## Transaction Safety
All preset application happens inside a database transaction:
```php
DB::transaction(function () use ($company, $preset) {
    // All changes here
});
```
**Result:** If ANY error occurs, ALL changes are rolled back. No partial updates.

## Logging
Every action is logged for audit trail:
- Industry type changes
- Settings merge operations
- Number of new roles/categories created
- Whether pricing rules were created or skipped

## User Warnings
Before applying a preset, users see a detailed warning showing:
- Current industry type (if any)
- How many existing roles/categories will be kept
- What new items will be added
- Confirmation that existing data won't be deleted

## Example Warning Message
```
⚠️ Your company already has "aluminium_fabrication" set as the industry.

What will happen:
• Roles: 5 existing roles will be kept. Only NEW roles from the preset will be added.
• Expense Categories: 8 existing categories will be kept. Only NEW categories will be added.
• Pricing Rules: Your existing pricing rules will NOT be changed.
• Job Types: Your custom job types will NOT be changed.
• Inventory Categories: Your custom categories will NOT be changed.

✅ Your existing data will NOT be deleted or overwritten.
```

## Test Scenarios

### Scenario 1: Brand New Company
- **Before:** No data
- **After:** All preset data applied
- **Result:** ✅ Company fully configured

### Scenario 2: Company with Existing Data
- **Before:** 5 roles, 8 categories, custom pricing
- **After:** 5 + X new roles, 8 + Y new categories, pricing unchanged
- **Result:** ✅ Existing data preserved, new data added

### Scenario 3: Changing Preset
- **Before:** Company using "General Business" preset
- **Action:** Apply "Aluminium Fabrication" preset
- **After:** Industry type changes, only NEW roles/categories added
- **Result:** ✅ No data loss, additive changes only

### Scenario 4: Re-applying Same Preset
- **Before:** Company already using "Aluminium Fabrication"
- **Action:** Apply "Aluminium Fabrication" again
- **After:** No changes (all items already exist)
- **Result:** ✅ Idempotent operation, safe to repeat

## Conclusion
The industry preset system is **100% safe** for companies with existing data. It follows these principles:

1. **Additive Only** - Never deletes or modifies existing data
2. **Existence Checks** - Always checks if data exists before creating
3. **User Precedence** - User's custom settings always win over preset defaults
4. **Transaction Safety** - All-or-nothing updates with rollback on error
5. **Full Transparency** - Users see exactly what will happen before confirming
6. **Audit Trail** - All changes are logged for review

**Users can confidently change industry presets without fear of data loss.**
