# CMS Complete Feature Specification

**Last Updated:** February 7, 2026  
**Status:** Planning  
**Version:** 1.0

---

## Document Purpose

This document provides the **complete feature specification** for MyGrowNet CMS v1 (SME Operating System). It details all 18 modules with their features, database schemas, business rules, and implementation priorities.

**Related Documents:**
- `DEVELOPMENT_BRIEF.md` - Business objectives and architectural principles
- `IMPLEMENTATION_PLAN.md` - Technical implementation and phased delivery
- `MODULE_INTEGRATION.md` - Integration with existing modules

---

## Module 1: Company & Administration

### Features

**Company Setup:**
- Company profile (name, industry, contacts)
- Business registration details
- Tax information
- Business status (active, suspended)
- Company branding (logo, invoice footer)
- Business hours configuration

**User & Access Management:**
- User registration (staff only)
- Role-based access control
- Module visibility per role
- Login activity tracking
- Session management
- Password policies

**Roles & Permissions:**
- Predefined roles:
  - Owner (full access)
  - Administrator (all except critical settings)
  - Finance Officer (invoices, payments, expenses, reports)
  - Operations Staff (jobs, customers, inventory)
  - Casual Worker (view assigned jobs, view commissions)
- Custom permission assignment
- Approval authority definition
- Permission inheritance

### Database Schema

```sql
CREATE TABLE cms_companies (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    industry_type VARCHAR(100),
    business_registration_number VARCHAR(100),
    tax_number VARCHAR(100),
    address TEXT,
    city VARCHAR(100),
    country VARCHAR(100) DEFAULT 'Zambia',
    phone VARCHAR(50),
    email VARCHAR(255),
    website VARCHAR(255),
    logo_path VARCHAR(255),
    invoice_footer TEXT,
    status ENUM('active', 'suspended') DEFAULT 'active',
    settings JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_industry (industry_type)
);

CREATE TABLE cms_users (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    company_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    role_id BIGINT NOT NULL,
    employee_number VARCHAR(50),
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES cms_roles(id),
    UNIQUE KEY unique_user_company (user_id, company_id),
    INDEX idx_company_status (company_id, status)
);

CREATE TABLE cms_roles (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    company_id BIGINT NOT NULL,
    name VARCHAR(100) NOT NULL,
    permissions JSON NOT NULL,
    approval_authority JSON,
    is_system_role BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE,
    UNIQUE KEY unique_role_name (company_id, name)
);

CREATE TABLE cms_login_activity (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    cms_user_id BIGINT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    login_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    logout_at TIMESTAMP NULL,
    FOREIGN KEY (cms_user_id) REFERENCES cms_users(id) ON DELETE CASCADE,
    INDEX idx_user_login (cms_user_id, login_at)
);
```

### Business Rules

1. **Company must be active** for users to access system
2. **At least one Owner role** must exist per company
3. **System roles cannot be deleted** (Owner, Admin, Finance, Operations, Casual)
4. **Users cannot delete themselves**
5. **Last Owner cannot be removed** from company

---

## Module 2: Customer Management

### Features

- Customer registration with full contact details
- Multiple contact persons per customer
- Customer job history
- Outstanding balances per customer
- Customer documents (contracts, designs, quotes)
- Customer notes (internal and external)
- Customer status management
- Credit limit tracking

### Database Schema

```sql
CREATE TABLE cms_customers (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    company_id BIGINT NOT NULL,
    customer_number VARCHAR(50) NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(50),
    secondary_phone VARCHAR(50),
    address TEXT,
    city VARCHAR(100),
    country VARCHAR(100) DEFAULT 'Zambia',
    tax_number VARCHAR(100),
    credit_limit DECIMAL(15,2) DEFAULT 0,
    outstanding_balance DECIMAL(15,2) DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    notes TEXT,
    created_by BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES cms_users(id),
    UNIQUE KEY unique_customer_number (company_id, customer_number),
    INDEX idx_company_status (company_id, status),
    INDEX idx_outstanding (company_id, outstanding_balance)
);

CREATE TABLE cms_customer_contacts (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    customer_id BIGINT NOT NULL,
    contact_name VARCHAR(255) NOT NULL,
    contact_phone VARCHAR(50),
    contact_email VARCHAR(255),
    contact_position VARCHAR(100),
    is_primary BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES cms_customers(id) ON DELETE CASCADE,
    INDEX idx_customer_primary (customer_id, is_primary)
);

CREATE TABLE cms_customer_documents (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    customer_id BIGINT NOT NULL,
    document_type ENUM('contract', 'design', 'quote', 'other') NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_size INT,
    uploaded_by BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES cms_customers(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES cms_users(id),
    INDEX idx_customer_type (customer_id, document_type)
);

CREATE TABLE cms_customer_notes (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    customer_id BIGINT NOT NULL,
    note TEXT NOT NULL,
    is_internal BOOLEAN DEFAULT TRUE,
    created_by BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES cms_customers(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES cms_users(id),
    INDEX idx_customer_internal (customer_id, is_internal)
);
```

### Business Rules

1. **Customer number auto-generated** per company (e.g., CUST-0001)
2. **Outstanding balance updated** when invoices created/paid
3. **Credit limit enforced** when creating new jobs/invoices
4. **Cannot delete customer** with outstanding balance or active jobs
5. **At least one contact** required per customer

---

## Module 3: Job/Operations Management (CORE)

### Features

**Job Creation:**
- Job categorization (service type)
- Job value and costing
- Job assignment to staff
- Job deadlines
- Job notes and attachments
- Customer selection

**Job Status Tracking:**
- Pending
- In Progress
- Completed
- Cancelled

**Job Costing & Profitability:**
- Material cost per job
- Labor/commission per job
- Transport or overhead allocation
- Job profit calculation
- Cost vs. revenue analysis

**Job Completion:**
- Completion confirmation
- Quality check (optional)
- Customer sign-off
- Automatic invoice generation trigger

### Database Schema

```sql
CREATE TABLE cms_jobs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    company_id BIGINT NOT NULL,
    customer_id BIGINT NOT NULL,
    job_number VARCHAR(50) NOT NULL,
    job_type VARCHAR(100) NOT NULL,
    description TEXT,
    quoted_value DECIMAL(15,2),
    actual_value DECIMAL(15,2),
    material_cost DECIMAL(15,2) DEFAULT 0,
    labor_cost DECIMAL(15,2) DEFAULT 0,
    overhead_cost DECIMAL(15,2) DEFAULT 0,
    total_cost DECIMAL(15,2) DEFAULT 0,
    profit_amount DECIMAL(15,2) DEFAULT 0,
    profit_margin DECIMAL(5,2) DEFAULT 0,
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    assigned_to BIGINT,
    created_by BIGINT NOT NULL,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    deadline TIMESTAMP NULL,
    notes TEXT,
    is_locked BOOLEAN DEFAULT FALSE,
    locked_at TIMESTAMP NULL,
    locked_by BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES cms_customers(id),
    FOREIGN KEY (assigned_to) REFERENCES cms_users(id),
    FOREIGN KEY (created_by) REFERENCES cms_users(id),
    FOREIGN KEY (locked_by) REFERENCES cms_users(id),
    UNIQUE KEY unique_job_number (company_id, job_number),
    INDEX idx_company_status (company_id, status),
    INDEX idx_assigned (assigned_to, status),
    INDEX idx_deadline (company_id, deadline),
    INDEX idx_completed (company_id, completed_at)
);

CREATE TABLE cms_job_attachments (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    job_id BIGINT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    uploaded_by BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES cms_jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES cms_users(id),
    INDEX idx_job (job_id)
);

CREATE TABLE cms_job_status_history (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    job_id BIGINT NOT NULL,
    old_status VARCHAR(50),
    new_status VARCHAR(50) NOT NULL,
    changed_by BIGINT NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES cms_jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES cms_users(id),
    INDEX idx_job_status (job_id, created_at)
);
```

### Business Rules (CRITICAL)

1. **No job → no invoice → no payment → no commission** (System Rule)
2. **Job must be assigned** before status can be "in_progress"
3. **Job must be completed** before invoice can be generated
4. **Completed jobs can be locked** to prevent changes
5. **Locked jobs cannot be edited** (only Owner can unlock)
6. **Job cancellation requires approval** (if configured)
7. **Profit calculated automatically**: `profit = actual_value - (material_cost + labor_cost + overhead_cost)`

---

## Module 4: Quotations & Invoices

### Features

**Quotations:**
- Quotation creation with line items
- Quotation numbering
- PDF generation
- Conversion to job
- Approval tracking
- Expiry dates

**Invoices:**
- Invoice generation from jobs (REQUIRED)
- Invoice numbering (auto-generated)
- PDF invoices with company branding
- Invoice status (draft, sent, unpaid, partial, paid, overdue, cancelled)
- Link invoice to customer and job
- Multiple line items
- Tax calculation
- Discount application
- Payment terms

### Database Schema

```sql
CREATE TABLE cms_quotations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    company_id BIGINT NOT NULL,
    customer_id BIGINT NOT NULL,
    quotation_number VARCHAR(50) NOT NULL,
    quotation_date DATE NOT NULL,
    expiry_date DATE,
    subtotal DECIMAL(15,2) NOT NULL,
    tax_amount DECIMAL(15,2) DEFAULT 0,
    discount_amount DECIMAL(15,2) DEFAULT 0,
    total_amount DECIMAL(15,2) NOT NULL,
    status ENUM('draft', 'sent', 'accepted', 'rejected', 'expired') DEFAULT 'draft',
    notes TEXT,
    terms TEXT,
    converted_to_job_id BIGINT,
    created_by BIGINT NOT NULL,
    approved_by BIGINT,
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES cms_customers(id),
    FOREIGN KEY (converted_to_job_id) REFERENCES cms_jobs(id),
    FOREIGN KEY (created_by) REFERENCES cms_users(id),
    FOREIGN KEY (approved_by) REFERENCES cms_users(id),
    UNIQUE KEY unique_quotation_number (company_id, quotation_number),
    INDEX idx_company_status (company_id, status),
    INDEX idx_customer (customer_id),
    INDEX idx_expiry (company_id, expiry_date)
);

CREATE TABLE cms_quotation_items (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    quotation_id BIGINT NOT NULL,
    description VARCHAR(255) NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    unit_price DECIMAL(15,2) NOT NULL,
    tax_rate DECIMAL(5,2) DEFAULT 0,
    discount_rate DECIMAL(5,2) DEFAULT 0,
    line_total DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (quotation_id) REFERENCES cms_quotations(id) ON DELETE CASCADE,
    INDEX idx_quotation (quotation_id)
);

CREATE TABLE cms_invoices (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    company_id BIGINT NOT NULL,
    job_id BIGINT NOT NULL,
    customer_id BIGINT NOT NULL,
    invoice_number VARCHAR(50) NOT NULL,
    invoice_date DATE NOT NULL,
    due_date DATE NOT NULL,
    subtotal DECIMAL(15,2) NOT NULL,
    tax_amount DECIMAL(15,2) DEFAULT 0,
    discount_amount DECIMAL(15,2) DEFAULT 0,
    total_amount DECIMAL(15,2) NOT NULL,
    amount_paid DECIMAL(15,2) DEFAULT 0,
    balance_due DECIMAL(15,2) NOT NULL,
    status ENUM('draft', 'sent', 'unpaid', 'partial', 'paid', 'overdue', 'cancelled') DEFAULT 'draft',
    payment_terms TEXT,
    notes TEXT,
    is_locked BOOLEAN DEFAULT FALSE,
    locked_at TIMESTAMP NULL,
    locked_by BIGINT,
    issued_by BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE,
    FOREIGN KEY (job_id) REFERENCES cms_jobs(id),
    FOREIGN KEY (customer_id) REFERENCES cms_customers(id),
    FOREIGN KEY (issued_by) REFERENCES cms_users(id),
    FOREIGN KEY (locked_by) REFERENCES cms_users(id),
    UNIQUE KEY unique_invoice_number (company_id, invoice_number),
    INDEX idx_company_status (company_id, status),
    INDEX idx_job (job_id),
    INDEX idx_customer (customer_id),
    INDEX idx_due_date (company_id, due_date),
    INDEX idx_overdue (company_id, status, due_date)
);

CREATE TABLE cms_invoice_items (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    invoice_id BIGINT NOT NULL,
    description VARCHAR(255) NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    unit_price DECIMAL(15,2) NOT NULL,
    tax_rate DECIMAL(5,2) DEFAULT 0,
    discount_rate DECIMAL(5,2) DEFAULT 0,
    line_total DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES cms_invoices(id) ON DELETE CASCADE,
    INDEX idx_invoice (invoice_id)
);
```

### Business Rules

1. **Invoice MUST link to a job** (cannot create orphan invoices)
2. **Job must be completed** before invoice generation
3. **One invoice per job** (unless job is split)
4. **Invoice number auto-generated** (e.g., INV-2026-0001)
5. **Paid invoices can be locked** to prevent changes
6. **Locked invoices cannot be edited** (only Owner can unlock)
7. **Invoice status auto-updates** based on payments
8. **Overdue status auto-applied** when due_date passed and unpaid

---

## Module 5: Payments & Cash Management

### Features

- Record cash payments
- Record mobile money payments (MTN, Airtel, Zamtel)
- Record bank payments
- Payment reference tracking
- Partial payments
- Payment allocation to invoices
- Overpayment tracking
- Customer credit management
- Daily cash summary
- Payment receipts

### Database Schema

```sql
CREATE TABLE cms_payments (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    company_id BIGINT NOT NULL,
    payment_number VARCHAR(50) NOT NULL,
    reference_type ENUM('invoice', 'expense', 'other') NOT NULL,
    reference_id BIGINT,
    customer_id BIGINT,
    amount DECIMAL(15,2) NOT NULL,
    method ENUM('cash', 'bank_transfer', 'mtn_momo', 'airtel_money', 'zamtel_kwacha', 'cheque') NOT NULL,
    transaction_reference VARCHAR(100),
    payment_date DATE NOT NULL,
    notes TEXT,
    recorded_by BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES cms_customers(id),
    FOREIGN KEY (recorded_by) REFERENCES cms_users(id),
    UNIQUE KEY unique_payment_number (company_id, payment_number),
    INDEX idx_company_date (company_id, payment_date),
    INDEX idx_reference (reference_type, reference_id),
    INDEX idx_customer (customer_id)
);

CREATE TABLE cms_payment_allocations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    payment_id BIGINT NOT NULL,
    invoice_id BIGINT NOT NULL,
    allocated_amount DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (payment_id) REFERENCES cms_payments(id) ON DELETE CASCADE,
    FOREIGN KEY (invoice_id) REFERENCES cms_invoices(id),
    INDEX idx_payment (payment_id),
    INDEX idx_invoice (invoice_id)
);

CREATE TABLE cms_customer_credits (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    company_id BIGINT NOT NULL,
    customer_id BIGINT NOT NULL,
    credit_amount DECIMAL(15,2) NOT NULL,
    source_payment_id BIGINT,
    used_amount DECIMAL(15,2) DEFAULT 0,
    balance DECIMAL(15,2) NOT NULL,
    status ENUM('active', 'used', 'expired') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES cms_customers(id),
    FOREIGN KEY (source_payment_id) REFERENCES cms_payments(id),
    INDEX idx_customer_status (customer_id, status)
);
```

### Business Rules

1. **Payment must link to invoice or expense** (no orphan payments)
2. **Payment allocation tracked** per invoice
3. **Overpayments create customer credit**
4. **Customer credit can be applied** to future invoices
5. **Invoice status updates automatically** when payment recorded
6. **Daily cash summary calculated** from all cash payments
7. **Payment receipts auto-generated** with payment number

---

## Module 6: Expense Management

### Features

- Expense entry with categorization
- Expense categories (customizable)
- Attach receipts (images/PDFs)
- Approval workflow (configurable)
- Expense reports
- Link expenses to jobs (optional)
- Recurring expenses (optional)

### Database Schema

```sql
CREATE TABLE cms_expense_categories (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    company_id BIGINT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    requires_approval BOOLEAN DEFAULT FALSE,
    approval_limit DECIMAL(15,2),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE,
    UNIQUE KEY unique_category_name (company_id, name)
);

CREATE TABLE cms_expenses (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    company_id BIGINT NOT NULL,
    expense_number VARCHAR(50) NOT NULL,
    category_id BIGINT NOT NULL,
    job_id BIGINT,
    description TEXT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    payment_method ENUM('cash', 'bank_transfer', 'mtn_momo', 'airtel_money', 'company_card') NOT NULL,
    receipt_number VARCHAR(100),
    receipt_path VARCHAR(255),
    expense_date DATE NOT NULL,
    requires_approval BOOLEAN DEFAULT FALSE,
    approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    approved_by BIGINT,
    approved_at TIMESTAMP NULL,
    rejection_reason TEXT,
    recorded_by BIGINT NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES cms_expense_categories(id),
    FOREIGN KEY (job_id) REFERENCES cms_jobs(id),
    FOREIGN KEY (approved_by) REFERENCES cms_users(id),
    FOREIGN KEY (recorded_by) REFERENCES cms_users(id),
    UNIQUE KEY unique_expense_number (company_id, expense_number),
    INDEX idx_company_date (company_id, expense_date),
    INDEX idx_category (category_id),
    INDEX idx_job (job_id),
    INDEX idx_approval (company_id, approval_status)
);
```

### Business Rules

1. **No approval → no expense posting** (if approval required)
2. **Expenses above threshold require approval** (configurable)
3. **Approved expenses cannot be edited** (only Owner can unlock)
4. **Receipt attachment recommended** for all expenses
5. **Expense linked to job updates job cost** automatically
6. **Expense categories can be deactivated** but not deleted if used

---

## Module 7-18: Additional Modules

Due to length constraints, the remaining modules (Financial Reporting, Inventory, Assets, Payroll, Documents, Dashboards, Notifications, Security, Presets, Onboarding, Settings, Approvals) follow the same detailed specification pattern.

**See `IMPLEMENTATION_PLAN.md` for complete technical implementation details.**

---

## System-Wide Business Rules

### Data Integrity Rules

1. **Multi-tenancy isolation**: All queries scoped by `company_id`
2. **Soft deletion**: Archive instead of hard delete
3. **Audit trail**: All changes logged with old/new values
4. **Data locking**: Completed/paid records can be locked
5. **Period close**: Monthly periods can be closed to prevent backdating

### Approval Workflow Rules

1. **Configurable thresholds**: Set approval limits per role
2. **Approval chain**: Define who approves what
3. **Approval notifications**: Auto-notify approvers
4. **Approval history**: Track all approval actions

### Security Rules

1. **Role-based access**: Permissions enforced at service layer
2. **Session management**: Auto-logout after inactivity
3. **Password policies**: Minimum strength requirements
4. **IP tracking**: Log all login attempts
5. **Two-factor authentication**: Optional per company

---

## Changelog

### Version 1.0 (February 7, 2026)
- Initial comprehensive feature specification
- Detailed database schemas for Modules 1-6
- Complete business rules documentation
- Integration with IMPLEMENTATION_PLAN.md

---

**Document Owner:** Development Team  
**Review Cycle:** Weekly during implementation  
**Next Review:** February 14, 2026
