# CMS Industry Presets System

**Last Updated:** February 10, 2026  
**Status:** Production Ready

## Overview

The Industry Presets system allows businesses to quickly configure their CMS with industry-specific roles, expense categories, job types, inventory categories, and default settings. This eliminates manual setup and ensures best practices for each industry type.

## Features

### 7 Industry Presets Available

1. **Printing & Branding** - For printing shops, branding agencies, signage businesses
2. **Construction & Building** - For construction companies, contractors, building services
3. **Retail & Shop** - For retail stores, shops, merchandise businesses
4. **Professional Services** - For consulting, accounting, legal, professional services
5. **Automotive & Repair** - For auto repair shops, mechanics, vehicle services
6. **Hospitality & Food** - For restaurants, cafes, catering, food services
7. **General Business** - Generic preset for any type of business

### Each Preset Includes

- **Predefined Roles** - Industry-specific user roles with appropriate permissions
- **Expense Categories** - Common expense types with approval workflows
- **Job Types** - Typical job/service types for the industry
- **Inventory Categories** - Relevant inventory classification
- **Asset Types** - Common asset types for the industry
- **Default Settings** - Currency, VAT, payment methods, approval thresholds

## Implementation

### Database

**Migration:** `database/migrations/2026_02_10_150000_create_cms_industry_presets_table.php`

**Table:** `cms_industry_presets`

**Fields:**
- `code` - Unique industry identifier (e.g., 'printing_branding')
- `name` - Display name
- `description` - Industry description
- `icon` - Icon identifier
- `roles` - JSON array of predefined roles
- `expense_categories` - JSON array of expense categories
- `job_types` - JSON array of common job types
- `inventory_categories` - JSON array of inventory categories
- `asset_types` - JSON array of asset types
- `default_settings` - JSON object with default company settings
- `is_active` - Active status
- `sort_order` - Display order

### Backend

**Model:** `app/Infrastructure/Persistence/Eloquent/CMS/IndustryPresetModel.php`
- Eloquent model with JSON casting
- Active and ordered scopes

**Service:** `app/Domain/CMS/Core/Services/IndustryPresetService.php`
- `getAllPresets()` - Get all active presets
- `getPresetByCode($code)` - Get specific preset
- `applyPresetToCompany($companyId, $presetCode)` - Apply preset to company
- `getPresetConfiguration($code)` - Get preset details for preview

**Controller:** `app/Http/Controllers/CMS/IndustryPresetController.php`
- `index()` - Display presets selection page
- `show($code)` - Get preset configuration (API)
- `apply(Request)` - Apply preset to current company

**Seeder:** `database/seeders/IndustryPresetsSeeder.php`
- Seeds all 7 industry presets with complete configurations

### Frontend

**Page:** `resources/js/Pages/CMS/Settings/IndustryPresets.vue`

**Features:**
- Grid display of all available presets
- Current industry indicator
- Preview modal with detailed configuration
- Apply preset functionality
- Responsive design

**Components Used:**
- Heroicons for industry icons
- Modal for preset preview
- Loading states
- Confirmation dialogs

### Routes

```php
// Settings - Industry Presets
Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('/industry-presets', [IndustryPresetController::class, 'index'])
        ->name('industry-presets.index');
    Route::get('/industry-presets/{code}', [IndustryPresetController::class, 'show'])
        ->name('industry-presets.show');
    Route::post('/industry-presets/apply', [IndustryPresetController::class, 'apply'])
        ->name('industry-presets.apply');
});
```

### Navigation

Added to CMS sidebar under Settings section:
- Icon: Cog6ToothIcon
- Label: "Industry Presets"
- Route: `cms.settings.industry-presets.index`

## Usage

### For Business Owners

1. Navigate to **Settings > Industry Presets** in CMS sidebar
2. Browse available industry presets
3. Click **View Details** to preview preset configuration
4. Click **Apply** to apply preset to your company
5. Confirm the action

### What Happens When Applied

1. Company `industry_type` is updated to preset code
2. Default settings are merged with existing company settings
3. Predefined roles are created (if they don't exist)
4. Expense categories are created (if they don't exist)
5. Job types, inventory categories, and asset types are available for reference

### Example: Printing & Branding Preset

**Roles Created:**
- Owner (full permissions)
- Manager (operations management)
- Designer (job creation and updates)
- Printer Operator (view jobs and inventory)

**Expense Categories:**
- Materials & Supplies (no approval required)
- Equipment & Maintenance (approval required > K5,000)
- Transport & Delivery (no approval required)
- Utilities (no approval required)
- Rent & Premises (approval required > K10,000)
- Marketing (approval required > K3,000)
- Office Supplies (no approval required)
- Staff Welfare (no approval required)

**Job Types:**
- Printing, Branding, Signage, T-Shirt Printing, Banner Design, Business Cards, Flyers, Posters

**Inventory Categories:**
- Ink & Toner, Paper & Cardstock, Vinyl & Stickers, T-Shirts & Apparel, Banners & Flags

**Asset Types:**
- Printer, Cutting Machine, Heat Press, Computer, Design Software

**Default Settings:**
```json
{
  "currency": "ZMW",
  "vat_enabled": true,
  "vat_rate": 16,
  "invoice_due_days": 30,
  "payment_methods": ["cash", "mobile_money", "bank_transfer"]
}
```

## Technical Details

### Preset Structure

Each preset is stored as a JSON object with the following structure:

```php
[
    'code' => 'printing_branding',
    'name' => 'Printing & Branding',
    'description' => 'For printing shops, branding agencies, and signage businesses',
    'icon' => 'printer',
    'sort_order' => 1,
    'roles' => [
        [
            'name' => 'Owner',
            'is_system_role' => true,
            'permissions' => ['*'],
            'approval_authority' => ['limit' => 999999999],
        ],
        // ... more roles
    ],
    'expense_categories' => [
        [
            'name' => 'Materials & Supplies',
            'description' => 'Ink, paper, vinyl, t-shirts, etc.',
            'requires_approval' => false,
        ],
        // ... more categories
    ],
    'job_types' => ['Printing', 'Branding', 'Signage', ...],
    'inventory_categories' => ['Ink & Toner', 'Paper & Cardstock', ...],
    'asset_types' => ['Printer', 'Cutting Machine', ...],
    'default_settings' => [
        'currency' => 'ZMW',
        'vat_enabled' => true,
        'vat_rate' => 16,
        // ... more settings
    ],
]
```

### Applying Presets

The `applyPresetToCompany()` method:

1. Validates company and preset exist
2. Updates company `industry_type`
3. Merges default settings (preserves existing settings)
4. Creates roles (skips if already exists)
5. Creates expense categories (skips if already exists)
6. Returns success/failure status

### Idempotency

The system is idempotent - applying the same preset multiple times will not create duplicates. Existing roles and categories are preserved.

## Testing

### Manual Testing Checklist

- [ ] Navigate to Industry Presets page
- [ ] Verify all 7 presets are displayed
- [ ] Click "View Details" on each preset
- [ ] Verify preview modal shows correct data
- [ ] Apply a preset to a test company
- [ ] Verify roles are created
- [ ] Verify expense categories are created
- [ ] Verify company settings are updated
- [ ] Verify current industry badge appears
- [ ] Try applying same preset again (should not duplicate)

### Seeding

Run the seeder to populate presets:

```bash
php artisan db:seed --class=IndustryPresetsSeeder
```

## Future Enhancements

### Potential Additions

1. **Custom Presets** - Allow users to create and save custom presets
2. **Preset Templates** - Export/import preset configurations
3. **Preset Versioning** - Track preset changes over time
4. **Preset Recommendations** - AI-based industry recommendations
5. **Partial Application** - Apply only specific parts of a preset
6. **Preset Marketplace** - Share presets with other users
7. **Multi-language Support** - Translate presets to different languages

### Additional Industries

Consider adding presets for:
- Healthcare & Medical
- Education & Training
- Real Estate & Property
- Agriculture & Farming
- Manufacturing & Production
- Transportation & Logistics
- IT & Technology Services

## Files Modified/Created

### Created Files

1. `database/migrations/2026_02_10_150000_create_cms_industry_presets_table.php`
2. `app/Infrastructure/Persistence/Eloquent/CMS/IndustryPresetModel.php`
3. `app/Domain/CMS/Core/Services/IndustryPresetService.php`
4. `app/Http/Controllers/CMS/IndustryPresetController.php`
5. `database/seeders/IndustryPresetsSeeder.php`
6. `resources/js/Pages/CMS/Settings/IndustryPresets.vue`
7. `docs/cms/INDUSTRY_PRESETS.md`

### Modified Files

1. `routes/cms.php` - Added industry preset routes
2. `resources/js/Layouts/CMSLayoutNew.vue` - Added Settings section with Industry Presets link

## Changelog

### February 10, 2026
- Created industry presets database table and migration
- Implemented IndustryPresetModel with JSON casting
- Created IndustryPresetService with apply logic
- Built IndustryPresetController with index, show, apply actions
- Seeded 7 industry presets with complete configurations
- Created IndustryPresets Vue page with preview modal
- Added routes for industry presets
- Updated CMS navigation with Settings section
- Created complete documentation

## Support

For issues or questions about industry presets:
1. Check this documentation
2. Review the seeder for preset examples
3. Test with the General Business preset first
4. Contact system administrator for custom preset requests
