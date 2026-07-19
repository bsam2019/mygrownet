# Material Planning Module

**Status:** ✅ Backend Complete | 🎨 Frontend 60% Complete  
**Version:** 1.0.0  
**Last Updated:** April 24, 2026  
**Module ID:** `material-planning`  
**Auto-Enabled:** Yes (for aluminium_fabrication and construction industries)  
**Settings Visibility:** ✅ Fixed - Now appears in CMS Settings → Modules tab

---

## Quick Start

```bash
# 1. Run migration
php artisan migrate

# 2. Add module to database
php artisan db:seed --class=ModuleSeeder

# 3. Seed default materials (optional)
php artisan db:seed --class=DefaultMaterialsSeeder

# 4. Clear caches
php artisan route:clear && php artisan config:clear

# 5. Access
Navigate to /cms/materials
```

**Note:** The Material Planning module is automatically enabled when you select "Aluminium & Fabrication" or "Construction & Building" as your industry type in CMS Settings. You can also manually enable/disable it from CMS Settings → Modules tab.

---

## Overview

Complete material planning system for aluminium fabrication and construction businesses. Plan materials for jobs, create purchase orders, track costs, and analyze variances.

### Key Features
- **Material Library** - Categorized catalog with pricing and suppliers
- **Job Material Planning** - Plan materials needed for each job with templates
- **Purchase Orders** - Full PO workflow (Draft → Sent → Received)
- **Cost Tracking** - Planned vs Actual cost analysis with variance calculation
- **Price History** - Automatic tracking of all price changes

---

## Database Structure

### Tables Created (8)
1. `cms_material_categories` - Material organization
2. `cms_materials` - Material catalog with pricing
3. `cms_material_price_history` - Price change tracking
4. `cms_job_material_plans` - Materials linked to jobs
5. `cms_material_purchase_orders` - Purchase order headers
6. `cms_purchase_order_items` - PO line items
7. `cms_material_templates` - Reusable material lists
8. `cms_material_template_items` - Template details

---

## API Endpoints

### Materials
```
GET    /cms/materials                    - List materials
POST   /cms/materials                    - Create material
PUT    /cms/materials/{id}               - Update material
DELETE /cms/materials/{id}               - Delete material
GET    /cms/materials/{id}/price-history - Price history
POST   /cms/materials/bulk-update-prices - Bulk price update
```

### Job Material Planning
```
GET    /cms/jobs/{job}/materials              - View job materials
POST   /cms/jobs/{job}/materials              - Add material
PUT    /cms/jobs/{job}/materials/{plan}       - Update plan
DELETE /cms/jobs/{job}/materials/{plan}       - Remove material
POST   /cms/jobs/{job}/materials/apply-template - Apply template
```

### Purchase Orders
```
GET    /cms/purchase-orders              - List POs
POST   /cms/purchase-orders              - Create PO
GET    /cms/purchase-orders/{id}         - View PO
POST   /cms/purchase-orders/{id}/approve - Approve PO
POST   /cms/purchase-orders/{id}/receive - Receive materials
POST   /cms/purchase-orders/jobs/{job}   - Create PO from job
```

---

## Usage Workflows

### 1. Setup Materials
1. Navigate to Materials
2. Add materials or use seeded defaults
3. Organize into categories
4. Update prices as needed

### 2. Plan Materials for Job
1. Open job → Materials tab
2. Click "Add Material"
3. Select material, enter quantity
4. Add wastage percentage (5-10%)
5. Save

**OR** Apply a template:
1. Click "Apply Template"
2. Select template for job type
3. Enter job size (e.g., 25 m²)
4. System calculates quantities automatically

### 3. Create Purchase Order
1. From job materials page
2. Select materials (status: Planned)
3. Click "Create Purchase Order"
4. Fill supplier details
5. Set delivery date
6. Submit

### 4. Receive Materials
1. Open purchase order
2. Click "Receive"
3. Enter actual quantities received
4. System updates costs automatically

### 5. Track Costs
1. View job materials summary
2. Compare planned vs actual
3. Analyze variances
4. Use data for future estimates

---

## Frontend Components

### ✅ Completed (8)
1. Materials/Index.vue - Material listing
2. Materials/Create.vue - Add material
3. Materials/Edit.vue - Edit material
4. Jobs/Materials/Index.vue - Job planning dashboard
5. Jobs/Materials/Components/AddMaterialModal.vue
6. Jobs/Materials/Components/EditMaterialModal.vue
7. Jobs/Materials/Components/ApplyTemplateModal.vue
8. PurchaseOrders/Index.vue - PO listing

### ⏳ Remaining (5) - ~8 hours
9. PurchaseOrders/Show.vue - View PO details
10. PurchaseOrders/CreateFromJob.vue - Create PO from job
11. PurchaseOrders/Create.vue - Manual PO
12. Materials/Categories/Index.vue - Category management
13. Materials/PriceHistory.vue - Price history view

---

## Code Structure

### Backend
```
app/
├── Domain/CMS/Materials/Services/
│   ├── MaterialService.php
│   ├── JobMaterialPlanningService.php
│   └── PurchaseOrderService.php
├── Http/Controllers/CMS/
│   ├── MaterialController.php
│   ├── MaterialCategoryController.php
│   ├── JobMaterialPlanningController.php
│   └── PurchaseOrderController.php
└── Infrastructure/Persistence/Eloquent/CMS/
    ├── MaterialCategoryModel.php
    ├── MaterialModel.php
    ├── MaterialPriceHistoryModel.php
    ├── JobMaterialPlanModel.php
    ├── MaterialPurchaseOrderModel.php
    ├── PurchaseOrderItemModel.php
    ├── MaterialTemplateModel.php
    └── MaterialTemplateItemModel.php
```

### Frontend
```
resources/js/Pages/CMS/
├── Materials/
│   ├── Index.vue
│   ├── Create.vue
│   └── Edit.vue
├── Jobs/Materials/
│   ├── Index.vue
│   └── Components/
└── PurchaseOrders/
    └── Index.vue
```

---

## Default Materials

The seeder provides 55+ materials across 10 categories:

**Aluminium Fabrication:**
- Aluminium Profiles (5 types)
- Glass & Glazing (4 types)
- Hardware & Fittings (6 types)
- Sealants & Adhesives (4 types)

**Construction:**
- Cement & Concrete (5 types)
- Building Materials (5 types)
- Steel & Metal (5 types)
- Timber & Wood (4 types)
- Electrical (5 types)
- Plumbing (5 types)

---

## Best Practices

### Material Coding
- Use consistent prefixes (ALU-, GLASS-, HARD-)
- Include size/spec in code
- Keep codes short but descriptive

### Wastage Percentages
- Aluminium profiles: 5-10%
- Glass: 3-5%
- Hardware: 0-2%
- Sealants: 10-15%

### Templates
- Create for common job types
- Review quarterly
- Base on historical data

### Purchase Orders
- Group by supplier
- Negotiate bulk discounts
- Track delivery performance

---

## Troubleshooting

**Materials not showing**
- Check materials are active
- Verify company_id matches

**Cannot create PO**
- Ensure materials have status "planned"
- Check job exists

**Price history not recording**
- Use updatePrice() method on model
- Don't update price directly

**Variance calculations wrong**
- Verify actual costs entered
- Check quantities in same units

---

## Future Enhancements

- [ ] Material photos/images
- [ ] Barcode scanning
- [ ] Supplier portal
- [ ] Automated reordering
- [ ] Advanced analytics
- [ ] Mobile app
- [ ] Accounting integration
- [ ] Multi-currency support

---

## Support

**Files:**
- Migration: `database/migrations/2025_04_24_000001_create_material_planning_tables.php`
- Seeder: `database/seeders/DefaultMaterialsSeeder.php`
- Routes: `routes/cms.php`

**Status:**
- Backend: 100% Complete ✅
- Frontend: 60% Complete 🎨
- Production Ready: YES (for completed features)

---

**Module is ready to use! Start planning materials for your jobs today.** 🚀
