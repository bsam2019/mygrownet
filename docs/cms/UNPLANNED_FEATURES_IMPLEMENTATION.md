# Unplanned Features Implementation Plan

**Last Updated:** February 13, 2026  
**Status:** Planning Phase  
**Purpose:** Implementation roadmap for all unplanned CMS features

---

## Overview

This document provides detailed implementation plans for all features marked as "⚪ Not Planned" or "⚪ Future" in the MISSING_FEATURES_ROADMAP.md. Features are organized by priority and complexity.

---

## Priority Classification

- **P1 - High Value**: Features that would significantly improve the CMS
- **P2 - Medium Value**: Nice-to-have features for competitive advantage
- **P3 - Low Value**: Advanced features for specific use cases
- **P4 - Out of Scope**: Features that don't align with SME focus

---

## A. SYSTEM FOUNDATION & ADMINISTRATION

### 1. Multi-Branch Management
**Priority:** P2 - Medium Value  
**Complexity:** High  
**Estimated Time:** 3 weeks  
**Current Status:** ⚪ Not Planned

#### Business Value
- Allows companies with multiple locations to manage all branches
- Centralized reporting across branches
- Branch-specific inventory and staff

#### Technical Requirements

**Database Schema:**
```sql
-- Add branch_id to relevant tables
ALTER TABLE cms_jobs ADD COLUMN branch_id BIGINT UNSIGNED;
ALTER TABLE cms_inventory_items ADD COLUMN branch_id BIGINT UNSIGNED;
ALTER TABLE cms_workers ADD COLUMN branch_id BIGINT UNSIGNED;

-- Create branches table
CREATE TABLE cms_branches (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50) UNIQUE,
    address TEXT,
    phone VARCHAR(50),
    email VARCHAR(255),
    manager_id BIGINT UNSIGNED,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE
);
```

**Implementation Steps:**
1. Create branch management module (CRUD)
2. Add branch selection to relevant forms
3. Filter data by branch in reports
4. Add branch-level permissions
5. Create branch comparison reports

**Files to Create:**
- `database/migrations/YYYY_MM_DD_create_cms_branches_table.php`
- `app/Infrastructure/Persistence/Eloquent/CMS/BranchModel.php`
- `app/Domain/CMS/Core/Services/BranchService.php`
- `app/Http/Controllers/CMS/BranchController.php`
- `resources/js/Pages/CMS/Branches/Index.vue`
- `resources/js/Pages/CMS/Branches/Create.vue`

---

### 2. Multi-Entity (Subsidiary) Management
**Priority:** P3 - Low Value  
**Complexity:** Very High  
**Estimated Time:** 6 weeks  
**Current Status:** ⚪ Not Planned

#### Business Value
- Manage multiple legal entities under one parent company
- Consolidated financial reporting
- Inter-company transactions

#### Technical Requirements

**Database Schema:**
```sql
CREATE TABLE cms_entities (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parent_entity_id BIGINT UNSIGNED,
    name VARCHAR(255) NOT NULL,
    legal_name VARCHAR(255),
    registration_number VARCHAR(100),
    tax_number VARCHAR(100),
    entity_type ENUM('parent', 'subsidiary', 'branch'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_entity_id) REFERENCES cms_entities(id)
);

-- Add entity_id to companies table
ALTER TABLE cms_companies ADD COLUMN entity_id BIGINT UNSIGNED;
```

**Implementation Steps:**
1. Create entity hierarchy system
2. Implement consolidation logic
3. Add inter-company transaction tracking
4. Create consolidated reports
5. Add entity-level permissions

**Recommendation:** Defer to v3.0 - Complex enterprise feature

---

### 3. Financial Year Configuration
**Priority:** P2 - Medium Value  
**Complexity:** Medium  
**Estimated Time:** 1 week  
**Current Status:** ⚪ Not Planned

#### Business Value
- Support non-calendar fiscal years
- Proper period-based reporting
- Year-end closing procedures

#### Technical Requirements

**Database Schema:**
```sql
ALTER TABLE cms_companies ADD COLUMN fiscal_year_start_month INT DEFAULT 1;
ALTER TABLE cms_companies ADD COLUMN fiscal_year_start_day INT DEFAULT 1;
ALTER TABLE cms_companies ADD COLUMN current_fiscal_year INT;

CREATE TABLE cms_fiscal_periods (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    year INT NOT NULL,
    period_number INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    is_closed BOOLEAN DEFAULT FALSE,
    closed_at TIMESTAMP NULL,
    closed_by BIGINT UNSIGNED,
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE
);
```

**Implementation Steps:**
1. Add fiscal year settings to company profile
2. Create period management system
3. Update all date filters to respect fiscal year
4. Add period closing functionality
5. Prevent edits to closed periods

**Files to Create:**
- `database/migrations/YYYY_MM_DD_add_fiscal_year_to_cms.php`
- `app/Domain/CMS/Core/Services/FiscalPeriodService.php`
- `app/Http/Controllers/CMS/FiscalPeriodController.php`
- `resources/js/Pages/CMS/Settings/FiscalYear.vue`

---


### 4. Custom Roles and Permission Matrix
**Priority:** P1 - High Value  
**Complexity:** Medium  
**Estimated Time:** 2 weeks  
**Current Status:** 🟡 Partial (Predefined roles only)

#### Business Value
- Flexible access control for different business structures
- Granular permission management
- Custom role creation per company

#### Technical Requirements

**Database Schema:**
```sql
CREATE TABLE cms_permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    category VARCHAR(50),
    description TEXT
);

CREATE TABLE cms_role_permissions (
    role_id BIGINT UNSIGNED NOT NULL,
    permission_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES cms_roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES cms_permissions(id) ON DELETE CASCADE
);

ALTER TABLE cms_roles ADD COLUMN is_custom BOOLEAN DEFAULT FALSE;
ALTER TABLE cms_roles ADD COLUMN created_by BIGINT UNSIGNED;
```

**Permission Categories:**
- Dashboard & Analytics
- Customers & Leads
- Jobs & Projects
- Invoices & Quotations
- Payments & Expenses
- Inventory & Assets
- Workers & Payroll
- Reports & Settings

**Implementation Steps:**
1. Create permissions seeder with all available permissions
2. Build role management UI with permission matrix
3. Add middleware for permission checking
4. Update all controllers to check permissions
5. Add role cloning functionality

**Files to Create:**
- `database/migrations/YYYY_MM_DD_create_cms_permissions_system.php`
- `database/seeders/CmsPermissionsSeeder.php`
- `app/Infrastructure/Persistence/Eloquent/CMS/PermissionModel.php`
- `app/Domain/CMS/Core/Services/PermissionService.php`
- `app/Http/Middleware/CMS/CheckPermission.php`
- `resources/js/Pages/CMS/Settings/Roles.vue`
- `resources/js/components/CMS/PermissionMatrix.vue`

---

### 5. Single Sign-On (SSO)
**Priority:** P3 - Low Value  
**Complexity:** High  
**Estimated Time:** 3 weeks  
**Current Status:** ⚪ Not Planned

#### Business Value
- Enterprise integration capability
- Simplified user management
- Enhanced security

#### Technical Requirements

**Supported Protocols:**
- SAML 2.0
- OAuth 2.0 / OpenID Connect
- LDAP/Active Directory

**Implementation Steps:**
1. Install Laravel Socialite or similar package
2. Create SSO configuration UI
3. Implement SAML/OAuth handlers
4. Add SSO login flow
5. Handle user provisioning
6. Add SSO audit logging

**Recommendation:** Defer to v2.0 - Enterprise feature with limited SME demand

---

## B. FINANCIAL MANAGEMENT & ACCOUNTING

### 6. General Ledger System
**Priority:** P1 - High Value  
**Complexity:** High  
**Estimated Time:** 3 weeks  
**Current Status:** ⚪ Future (v2.0)

#### Business Value
- Complete double-entry accounting
- Full audit trail
- Professional financial statements
- Compliance with accounting standards

#### Technical Requirements

**Database Schema:**
```sql
-- Already have cms_accounts, cms_journal_entries, cms_journal_lines

-- Add GL-specific fields
ALTER TABLE cms_journal_entries ADD COLUMN reference_type VARCHAR(50);
ALTER TABLE cms_journal_entries ADD COLUMN reference_id BIGINT UNSIGNED;
ALTER TABLE cms_journal_entries ADD COLUMN fiscal_period_id BIGINT UNSIGNED;

CREATE TABLE cms_account_balances (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    account_id BIGINT UNSIGNED NOT NULL,
    fiscal_period_id BIGINT UNSIGNED NOT NULL,
    opening_balance DECIMAL(15,2) DEFAULT 0,
    debit_total DECIMAL(15,2) DEFAULT 0,
    credit_total DECIMAL(15,2) DEFAULT 0,
    closing_balance DECIMAL(15,2) DEFAULT 0,
    FOREIGN KEY (account_id) REFERENCES cms_accounts(id) ON DELETE CASCADE,
    UNIQUE KEY unique_account_period (account_id, fiscal_period_id)
);
```

**Implementation Steps:**
1. Extend ChartOfAccountsService with GL functionality
2. Auto-generate journal entries from transactions
3. Create account balance calculation system
4. Build general ledger report
5. Add account reconciliation tools
6. Create period closing workflow

**Files to Create:**
- `database/migrations/YYYY_MM_DD_enhance_chart_of_accounts_for_gl.php`
- `app/Domain/CMS/Core/Services/GeneralLedgerService.php`
- `resources/js/Pages/CMS/Accounting/GeneralLedger.vue`
- `resources/js/Pages/CMS/Accounting/AccountReconciliation.vue`

---


### 7. Balance Sheet Report
**Priority:** P1 - High Value  
**Complexity:** Medium  
**Estimated Time:** 1 week  
**Current Status:** ⚪ Future (v2.0)

#### Business Value
- Complete financial position snapshot
- Required for loans and investors
- Professional financial reporting

#### Technical Requirements

**Report Sections:**
- Assets (Current + Non-Current)
- Liabilities (Current + Long-Term)
- Equity (Capital + Retained Earnings)

**Implementation Steps:**
1. Create balance sheet calculation service
2. Query account balances by type
3. Calculate retained earnings
4. Build balance sheet report UI
5. Add comparative periods (YoY)
6. Export to PDF/Excel

**Files to Create:**
- `app/Domain/CMS/Core/Services/BalanceSheetService.php`
- `resources/js/Pages/CMS/Reports/BalanceSheet.vue`
- `resources/views/pdf/cms/balance-sheet.blade.php`

**Dependencies:** Requires General Ledger implementation

---

### 8. Bank Reconciliation
**Priority:** P1 - High Value  
**Complexity:** Medium  
**Estimated Time:** 2 weeks  
**Current Status:** ⚪ Future (v2.0)

#### Business Value
- Ensure accuracy of financial records
- Detect errors and fraud
- Professional accounting practice

#### Technical Requirements

**Database Schema:**
```sql
CREATE TABLE cms_bank_accounts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    account_name VARCHAR(255) NOT NULL,
    account_number VARCHAR(100),
    bank_name VARCHAR(255),
    branch VARCHAR(255),
    currency_code VARCHAR(3) DEFAULT 'ZMW',
    opening_balance DECIMAL(15,2) DEFAULT 0,
    current_balance DECIMAL(15,2) DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE
);

CREATE TABLE cms_bank_reconciliations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bank_account_id BIGINT UNSIGNED NOT NULL,
    statement_date DATE NOT NULL,
    statement_balance DECIMAL(15,2) NOT NULL,
    book_balance DECIMAL(15,2) NOT NULL,
    reconciled_balance DECIMAL(15,2) NOT NULL,
    status ENUM('draft', 'completed') DEFAULT 'draft',
    reconciled_by BIGINT UNSIGNED,
    reconciled_at TIMESTAMP NULL,
    FOREIGN KEY (bank_account_id) REFERENCES cms_bank_accounts(id) ON DELETE CASCADE
);

CREATE TABLE cms_bank_reconciliation_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reconciliation_id BIGINT UNSIGNED NOT NULL,
    transaction_type ENUM('payment', 'receipt', 'adjustment'),
    transaction_id BIGINT UNSIGNED,
    description TEXT,
    amount DECIMAL(15,2) NOT NULL,
    is_reconciled BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (reconciliation_id) REFERENCES cms_bank_reconciliations(id) ON DELETE CASCADE
);
```

**Implementation Steps:**
1. Create bank account management
2. Link payments to bank accounts
3. Build reconciliation matching UI
4. Add automatic matching suggestions
5. Create reconciliation report
6. Add bank statement import (CSV)

**Files to Create:**
- `database/migrations/YYYY_MM_DD_create_cms_bank_reconciliation.php`
- `app/Infrastructure/Persistence/Eloquent/CMS/BankAccountModel.php`
- `app/Infrastructure/Persistence/Eloquent/CMS/BankReconciliationModel.php`
- `app/Domain/CMS/Core/Services/BankReconciliationService.php`
- `app/Http/Controllers/CMS/BankReconciliationController.php`
- `resources/js/Pages/CMS/Banking/Accounts.vue`
- `resources/js/Pages/CMS/Banking/Reconciliation.vue`

---

### 9. Credit Notes & Debit Notes
**Priority:** P2 - Medium Value  
**Complexity:** Medium  
**Estimated Time:** 1 week  
**Current Status:** 🟡 Partial (Credit tracking only)

#### Business Value
- Proper handling of returns and adjustments
- Professional invoicing
- Accurate financial records

#### Technical Requirements

**Database Schema:**
```sql
CREATE TABLE cms_credit_notes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    customer_id BIGINT UNSIGNED NOT NULL,
    invoice_id BIGINT UNSIGNED,
    credit_note_number VARCHAR(50) UNIQUE NOT NULL,
    date DATE NOT NULL,
    reason TEXT,
    subtotal DECIMAL(15,2) NOT NULL,
    tax_amount DECIMAL(15,2) DEFAULT 0,
    total_amount DECIMAL(15,2) NOT NULL,
    status ENUM('draft', 'issued', 'applied', 'void') DEFAULT 'draft',
    created_by BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES cms_customers(id) ON DELETE CASCADE,
    FOREIGN KEY (invoice_id) REFERENCES cms_invoices(id) ON DELETE SET NULL
);

CREATE TABLE cms_credit_note_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    credit_note_id BIGINT UNSIGNED NOT NULL,
    description TEXT NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    unit_price DECIMAL(15,2) NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (credit_note_id) REFERENCES cms_credit_notes(id) ON DELETE CASCADE
);

-- Similar structure for debit notes
```

**Implementation Steps:**
1. Create credit note CRUD
2. Link to original invoices
3. Add credit note application to invoices
4. Create debit note system
5. Update customer balance calculations
6. Add PDF generation

**Files to Create:**
- `database/migrations/YYYY_MM_DD_create_cms_credit_debit_notes.php`
- `app/Infrastructure/Persistence/Eloquent/CMS/CreditNoteModel.php`
- `app/Infrastructure/Persistence/Eloquent/CMS/DebitNoteModel.php`
- `app/Domain/CMS/Core/Services/CreditNoteService.php`
- `app/Http/Controllers/CMS/CreditNoteController.php`
- `resources/js/Pages/CMS/CreditNotes/Index.vue`
- `resources/views/pdf/cms/credit-note.blade.php`

---


### 10. Withholding Tax Tracking
**Priority:** P2 - Medium Value  
**Complexity:** Low  
**Estimated Time:** 3 days  
**Current Status:** ⚪ Future (v2.0)

#### Business Value
- Zambian tax compliance
- Automatic WHT calculation
- ZRA reporting

#### Technical Requirements

**Database Schema:**
```sql
ALTER TABLE cms_payments ADD COLUMN withholding_tax_rate DECIMAL(5,2) DEFAULT 0;
ALTER TABLE cms_payments ADD COLUMN withholding_tax_amount DECIMAL(15,2) DEFAULT 0;
ALTER TABLE cms_payments ADD COLUMN net_payment DECIMAL(15,2);

CREATE TABLE cms_withholding_tax_rates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    service_type VARCHAR(100) NOT NULL,
    rate DECIMAL(5,2) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE
);
```

**Zambian WHT Rates:**
- Management/consultancy fees: 15%
- Professional fees: 15%
- Commissions: 15%
- Rent: 10%
- Interest: 15%
- Dividends: 15%

**Implementation Steps:**
1. Add WHT rate configuration
2. Auto-calculate WHT on payments
3. Add WHT fields to payment forms
4. Create WHT certificate generation
5. Build WHT report for ZRA

**Files to Create:**
- `database/migrations/YYYY_MM_DD_add_withholding_tax_to_cms.php`
- `app/Domain/CMS/Core/Services/WithholdingTaxService.php`
- `resources/js/Pages/CMS/Reports/WithholdingTax.vue`
- `resources/views/pdf/cms/wht-certificate.blade.php`

---

## C. SALES & CRM

### 11. Contract Management
**Priority:** P2 - Medium Value  
**Complexity:** Medium  
**Estimated Time:** 2 weeks  
**Current Status:** 🟡 Partial (Document storage only)

#### Business Value
- Centralized contract repository
- Renewal reminders
- Contract value tracking
- Compliance management

#### Technical Requirements

**Database Schema:**
```sql
CREATE TABLE cms_contracts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    customer_id BIGINT UNSIGNED,
    vendor_id BIGINT UNSIGNED,
    contract_number VARCHAR(100) UNIQUE NOT NULL,
    title VARCHAR(255) NOT NULL,
    contract_type ENUM('service', 'supply', 'employment', 'lease', 'other'),
    start_date DATE NOT NULL,
    end_date DATE,
    value DECIMAL(15,2),
    currency_code VARCHAR(3) DEFAULT 'ZMW',
    status ENUM('draft', 'active', 'expired', 'terminated', 'renewed'),
    auto_renew BOOLEAN DEFAULT FALSE,
    renewal_notice_days INT DEFAULT 30,
    terms TEXT,
    created_by BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES cms_customers(id) ON DELETE SET NULL
);

CREATE TABLE cms_contract_documents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    contract_id BIGINT UNSIGNED NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT,
    uploaded_by BIGINT UNSIGNED,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (contract_id) REFERENCES cms_contracts(id) ON DELETE CASCADE
);

CREATE TABLE cms_contract_renewals (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    original_contract_id BIGINT UNSIGNED NOT NULL,
    new_contract_id BIGINT UNSIGNED,
    renewal_date DATE NOT NULL,
    notes TEXT,
    FOREIGN KEY (original_contract_id) REFERENCES cms_contracts(id) ON DELETE CASCADE
);
```

**Implementation Steps:**
1. Create contract CRUD system
2. Add document attachment
3. Build renewal reminder system
4. Create contract value tracking
5. Add contract templates
6. Build expiry dashboard

**Files to Create:**
- `database/migrations/YYYY_MM_DD_create_cms_contracts_system.php`
- `app/Infrastructure/Persistence/Eloquent/CMS/ContractModel.php`
- `app/Domain/CMS/Core/Services/ContractService.php`
- `app/Http/Controllers/CMS/ContractController.php`
- `app/Console/Commands/SendContractRenewalReminders.php`
- `resources/js/Pages/CMS/Contracts/Index.vue`
- `resources/js/Pages/CMS/Contracts/Show.vue`

---

### 12. Support Ticket System
**Priority:** P2 - Medium Value  
**Complexity:** Medium  
**Estimated Time:** 2 weeks  
**Current Status:** ⚪ Future (v2.0)

#### Business Value
- Customer support management
- Issue tracking
- SLA compliance
- Customer satisfaction

#### Technical Requirements

**Database Schema:**
```sql
CREATE TABLE cms_support_tickets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    customer_id BIGINT UNSIGNED NOT NULL,
    job_id BIGINT UNSIGNED,
    ticket_number VARCHAR(50) UNIQUE NOT NULL,
    subject VARCHAR(255) NOT NULL,
    description TEXT,
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    status ENUM('open', 'in_progress', 'waiting_customer', 'resolved', 'closed'),
    category VARCHAR(100),
    assigned_to BIGINT UNSIGNED,
    created_by BIGINT UNSIGNED,
    resolved_at TIMESTAMP NULL,
    closed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES cms_customers(id) ON DELETE CASCADE
);

CREATE TABLE cms_ticket_responses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED,
    message TEXT NOT NULL,
    is_internal BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES cms_support_tickets(id) ON DELETE CASCADE
);

CREATE TABLE cms_ticket_attachments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id BIGINT UNSIGNED NOT NULL,
    response_id BIGINT UNSIGNED,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES cms_support_tickets(id) ON DELETE CASCADE
);
```

**Implementation Steps:**
1. Create ticket CRUD system
2. Add response/comment system
3. Build assignment workflow
4. Add email notifications
5. Create SLA tracking
6. Build ticket dashboard

**Files to Create:**
- `database/migrations/YYYY_MM_DD_create_cms_support_tickets.php`
- `app/Infrastructure/Persistence/Eloquent/CMS/SupportTicketModel.php`
- `app/Domain/CMS/Core/Services/SupportTicketService.php`
- `app/Http/Controllers/CMS/SupportTicketController.php`
- `resources/js/Pages/CMS/Support/Index.vue`
- `resources/js/Pages/CMS/Support/Show.vue`

---


## D. PROCUREMENT & INVENTORY

### 13. Multi-Warehouse Management
**Priority:** P2 - Medium Value  
**Complexity:** High  
**Estimated Time:** 3 weeks  
**Current Status:** ⚪ Future (Single location only)

#### Business Value
- Manage inventory across multiple locations
- Stock transfers between warehouses
- Location-specific reporting

#### Technical Requirements

**Database Schema:**
```sql
CREATE TABLE cms_warehouses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50) UNIQUE,
    address TEXT,
    manager_id BIGINT UNSIGNED,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE
);

ALTER TABLE cms_inventory_items ADD COLUMN warehouse_id BIGINT UNSIGNED;

CREATE TABLE cms_warehouse_stock (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    inventory_item_id BIGINT UNSIGNED NOT NULL,
    warehouse_id BIGINT UNSIGNED NOT NULL,
    quantity DECIMAL(10,2) NOT NULL DEFAULT 0,
    reorder_level DECIMAL(10,2),
    FOREIGN KEY (inventory_item_id) REFERENCES cms_inventory_items(id) ON DELETE CASCADE,
    FOREIGN KEY (warehouse_id) REFERENCES cms_warehouses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_item_warehouse (inventory_item_id, warehouse_id)
);

CREATE TABLE cms_stock_transfers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    transfer_number VARCHAR(50) UNIQUE NOT NULL,
    from_warehouse_id BIGINT UNSIGNED NOT NULL,
    to_warehouse_id BIGINT UNSIGNED NOT NULL,
    transfer_date DATE NOT NULL,
    status ENUM('pending', 'in_transit', 'received', 'cancelled'),
    notes TEXT,
    created_by BIGINT UNSIGNED,
    approved_by BIGINT UNSIGNED,
    received_by BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (from_warehouse_id) REFERENCES cms_warehouses(id),
    FOREIGN KEY (to_warehouse_id) REFERENCES cms_warehouses(id)
);

CREATE TABLE cms_stock_transfer_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transfer_id BIGINT UNSIGNED NOT NULL,
    inventory_item_id BIGINT UNSIGNED NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    received_quantity DECIMAL(10,2) DEFAULT 0,
    FOREIGN KEY (transfer_id) REFERENCES cms_stock_transfers(id) ON DELETE CASCADE,
    FOREIGN KEY (inventory_item_id) REFERENCES cms_inventory_items(id)
);
```

**Implementation Steps:**
1. Create warehouse management
2. Split inventory by warehouse
3. Build stock transfer system
4. Add warehouse selection to transactions
5. Create warehouse-specific reports
6. Add stock level alerts per warehouse

**Files to Create:**
- `database/migrations/YYYY_MM_DD_create_cms_multi_warehouse.php`
- `app/Infrastructure/Persistence/Eloquent/CMS/WarehouseModel.php`
- `app/Infrastructure/Persistence/Eloquent/CMS/StockTransferModel.php`
- `app/Domain/CMS/Core/Services/WarehouseService.php`
- `app/Http/Controllers/CMS/WarehouseController.php`
- `resources/js/Pages/CMS/Warehouses/Index.vue`
- `resources/js/Pages/CMS/StockTransfers/Index.vue`

---

### 14. Advanced Stock Valuation (FIFO)
**Priority:** P2 - Medium Value  
**Complexity:** High  
**Estimated Time:** 2 weeks  
**Current Status:** 🟡 Partial (Average cost only)

#### Business Value
- Accurate inventory valuation
- Better cost of goods sold calculation
- Compliance with accounting standards

#### Technical Requirements

**Database Schema:**
```sql
CREATE TABLE cms_inventory_batches (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    inventory_item_id BIGINT UNSIGNED NOT NULL,
    warehouse_id BIGINT UNSIGNED,
    batch_number VARCHAR(100),
    purchase_date DATE NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    remaining_quantity DECIMAL(10,2) NOT NULL,
    unit_cost DECIMAL(15,2) NOT NULL,
    total_cost DECIMAL(15,2) NOT NULL,
    reference_type VARCHAR(50),
    reference_id BIGINT UNSIGNED,
    FOREIGN KEY (inventory_item_id) REFERENCES cms_inventory_items(id) ON DELETE CASCADE
);

ALTER TABLE cms_inventory_items ADD COLUMN valuation_method ENUM('fifo', 'lifo', 'average') DEFAULT 'average';
```

**Implementation Steps:**
1. Create batch tracking system
2. Implement FIFO calculation logic
3. Update stock movement to use batches
4. Recalculate COGS using FIFO
5. Add batch selection in stock issues
6. Create inventory valuation report

**Files to Create:**
- `database/migrations/YYYY_MM_DD_add_inventory_batch_tracking.php`
- `app/Infrastructure/Persistence/Eloquent/CMS/InventoryBatchModel.php`
- `app/Domain/CMS/Core/Services/InventoryValuationService.php`
- `resources/js/Pages/CMS/Reports/InventoryValuation.vue`

---

## E. HUMAN RESOURCES

### 15. Leave Management System
**Priority:** P1 - High Value  
**Complexity:** Medium  
**Estimated Time:** 2 weeks  
**Current Status:** ⚪ Future (v2.0)

#### Business Value
- Track employee leave balances
- Approval workflow
- Leave calendar
- Statutory compliance

#### Technical Requirements

**Database Schema:**
```sql
CREATE TABLE cms_leave_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(50),
    days_per_year DECIMAL(5,2),
    is_paid BOOLEAN DEFAULT TRUE,
    requires_approval BOOLEAN DEFAULT TRUE,
    carry_forward BOOLEAN DEFAULT FALSE,
    max_carry_forward_days DECIMAL(5,2),
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE
);

CREATE TABLE cms_leave_balances (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    worker_id BIGINT UNSIGNED NOT NULL,
    leave_type_id BIGINT UNSIGNED NOT NULL,
    year INT NOT NULL,
    total_days DECIMAL(5,2) NOT NULL,
    used_days DECIMAL(5,2) DEFAULT 0,
    remaining_days DECIMAL(5,2) NOT NULL,
    carried_forward_days DECIMAL(5,2) DEFAULT 0,
    FOREIGN KEY (worker_id) REFERENCES cms_workers(id) ON DELETE CASCADE,
    FOREIGN KEY (leave_type_id) REFERENCES cms_leave_types(id) ON DELETE CASCADE,
    UNIQUE KEY unique_worker_type_year (worker_id, leave_type_id, year)
);

CREATE TABLE cms_leave_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    worker_id BIGINT UNSIGNED NOT NULL,
    leave_type_id BIGINT UNSIGNED NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_days DECIMAL(5,2) NOT NULL,
    reason TEXT,
    status ENUM('pending', 'approved', 'rejected', 'cancelled') DEFAULT 'pending',
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_by BIGINT UNSIGNED,
    reviewed_at TIMESTAMP NULL,
    review_notes TEXT,
    FOREIGN KEY (worker_id) REFERENCES cms_workers(id) ON DELETE CASCADE,
    FOREIGN KEY (leave_type_id) REFERENCES cms_leave_types(id)
);
```

**Zambian Leave Types:**
- Annual Leave: 24 days per year
- Sick Leave: As per company policy
- Maternity Leave: 84 days (12 weeks)
- Compassionate Leave: As per company policy
- Study Leave: As per company policy

**Implementation Steps:**
1. Create leave type configuration
2. Build leave balance tracking
3. Create leave request system
4. Add approval workflow
5. Build leave calendar
6. Create leave reports

**Files to Create:**
- `database/migrations/YYYY_MM_DD_create_cms_leave_management.php`
- `app/Infrastructure/Persistence/Eloquent/CMS/LeaveTypeModel.php`
- `app/Infrastructure/Persistence/Eloquent/CMS/LeaveRequestModel.php`
- `app/Domain/CMS/Core/Services/LeaveManagementService.php`
- `app/Http/Controllers/CMS/LeaveController.php`
- `resources/js/Pages/CMS/Leave/Requests.vue`
- `resources/js/Pages/CMS/Leave/Calendar.vue`

---


### 16. Performance Management System
**Priority:** P2 - Medium Value  
**Complexity:** High  
**Estimated Time:** 3 weeks  
**Current Status:** ⚪ Future (v2.0)

#### Business Value
- Employee performance tracking
- Goal setting and monitoring
- Performance reviews
- Development planning

#### Technical Requirements

**Database Schema:**
```sql
CREATE TABLE cms_performance_periods (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('planning', 'active', 'review', 'closed'),
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE
);

CREATE TABLE cms_performance_goals (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    worker_id BIGINT UNSIGNED NOT NULL,
    period_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    target_value DECIMAL(10,2),
    actual_value DECIMAL(10,2),
    weight DECIMAL(5,2),
    status ENUM('not_started', 'in_progress', 'achieved', 'not_achieved'),
    due_date DATE,
    FOREIGN KEY (worker_id) REFERENCES cms_workers(id) ON DELETE CASCADE,
    FOREIGN KEY (period_id) REFERENCES cms_performance_periods(id) ON DELETE CASCADE
);

CREATE TABLE cms_performance_reviews (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    worker_id BIGINT UNSIGNED NOT NULL,
    period_id BIGINT UNSIGNED NOT NULL,
    reviewer_id BIGINT UNSIGNED NOT NULL,
    review_date DATE NOT NULL,
    overall_rating DECIMAL(3,2),
    strengths TEXT,
    areas_for_improvement TEXT,
    development_plan TEXT,
    status ENUM('draft', 'submitted', 'acknowledged'),
    FOREIGN KEY (worker_id) REFERENCES cms_workers(id) ON DELETE CASCADE,
    FOREIGN KEY (period_id) REFERENCES cms_performance_periods(id) ON DELETE CASCADE
);

CREATE TABLE cms_performance_competencies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    review_id BIGINT UNSIGNED NOT NULL,
    competency_name VARCHAR(100) NOT NULL,
    rating DECIMAL(3,2) NOT NULL,
    comments TEXT,
    FOREIGN KEY (review_id) REFERENCES cms_performance_reviews(id) ON DELETE CASCADE
);
```

**Implementation Steps:**
1. Create performance period management
2. Build goal setting system
3. Create review templates
4. Add competency framework
5. Build review workflow
6. Create performance dashboards

**Files to Create:**
- `database/migrations/YYYY_MM_DD_create_cms_performance_management.php`
- `app/Infrastructure/Persistence/Eloquent/CMS/PerformanceReviewModel.php`
- `app/Domain/CMS/Core/Services/PerformanceManagementService.php`
- `app/Http/Controllers/CMS/PerformanceController.php`
- `resources/js/Pages/CMS/Performance/Goals.vue`
- `resources/js/Pages/CMS/Performance/Reviews.vue`

---

## F. PROJECT MANAGEMENT

### 17. Gantt Chart Visualization
**Priority:** P2 - Medium Value  
**Complexity:** Medium  
**Estimated Time:** 1 week  
**Current Status:** ⚪ Future (v2.0)

#### Business Value
- Visual project timeline
- Dependency tracking
- Resource planning
- Progress monitoring

#### Technical Requirements

**Database Schema:**
```sql
ALTER TABLE cms_jobs ADD COLUMN parent_job_id BIGINT UNSIGNED;
ALTER TABLE cms_jobs ADD COLUMN progress_percentage DECIMAL(5,2) DEFAULT 0;

CREATE TABLE cms_job_dependencies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_id BIGINT UNSIGNED NOT NULL,
    depends_on_job_id BIGINT UNSIGNED NOT NULL,
    dependency_type ENUM('finish_to_start', 'start_to_start', 'finish_to_finish', 'start_to_finish'),
    lag_days INT DEFAULT 0,
    FOREIGN KEY (job_id) REFERENCES cms_jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (depends_on_job_id) REFERENCES cms_jobs(id) ON DELETE CASCADE
);

CREATE TABLE cms_job_milestones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    due_date DATE NOT NULL,
    is_completed BOOLEAN DEFAULT FALSE,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (job_id) REFERENCES cms_jobs(id) ON DELETE CASCADE
);
```

**Implementation Steps:**
1. Add job hierarchy (parent/child)
2. Create dependency system
3. Integrate Gantt chart library (dhtmlxGantt or similar)
4. Build Gantt view component
5. Add drag-and-drop rescheduling
6. Create critical path calculation

**Files to Create:**
- `database/migrations/YYYY_MM_DD_add_project_planning_features.php`
- `app/Domain/CMS/Core/Services/ProjectPlanningService.php`
- `resources/js/Pages/CMS/Projects/GanttView.vue`
- `resources/js/components/CMS/GanttChart.vue`

**Recommended Library:** `@dhtmlx/trial-gantt` or `frappe-gantt`

---

## G. COMMUNICATION & COLLABORATION

### 18. Internal Messaging System
**Priority:** P2 - Medium Value  
**Complexity:** High  
**Estimated Time:** 3 weeks  
**Current Status:** ⚪ Future (v2.0)

#### Business Value
- Team communication
- Reduce email clutter
- Real-time collaboration
- Message history

#### Technical Requirements

**Database Schema:**
```sql
CREATE TABLE cms_conversations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255),
    type ENUM('direct', 'group') DEFAULT 'direct',
    created_by BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE
);

CREATE TABLE cms_conversation_participants (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conversation_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_read_at TIMESTAMP NULL,
    FOREIGN KEY (conversation_id) REFERENCES cms_conversations(id) ON DELETE CASCADE,
    UNIQUE KEY unique_conversation_user (conversation_id, user_id)
);

CREATE TABLE cms_messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conversation_id BIGINT UNSIGNED NOT NULL,
    sender_id BIGINT UNSIGNED NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES cms_conversations(id) ON DELETE CASCADE
);

CREATE TABLE cms_message_attachments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    message_id BIGINT UNSIGNED NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT,
    FOREIGN KEY (message_id) REFERENCES cms_messages(id) ON DELETE CASCADE
);
```

**Implementation Steps:**
1. Create conversation system
2. Build real-time messaging with Laravel Echo
3. Add file attachments
4. Create notification system
5. Build message search
6. Add typing indicators

**Files to Create:**
- `database/migrations/YYYY_MM_DD_create_cms_messaging_system.php`
- `app/Infrastructure/Persistence/Eloquent/CMS/ConversationModel.php`
- `app/Infrastructure/Persistence/Eloquent/CMS/MessageModel.php`
- `app/Domain/CMS/Core/Services/MessagingService.php`
- `app/Http/Controllers/CMS/MessagingController.php`
- `app/Events/CMS/MessageSent.php`
- `resources/js/Pages/CMS/Messages/Index.vue`
- `resources/js/components/CMS/ChatWindow.vue`

**Dependencies:** Laravel Echo, Pusher or Laravel WebSockets

---

### 19. Calendar & Meeting Scheduler
**Priority:** P2 - Medium Value  
**Complexity:** Medium  
**Estimated Time:** 2 weeks  
**Current Status:** ⚪ Future (v2.0)

#### Business Value
- Schedule meetings
- Team calendar
- Availability management
- Meeting reminders

#### Technical Requirements

**Database Schema:**
```sql
CREATE TABLE cms_calendar_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    event_type ENUM('meeting', 'task', 'reminder', 'other'),
    start_datetime DATETIME NOT NULL,
    end_datetime DATETIME NOT NULL,
    location VARCHAR(255),
    is_all_day BOOLEAN DEFAULT FALSE,
    recurrence_rule VARCHAR(255),
    created_by BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE
);

CREATE TABLE cms_event_attendees (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    response ENUM('pending', 'accepted', 'declined', 'tentative') DEFAULT 'pending',
    responded_at TIMESTAMP NULL,
    FOREIGN KEY (event_id) REFERENCES cms_calendar_events(id) ON DELETE CASCADE
);

CREATE TABLE cms_meeting_rooms (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    capacity INT,
    location VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE
);
```

**Implementation Steps:**
1. Create calendar event system
2. Build calendar UI (FullCalendar.js)
3. Add meeting scheduling
4. Create availability checker
5. Add email invitations
6. Build meeting room booking

**Files to Create:**
- `database/migrations/YYYY_MM_DD_create_cms_calendar_system.php`
- `app/Infrastructure/Persistence/Eloquent/CMS/CalendarEventModel.php`
- `app/Domain/CMS/Core/Services/CalendarService.php`
- `app/Http/Controllers/CMS/CalendarController.php`
- `resources/js/Pages/CMS/Calendar/Index.vue`
- `resources/js/components/CMS/CalendarView.vue`

**Recommended Library:** `@fullcalendar/vue3`

---


## H. API & INTEGRATIONS

### 20. REST API for Third-Party Integrations
**Priority:** P2 - Medium Value  
**Complexity:** High  
**Estimated Time:** 4 weeks  
**Current Status:** ⚪ Not Planned (Future v2.0)

#### Business Value
- Third-party integrations
- Mobile app development
- Automation capabilities
- Ecosystem expansion

#### Technical Requirements

**API Features:**
- RESTful endpoints for all resources
- OAuth 2.0 authentication
- Rate limiting
- API versioning
- Comprehensive documentation

**Database Schema:**
```sql
CREATE TABLE cms_api_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    scopes TEXT,
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE
);

CREATE TABLE cms_api_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    token_id BIGINT UNSIGNED,
    endpoint VARCHAR(255) NOT NULL,
    method VARCHAR(10) NOT NULL,
    status_code INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    request_body TEXT,
    response_body TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**Implementation Steps:**
1. Install Laravel Sanctum or Passport
2. Create API resource controllers
3. Add API authentication
4. Implement rate limiting
5. Create API documentation (OpenAPI/Swagger)
6. Add webhook system
7. Build API testing suite

**API Endpoints:**
- `/api/v1/customers` - Customer management
- `/api/v1/jobs` - Job management
- `/api/v1/invoices` - Invoice management
- `/api/v1/payments` - Payment tracking
- `/api/v1/inventory` - Inventory management
- `/api/v1/reports` - Report generation

**Files to Create:**
- `app/Http/Controllers/API/V1/CustomerApiController.php`
- `app/Http/Controllers/API/V1/InvoiceApiController.php`
- `app/Http/Resources/CustomerResource.php`
- `app/Http/Middleware/API/RateLimitApi.php`
- `routes/api.php` (enhance)
- `docs/api/API_DOCUMENTATION.md`

---

### 21. Webhook System
**Priority:** P2 - Medium Value  
**Complexity:** Medium  
**Estimated Time:** 1 week  
**Current Status:** ⚪ Not Planned

#### Business Value
- Real-time event notifications
- Integration automation
- Third-party sync

#### Technical Requirements

**Database Schema:**
```sql
CREATE TABLE cms_webhooks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    url VARCHAR(500) NOT NULL,
    events TEXT NOT NULL,
    secret VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE
);

CREATE TABLE cms_webhook_deliveries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    webhook_id BIGINT UNSIGNED NOT NULL,
    event_type VARCHAR(100) NOT NULL,
    payload TEXT NOT NULL,
    response_status INT,
    response_body TEXT,
    delivered_at TIMESTAMP NULL,
    failed_at TIMESTAMP NULL,
    retry_count INT DEFAULT 0,
    FOREIGN KEY (webhook_id) REFERENCES cms_webhooks(id) ON DELETE CASCADE
);
```

**Webhook Events:**
- `invoice.created`
- `invoice.paid`
- `payment.received`
- `job.completed`
- `customer.created`
- `inventory.low_stock`

**Implementation Steps:**
1. Create webhook management UI
2. Build webhook delivery system
3. Add signature verification
4. Implement retry logic
5. Create delivery logs
6. Add webhook testing tool

**Files to Create:**
- `database/migrations/YYYY_MM_DD_create_cms_webhooks.php`
- `app/Infrastructure/Persistence/Eloquent/CMS/WebhookModel.php`
- `app/Domain/CMS/Core/Services/WebhookService.php`
- `app/Jobs/DeliverWebhook.php`
- `app/Http/Controllers/CMS/WebhookController.php`
- `resources/js/Pages/CMS/Settings/Webhooks.vue`

---

### 22. Payment Gateway Integration
**Priority:** P3 - Low Value  
**Complexity:** High  
**Estimated Time:** 3 weeks  
**Current Status:** ⚪ Not Planned

#### Business Value
- Online payment collection
- Automated payment processing
- Reduced manual work

#### Technical Requirements

**Supported Gateways:**
- Stripe
- PayPal
- Flutterwave (Africa)
- Paystack (Africa)

**Database Schema:**
```sql
CREATE TABLE cms_payment_gateways (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    gateway_name VARCHAR(50) NOT NULL,
    api_key VARCHAR(255),
    api_secret VARCHAR(255),
    webhook_secret VARCHAR(255),
    is_live BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE
);

CREATE TABLE cms_online_payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    payment_id BIGINT UNSIGNED NOT NULL,
    gateway_name VARCHAR(50) NOT NULL,
    transaction_id VARCHAR(255) UNIQUE,
    gateway_response TEXT,
    status ENUM('pending', 'completed', 'failed', 'refunded'),
    amount DECIMAL(15,2) NOT NULL,
    currency_code VARCHAR(3),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (payment_id) REFERENCES cms_payments(id) ON DELETE CASCADE
);
```

**Implementation Steps:**
1. Install payment gateway SDKs
2. Create gateway configuration UI
3. Build payment link generation
4. Add webhook handlers
5. Implement refund system
6. Create payment reconciliation

**Files to Create:**
- `database/migrations/YYYY_MM_DD_create_cms_payment_gateways.php`
- `app/Services/CMS/PaymentGateway/StripeService.php`
- `app/Services/CMS/PaymentGateway/FlutterwaveService.php`
- `app/Http/Controllers/CMS/PaymentGatewayController.php`
- `resources/js/Pages/CMS/Settings/PaymentGateways.vue`

**Note:** MyGrowNet doesn't collect payments on behalf of clients, so this is optional for companies wanting online payment capability.

---

## I. ADVANCED FEATURES

### 23. Multi-Language Support
**Priority:** P3 - Low Value  
**Complexity:** Medium  
**Estimated Time:** 2 weeks  
**Current Status:** ⚪ Not Planned

#### Business Value
- International markets
- Multi-lingual teams
- Better user experience

#### Technical Requirements

**Implementation Steps:**
1. Install Laravel localization
2. Extract all text to language files
3. Create language switcher UI
4. Add RTL support for Arabic
5. Translate to key languages (French, Portuguese for Africa)
6. Add date/number localization

**Languages to Support:**
- English (default)
- French (for Francophone Africa)
- Portuguese (for Lusophone Africa)
- Arabic (optional)

**Files to Create:**
- `lang/en/cms.php`
- `lang/fr/cms.php`
- `lang/pt/cms.php`
- `resources/js/composables/useTranslation.ts`
- `resources/js/components/CMS/LanguageSwitcher.vue`

---

### 24. Document Version Control
**Priority:** P2 - Medium Value  
**Complexity:** Medium  
**Estimated Time:** 1 week  
**Current Status:** ⚪ Future (v2.0)

#### Business Value
- Track document changes
- Restore previous versions
- Audit trail for documents

#### Technical Requirements

**Database Schema:**
```sql
CREATE TABLE cms_document_versions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    document_type VARCHAR(50) NOT NULL,
    document_id BIGINT UNSIGNED NOT NULL,
    version_number INT NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT,
    uploaded_by BIGINT UNSIGNED,
    change_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**Implementation Steps:**
1. Modify document upload to create versions
2. Add version listing UI
3. Create version comparison
4. Add restore functionality
5. Implement version cleanup (keep last N versions)

**Files to Create:**
- `database/migrations/YYYY_MM_DD_create_cms_document_versions.php`
- `app/Domain/CMS/Core/Services/DocumentVersionService.php`
- `resources/js/components/CMS/DocumentVersionHistory.vue`

---

### 25. Custom Report Builder
**Priority:** P2 - Medium Value  
**Complexity:** Very High  
**Estimated Time:** 4 weeks  
**Current Status:** ⚪ Future (v2.0)

#### Business Value
- User-defined reports
- Flexible data analysis
- Reduced custom development

#### Technical Requirements

**Database Schema:**
```sql
CREATE TABLE cms_custom_reports (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    data_source VARCHAR(100) NOT NULL,
    columns TEXT NOT NULL,
    filters TEXT,
    grouping TEXT,
    sorting TEXT,
    created_by BIGINT UNSIGNED,
    is_shared BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE
);

CREATE TABLE cms_report_schedules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    report_id BIGINT UNSIGNED NOT NULL,
    frequency ENUM('daily', 'weekly', 'monthly'),
    recipients TEXT NOT NULL,
    last_sent_at TIMESTAMP NULL,
    FOREIGN KEY (report_id) REFERENCES cms_custom_reports(id) ON DELETE CASCADE
);
```

**Implementation Steps:**
1. Create report builder UI (drag-and-drop)
2. Build query generator
3. Add filter builder
4. Create chart options
5. Add export functionality
6. Implement report scheduling

**Files to Create:**
- `database/migrations/YYYY_MM_DD_create_cms_custom_reports.php`
- `app/Domain/CMS/Core/Services/ReportBuilderService.php`
- `app/Http/Controllers/CMS/CustomReportController.php`
- `resources/js/Pages/CMS/Reports/Builder.vue`
- `resources/js/components/CMS/ReportBuilder/QueryBuilder.vue`

---

## J. INFRASTRUCTURE & SCALABILITY

### 26. Multi-Tenant Support
**Priority:** P3 - Low Value  
**Complexity:** Very High  
**Estimated Time:** 6 weeks  
**Current Status:** ⚪ Future (v2.0)

#### Business Value
- SaaS deployment model
- Centralized management
- Cost efficiency

#### Technical Requirements

**Tenancy Models:**
- Single database with tenant_id (current approach)
- Database per tenant
- Schema per tenant

**Implementation Steps:**
1. Install Laravel multi-tenancy package
2. Add tenant identification middleware
3. Implement tenant isolation
4. Create tenant provisioning
5. Add tenant-specific domains
6. Build tenant management dashboard

**Recommendation:** Current single-tenant approach is sufficient for v1.0. Multi-tenancy is a major architectural change best suited for v3.0 if SaaS model is adopted.

---

### 27. Data Export & Migration Tools
**Priority:** P2 - Medium Value  
**Complexity:** Medium  
**Estimated Time:** 2 weeks  
**Current Status:** 🟡 Partial (CSV export only)

#### Business Value
- Data portability
- Backup capabilities
- Migration to other systems

#### Technical Requirements

**Export Formats:**
- CSV (already implemented)
- Excel (XLSX)
- JSON
- XML
- SQL dump

**Database Schema:**
```sql
CREATE TABLE cms_data_exports (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    export_type VARCHAR(50) NOT NULL,
    format VARCHAR(20) NOT NULL,
    file_path VARCHAR(500),
    status ENUM('pending', 'processing', 'completed', 'failed'),
    requested_by BIGINT UNSIGNED,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE
);
```

**Implementation Steps:**
1. Create export job system
2. Add Excel export (Laravel Excel)
3. Build full data export
4. Create import wizard
5. Add data validation
6. Build migration tools

**Files to Create:**
- `database/migrations/YYYY_MM_DD_create_cms_data_exports.php`
- `app/Jobs/ExportCompanyData.php`
- `app/Domain/CMS/Core/Services/DataExportService.php`
- `app/Http/Controllers/CMS/DataExportController.php`
- `resources/js/Pages/CMS/Settings/DataExport.vue`

---


## K. IMPLEMENTATION ROADMAP

### Phase 1: High-Value Quick Wins (8 weeks)
**Focus:** Features that provide immediate value with reasonable effort

**Week 1-2:**
- Custom Roles and Permission Matrix (P1)
- Fiscal Year Configuration (P2)

**Week 3-4:**
- Leave Management System (P1)
- Withholding Tax Tracking (P2)

**Week 5-6:**
- Bank Reconciliation (P1)
- Credit Notes & Debit Notes (P2)

**Week 7-8:**
- General Ledger System (P1)
- Balance Sheet Report (P1)

**Expected Outcome:** Core accounting and HR features complete

---

### Phase 2: Business Operations (8 weeks)
**Focus:** Operational efficiency features

**Week 9-10:**
- Contract Management (P2)
- Support Ticket System (P2)

**Week 11-12:**
- Multi-Warehouse Management (P2)
- Advanced Stock Valuation (P2)

**Week 13-14:**
- Performance Management System (P2)
- Gantt Chart Visualization (P2)

**Week 15-16:**
- Internal Messaging System (P2)
- Calendar & Meeting Scheduler (P2)

**Expected Outcome:** Complete operational management suite

---

### Phase 3: Integration & Automation (6 weeks)
**Focus:** API and third-party integrations

**Week 17-20:**
- REST API for Third-Party Integrations (P2)

**Week 21-22:**
- Webhook System (P2)
- Data Export & Migration Tools (P2)

**Expected Outcome:** Integration-ready platform

---

### Phase 4: Advanced Features (8 weeks)
**Focus:** Competitive advantage features

**Week 23-24:**
- Custom Report Builder (P2)
- Document Version Control (P2)

**Week 25-26:**
- Multi-Branch Management (P2)
- Multi-Language Support (P3)

**Week 27-28:**
- Payment Gateway Integration (P3)
- Gantt Chart enhancements

**Week 29-30:**
- Testing, optimization, documentation

**Expected Outcome:** Feature-complete enterprise CMS

---

## L. PRIORITY MATRIX

### Must-Have (Implement First)
1. Custom Roles and Permission Matrix
2. General Ledger System
3. Balance Sheet Report
4. Bank Reconciliation
5. Leave Management System

### Should-Have (Implement Second)
6. Fiscal Year Configuration
7. Credit Notes & Debit Notes
8. Withholding Tax Tracking
9. Contract Management
10. Support Ticket System
11. Multi-Warehouse Management
12. REST API

### Nice-to-Have (Implement Third)
13. Performance Management
14. Internal Messaging
15. Calendar & Meeting Scheduler
16. Custom Report Builder
17. Multi-Branch Management
18. Webhook System

### Future Consideration (v3.0+)
19. Multi-Entity Management
20. Single Sign-On
21. Multi-Tenant Support
22. Payment Gateway Integration
23. Multi-Language Support

---

## M. RESOURCE REQUIREMENTS

### Development Team
- **Backend Developer:** 2 full-time (Laravel, PHP)
- **Frontend Developer:** 1 full-time (Vue.js, TypeScript)
- **Full-Stack Developer:** 1 full-time (Both)
- **QA Engineer:** 1 part-time
- **DevOps Engineer:** 1 part-time

### Infrastructure
- **Development Environment:** Local/Docker
- **Staging Environment:** Cloud server
- **Production Environment:** Scalable cloud infrastructure
- **Database:** MySQL/PostgreSQL
- **Storage:** S3-compatible object storage
- **Queue System:** Redis

### Tools & Services
- **Version Control:** Git/GitHub
- **CI/CD:** GitHub Actions
- **Monitoring:** Laravel Telescope, Sentry
- **Documentation:** Markdown, Swagger/OpenAPI
- **Project Management:** Jira, Trello, or Linear

---

## N. COST ESTIMATES

### Development Costs (30 weeks)
- **Backend Development:** $60,000 - $90,000
- **Frontend Development:** $40,000 - $60,000
- **QA & Testing:** $15,000 - $25,000
- **DevOps & Infrastructure:** $10,000 - $15,000
- **Project Management:** $15,000 - $20,000

**Total Development:** $140,000 - $210,000

### Infrastructure Costs (Annual)
- **Cloud Hosting:** $3,000 - $6,000
- **Database:** $1,200 - $2,400
- **Storage:** $600 - $1,200
- **Monitoring & Logging:** $600 - $1,200
- **Third-Party Services:** $1,200 - $2,400

**Total Infrastructure:** $6,600 - $13,200/year

### Ongoing Costs (Annual)
- **Maintenance & Support:** $30,000 - $50,000
- **Feature Enhancements:** $20,000 - $40,000
- **Security Updates:** $10,000 - $15,000

**Total Ongoing:** $60,000 - $105,000/year

---

## O. RISK ASSESSMENT

### Technical Risks

**High Risk:**
- Multi-tenant architecture complexity
- Real-time messaging scalability
- API security vulnerabilities

**Mitigation:**
- Defer multi-tenancy to v3.0
- Use proven libraries (Laravel Echo, Pusher)
- Implement OAuth 2.0, rate limiting

**Medium Risk:**
- Database performance with large datasets
- Complex report generation
- Integration compatibility

**Mitigation:**
- Database indexing and optimization
- Queue-based report generation
- Comprehensive API testing

**Low Risk:**
- UI/UX consistency
- Browser compatibility
- Mobile responsiveness

**Mitigation:**
- Design system and component library
- Cross-browser testing
- PWA already implemented

---

### Business Risks

**Market Risk:**
- Competition from established players
- Changing customer requirements

**Mitigation:**
- Focus on Zambian market specifics
- Agile development for quick pivots

**Adoption Risk:**
- User resistance to change
- Training requirements

**Mitigation:**
- Comprehensive onboarding wizard
- Video tutorials and documentation
- Excellent customer support

---

## P. SUCCESS METRICS

### Development Metrics
- **Code Coverage:** >80%
- **Bug Rate:** <5 bugs per 1000 lines of code
- **API Response Time:** <200ms average
- **Page Load Time:** <2 seconds

### Business Metrics
- **User Adoption:** 80% of features used within 3 months
- **Customer Satisfaction:** >4.5/5 rating
- **Support Tickets:** <10% of users need support
- **Retention Rate:** >90% annual retention

### Technical Metrics
- **Uptime:** 99.9% availability
- **Database Performance:** <100ms query time
- **API Reliability:** 99.5% success rate
- **Security:** Zero critical vulnerabilities

---

## Q. TESTING STRATEGY

### Unit Testing
- All service classes
- All value objects
- Critical business logic
- Target: 80% code coverage

### Integration Testing
- API endpoints
- Database operations
- Third-party integrations
- Payment processing

### End-to-End Testing
- Critical user workflows
- Invoice creation to payment
- Job lifecycle
- Report generation

### Performance Testing
- Load testing (1000 concurrent users)
- Stress testing
- Database query optimization
- API response times

### Security Testing
- Penetration testing
- Vulnerability scanning
- Authentication/authorization
- Data encryption

---

## R. DOCUMENTATION REQUIREMENTS

### Technical Documentation
- API documentation (OpenAPI/Swagger)
- Database schema documentation
- Architecture diagrams
- Deployment guides

### User Documentation
- User manual
- Video tutorials
- FAQ section
- Troubleshooting guides

### Developer Documentation
- Setup instructions
- Coding standards
- Contribution guidelines
- Testing procedures

---

## S. DEPLOYMENT STRATEGY

### Staging Deployment
- Deploy to staging environment
- Run automated tests
- Manual QA testing
- Performance testing

### Production Deployment
- Blue-green deployment
- Database migrations
- Feature flags for gradual rollout
- Rollback plan

### Post-Deployment
- Monitor error rates
- Check performance metrics
- Gather user feedback
- Hot-fix critical issues

---

## T. MAINTENANCE PLAN

### Regular Maintenance
- **Weekly:** Security updates, bug fixes
- **Monthly:** Performance optimization, minor features
- **Quarterly:** Major feature releases, infrastructure upgrades
- **Annually:** Architecture review, technology updates

### Support Levels
- **Critical:** 1-hour response, 4-hour resolution
- **High:** 4-hour response, 24-hour resolution
- **Medium:** 24-hour response, 3-day resolution
- **Low:** 3-day response, 1-week resolution

---

## CONCLUSION

This implementation plan provides a comprehensive roadmap for all unplanned CMS features. The phased approach ensures:

1. **Quick Wins First:** High-value features implemented early
2. **Risk Management:** Complex features deferred appropriately
3. **Resource Efficiency:** Realistic timelines and budgets
4. **Quality Focus:** Comprehensive testing and documentation
5. **Business Alignment:** Features prioritized by business value

**Recommended Next Steps:**
1. Review and approve priority matrix
2. Allocate development resources
3. Begin Phase 1 implementation
4. Set up project tracking
5. Establish success metrics

**Total Timeline:** 30 weeks (7.5 months) for all planned features  
**Total Investment:** $140,000 - $210,000 development + ongoing costs

---

**Last Updated:** February 13, 2026  
**Document Owner:** Development Team  
**Review Cycle:** Monthly during implementation  
**Next Review:** March 13, 2026


---

## APPENDIX A: FEATURE COVERAGE VERIFICATION

### Features from MISSING_FEATURES_ROADMAP.md

**System Foundation & Administration:**
1. ✅ Multi-branch management - Covered (#1)
2. ✅ Multi-entity management - Covered (#2)
3. ✅ Financial year configuration - Covered (#3)
4. ✅ Custom roles and permission matrix - Covered (#4)
5. ✅ Single Sign-On (SSO) - Covered (#5)
6. ⚠️ Backup & disaster recovery - Infrastructure (not application feature)
7. ⚠️ Time zone management - Low priority, not included

**Corporate Governance:**
- All marked "Out of scope" - Intentionally excluded

**Financial Management:**
8. ✅ General ledger - Covered (#6)
9. ✅ Balance sheet - Covered (#7)
10. ✅ Bank reconciliation - Covered (#8)
11. ✅ Credit notes & debit notes - Covered (#9)
12. ✅ Withholding tax tracking - Covered (#10)
13. ⚠️ Journal entries - Part of General Ledger (#6)
14. ⚠️ Trial balance - Part of Chart of Accounts (already implemented)
15. ⚠️ Cost center accounting - Not mentioned, adding below
16. ⚠️ Department-level reporting - Not mentioned, adding below
17. ⚠️ Deferred revenue tracking - Not mentioned, adding below
18. ⚠️ Asset revaluation - Not mentioned, adding below
19. ⚠️ Financial consolidation - Part of Multi-entity (#2)

**Sales & CRM:**
20. ✅ Contract management - Covered (#11)
21. ✅ Support ticket system - Covered (#12)
22. ⚠️ Loyalty program management - Out of scope

**Procurement & Inventory:**
23. ✅ Multi-warehouse management - Covered (#13)
24. ✅ Advanced stock valuation (FIFO) - Covered (#14)
25. ⚠️ Stock transfers - Part of Multi-warehouse (#13)
26. ⚠️ Purchase requisitions - Not mentioned, adding below
27. ⚠️ Import/export documentation - Out of scope

**Human Resources:**
28. ✅ Leave management - Covered (#15)
29. ✅ Performance management - Covered (#16)
30. ⚠️ Employee lifecycle tracking - Not mentioned, adding below
31. ⚠️ Pension management - Not mentioned, adding below
32. ⚠️ Recruitment management (ATS) - Out of scope
33. ⚠️ Interview scheduling - Out of scope

**Project Management:**
34. ✅ Gantt charts - Covered (#17)

**Communication & Collaboration:**
35. ✅ Internal messaging - Covered (#18)
36. ✅ Calendar & meeting scheduler - Covered (#19)
37. ⚠️ Announcements system - Not mentioned, adding below

**API & Integrations:**
38. ✅ REST API - Covered (#20)
39. ✅ Webhook system - Covered (#21)
40. ✅ Payment gateway integration - Covered (#22)
41. ⚠️ Zapier integration - Part of API/Webhooks
42. ⚠️ QuickBooks sync - Part of API
43. ⚠️ Xero sync - Part of API

**Advanced Features:**
44. ✅ Multi-language support - Covered (#23)
45. ✅ Document version control - Covered (#24)
46. ✅ Custom report builder - Covered (#25)

**Infrastructure:**
47. ✅ Multi-tenant support - Covered (#26)
48. ✅ Data export & migration tools - Covered (#27)
49. ⚠️ Load balancing - Infrastructure (not application feature)
50. ⚠️ System health monitoring - Infrastructure (not application feature)

### Missing Features to Add

Let me add the features I missed:

---


## ADDITIONAL UNPLANNED FEATURES

### 28. Cost Center Accounting
**Priority:** P2 - Medium Value  
**Complexity:** Medium  
**Estimated Time:** 2 weeks  
**Current Status:** ⚪ Future (v2.0)

#### Business Value
- Track costs by department/project
- Better cost allocation
- Departmental profitability

#### Technical Requirements

**Database Schema:**
```sql
CREATE TABLE cms_cost_centers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    parent_id BIGINT UNSIGNED,
    manager_id BIGINT UNSIGNED,
    budget DECIMAL(15,2),
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES cms_cost_centers(id) ON DELETE SET NULL
);

-- Add cost_center_id to relevant tables
ALTER TABLE cms_expenses ADD COLUMN cost_center_id BIGINT UNSIGNED;
ALTER TABLE cms_jobs ADD COLUMN cost_center_id BIGINT UNSIGNED;
ALTER TABLE cms_workers ADD COLUMN cost_center_id BIGINT UNSIGNED;
```

**Implementation Steps:**
1. Create cost center management
2. Add cost center selection to transactions
3. Build cost center reports
4. Add budget tracking per cost center
5. Create variance analysis

---

### 29. Purchase Requisitions
**Priority:** P2 - Medium Value  
**Complexity:** Low  
**Estimated Time:** 1 week  
**Current Status:** ⚪ Future (v2.0)

#### Business Value
- Formal purchase request process
- Budget control
- Approval workflow

#### Technical Requirements

**Database Schema:**
```sql
CREATE TABLE cms_purchase_requisitions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    requisition_number VARCHAR(50) UNIQUE NOT NULL,
    requested_by BIGINT UNSIGNED NOT NULL,
    department VARCHAR(100),
    cost_center_id BIGINT UNSIGNED,
    date_required DATE NOT NULL,
    justification TEXT,
    status ENUM('draft', 'submitted', 'approved', 'rejected', 'converted'),
    total_amount DECIMAL(15,2),
    approved_by BIGINT UNSIGNED,
    approved_at TIMESTAMP NULL,
    purchase_order_id BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE
);

CREATE TABLE cms_purchase_requisition_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    requisition_id BIGINT UNSIGNED NOT NULL,
    description TEXT NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    estimated_unit_price DECIMAL(15,2),
    estimated_total DECIMAL(15,2),
    FOREIGN KEY (requisition_id) REFERENCES cms_purchase_requisitions(id) ON DELETE CASCADE
);
```

**Implementation Steps:**
1. Create requisition CRUD
2. Add approval workflow
3. Build conversion to PO
4. Add budget checking
5. Create requisition reports

---

### 30. Employee Lifecycle Tracking
**Priority:** P2 - Medium Value  
**Complexity:** Medium  
**Estimated Time:** 2 weeks  
**Current Status:** ⚪ Future (v2.0)

#### Business Value
- Track employee journey
- Onboarding/offboarding checklists
- Compliance documentation

#### Technical Requirements

**Database Schema:**
```sql
CREATE TABLE cms_employee_lifecycle_stages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    worker_id BIGINT UNSIGNED NOT NULL,
    stage ENUM('recruitment', 'onboarding', 'active', 'offboarding', 'exited'),
    start_date DATE NOT NULL,
    end_date DATE,
    notes TEXT,
    FOREIGN KEY (worker_id) REFERENCES cms_workers(id) ON DELETE CASCADE
);

CREATE TABLE cms_onboarding_checklists (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    is_template BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE
);

CREATE TABLE cms_onboarding_tasks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    checklist_id BIGINT UNSIGNED NOT NULL,
    worker_id BIGINT UNSIGNED,
    task_name VARCHAR(255) NOT NULL,
    description TEXT,
    assigned_to BIGINT UNSIGNED,
    due_days INT,
    is_completed BOOLEAN DEFAULT FALSE,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (checklist_id) REFERENCES cms_onboarding_checklists(id) ON DELETE CASCADE
);

CREATE TABLE cms_exit_interviews (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    worker_id BIGINT UNSIGNED NOT NULL,
    exit_date DATE NOT NULL,
    reason VARCHAR(255),
    feedback TEXT,
    would_rehire BOOLEAN,
    conducted_by BIGINT UNSIGNED,
    conducted_at TIMESTAMP NULL,
    FOREIGN KEY (worker_id) REFERENCES cms_workers(id) ON DELETE CASCADE
);
```

**Implementation Steps:**
1. Create lifecycle stage tracking
2. Build onboarding checklist system
3. Add offboarding workflow
4. Create exit interview forms
5. Build lifecycle reports

---

### 31. Pension Management
**Priority:** P3 - Low Value  
**Complexity:** Medium  
**Estimated Time:** 1 week  
**Current Status:** ⚪ Future (v2.0)

#### Business Value
- NAPSA compliance (Zambia)
- Automatic pension calculations
- Contribution tracking

#### Technical Requirements

**Database Schema:**
```sql
CREATE TABLE cms_pension_schemes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    scheme_name VARCHAR(255) NOT NULL,
    scheme_type ENUM('napsa', 'private'),
    employer_rate DECIMAL(5,2),
    employee_rate DECIMAL(5,2),
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE
);

CREATE TABLE cms_pension_contributions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    worker_id BIGINT UNSIGNED NOT NULL,
    payroll_run_id BIGINT UNSIGNED NOT NULL,
    scheme_id BIGINT UNSIGNED NOT NULL,
    period_month INT NOT NULL,
    period_year INT NOT NULL,
    gross_salary DECIMAL(15,2) NOT NULL,
    employee_contribution DECIMAL(15,2) NOT NULL,
    employer_contribution DECIMAL(15,2) NOT NULL,
    total_contribution DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (worker_id) REFERENCES cms_workers(id) ON DELETE CASCADE,
    FOREIGN KEY (scheme_id) REFERENCES cms_pension_schemes(id)
);
```

**Zambian NAPSA Rates:**
- Employee: 5% of gross salary
- Employer: 5% of gross salary
- Total: 10%

**Implementation Steps:**
1. Create pension scheme configuration
2. Auto-calculate contributions in payroll
3. Build contribution reports
4. Add NAPSA submission file generation
5. Create contribution history

---

### 32. Announcements System
**Priority:** P2 - Medium Value  
**Complexity:** Low  
**Estimated Time:** 3 days  
**Current Status:** ⚪ Future (v2.0)

#### Business Value
- Company-wide communications
- Important updates
- Policy changes

#### Technical Requirements

**Database Schema:**
```sql
CREATE TABLE cms_announcements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    start_date DATETIME NOT NULL,
    end_date DATETIME,
    target_audience ENUM('all', 'managers', 'staff', 'custom'),
    is_published BOOLEAN DEFAULT FALSE,
    created_by BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE
);

CREATE TABLE cms_announcement_recipients (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    announcement_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    FOREIGN KEY (announcement_id) REFERENCES cms_announcements(id) ON DELETE CASCADE
);
```

**Implementation Steps:**
1. Create announcement CRUD
2. Add audience targeting
3. Build notification system
4. Create announcement banner
5. Add read tracking

---

### 33. Deferred Revenue Tracking
**Priority:** P3 - Low Value  
**Complexity:** Medium  
**Estimated Time:** 1 week  
**Current Status:** ⚪ Future (v2.0)

#### Business Value
- Proper revenue recognition
- Subscription accounting
- IFRS compliance

#### Technical Requirements

**Database Schema:**
```sql
CREATE TABLE cms_deferred_revenue (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    invoice_id BIGINT UNSIGNED NOT NULL,
    total_amount DECIMAL(15,2) NOT NULL,
    recognized_amount DECIMAL(15,2) DEFAULT 0,
    remaining_amount DECIMAL(15,2) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    recognition_method ENUM('straight_line', 'milestone'),
    status ENUM('active', 'completed'),
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE,
    FOREIGN KEY (invoice_id) REFERENCES cms_invoices(id) ON DELETE CASCADE
);

CREATE TABLE cms_revenue_recognition_schedule (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    deferred_revenue_id BIGINT UNSIGNED NOT NULL,
    recognition_date DATE NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    is_recognized BOOLEAN DEFAULT FALSE,
    recognized_at TIMESTAMP NULL,
    FOREIGN KEY (deferred_revenue_id) REFERENCES cms_deferred_revenue(id) ON DELETE CASCADE
);
```

**Implementation Steps:**
1. Create deferred revenue tracking
2. Build recognition schedule
3. Add automatic recognition job
4. Create deferred revenue reports
5. Integrate with financial statements

---

### 34. Asset Revaluation
**Priority:** P3 - Low Value  
**Complexity:** Medium  
**Estimated Time:** 1 week  
**Current Status:** ⚪ Future (v2.0)

#### Business Value
- Fair value accounting
- Asset value adjustments
- Financial accuracy

#### Technical Requirements

**Database Schema:**
```sql
CREATE TABLE cms_asset_revaluations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    asset_id BIGINT UNSIGNED NOT NULL,
    revaluation_date DATE NOT NULL,
    previous_value DECIMAL(15,2) NOT NULL,
    new_value DECIMAL(15,2) NOT NULL,
    revaluation_amount DECIMAL(15,2) NOT NULL,
    reason TEXT,
    appraiser_name VARCHAR(255),
    approved_by BIGINT UNSIGNED,
    approved_at TIMESTAMP NULL,
    FOREIGN KEY (asset_id) REFERENCES cms_assets(id) ON DELETE CASCADE
);
```

**Implementation Steps:**
1. Create revaluation recording
2. Add approval workflow
3. Update asset values
4. Create revaluation reports
5. Integrate with balance sheet

---

## UPDATED SUMMARY

**Total Unplanned Features Documented:** 34

**By Priority:**
- P1 (High Value): 5 features
- P2 (Medium Value): 20 features
- P3 (Low Value): 6 features
- P4 (Out of Scope): 3 features

**By Complexity:**
- Low: 5 features
- Medium: 18 features
- High: 8 features
- Very High: 3 features

**Total Estimated Time:** 
- P1 Features: 11 weeks
- P2 Features: 38 weeks
- P3 Features: 8 weeks
- **Grand Total: 57 weeks (14 months)**

**Recommended Phased Approach:**
- Phase 1 (P1 features): 11 weeks
- Phase 2 (P2 high-impact): 20 weeks
- Phase 3 (P2 remaining): 18 weeks
- Phase 4 (P3 optional): 8 weeks

---

**Last Updated:** February 13, 2026  
**Features Covered:** 34/34 (100%)  
**Document Status:** Complete
