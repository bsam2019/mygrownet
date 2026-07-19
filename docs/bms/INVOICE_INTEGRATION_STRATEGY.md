# CMS Invoice Integration Strategy

**Last Updated:** February 7, 2026
**Status:** Planning

## Overview

The MyGrowNet platform currently has **THREE separate invoice systems**:

1. **Quick Invoice Generator** - Public tool for anyone (no account required)
2. **GrowFinance Invoices** - For MyGrowNet members managing their businesses
3. **CMS Invoices** (NEW) - For SME companies using the CMS

This document outlines the integration strategy to ensure these systems coexist harmoniously while serving different purposes.

## Current Invoice Systems

### 1. Quick Invoice Generator

**Purpose:** Lead generation tool - free invoice generator for public use

**Location:** `/quick-invoice`

**Key Features:**
- No account required (guest users)
- 5 PDF templates (Classic, Modern, Minimal, Professional, Bold)
- Custom branding (logo, colors, signature)
- Document types: Invoice, Delivery Note, Quotation, Receipt
- Share via WhatsApp, Email, Download PDF
- Optional: Save templates for registered users

**Architecture:**
- Domain-Driven Design
- UUID-based documents
- Session-based for guests, user-based for members
- On-demand PDF generation (no storage)
- Tables: `quick_invoice_documents`, `quick_invoice_items`, `quick_invoice_profiles`

**Target Users:** General public, potential MyGrowNet members

---

### 2. GrowFinance Invoices

**Purpose:** Business management tool for MyGrowNet members

**Location:** `/growfinance/invoices`

**Key Features:**
- Requires MyGrowNet membership
- Subscription-based limits (Free: 5/month, Professional: 50/month, Enterprise: unlimited)
- Customer management integration
- Payment tracking
- Invoice templates with custom colors
- PDF export (Professional+ feature)
- Email sending
- Status workflow: Draft → Sent → Partial → Paid → Cancelled

**Architecture:**
- Domain-Driven Design
- Auto-incrementing invoice numbers (INV-000001)
- Business-scoped (business_id)
- Tables: `growfinance_invoices`, `growfinance_invoice_items`, `growfinance_invoice_templates`

**Target Users:** MyGrowNet members running businesses

---

### 3. CMS Invoices (NEW)

**Purpose:** Integrated invoicing for SME companies using the full CMS

**Location:** `/cms/invoices`

**Key Features:**
- Multi-tenant (company_id scoping)
- Auto-generated from completed jobs
- Job-to-invoice automation
- Payment allocation system
- Customer balance tracking
- Commission calculation trigger
- Audit trail integration
- Status workflow: Draft → Sent → Partial → Paid → Cancelled → Void

**Architecture:**
- Domain-Driven Design
- Auto-incrementing invoice numbers (INV-2026-0001)
- Company-scoped (company_id)
- Tables: `cms_invoices`, `cms_invoice_items`, `cms_payments`, `cms_payment_allocations`

**Target Users:** SME companies using CMS (e.g., Geopamu Investments Limited)

---

## Integration Strategy

### Decision: Keep Systems Separate

**Rationale:**
1. **Different purposes** - Each system serves a distinct user base and use case
2. **Different scoping** - Quick Invoice (session/user), GrowFinance (business_id), CMS (company_id)
3. **Different features** - Each has unique requirements and workflows
4. **Different access control** - Public vs Member vs CMS User
5. **Future extraction** - CMS designed to be extracted as standalone SaaS product

### Coexistence Strategy

#### 1. Clear Separation of Concerns

```
┌─────────────────────────────────────────────────────────────┐
│                    MyGrowNet Platform                        │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌──────────────────┐  ┌──────────────────┐  ┌───────────┐ │
│  │ Quick Invoice    │  │ GrowFinance      │  │ CMS       │ │
│  │ Generator        │  │ Invoices         │  │ Invoices  │ │
│  ├──────────────────┤  ├──────────────────┤  ├───────────┤ │
│  │ Public Tool      │  │ Member Tool      │  │ SME Tool  │ │
│  │ Lead Gen         │  │ Business Mgmt    │  │ Full ERP  │ │
│  │ No Auth Required │  │ Auth Required    │  │ CMS Access│ │
│  │ Session/User     │  │ business_id      │  │ company_id│ │
│  └──────────────────┘  └──────────────────┘  └───────────┘ │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

#### 2. Shared Components (Where Appropriate)

**PDF Generation:**
- All three systems use similar PDF generation logic
- Consider extracting common PDF service (future optimization)
- For now: Keep separate implementations

**Invoice Number Generation:**
- Each system has its own format:
  - Quick Invoice: `INV-260102-LQU2` (date-based + random)
  - GrowFinance: `INV-000001` (sequential per business)
  - CMS: `INV-2026-0001` (year + sequential per company)
- Keep separate to maintain independence

**Email Sending:**
- All use Laravel Mail
- Can share email templates with conditional logic
- Keep separate for now, consolidate later if needed

#### 3. No Cross-System Dependencies

**Rule:** Each invoice system operates independently

- Quick Invoice does NOT depend on GrowFinance or CMS
- GrowFinance does NOT depend on Quick Invoice or CMS
- CMS does NOT depend on Quick Invoice or GrowFinance

**Exception:** CMS may integrate with GrowFinance for job-to-invoice automation (via events)

#### 4. User Experience Clarity

**Navigation:**
- Quick Invoice: Public menu, Tools page
- GrowFinance: Member dashboard → GrowFinance → Invoices
- CMS: CMS dashboard → Invoices

**Branding:**
- Quick Invoice: "Quick Invoice Generator" (free tool)
- GrowFinance: "GrowFinance Invoices" (business management)
- CMS: "Invoices" (company operations)

**Upgrade Paths:**
- Quick Invoice users → Prompted to register for MyGrowNet
- GrowFinance users → Can upgrade subscription for more features
- CMS users → Separate CMS subscription (future SaaS product)

---

## CMS Invoice Implementation Plan

### Phase 2A: Core Invoice Functionality

**Status:** In Progress

**Completed:**
- ✅ Invoice and payment migrations
- ✅ InvoiceModel, PaymentModel, InvoiceItemModel, PaymentAllocationModel
- ✅ InvoiceService with auto-invoice generation
- ✅ InvoiceNumber value object
- ✅ Job-to-invoice relationship

**Remaining:**
1. **PaymentService** - Payment allocation logic
2. **InvoiceController** - CRUD operations
3. **PaymentController** - Payment recording
4. **Vue Pages:**
   - `resources/js/Pages/CMS/Invoices/Index.vue`
   - `resources/js/Pages/CMS/Invoices/Create.vue`
   - `resources/js/Pages/CMS/Invoices/Show.vue`
   - `resources/js/Pages/CMS/Payments/Index.vue`
   - `resources/js/Pages/CMS/Payments/Create.vue`
5. **Routes** - Invoice and payment routes
6. **Dashboard Integration** - Invoice/payment stats
7. **PDF Generation** - CMS-specific invoice PDF templates

### Phase 2B: Advanced Features

**Future:**
- Recurring invoices
- Invoice templates (like GrowFinance)
- Email automation
- Payment reminders
- Overdue tracking
- Credit notes
- Multi-currency support

---

## Technical Considerations

### Database Isolation

**Tables:**
- Quick Invoice: `quick_invoice_*`
- GrowFinance: `growfinance_*`
- CMS: `cms_*`

**No foreign keys between systems** - Maintains independence

### Service Layer Isolation

**Namespaces:**
- Quick Invoice: `App\Domain\QuickInvoice\*`
- GrowFinance: `App\Domain\GrowFinance\*`
- CMS: `App\Domain\CMS\*`

**No cross-namespace dependencies**

### Route Isolation

**Prefixes:**
- Quick Invoice: `/quick-invoice`
- GrowFinance: `/growfinance/invoices`
- CMS: `/cms/invoices`

**Separate route files:**
- `routes/quick-invoice.php`
- `routes/growfinance.php`
- `routes/cms.php`

### Future Extraction (CMS)

**Goal:** Extract CMS as standalone SaaS product

**Requirements:**
- Zero dependencies on MyGrowNet-specific code
- Self-contained domain logic
- Event-driven integration points
- Configurable branding
- Multi-tenant architecture

**Current Status:** ✅ Architecture supports extraction

---

## Recommendations

### For CMS Invoice Implementation

1. **Do NOT reuse Quick Invoice or GrowFinance code directly**
   - Reason: Different requirements, different scoping, future extraction
   - Exception: Can reference for inspiration

2. **Build CMS-specific invoice system**
   - Tailored to SME operations
   - Job-to-invoice automation
   - Payment allocation
   - Commission triggers
   - Audit trail integration

3. **Use similar patterns but independent implementation**
   - Follow DDD principles (like other systems)
   - Use value objects (InvoiceNumber, InvoiceStatus, etc.)
   - Repository pattern
   - Service layer

4. **Plan for PDF generation**
   - Create CMS-specific invoice templates
   - Include company branding
   - Professional layout for SME use
   - Can reference GrowFinance PDF templates for inspiration

5. **Consider future features**
   - Invoice templates (like GrowFinance)
   - Email automation
   - Payment reminders
   - Recurring invoices

### For Platform Consistency

1. **Shared UI components** (where appropriate)
   - Invoice list table component
   - Payment form component
   - Status badges
   - Date pickers

2. **Consistent terminology**
   - Invoice statuses: Draft, Sent, Partial, Paid, Cancelled, Void
   - Payment methods: Cash, Bank Transfer, Mobile Money, Cheque
   - Currency: ZMW (Zambian Kwacha)

3. **Consistent UX patterns**
   - Similar workflows across systems
   - Familiar UI elements
   - Consistent color coding (status colors)

---

## Next Steps

1. ✅ **Document integration strategy** (this document)
2. **Complete PaymentService** - Payment allocation logic
3. **Create InvoiceController** - CRUD operations
4. **Create PaymentController** - Payment recording
5. **Build Vue pages** - Invoices and payments UI
6. **Add routes** - Invoice and payment routes
7. **Update dashboard** - Show invoice/payment stats
8. **Create PDF templates** - CMS-specific invoice PDFs
9. **Test workflow** - Job → Complete → Invoice → Payment → Balance Update
10. **Document usage** - Update CMS documentation

---

## Conclusion

The three invoice systems serve different purposes and should remain separate:

- **Quick Invoice** = Lead generation tool (public)
- **GrowFinance** = Business management tool (members)
- **CMS** = Full ERP system (SME companies)

The CMS invoice system will be built independently with its own domain logic, tailored to SME operations, and designed for future extraction as a standalone SaaS product.

**Decision:** Proceed with independent CMS invoice implementation, referencing existing systems for inspiration but not reusing code directly.

