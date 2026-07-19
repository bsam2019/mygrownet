# MyGrowNet CMS Implementation Plan

**Last Updated:** February 7, 2026  
**Status:** Planning  
**Version:** 1.1 - Comprehensive Feature Set

## Overview

This document outlines the technical implementation strategy for the MyGrowNet CMS (SME Operating System v1), following Domain-Driven Design principles and event-driven architecture.

## Complete Feature Scope (CMS v1)

This implementation includes **18 core modules** with essential operational safeguards and future-ready architecture.

### Module Overview

| # | Module | Priority | Phase |
|---|--------|----------|-------|
| 1 | Company & Administration | CRITICAL | Phase 1 |
| 2 | Customer Management | CRITICAL | Phase 1 |
| 3 | Job/Operations Management | CRITICAL | Phase 1-2 |
| 4 | Quotations & Invoices | HIGH | Phase 2 |
| 5 | Payments & Cash Management | HIGH | Phase 2 |
| 6 | Expense Management | HIGH | Phase 2 |
| 7 | Financial Reporting | HIGH | Phase 5 |
| 8 | Inventory Management | MEDIUM | Phase 3 |
| 9 | Asset Management | MEDIUM | Phase 3 |
| 10 | Payroll & Commission | HIGH | Phase 4 |
| 11 | Document Management | MEDIUM | Phase 3 |
| 12 | Dashboards & Analytics | HIGH | Phase 5 |
| 13 | Notifications & Audit Trail | HIGH | All Phases |
| 14 | Security & Governance | CRITICAL | All Phases |
| 15 | Industry Presets | MEDIUM | Phase 6 |
| 16 | Onboarding & Setup | HIGH | Phase 6 |
| 17 | Settings & Configuration | HIGH | Phase 1 |
| 18 | Approval Workflows | HIGH | Phase 2-4 |

### Essential Additions (v1.1)

**New features added based on SME operational requirements:**

1. **Settings & Configuration Module** - Business hours, currency, tax settings, approval thresholds
2. **Job Costing & Profitability** - Material cost, labor cost, overhead allocation, profit calculation
3. **Payment Allocation Logic** - Allocate payments to invoices, track overpayments, manage credits
4. **Approval Workflows** - Configurable approvals for expenses, assets, commissions, job cancellations
5. **Data Locking & Period Close** - Lock completed jobs, lock paid invoices, monthly period close
6. **Soft Deletion & Recovery** - Archive instead of delete, restore capability, permission-based deletion
7. **Attendance/Presence Tracking** - Clock-in/out, job-based attendance (optional per company)
8. **Internal Notes & Comments** - Notes on jobs, customers, internal-only communication
9. **Enhanced Audit Trail** - Old value → new value tracking, who/when/why changed
10. **Backup & Export** - Manual data export (CSV/PDF), backup indicators
11. **API Layer (Internal)** - Clean service APIs for future mobile app and integrations
12. **Feature Flags** - Enable/disable features per tenant for pricing tiers

### Exclusions (Clear Boundaries)

**CMS v1 will NOT include:**
- ❌ MLM features
- ❌ Marketplace selling
- ❌ Advertising tools
- ❌ Personal life management
- ❌ Family tree features
- ❌ AI features
- ❌ Advanced analytics
- ❌ Inventory forecasting
- ❌ Full payroll compliance engines
- ❌ Banking integrations

**These are v2+ features to keep the product focused and safe.**

## Architecture Overview

### Domain Structure

```
app/
├── Domain/
│   ├── CMS/
│   │   ├── Core/                    # Shared core domain
│   │   │   ├── Entities/
│   │   │   │   ├── Company.php
│   │   │   │   ├── CMSUser.php
│   │   │   │   ├── Customer.php
│   │   │   │   ├── Job.php
│   │   │   │   └── Payment.php
│   │   │   ├── ValueObjects/
│   │   │   │   ├── CompanyId.php
│   │   │   │   ├── JobStatus.php
│   │   │   │   └── PaymentMethod.php
│   │   │   ├── Events/
│   │   │   │   ├── JobCreated.php
│   │   │   │   ├── JobCompleted.php
│   │   │   │   └── PaymentRecorded.php
│   │   │   └── Repositories/
│   │   │       ├── CompanyRepositoryInterface.php
│   │   │       └── JobRepositoryInterface.php
│   │   │
│   │   ├── Administration/          # Admin bounded context
│   │   │   ├── Entities/
│   │   │   ├── Services/
│   │   │   └── Repositories/
│   │   │
│   │   ├── Operations/              # Job Management bounded context
│   │   │   ├── Entities/
│   │   │   ├── Services/
│   │   │   └── Repositories/
│   │   │
│   │   ├── Finance/                 # Finance bounded context
│   │   │   ├── Entities/
│   │   │   │   ├── Invoice.php
│   │   │   │   └── Expense.php
│   │   │   ├── Services/
│   │   │   └── Repositories/
│   │   │
│   │   ├── Inventory/               # Inventory bounded context
│   │   │   ├── Entities/
│   │   │   ├── Services/
│   │   │   └── Repositories/
│   │   │
│   │   └── Payroll/                 # Payroll bounded context
│   │       ├── Entities/
│   │       ├── Services/
│   │       └── Repositories/
│   │
├── Application/
│   └── CMS/
│       ├── UseCases/
│       │   ├── CreateJobUseCase.php
│       │   ├── CompleteJobUseCase.php
│       │   └── GenerateInvoiceUseCase.php
│       ├── Commands/
│       ├── Queries/
│       └── DTOs/
│
├── Infrastructure/
│   └── CMS/
│       ├── Persistence/
│       │   ├── Eloquent/
│       │   └── Repositories/
│       └── Events/
│           └── Listeners/
│
└── Presentation/
    └── Http/
        └── Controllers/
            └── CMS/
```

## Phase 1: Core Foundation & Administration (Weeks 1-2)

### Module 1: Company & Administration

**Features:**
- Company profile (name, industry, contacts, logo)
- Business status (active/suspended)
- Company branding (logo, invoice footer)
- User registration (staff only)
- Role-based access control
- Module visibility per role
- Login activity tracking
- Predefined roles (Owner, Admin, Finance, Staff, Casual)
- Custom permission assignment
- Approval authority definition

**Database Migrations:**

```php
// cms_companies table
- id
- name
- industry_type
- business_registration_number
- tax_number
- address
- phone
- email
- website
- logo_path
- invoice_footer
- status (active/suspended)
- settings (JSON) // business_hours, currency, tax_settings, etc.
- created_at, updated_at

// cms_company_settings table
- id
- company_id (FK)
- setting_key
- setting_value (JSON)
- created_at, updated_at

// cms_users table
- id
- company_id (FK)
- user_id (FK to main users table)
- role_id (FK)
- status (active/inactive/suspended)
- last_login_at
- created_at, updated_at

// cms_roles table
- id
- company_id (FK)
- name
- permissions (JSON)
- approval_authority (JSON) // expense_limit, commission_approval, etc.
- is_system_role (boolean)
- created_at, updated_at

// cms_login_activity table
- id
- cms_user_id (FK)
- ip_address
- user_agent
- login_at
- logout_at
- created_at

// cms_audit_trail table
- id
- company_id (FK)
- user_id (FK)
- entity_type (job/invoice/expense/etc.)
- entity_id
- action (created/updated/deleted/approved)
- old_values (JSON)
- new_values (JSON)
- ip_address
- created_at
```

### Module 2: Customer Management

**Features:**
- Customer registration
- Customer contact details
- Customer job history
- Outstanding balances per customer
- Customer documents (contracts, designs)
- Customer notes

**Database Migrations:**

```php
// cms_customers table
- id
- company_id (FK)
- customer_number (unique per company)
- name
- email
- phone
- secondary_phone
- address
- city
- country
- tax_number
- credit_limit
- outstanding_balance
- status (active/inactive)
- notes (text)
- created_by (FK to cms_users)
- created_at, updated_at

// cms_customer_contacts table
- id
- customer_id (FK)
- contact_name
- contact_phone
- contact_email
- contact_position
- is_primary (boolean)
- created_at, updated_at

// cms_customer_documents table
- id
- customer_id (FK)
- document_type (contract/design/quote/other)
- file_path
- file_name
- uploaded_by (FK to cms_users)
- created_at, updated_at

// cms_customer_notes table
- id
- customer_id (FK)
- note
- is_internal (boolean)
- created_by (FK to cms_users)
- created_at, updated_at
```

### Module 17: Settings & Configuration

**Features:**
- Business hours configuration
- Currency settings (ZMW default)
- Payment methods enabled/disabled
- Tax settings (VAT on/off, tax rate)
- Invoice terms (due days, late fees)
- Approval thresholds (who approves what amount)
- Email templates
- Notification preferences

**Database Migrations:**

```php
// cms_system_settings table
- id
- company_id (FK)
- category (general/finance/operations/notifications)
- setting_key
- setting_value (JSON)
- is_editable (boolean)
- created_at, updated_at

// Example settings stored:
{
  "business_hours": {
    "monday": {"open": "08:00", "close": "17:00"},
    "tuesday": {"open": "08:00", "close": "17:00"},
    // ...
  },
  "currency": "ZMW",
  "tax_settings": {
    "vat_enabled": true,
    "vat_rate": 16,
    "vat_number": "1234567890"
  },
  "invoice_terms": {
    "default_due_days": 30,
    "late_fee_percentage": 2
  },
  "approval_thresholds": {
    "expense_approval_limit": 5000,
    "commission_approval_required": true,
    "job_cancellation_approval": true
  },
  "payment_methods": {
    "cash": true,
    "bank_transfer": true,
    "mtn_momo": true,
    "airtel_money": true
  }
}
```

### Week 1: Core Domain Setup

**Domain Entities:**

1. `Company` - Tenant entity with industry type and settings
2. `CMSUser` - Company-specific user with role
3. `Role` - Role with permissions and approval authority
4. `Customer` - Company customers with contacts
5. `SystemSettings` - Configurable company settings

**Value Objects:**

1. `CompanyId` - Tenant identifier
2. `CustomerNumber` - Auto-generated unique identifier
3. `PermissionSet` - Collection of permissions
4. `ApprovalAuthority` - Approval limits and rules

**Services:**

1. `CompanyService` - Company management
2. `UserManagementService` - User and role management
3. `CustomerService` - Customer CRUD operations
4. `SettingsService` - System settings management
5. `AuditTrailService` - Activity logging

**Domain Entities:**

1. `Company` - Tenant entity with industry type
2. `CMSUser` - Company-specific user with role
3. `Customer` - Company customers
4. `Job` - Core business entity
5. `Payment` - Financial transactions

**Value Objects:**

1. `CompanyId` - Tenant identifier
2. `JobStatus` - Enum (pending, in_progress, completed, cancelled)
3. `PaymentMethod` - Enum (cash, bank, mobile_money)
4. `JobNumber` - Auto-generated unique identifier

### Week 2: Job Management Module

**Features:**
- Create job with customer
- Assign worker to job
- Update job status
- Attach documents to job
- View job list with filters
- Job detail view

**Use Cases:**
- `CreateJobUseCase`
- `AssignJobUseCase`
- `UpdateJobStatusUseCase`
- `CompleteJobUseCase`

**Domain Events:**
- `JobCreated`
- `JobAssigned`
- `JobStatusChanged`
- `JobCompleted`

**Controllers:**
- `JobController` - CRUD operations
- `JobAssignmentController` - Worker assignment
- `JobStatusController` - Status updates

**Frontend Pages:**
- `/cms/jobs` - Job list
- `/cms/jobs/create` - Create job
- `/cms/jobs/{id}` - Job details
- `/cms/jobs/{id}/edit` - Edit job

## Phase 2: Finance Integration (Weeks 3-4)

### Week 3: Invoice Generation

**Database Migrations:**

```php
// cms_invoices table
- id
- company_id (FK)
- job_id (FK) - REQUIRED
- invoice_number (unique per company)
- customer_id (FK)
- subtotal
- tax_amount
- total_amount
- status (draft/sent/paid/cancelled)
- due_date
- issued_by (FK to cms_users)
- created_at, updated_at

// cms_invoice_items table
- id
- invoice_id (FK)
- description
- quantity
- unit_price
- total
- created_at, updated_at
```

**Features:**
- Generate invoice from completed job
- Add line items
- Calculate totals
- Send invoice to customer
- Record payment against invoice

**Use Cases:**
- `GenerateInvoiceFromJobUseCase`
- `RecordInvoicePaymentUseCase`

**Domain Events:**
- `InvoiceGenerated`
- `InvoicePaymentRecorded`

**Business Rules (Enforced):**
```php
// In GenerateInvoiceFromJobUseCase
if (!$job->isCompleted()) {
    throw new JobNotCompletedException();
}

if ($job->hasInvoice()) {
    throw new InvoiceAlreadyExistsException();
}
```

### Week 4: Expense Management

**Database Migrations:**

```php
// cms_expenses table
- id
- company_id (FK)
- category
- description
- amount
- payment_method
- receipt_number
- requires_approval
- approved_by (FK to cms_users)
- approved_at
- recorded_by (FK to cms_users)
- expense_date
- created_at, updated_at
```

**Features:**
- Record expenses
- Categorize expenses
- Approval workflow
- Link payment to expense

**Business Rules:**
```php
// Expenses requiring approval cannot be paid until approved
if ($expense->requiresApproval() && !$expense->isApproved()) {
    throw new ExpenseNotApprovedException();
}
```

## Phase 3: Inventory & Assets (Weeks 5-6)

### Week 5: Inventory Management

**Database Migrations:**

```php
// cms_inventory_items table
- id
- company_id (FK)
- name
- sku
- category
- unit_of_measure
- quantity_in_stock
- reorder_level
- unit_cost
- created_at, updated_at

// cms_inventory_transactions table
- id
- company_id (FK)
- inventory_item_id (FK)
- transaction_type (purchase/usage/adjustment)
- quantity
- reference_type (job/adjustment)
- reference_id
- recorded_by (FK to cms_users)
- transaction_date
- created_at, updated_at

// cms_job_materials table
- id
- job_id (FK)
- inventory_item_id (FK)
- quantity_used
- recorded_by (FK to cms_users)
- created_at, updated_at
```

**Features:**
- Add inventory items
- Track stock levels
- Record usage against jobs
- Stock adjustments
- Low stock alerts

**Event Listeners:**
```php
// When job is completed, deduct materials
class DeductJobMaterialsListener
{
    public function handle(JobCompleted $event)
    {
        $job = $event->job;
        foreach ($job->materials as $material) {
            $this->inventoryService->deductStock(
                $material->inventory_item_id,
                $material->quantity_used,
                'job',
                $job->id
            );
        }
    }
}
```

### Week 6: Asset Management

**Database Migrations:**

```php
// cms_assets table
- id
- company_id (FK)
- name
- asset_type (machine/tool/vehicle)
- serial_number
- purchase_date
- purchase_cost
- assigned_to (FK to cms_users)
- status (available/in_use/maintenance/retired)
- created_at, updated_at

// cms_asset_maintenance table
- id
- asset_id (FK)
- maintenance_type
- description
- cost
- performed_by
- maintenance_date
- next_maintenance_date
- created_at, updated_at
```

**Features:**
- Register assets
- Assign to users
- Track maintenance
- Asset usage history

## Phase 4: Payroll & Commission (Weeks 7-8)

### Week 7: Worker Management

**Database Migrations:**

```php
// cms_workers table
- id
- company_id (FK)
- cms_user_id (FK)
- worker_type (casual/employee)
- commission_rate (percentage or fixed)
- commission_model (per_job/percentage)
- payment_method
- bank_details (JSON)
- status
- created_at, updated_at

// cms_commissions table
- id
- company_id (FK)
- worker_id (FK)
- job_id (FK) - REQUIRED
- amount
- calculation_basis (JSON)
- status (pending/approved/paid)
- approved_by (FK to cms_users)
- paid_at
- created_at, updated_at
```

**Features:**
- Register workers
- Set commission rules
- Auto-calculate commission on job completion
- Approve commissions
- Record commission payments

**Event Listeners:**
```php
class CalculateJobCommissionListener
{
    public function handle(JobCompleted $event)
    {
        $job = $event->job;
        $worker = $job->assignedWorker;
        
        if (!$worker) return;
        
        $commission = $this->commissionService->calculate(
            $worker,
            $job
        );
        
        event(new CommissionCalculated($commission));
    }
}
```

### Week 8: Payroll Processing

**Features:**
- View pending commissions
- Bulk approve commissions
- Generate payment reports
- Record bulk payments

## Phase 5: Reporting & Dashboard (Weeks 9-10)

### Week 9: Dashboard

**Metrics:**
- Jobs today (pending/in_progress/completed)
- Revenue this month
- Outstanding invoices
- Top performing workers
- Low stock items
- Pending approvals

**Widgets:**
- Revenue chart (last 6 months)
- Job status breakdown
- Worker performance
- Inventory alerts

### Week 10: Reports

**Reports:**
1. **Financial Reports:**
   - Profit & Loss
   - Cashbook
   - Outstanding invoices
   - Expense summary

2. **Operations Reports:**
   - Jobs by status
   - Jobs by worker
   - Job completion time
   - Customer activity

3. **Inventory Reports:**
   - Stock levels
   - Usage by job
   - Reorder list

4. **Payroll Reports:**
   - Commission summary
   - Worker earnings
   - Pending payments

## Phase 6: Geopamu Preset & Onboarding (Week 11)

### Industry Preset System

**Database:**

```php
// cms_industry_presets table
- id
- industry_type
- preset_name
- configuration (JSON)
- created_at, updated_at
```

**Geopamu Preset Configuration:**

```json
{
  "industry_type": "printing_branding",
  "job_types": [
    "T-shirt Printing",
    "Branding",
    "Signage",
    "Business Cards",
    "Banners"
  ],
  "inventory_items": [
    {"name": "Ink - Black", "unit": "ml"},
    {"name": "Ink - Color", "unit": "ml"},
    {"name": "T-shirts - White", "unit": "pcs"},
    {"name": "Vinyl", "unit": "meters"}
  ],
  "roles": [
    {
      "name": "Owner",
      "permissions": ["*"]
    },
    {
      "name": "Operations Manager",
      "permissions": ["jobs.*", "inventory.view"]
    },
    {
      "name": "Printer",
      "permissions": ["jobs.view", "jobs.update_status"]
    }
  ],
  "commission_model": {
    "type": "per_job",
    "rates": {
      "T-shirt Printing": 15,
      "Branding": 20,
      "Signage": 25
    }
  }
}
```

**Onboarding Wizard:**

1. Company setup
2. Select industry preset
3. Add users & assign roles
4. Configure commission rules
5. Add initial inventory
6. Create first job (tutorial)

## Technical Implementation Details

### Multi-Tenancy Implementation

**Middleware:**

```php
class EnsureCMSTenant
{
    public function handle($request, Closure $next)
    {
        $user = $request->user();
        
        if (!$user->hasCompany()) {
            return redirect()->route('cms.onboarding');
        }
        
        // Set tenant context
        app()->instance('cms.company_id', $user->company_id);
        
        return $next($request);
    }
}
```

**Global Scope:**

```php
class CompanyScope extends Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $companyId = app('cms.company_id');
        
        if ($companyId) {
            $builder->where('company_id', $companyId);
        }
    }
}
```

### Event-Driven Communication

**Event Registration:**

```php
// In EventServiceProvider
protected $listen = [
    JobCompleted::class => [
        GenerateInvoiceListener::class,
        CalculateCommissionListener::class,
        DeductInventoryListener::class,
        UpdateDashboardListener::class,
    ],
    InvoiceGenerated::class => [
        NotifyCustomerListener::class,
        UpdateFinancialReportsListener::class,
    ],
    PaymentRecorded::class => [
        UpdateInvoiceStatusListener::class,
        UpdateCashbookListener::class,
    ],
];
```

### Business Rule Enforcement

**Service Layer Validation:**

```php
class GenerateInvoiceFromJobUseCase
{
    public function execute(GenerateInvoiceCommand $command): Invoice
    {
        $job = $this->jobRepository->findById($command->jobId);
        
        // Rule: Job must be completed
        if (!$job->isCompleted()) {
            throw new BusinessRuleViolationException(
                'Cannot generate invoice for incomplete job'
            );
        }
        
        // Rule: Job must not already have an invoice
        if ($this->invoiceRepository->existsForJob($job->getId())) {
            throw new BusinessRuleViolationException(
                'Invoice already exists for this job'
            );
        }
        
        // Generate invoice
        $invoice = Invoice::generateFromJob($job);
        $this->invoiceRepository->save($invoice);
        
        event(new InvoiceGenerated($invoice));
        
        return $invoice;
    }
}
```

## Testing Strategy

### Unit Tests
- Domain entities business logic
- Value objects validation
- Use cases

### Integration Tests
- Repository implementations
- Event listeners
- Service integrations

### Feature Tests
- Complete workflows
- Multi-tenancy isolation
- Business rule enforcement

## Deployment Checklist

- [ ] Run migrations
- [ ] Seed Geopamu preset
- [ ] Create initial company
- [ ] Assign roles
- [ ] Configure permissions
- [ ] Test job workflow
- [ ] Test invoice generation
- [ ] Test commission calculation
- [ ] Verify multi-tenancy isolation
- [ ] Load test with multiple companies

## Success Criteria

1. **Traceability:** Every transaction links back to a job
2. **Accountability:** All actions logged with user ID
3. **Discipline:** Business rules enforced at service level
4. **Isolation:** Complete data separation between companies
5. **Performance:** Dashboard loads < 2 seconds
6. **Usability:** SME owner can complete full workflow without training

---

## Related Documents

- [Development Brief](./DEVELOPMENT_BRIEF.md) - Business objectives and requirements
- [Complete Feature Specification](./COMPLETE_FEATURE_SPECIFICATION.md) - Detailed feature specs for all 18 modules ⭐ **NEW**
- [Module Integration](./MODULE_INTEGRATION.md) - Integration with GrowFinance, GrowBiz, BizBoost

---

## Changelog

### Version 1.1 (February 7, 2026)
- Added comprehensive 18-module feature scope
- Added essential operational safeguards (approval workflows, data locking, audit trail)
- Added Settings & Configuration module
- Added Job Costing & Profitability features
- Added Payment Allocation Logic
- Added Soft Deletion & Recovery
- Added Internal Notes & Comments
- Added Backup & Export capabilities
- Added API Layer (internal) for future mobile app
- Added Feature Flags for pricing tiers
- Created separate COMPLETE_FEATURE_SPECIFICATION.md for detailed specs
- Updated exclusions list (v2+ features)

### Version 1.0 (February 5, 2026)
- Initial implementation plan
- Domain-driven design architecture
- 6-phase delivery plan (11 weeks)
- Event-driven communication patterns
- Multi-tenancy implementation
- Geopamu industry preset
