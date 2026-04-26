# Aluminium Fabrication Workflow - Multitenant CMS Analysis

**Date:** April 17, 2026  
**Purpose:** Analyze existing multitenant architecture and design aluminium fabrication workflow  
**Target Tenant:** Geopamu Aluminium & Construction

---

## Executive Summary

The MyGrowNet CMS is a **fully multitenant system** where each company operates as an isolated tenant. The system uses `company_id` as the tenant identifier and enforces tenant isolation through:
- Database-level scoping (WHERE company_id = X)
- Model scopes (`scopeForCompany`)
- Session-based company context
- Middleware enforcement

**Key Finding:** The existing architecture is **ready for aluminium fabrication workflow** with minimal changes. We can reuse:
- Customer module (clients)
- Job module (work orders)
- Invoice module (billing)
- Quotation module (quotes)

---

## Current Multitenant Architecture

### 1. Tenant Identification

**Primary Key:** `company_id` (integer, foreign key to `cms_companies.id`)

**How it works:**
```php
// Every CMS table has company_id
cms_customers.company_id
cms_jobs.company_id
cms_invoices.company_id
cms_quotations.company_id
// ... all CMS tables
```

**Session Storage:**
```php
// Set in middleware (EnsureCmsAccess)
session(['cms_company_id' => $cmsUser->company_id]);

// Accessed via
auth()->user()->cmsUser->company_id
```

### 2. Tenant Scoping Enforcement

#### A. Middleware Level
**File:** `app/Http/Middleware/EnsureCmsAccess.php`

```php
// Shares company data with all CMS views
Inertia::share([
    'cmsUser' => fn () => $user->cmsUser->load('role'),
    'company' => fn () => $user->cmsUser->company,
]);
```

#### B. Model Level
**Pattern:** All CMS models have `scopeForCompany` method

```php
// Example from CustomerModel
public function scopeForCompany(Builder $query, int $companyId): Builder
{
    return $query->where('company_id', $companyId);
}

// Usage
$customers = CustomerModel::forCompany($companyId)->get();
```

#### C. Query Level
**Pattern:** Explicit WHERE clauses in controllers

```php
// Example from controllers
$jobs = JobModel::where('company_id', $companyId)
    ->where('status', 'in_progress')
    ->get();
```

**⚠️ Important:** There is **NO global scope** automatically applied. Tenant scoping must be **explicitly enforced** in every query.

### 3. Existing Modules (Tenant-Scoped)

#### Customers Module
**Table:** `cms_customers`
**Fields:**
- `company_id` ✅ Tenant scoped
- `customer_number` (auto-generated per company)
- `name`, `email`, `phone`, `address`
- `credit_limit`, `outstanding_balance`
- `status` (active/inactive)

**Relationships:**
- `hasMany` jobs
- `hasMany` invoices
- `hasMany` quotations

#### Jobs Module
**Table:** `cms_jobs`
**Fields:**
- `company_id` ✅ Tenant scoped
- `customer_id` (FK to cms_customers)
- `job_number` (auto-generated per company)
- `job_type`, `description`
- `quoted_value`, `actual_value`
- `material_cost`, `labor_cost`, `overhead_cost`
- `total_cost`, `profit_amount`, `profit_margin`
- `status` (pending, in_progress, completed, cancelled)
- `assigned_to`, `created_by`
- `started_at`, `completed_at`, `deadline`

**Relationships:**
- `belongsTo` company
- `belongsTo` customer
- `hasOne` invoice
- `hasMany` attachments

#### Quotations Module
**Table:** `cms_quotations`
**Fields:**
- `company_id` ✅ Tenant scoped
- `customer_id` (FK to cms_customers)
- `quotation_number` (auto-generated per company)
- `quotation_date`, `expiry_date`
- `subtotal`, `tax_amount`, `discount_amount`, `total_amount`
- `status` (draft, sent, approved, rejected, expired)
- `converted_to_job_id` (FK to cms_jobs)
- `created_by`, `approved_by`, `approved_at`

**Relationships:**
- `belongsTo` company
- `belongsTo` customer
- `hasMany` items (quotation_items)
- `belongsTo` convertedToJob

#### Invoices Module
**Table:** `cms_invoices`
**Fields:**
- `company_id` ✅ Tenant scoped
- `customer_id` (FK to cms_customers)
- `job_id` (FK to cms_jobs)
- `invoice_number` (auto-generated per company)
- `invoice_date`, `due_date`
- `subtotal`, `tax_amount`, `total_amount`
- `amount_paid`, `amount_due`
- `status` (draft, sent, paid, partial, overdue, cancelled)

**Relationships:**
- `belongsTo` company
- `belongsTo` customer
- `belongsTo` job
- `hasMany` items (invoice_items)
- `hasMany` payments

### 4. Company Settings

**Table:** `cms_companies`
**Settings Field:** `settings` (JSON column)

**Current Usage:**
```php
// Settings are stored as JSON
$company->settings = [
    'invoice_prefix' => 'INV',
    'quotation_prefix' => 'QUO',
    'tax_rate' => 16.0,
    // ... custom settings per company
];
```

**✅ Perfect for:** Storing per-tenant pricing rules, cost rules, profit margins

---

## Aluminium Fabrication Workflow Design

### Required Workflow

```
Measurement → Quotation → Client Approval → Work Order → 
Fabrication → Installation → Completion → Invoice
```

### Database Schema (New Tables)

#### 1. Measurement Records
**Table:** `cms_measurement_records`

```sql
CREATE TABLE cms_measurement_records (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    company_id BIGINT UNSIGNED NOT NULL,  -- ✅ TENANT SCOPED
    customer_id BIGINT UNSIGNED NOT NULL,
    measurement_number VARCHAR(50) NOT NULL,
    project_name VARCHAR(255) NOT NULL,
    location VARCHAR(255),
    measured_by BIGINT UNSIGNED,  -- FK to cms_users
    measurement_date DATE NOT NULL,
    status ENUM('draft', 'completed', 'quoted') DEFAULT 'draft',
    notes TEXT,
    created_by BIGINT UNSIGNED,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (company_id) REFERENCES cms_companies(id),
    FOREIGN KEY (customer_id) REFERENCES cms_customers(id),
    FOREIGN KEY (measured_by) REFERENCES cms_users(id),
    FOREIGN KEY (created_by) REFERENCES cms_users(id),
    
    UNIQUE KEY unique_measurement_number (company_id, measurement_number),
    INDEX idx_company_customer (company_id, customer_id),
    INDEX idx_company_status (company_id, status)
);
```

#### 2. Measurement Items
**Table:** `cms_measurement_items`

```sql
CREATE TABLE cms_measurement_items (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    company_id BIGINT UNSIGNED NOT NULL,  -- ✅ TENANT SCOPED
    measurement_id BIGINT UNSIGNED NOT NULL,
    location_name VARCHAR(255) NOT NULL,
    type ENUM('sliding_window', 'casement_window', 'sliding_door', 'hinged_door', 'other') NOT NULL,
    
    -- Measurements (in mm or cm)
    width_top DECIMAL(10,2) NOT NULL,
    width_middle DECIMAL(10,2) NOT NULL,
    width_bottom DECIMAL(10,2) NOT NULL,
    height_left DECIMAL(10,2) NOT NULL,
    height_right DECIMAL(10,2) NOT NULL,
    
    -- Auto-calculated fields
    final_width DECIMAL(10,2) GENERATED ALWAYS AS (LEAST(width_top, width_middle, width_bottom)) STORED,
    final_height DECIMAL(10,2) GENERATED ALWAYS AS (LEAST(height_left, height_right)) STORED,
    area DECIMAL(10,4) GENERATED ALWAYS AS ((final_width * final_height) / 1000000) STORED,  -- in m²
    
    quantity INT NOT NULL DEFAULT 1,
    total_area DECIMAL(10,4) GENERATED ALWAYS AS (area * quantity) STORED,  -- in m²
    
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (company_id) REFERENCES cms_companies(id),
    FOREIGN KEY (measurement_id) REFERENCES cms_measurement_records(id) ON DELETE CASCADE,
    
    INDEX idx_company_measurement (company_id, measurement_id)
);
```

#### 3. Pricing Rules (Per Tenant)
**Table:** `cms_pricing_rules`

```sql
CREATE TABLE cms_pricing_rules (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    company_id BIGINT UNSIGNED NOT NULL UNIQUE,  -- ✅ ONE PER TENANT
    
    -- Selling prices per m² (what customer pays)
    sliding_window_rate DECIMAL(10,2) NOT NULL DEFAULT 0,
    casement_window_rate DECIMAL(10,2) NOT NULL DEFAULT 0,
    sliding_door_rate DECIMAL(10,2) NOT NULL DEFAULT 0,
    hinged_door_rate DECIMAL(10,2) NOT NULL DEFAULT 0,
    other_rate DECIMAL(10,2) NOT NULL DEFAULT 0,
    
    -- Internal costs per m² (for profit calculation - NOT shown to client)
    material_cost_per_m2 DECIMAL(10,2) NOT NULL DEFAULT 0,
    labour_cost_per_m2 DECIMAL(10,2) NOT NULL DEFAULT 0,
    overhead_cost_per_m2 DECIMAL(10,2) NOT NULL DEFAULT 0,
    
    -- Profit rules
    minimum_profit_percent DECIMAL(5,2) NOT NULL DEFAULT 20.00,
    
    -- Tax
    tax_rate DECIMAL(5,2) NOT NULL DEFAULT 16.00,
    
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE
);
```

### Integration with Existing Modules

#### 1. Quotation Generation from Measurement

**Flow:**
```
Measurement Record (completed)
    ↓
Generate Quotation Button
    ↓
Fetch tenant pricing rules
    ↓
Calculate totals per item type
    ↓
Create cms_quotation record
    ↓
Create cms_quotation_items records
    ↓
Link: quotation.measurement_id = measurement_record.id
```

**Calculation Logic:**
```php
// For each measurement item
$sellingPrice = $item->total_area * $pricingRules->{$item->type . '_rate'};

// Internal costing (NOT shown to client)
$materialCost = $item->total_area * $pricingRules->material_cost_per_m2;
$labourCost = $item->total_area * $pricingRules->labour_cost_per_m2;
$overheadCost = $item->total_area * $pricingRules->overhead_cost_per_m2;
$totalCost = $materialCost + $labourCost + $overheadCost;

$profit = $sellingPrice - $totalCost;
$profitPercent = ($profit / $sellingPrice) * 100;

// Validate minimum profit
if ($profitPercent < $pricingRules->minimum_profit_percent) {
    // Warning: Profit margin below minimum
}
```

#### 2. Work Order (Job) Conversion

**Flow:**
```
Quotation (approved by client)
    ↓
Convert to Job Button
    ↓
Create cms_job record
    ↓
job.quoted_value = quotation.total_amount
job.status = 'pending'
job.customer_id = quotation.customer_id
    ↓
Update quotation.converted_to_job_id
```

**Job Status Flow:**
```
pending → materials_ordered → fabrication → ready_for_install → 
installing → completed → invoiced
```

#### 3. Invoice Generation

**Flow:**
```
Job (status = completed)
    ↓
Generate Invoice Button
    ↓
Create cms_invoice record
    ↓
invoice.job_id = job.id
invoice.total_amount = job.actual_value
    ↓
Create invoice items from job details
```

---

## Security & Tenant Isolation

### Critical Rules

1. **ALL queries MUST include company_id filter**
   ```php
   // ✅ CORRECT
   $measurements = MeasurementRecordModel::where('company_id', $companyId)->get();
   
   // ❌ WRONG - exposes all tenants
   $measurements = MeasurementRecordModel::all();
   ```

2. **Use model scopes consistently**
   ```php
   // Add to all new models
   public function scopeForCompany(Builder $query, int $companyId): Builder
   {
       return $query->where('company_id', $companyId);
   }
   ```

3. **Validate tenant ownership before updates/deletes**
   ```php
   $measurement = MeasurementRecordModel::where('id', $id)
       ->where('company_id', $companyId)
       ->firstOrFail();  // 404 if not found or wrong tenant
   ```

4. **Foreign key constraints must respect tenant boundaries**
   ```sql
   -- When linking measurement to quotation
   -- Ensure both belong to same company_id
   ```

5. **No global pricing - each tenant has own rules**
   ```php
   // ✅ CORRECT
   $pricing = PricingRulesModel::where('company_id', $companyId)->first();
   
   // ❌ WRONG
   $pricing = PricingRulesModel::first();  // Could get another tenant's pricing
   ```

### Middleware Protection

**Existing:** `EnsureCmsAccess` middleware already enforces:
- User must have active CMS account
- User's company must have valid access
- Company ID is available in session

**Additional:** Create `EnsureCompanyOwnership` middleware for sensitive operations:
```php
// Validate resource belongs to user's company
if ($resource->company_id !== auth()->user()->cmsUser->company_id) {
    abort(403, 'Unauthorized access to resource');
}
```

---

## UI Requirements

### 1. Company Settings Page

**Location:** `/cms/settings/pricing`

**Features:**
- Edit pricing rules (rates per m²)
- Edit cost rules (material, labour, overhead)
- Set minimum profit percentage
- Set tax rate
- Preview profit margins

**Access Control:** Only Owner/Admin roles

### 2. Measurements Page

**Location:** `/cms/measurements`

**Features:**
- List all measurements (tenant-scoped)
- Create new measurement
- Edit measurement
- Add/remove measurement items
- Auto-calculate dimensions
- Generate quotation from measurement
- Print measurement report

**Filters:**
- By customer
- By status (draft, completed, quoted)
- By date range
- By project name

### 3. Quotations Page (Enhanced)

**Location:** `/cms/quotations`

**Existing Features:**
- List quotations
- Create/edit quotations
- Send to customer
- Track status

**New Features:**
- Link to source measurement
- Show measurement details
- Display profit margin (internal only)
- Convert to job button

### 4. Jobs Board (Enhanced)

**Location:** `/cms/jobs`

**Existing Features:**
- Kanban board by status
- Job details
- Assign to user
- Track costs

**New Features:**
- Fabrication status tracking
- Installation scheduling
- Material tracking
- Progress photos

### 5. Dashboard Widgets

**New Widgets:**
- Pending measurements
- Quotations awaiting approval
- Jobs in fabrication
- Jobs ready for installation
- Profit margin summary (this month)

---

## Implementation Plan

### Phase 1: Database & Models (Week 1)
1. Create migrations for new tables
2. Create Eloquent models with tenant scoping
3. Add relationships to existing models
4. Seed default pricing rules for existing companies

### Phase 2: Pricing Rules (Week 1)
1. Create pricing rules CRUD
2. Add to company settings page
3. Validation and business rules
4. Default values for new companies

### Phase 3: Measurements Module (Week 2)
1. Measurement CRUD operations
2. Measurement items management
3. Auto-calculations (dimensions, area)
4. Measurement report generation
5. UI components (Vue)

### Phase 4: Quotation Integration (Week 2)
1. Generate quotation from measurement
2. Link measurement to quotation
3. Profit margin calculations
4. Enhanced quotation view

### Phase 5: Job Workflow (Week 3)
1. Convert quotation to job
2. Enhanced job statuses
3. Fabrication tracking
4. Installation scheduling
5. Job board enhancements

### Phase 6: Invoice Integration (Week 3)
1. Generate invoice from completed job
2. Link invoice to job and quotation
3. Payment tracking
4. Financial reports

### Phase 7: Testing & Refinement (Week 4)
1. Multi-tenant testing
2. Security audit
3. Performance optimization
4. User acceptance testing
5. Documentation

---

## Data Migration

### For Existing Tenants

**Geopamu Aluminium & Construction:**
```sql
-- Create default pricing rules
INSERT INTO cms_pricing_rules (company_id, sliding_window_rate, casement_window_rate, ...)
VALUES (
    (SELECT id FROM cms_companies WHERE name = 'Geopamu Aluminium & Construction'),
    500.00,  -- sliding_window_rate (K500 per m²)
    550.00,  -- casement_window_rate
    600.00,  -- sliding_door_rate
    650.00,  -- hinged_door_rate
    400.00,  -- other_rate
    200.00,  -- material_cost_per_m2
    100.00,  -- labour_cost_per_m2
    50.00,   -- overhead_cost_per_m2
    25.00,   -- minimum_profit_percent
    16.00    -- tax_rate
);
```

**Other Tenants:**
- No impact - they don't see aluminium features unless they enable them
- Optional: Add "Enable Aluminium Module" toggle in company settings

---

## API Endpoints (RESTful)

### Measurements
```
GET    /cms/measurements                    - List (tenant-scoped)
POST   /cms/measurements                    - Create
GET    /cms/measurements/{id}               - Show
PUT    /cms/measurements/{id}               - Update
DELETE /cms/measurements/{id}               - Delete
POST   /cms/measurements/{id}/items         - Add item
PUT    /cms/measurements/{id}/items/{itemId} - Update item
DELETE /cms/measurements/{id}/items/{itemId} - Delete item
POST   /cms/measurements/{id}/generate-quotation - Generate quotation
```

### Pricing Rules
```
GET    /cms/settings/pricing-rules          - Show (tenant-scoped)
PUT    /cms/settings/pricing-rules          - Update
```

### Enhanced Quotations
```
GET    /cms/quotations/{id}/measurement     - Get linked measurement
POST   /cms/quotations/{id}/convert-to-job  - Convert to job
```

### Enhanced Jobs
```
PUT    /cms/jobs/{id}/status                - Update status
POST   /cms/jobs/{id}/materials             - Track materials
POST   /cms/jobs/{id}/progress              - Add progress update
```

---

## Testing Strategy

### Unit Tests
- Measurement calculations (dimensions, area)
- Pricing calculations (profit margins)
- Tenant scoping (ensure isolation)
- Business rules validation

### Integration Tests
- Measurement → Quotation flow
- Quotation → Job flow
- Job → Invoice flow
- Multi-tenant data isolation

### Security Tests
- Cross-tenant access attempts
- Unauthorized pricing rule changes
- SQL injection prevention
- XSS prevention

### Performance Tests
- Large measurement lists (1000+ records)
- Complex calculations
- Report generation
- Dashboard loading

---

## Risks & Mitigation

### Risk 1: Tenant Data Leakage
**Mitigation:**
- Enforce company_id in ALL queries
- Add database-level row security (if supported)
- Regular security audits
- Automated tests for tenant isolation

### Risk 2: Pricing Rule Conflicts
**Mitigation:**
- One pricing rule per company (UNIQUE constraint)
- Validation before save
- Audit log for pricing changes
- Rollback capability

### Risk 3: Calculation Errors
**Mitigation:**
- Extensive unit tests
- Manual verification by users
- Audit trail for all calculations
- Ability to override auto-calculations

### Risk 4: Performance Issues
**Mitigation:**
- Database indexes on company_id
- Eager loading relationships
- Caching pricing rules
- Pagination for large lists

---

## Success Criteria

1. ✅ Geopamu can create measurements
2. ✅ Geopamu can generate quotations from measurements
3. ✅ Geopamu can convert quotations to jobs
4. ✅ Geopamu can track fabrication progress
5. ✅ Geopamu can generate invoices from completed jobs
6. ✅ Other tenants are NOT affected
7. ✅ No cross-tenant data leakage
8. ✅ Profit margins calculated correctly
9. ✅ All data is tenant-scoped
10. ✅ Performance is acceptable (<2s page load)

---

## Conclusion

The MyGrowNet CMS is **architecturally ready** for aluminium fabrication workflow. The existing multitenant infrastructure provides:

✅ Robust tenant isolation (company_id)  
✅ Reusable modules (customers, jobs, invoices, quotations)  
✅ Flexible settings system (JSON column)  
✅ Secure middleware enforcement  
✅ Scalable database design  

**Recommendation:** Proceed with implementation following the phased approach outlined above.

**Estimated Timeline:** 4 weeks for complete implementation and testing.

**Next Step:** Review and approve this analysis, then begin Phase 1 (Database & Models).

---

**Document Status:** Ready for Review  
**Author:** AI Assistant  
**Date:** April 17, 2026
