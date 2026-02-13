# MyGrowNet CMS Development Brief

**Project:** SME Operating System – v1  
**Status:** Planning  
**Last Updated:** February 5, 2026

## 1. OBJECTIVE (VERY IMPORTANT)

Build a modular SME Company Management System (CMS) inside the existing MyGrowNet ecosystem, using a DDD (Domain-Driven Design) approach, where:

- Modules are independent but logically linked
- The system operates as one company
- It can later be sold to SMEs
- Geopamu Investments Limited is the first pilot tenant
- The CMS must enforce discipline, accountability, and traceability

## 2. ARCHITECTURAL PRINCIPLES (NON-NEGOTIABLE)

### 2.1 Domain-Driven Design

- Each module has its own bounded context
- Shared core entities live in a Core Domain
- Modules communicate via domain events, not direct coupling

### 2.2 Multi-Tenant

- One database
- Tenant ID on all business tables
- Strict data isolation per company

### 2.3 Rule-Driven System

**If something is not linked in the system: It does not exist**

## 3. CORE SHARED DOMAIN (FOUNDATION)

These entities must exist once and be referenced by all modules.

### 3.1 Core Entities

**Company (Tenant)**
- id
- name
- industry_type
- status

**User**
- id
- company_id
- name
- email
- status

**Role**
- id
- company_id
- name
- permissions (JSON)

**Customer**
- id
- company_id
- name
- contact_details

**Job**
- id
- company_id
- customer_id
- job_type
- total_value
- status
- created_by

**Payment**
- id
- company_id
- reference_type (invoice/expense)
- reference_id
- amount
- method
- date

**Asset**
- id
- company_id
- name
- assigned_to
- status

**Document**
- id
- company_id
- linked_entity_type
- linked_entity_id
- file_path

## 4. CMS MODULES (SCOPE v1)

Only the following modules are included in CMS v1.

### 4.1 Administration Module

- Company profile
- User management
- Role & permission management
- Module visibility control

### 4.2 Operations / Job Management

- Create jobs
- Assign workers
- Track job status:
  - Pending
  - In Progress
  - Completed
- Attach documents
- Link materials used

**Rule:** A job must exist before any invoice or commission.

### 4.3 Finance (GrowFinance – Restricted Mode)

- Generate invoices from jobs
- Record payments
- Record expenses
- View cashbook
- Generate basic P&L

**Rules:**
- Every invoice must link to a job
- Every payment must link to an invoice or expense
- No manual orphan entries

### 4.4 Inventory & Assets

**Inventory:**
- Consumables
- Quantity tracking
- Job usage deduction

**Assets:**
- Machines
- Tools
- Assignment to users
- Maintenance log

### 4.5 Payroll & Commission

- Worker type:
  - Casual
  - Employee
- Commission rules per job
- Auto-calculation when job is completed
- Payment status tracking

**Rule:** No completed job → no commission

### 4.6 Document Management

- Upload documents
- Attach to:
  - Jobs
  - Users
  - Assets
  - Company
- Permission-based access

## 5. EVENT-DRIVEN LINKING (VERY IMPORTANT)

Modules must not call each other directly.

### Required Domain Events

- `JobCreated`
- `JobAssigned`
- `JobCompleted`
- `InvoiceGenerated`
- `PaymentRecorded`
- `CommissionCalculated`
- `InventoryDeducted`

### Example Flow

```
Job marked as Completed
    ↓
Event: JobCompleted
    ↓
Listeners:
    - Finance → Generate invoice
    - Payroll → Calculate commission
    - Inventory → Deduct materials
    - Reporting → Update dashboards
```

## 6. GEOPAMU PRESET (INDUSTRY TEMPLATE)

**Industry Type:** Printing & Branding

**Default Configuration:**
- Job types: T-shirt printing, branding, signage
- Commission model: Per job
- Inventory items: Ink, T-shirts, vinyl
- Roles predefined
- Chart of accounts preloaded

Preset must be reusable for other printing SMEs.

## 7. ROLES & PERMISSIONS MATRIX

### Owner / Director
- Full system access
- Approvals
- Reports

### Administrator
- Create jobs
- Assign staff
- Manage documents

### Finance Officer
- Invoices
- Payments
- Expenses
- Reports

### Operations Staff
- View assigned jobs
- Update job status

### Casual Worker
- View assigned jobs
- View commission
- No finance or asset access

**Principle:** Access is minimal and role-based.

## 8. UI / UX REQUIREMENTS

- CMS presented as one product
- Hide non-CMS modules (MLM, Ubumi, etc.)
- Dashboard must show:
  - Jobs today
  - Revenue
  - Outstanding payments
  - Worker performance
- Simple, clean, SME-friendly UI

## 9. DATA RULES (ENFORCEMENT)

System must enforce:

- No job → no invoice
- No invoice → no payment
- No job → no commission
- No approval → no expense
- No tenant → no access

**These rules must be enforced at service level, not UI only.**

## 10. PHASED DELIVERY PLAN

### Phase 1
- Core entities
- Job management
- Finance linking
- Roles & permissions

### Phase 2
- Inventory
- Assets
- Payroll & commission

### Phase 3
- Reporting
- Presets
- Onboarding wizard

## 11. NON-GOALS (DO NOT BUILD NOW)

- MLM logic
- Marketplace selling
- Advertising tools
- Family tree features

**CMS must remain business-focused.**

## 12. FINAL NOTE TO KIRO

This is not just software. It is a **business operating system**.

Design decisions must:
- Favor clarity over cleverness
- Favor discipline over flexibility
- Favor traceability over speed

---

## Related Documents

- [CMS Implementation Plan](./IMPLEMENTATION_PLAN.md)
- [CMS Architecture](./ARCHITECTURE.md)
