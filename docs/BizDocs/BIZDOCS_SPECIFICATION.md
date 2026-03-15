# BizDocs Module - Complete Specification

**Version:** 1.0  
**Last Updated:** March 15, 2026  
**Status:** Planning Phase

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [Domain Model](#2-domain-model)
3. [Document Types & Workflows](#3-document-types--workflows)
4. [Technical Architecture](#4-technical-architecture)
5. [Database Schema](#5-database-schema)
6. [Implementation Plan](#6-implementation-plan)
7. [Development Phases](#7-development-phases)
8. [Migration Strategy](#8-migration-strategy)

---

## 1. Executive Summary

### 1.1 Overview

BizDocs is a comprehensive business document management module for MyGrowNet that replaces the current QuickInvoice system. Built using Domain-Driven Design principles, it provides:

- Multi-document type support (invoices, receipts, quotations, delivery notes, etc.)
- Flexible three-tier template system (system, industry, custom)
- Professional PDF generation with WhatsApp sharing
- Print-ready stationery generation for physical document books
- Foundation for future accounting, inventory, and e-commerce integration

### 1.2 Business Goals

- Replace QuickInvoice with a scalable document management solution
- Enable businesses to create professional documents across multiple types
- Support modern sharing methods (WhatsApp, PDF, printing)
- Provide flexible templating for different industries
- Lay groundwork for MyGrowNet ecosystem integration

### 1.3 Success Metrics (3 months post Phase 1)

| Metric | Target |
|--------|--------|
| Total documents created | >1,000 |
| Businesses using templates | >60% |
| PDFs shared via WhatsApp | >40% |
| Custom templates created | >20% of businesses |
| Print stationery generated | >100 files |

---

## 2. Domain Model

### 2.1 Bounded Contexts

BizDocs follows DDD principles with four primary bounded contexts:


#### 2.1.1 Document Management Context

**Purpose:** Core document lifecycle management

**Entities:**
- `Document` (aggregate root) - Invoice, Receipt, Quotation, Delivery Note
- `DocumentItem` - Line items within a document
- `DocumentPayment` - Payment records against invoices

**Value Objects:**
- `DocumentNumber` - Sequential identifier (e.g., INV-2026-0001)
- `DocumentStatus` - Draft, Sent, Paid, Cancelled, etc.
- `DocumentType` - Invoice, Receipt, Quotation, DeliveryNote
- `Money` - Amount with currency
- `DateRange` - Issue date, due date, validity period

**Domain Services:**
- `DocumentNumberingService` - Generate sequential numbers
- `DocumentStatusTransitionService` - Validate status changes
- `DocumentConversionService` - Convert quotation to invoice, invoice to receipt

**Repository Interface:**
- `DocumentRepositoryInterface`

**Domain Events:**
- `DocumentCreated`, `DocumentFinalized`, `DocumentSent`
- `DocumentPaid`, `DocumentCancelled`, `InvoiceOverdue`
- `QuotationAccepted`, `QuotationRejected`, `PaymentRecorded`

#### 2.1.2 Customer Management Context

**Purpose:** Customer data and relationship management

**Entities:**
- `Customer` (aggregate root)

**Value Objects:**
- `CustomerIdentity` - Name, TPIN
- `ContactInformation` - Phone, email, address

**Domain Services:**
- `CustomerDuplicationDetectionService` - Prevent duplicate customers

**Repository Interface:**
- `CustomerRepositoryInterface`

#### 2.1.3 Template Management Context

**Purpose:** Document template creation and rendering

**Entities:**
- `DocumentTemplate` (aggregate root)

**Value Objects:**
- `TemplateStructure` - JSON configuration
- `TemplateVisibility` - System, Industry, Business

**Domain Services:**
- `TemplateRenderingService` - Render documents from templates
- `TemplateCloningService` - Clone templates for customization

**Repository Interface:**
- `TemplateRepositoryInterface`

**Domain Events:**
- `TemplateCreated`, `TemplateCloned`, `TemplateUpdated`

#### 2.1.4 Business Identity Context

**Purpose:** Business profile and branding

**Entities:**
- `BusinessProfile` (aggregate root)

**Value Objects:**
- `BusinessIdentity` - Name, TPIN, registration
- `BrandingAssets` - Logo, signature, stamp
- `BankDetails` - Bank name, account, branch

**Repository Interface:**
- `BusinessProfileRepositoryInterface`

### 2.2 Ubiquitous Language

| Term | Definition |
|------|------------|
| **Document** | Any business transactional document |
| **Line Item** | Individual product/service entry within a document |
| **Finalize** | Transition document from draft to active status |
| **Template** | Reusable document layout and styling configuration |
| **Stationery** | Print-ready physical document books |
| **Document Number** | Unique sequential identifier per document type |
| **Business Profile** | Complete business identity and branding |



---

## 3. Document Types & Workflows

### 3.1 Phase 1 Document Types

#### 3.1.1 Invoice

**Purpose:** Billable document for goods/services rendered

**Status Workflow:**
```
Draft → Sent → [Overdue] → Partially Paid → Paid
                ↓
            Cancelled
```

**Key Fields:**
- Issue date, due date
- Customer details
- Line items (description, quantity, unit price, tax, discount)
- Subtotal, tax total, discount total, grand total
- Payment terms, notes

**Business Rules:**
- Cannot edit after finalized (except status changes)
- Overdue status auto-applied when past due date
- Can record multiple partial payments
- Generates receipt when payment recorded

#### 3.1.2 Receipt

**Purpose:** Proof of payment issued after funds received

**Status Workflow:**
```
Draft → Issued → Voided
```

**Key Fields:**
- Issue date
- Payment method (cash, mobile money, bank transfer)
- Amount received
- Reference to invoice (optional)
- Payment reference number

**Business Rules:**
- Cannot edit after issued
- Can be voided with reason
- Auto-generated when invoice payment recorded

#### 3.1.3 Quotation

**Purpose:** Formal price offer to prospective customer

**Status Workflow:**
```
Draft → Sent → Accepted → [Convert to Invoice]
              ↓
           Rejected
              ↓
           Expired
```

**Key Fields:**
- Issue date, validity date
- Customer details
- Line items with pricing
- Terms and conditions
- Validity period

**Business Rules:**
- Auto-expires after validity date
- Can convert to invoice when accepted
- Cannot edit after sent (must create new version)

#### 3.1.4 Delivery Note

**Purpose:** Document accompanying goods during delivery

**Status Workflow:**
```
Draft → Sent → Delivered → Acknowledged
```

**Key Fields:**
- Delivery date
- Delivery address
- Items delivered (description, quantity)
- Carrier/driver information
- Recipient signature

**Business Rules:**
- Can be linked to invoice
- Acknowledged status requires signature/confirmation

### 3.2 Phase 2 Document Types

| Document Type | Purpose |
|---------------|---------|
| **Proforma Invoice** | Preliminary invoice before final confirmation |
| **Credit Note** | Reduces amount owed on previous invoice |
| **Debit Note** | Requests additional payment beyond prior invoice |
| **Purchase Order** | Authorizes purchase from supplier |

### 3.3 Phase 3 Document Types

| Document Type | Purpose |
|---------------|---------|
| **Expense Voucher** | Records business expense claim |
| **Payment Voucher** | Internal record of payment made |
| **Stock Issue Note** | Records stock released from inventory |
| **Service Report** | Summary of services performed for client |

### 3.4 Document Numbering System

**Format:** `{PREFIX}-{YEAR}-{NUMBER}`

**Examples:**
- Invoice: `INV-2026-0001`
- Receipt: `RCPT-2026-0001`
- Quotation: `QTN-2026-0001`
- Delivery Note: `DN-2026-0001`

**Configuration (per business, per document type):**
- Custom prefix (e.g., `ACME-INV`)
- Year format: full (2026) or short (26)
- Starting number (for migration)
- Zero padding (default: 4 digits)
- Annual reset (enabled by default)

**Technical Implementation:**
- Atomic increment with row-level lock
- Stored in `document_sequences` table
- Managed by `DocumentNumberingService`



---

## 4. Technical Architecture

### 4.1 Domain Layer Structure

```
app/Domain/BizDocs/
├── DocumentManagement/
│   ├── Entities/
│   │   ├── Document.php
│   │   ├── DocumentItem.php
│   │   └── DocumentPayment.php
│   ├── ValueObjects/
│   │   ├── DocumentNumber.php
│   │   ├── DocumentStatus.php
│   │   ├── DocumentType.php
│   │   ├── Money.php
│   │   └── DateRange.php
│   ├── Services/
│   │   ├── DocumentNumberingService.php
│   │   ├── DocumentStatusTransitionService.php
│   │   └── DocumentConversionService.php
│   ├── Repositories/
│   │   └── DocumentRepositoryInterface.php
│   └── Events/
│       ├── DocumentCreated.php
│       ├── DocumentFinalized.php
│       └── DocumentPaid.php
├── CustomerManagement/
│   ├── Entities/
│   │   └── Customer.php
│   ├── ValueObjects/
│   │   ├── CustomerIdentity.php
│   │   └── ContactInformation.php
│   ├── Services/
│   │   └── CustomerDuplicationDetectionService.php
│   └── Repositories/
│       └── CustomerRepositoryInterface.php
├── TemplateManagement/
│   ├── Entities/
│   │   └── DocumentTemplate.php
│   ├── ValueObjects/
│   │   ├── TemplateStructure.php
│   │   └── TemplateVisibility.php
│   ├── Services/
│   │   ├── TemplateRenderingService.php
│   │   └── TemplateCloningService.php
│   └── Repositories/
│       └── TemplateRepositoryInterface.php
└── BusinessIdentity/
    ├── Entities/
    │   └── BusinessProfile.php
    ├── ValueObjects/
    │   ├── BusinessIdentity.php
    │   ├── BrandingAssets.php
    │   └── BankDetails.php
    └── Repositories/
        └── BusinessProfileRepositoryInterface.php
```

### 4.2 Application Layer Structure

```
app/Application/BizDocs/
├── UseCases/
│   ├── Document/
│   │   ├── CreateDocumentUseCase.php
│   │   ├── FinalizeDocumentUseCase.php
│   │   ├── ConvertQuotationToInvoiceUseCase.php
│   │   └── RecordPaymentUseCase.php
│   ├── Template/
│   │   ├── CreateCustomTemplateUseCase.php
│   │   ├── CloneTemplateUseCase.php
│   │   └── RenderDocumentUseCase.php
│   └── Customer/
│       ├── CreateCustomerUseCase.php
│       └── FindOrCreateCustomerUseCase.php
├── DTOs/
│   ├── CreateDocumentDTO.php
│   ├── DocumentItemDTO.php
│   ├── CustomerDTO.php
│   └── TemplateConfigDTO.php
└── Services/
    ├── PdfGenerationService.php
    ├── WhatsAppSharingService.php
    └── StationeryGeneratorService.php
```

### 4.3 Infrastructure Layer Structure

```
app/Infrastructure/BizDocs/
├── Persistence/
│   ├── Eloquent/
│   │   ├── DocumentModel.php
│   │   ├── DocumentItemModel.php
│   │   ├── CustomerModel.php
│   │   ├── DocumentTemplateModel.php
│   │   └── BusinessProfileModel.php
│   └── Repositories/
│       ├── EloquentDocumentRepository.php
│       ├── EloquentCustomerRepository.php
│       ├── EloquentTemplateRepository.php
│       └── EloquentBusinessProfileRepository.php
├── External/
│   ├── WhatsAppService.php
│   └── PdfEngine/
│       ├── SnappyPdfEngine.php
│       └── BrowsershotPdfEngine.php
└── Listeners/
    ├── SendDocumentNotification.php
    ├── UpdateInvoiceStatus.php
    └── GenerateReceiptOnPayment.php
```

### 4.4 Presentation Layer Structure

```
app/Presentation/BizDocs/
├── Http/
│   ├── Controllers/
│   │   ├── DocumentController.php
│   │   ├── TemplateController.php
│   │   ├── CustomerController.php
│   │   ├── BusinessProfileController.php
│   │   └── StationeryController.php
│   ├── Requests/
│   │   ├── CreateDocumentRequest.php
│   │   ├── UpdateDocumentRequest.php
│   │   ├── CreateTemplateRequest.php
│   │   └── RecordPaymentRequest.php
│   └── Resources/
│       ├── DocumentResource.php
│       ├── CustomerResource.php
│       └── TemplateResource.php
└── Console/
    └── Commands/
        ├── GenerateStationeryCommand.php
        └── MigrateQuickInvoiceCommand.php
```

### 4.5 Frontend Structure

```
resources/js/Pages/BizDocs/
├── Dashboard.vue
├── Documents/
│   ├── Create.vue
│   ├── Edit.vue
│   ├── Preview.vue
│   └── List.vue
├── Templates/
│   ├── Gallery.vue
│   ├── Builder.vue
│   └── Preview.vue
├── Customers/
│   ├── List.vue
│   └── Form.vue
├── BusinessProfile/
│   └── Edit.vue
└── Stationery/
    └── Generator.vue

resources/js/Components/BizDocs/
├── DocumentForm/
│   ├── DocumentHeader.vue
│   ├── CustomerSelector.vue
│   ├── LineItemsTable.vue
│   └── DocumentTotals.vue
├── TemplateBuilder/
│   ├── HeaderEditor.vue
│   ├── ItemsTableEditor.vue
│   ├── FooterEditor.vue
│   └── StyleEditor.vue
└── Shared/
    ├── DocumentPreview.vue
    ├── StatusBadge.vue
    └── WhatsAppShareButton.vue
```

### 4.6 Technology Stack

| Layer | Technology | Notes |
|-------|------------|-------|
| Backend | Laravel 12 (PHP 8.2+) | Domain logic, Eloquent ORM |
| Frontend | Vue 3 (Composition API) | TypeScript, script setup |
| Routing | Inertia.js | SPA-style navigation |
| Styling | Tailwind CSS v3 | Utility-first CSS |
| Database | MySQL 8 | Relational data, JSON columns |
| PDF Engine | Laravel Snappy (wkhtmltopdf) | Primary; Browsershot as fallback |
| File Storage | Laravel Storage | Local or S3 |
| Queue | Laravel Queues | Redis or DB driver |
| Testing | Pest PHP | Backend testing |

### 4.7 PDF Generation Flow

1. Document data + template structure merged
2. Queued job `GenerateDocumentPdf` dispatches rendering
3. Blade view rendered via Snappy or Browsershot
4. PDF stored in `storage/app/public/documents/{business_id}/`
5. Signed URL (24-hour expiry) returned to frontend

### 4.8 WhatsApp Integration (Phase 1)

**Approach:** `wa.me` deep links (no API credentials required)

**Flow:**
1. Generate PDF server-side
2. Create signed download URL (24-hour expiry)
3. Construct `wa.me` link: `https://wa.me/{phone}?text={encoded_message}`
4. Open WhatsApp app/web

**Message Template:**
```
Hello [Customer Name], please find your [Document Type] #[Number] attached.
Total: [Currency] [Amount]. Thank you for your business.
[PDF Download Link]
```

**Phase 4 Enhancement:** WhatsApp Business API for server-side delivery



---

## 5. Database Schema

### 5.1 Core Tables

#### business_profiles
```sql
id                  BIGINT UNSIGNED PRIMARY KEY
user_id             BIGINT UNSIGNED (FK to users)
business_name       VARCHAR(255) NOT NULL
logo                VARCHAR(255) NULL
address             TEXT NOT NULL
phone               VARCHAR(50) NOT NULL
email               VARCHAR(255) NULL
tpin                VARCHAR(50) NULL
website             VARCHAR(255) NULL
bank_name           VARCHAR(255) NULL
bank_account        VARCHAR(100) NULL
bank_branch         VARCHAR(255) NULL
default_currency    VARCHAR(3) DEFAULT 'ZMW'
signature_image     VARCHAR(255) NULL
stamp_image         VARCHAR(255) NULL
created_at          TIMESTAMP
updated_at          TIMESTAMP
```

#### customers
```sql
id                  BIGINT UNSIGNED PRIMARY KEY
business_id         BIGINT UNSIGNED (FK to business_profiles)
name                VARCHAR(255) NOT NULL
address             TEXT NULL
phone               VARCHAR(50) NULL
email               VARCHAR(255) NULL
tpin                VARCHAR(50) NULL
notes               TEXT NULL
created_at          TIMESTAMP
updated_at          TIMESTAMP
deleted_at          TIMESTAMP NULL

INDEX idx_business_phone (business_id, phone)
INDEX idx_business_email (business_id, email)
```

#### documents
```sql
id                  BIGINT UNSIGNED PRIMARY KEY
business_id         BIGINT UNSIGNED (FK to business_profiles)
customer_id         BIGINT UNSIGNED (FK to customers)
template_id         BIGINT UNSIGNED (FK to document_templates) NULL
document_type       ENUM('invoice', 'receipt', 'quotation', 'delivery_note', ...)
document_number     VARCHAR(100) NOT NULL
issue_date          DATE NOT NULL
due_date            DATE NULL
validity_date       DATE NULL
subtotal            DECIMAL(15,2) NOT NULL
tax_total           DECIMAL(15,2) DEFAULT 0
discount_total      DECIMAL(15,2) DEFAULT 0
grand_total         DECIMAL(15,2) NOT NULL
currency            VARCHAR(3) DEFAULT 'ZMW'
status              VARCHAR(50) NOT NULL
notes               TEXT NULL
terms               TEXT NULL
payment_instructions TEXT NULL
pdf_path            VARCHAR(255) NULL
created_at          TIMESTAMP
updated_at          TIMESTAMP
deleted_at          TIMESTAMP NULL

UNIQUE KEY unique_doc_number (business_id, document_type, document_number)
INDEX idx_business_type_status (business_id, document_type, status)
INDEX idx_customer (customer_id)
INDEX idx_issue_date (issue_date)
INDEX idx_due_date (due_date)
```

#### document_items
```sql
id                  BIGINT UNSIGNED PRIMARY KEY
document_id         BIGINT UNSIGNED (FK to documents)
description         TEXT NOT NULL
quantity            DECIMAL(10,2) NOT NULL
unit_price          DECIMAL(15,2) NOT NULL
tax_rate            DECIMAL(5,2) DEFAULT 0
discount_amount     DECIMAL(15,2) DEFAULT 0
line_total          DECIMAL(15,2) NOT NULL
sort_order          INT DEFAULT 0
created_at          TIMESTAMP
updated_at          TIMESTAMP

INDEX idx_document (document_id)
```

#### document_payments
```sql
id                  BIGINT UNSIGNED PRIMARY KEY
document_id         BIGINT UNSIGNED (FK to documents)
payment_date        DATE NOT NULL
amount              DECIMAL(15,2) NOT NULL
payment_method      VARCHAR(50) NOT NULL
reference_number    VARCHAR(100) NULL
notes               TEXT NULL
created_at          TIMESTAMP
updated_at          TIMESTAMP

INDEX idx_document (document_id)
INDEX idx_payment_date (payment_date)
```

#### document_templates
```sql
id                  BIGINT UNSIGNED PRIMARY KEY
name                VARCHAR(255) NOT NULL
document_type       ENUM('invoice', 'receipt', 'quotation', 'delivery_note', ...)
visibility          ENUM('system', 'industry', 'business')
owner_id            BIGINT UNSIGNED (FK to business_profiles) NULL
industry_tag        VARCHAR(100) NULL
template_structure  JSON NOT NULL
thumbnail_path      VARCHAR(255) NULL
is_default          BOOLEAN DEFAULT FALSE
created_at          TIMESTAMP
updated_at          TIMESTAMP

INDEX idx_visibility_type (visibility, document_type)
INDEX idx_owner (owner_id)
INDEX idx_industry (industry_tag)
```

#### document_sequences
```sql
id                  BIGINT UNSIGNED PRIMARY KEY
business_id         BIGINT UNSIGNED (FK to business_profiles)
document_type       VARCHAR(50) NOT NULL
year                INT NOT NULL
last_number         INT NOT NULL DEFAULT 0
prefix              VARCHAR(50) NOT NULL
padding             INT DEFAULT 4
created_at          TIMESTAMP
updated_at          TIMESTAMP

UNIQUE KEY unique_sequence (business_id, document_type, year)
```

### 5.2 Template Structure JSON Schema

```json
{
  "header": {
    "logo_size": "small|medium|large",
    "logo_position": "left|center|right",
    "business_info_layout": "stacked|inline",
    "background_color": "#hex"
  },
  "customer_block": {
    "position": "left|right",
    "fields": ["name", "address", "phone", "email", "tpin"],
    "label_text": "Bill To|Customer|Client"
  },
  "document_title": {
    "text": "INVOICE|RECEIPT|QUOTATION",
    "alignment": "left|center|right",
    "font_size": "xl|2xl|3xl",
    "font_weight": "normal|bold"
  },
  "items_table": {
    "columns": ["description", "quantity", "unit_price", "tax", "discount", "total"],
    "column_order": [0, 1, 2, 3, 4, 5],
    "show_borders": true,
    "row_striping": true,
    "row_striping_color": "#hex",
    "font_size": "sm|base|lg"
  },
  "totals_block": {
    "tax_display": "inclusive|exclusive",
    "show_discount": true,
    "alignment": "left|right",
    "background_color": "#hex"
  },
  "footer": {
    "show_notes": true,
    "show_terms": true,
    "show_signature": true,
    "show_stamp": true,
    "signature_position": "left|center|right",
    "stamp_position": "left|center|right"
  },
  "watermark": {
    "enabled": false,
    "type": "text|image",
    "content": "DRAFT|PAID|path/to/image",
    "opacity": 0.1,
    "rotation": 45
  },
  "global_styles": {
    "primary_color": "#hex",
    "font_family": "sans-serif|serif|monospace",
    "font_size": "small|medium|large",
    "page_size": "A4|A5|half-page"
  }
}
```

### 5.3 Relationships

```
business_profiles
  ├── has many → customers
  ├── has many → documents
  ├── has many → document_templates (custom)
  └── has many → document_sequences

customers
  └── has many → documents

documents
  ├── belongs to → business_profile
  ├── belongs to → customer
  ├── belongs to → document_template
  ├── has many → document_items
  └── has many → document_payments

document_templates
  └── belongs to → business_profile (if custom)
```



---

## 6. Implementation Plan

### 6.1 Phase 1: MVP (8-10 weeks)

**Goal:** Replace QuickInvoice with full BizDocs engine

#### Week 1-2: Foundation & Domain Setup

**Tasks:**
- [ ] Create domain directory structure
- [ ] Implement core value objects (Money, DocumentNumber, DocumentStatus)
- [ ] Create domain entities (Document, Customer, DocumentTemplate)
- [ ] Define repository interfaces
- [ ] Set up domain events

**Deliverables:**
- Complete domain layer structure
- Unit tests for value objects and entities

#### Week 3-4: Infrastructure & Persistence

**Tasks:**
- [ ] Create database migrations
- [ ] Implement Eloquent models
- [ ] Implement repository implementations
- [ ] Set up event listeners
- [ ] Configure file storage for PDFs and images

**Deliverables:**
- Working database schema
- Repository implementations with tests
- File storage configuration

#### Week 5-6: Application Layer & Use Cases

**Tasks:**
- [ ] Implement document creation use case
- [ ] Implement document finalization use case
- [ ] Implement customer management use cases
- [ ] Implement template rendering service
- [ ] Set up PDF generation service (Snappy)
- [ ] Implement document numbering service

**Deliverables:**
- Complete use cases with tests
- PDF generation working
- Document numbering system functional

#### Week 7-8: Presentation Layer & UI

**Tasks:**
- [ ] Create Inertia controllers
- [ ] Build document creation form (Vue)
- [ ] Build document list/dashboard (Vue)
- [ ] Build customer selector component
- [ ] Build line items table component
- [ ] Implement document preview
- [ ] Create system templates (Blade views)

**Deliverables:**
- Working document creation flow
- Document dashboard
- System templates

#### Week 9-10: Features & Polish

**Tasks:**
- [ ] Implement WhatsApp sharing (wa.me)
- [ ] Build business profile setup
- [ ] Create industry templates
- [ ] Implement document status transitions
- [ ] Add validation and error handling
- [ ] User testing and bug fixes

**Deliverables:**
- Complete Phase 1 feature set
- WhatsApp sharing functional
- Business profile setup
- Industry templates

### 6.2 Phase 2: Self-Service Templates (6-8 weeks)

**Goal:** Custom templates and payment lifecycle

#### Week 1-2: Template Builder Foundation

**Tasks:**
- [ ] Design template builder UI/UX
- [ ] Implement template cloning service
- [ ] Build template structure editor
- [ ] Create live preview component

#### Week 3-4: Template Builder Features

**Tasks:**
- [ ] Header section editor
- [ ] Items table editor
- [ ] Footer section editor
- [ ] Global styles editor
- [ ] Custom fields system

#### Week 5-6: Payment Tracking

**Tasks:**
- [ ] Implement payment recording use case
- [ ] Build payment history UI
- [ ] Auto-generate receipts on payment
- [ ] Invoice status auto-update logic
- [ ] Payment reminders

#### Week 7-8: Document Conversions & Stationery

**Tasks:**
- [ ] Quotation to invoice conversion
- [ ] Invoice to receipt conversion
- [ ] Print stationery generator
- [ ] Additional document types (Proforma, Credit Note, Debit Note, PO)

### 6.3 Phase 3: Enterprise Features (8-10 weeks)

**Goal:** Multi-user, analytics, and advanced features

#### Week 1-3: Multi-User & Permissions

**Tasks:**
- [ ] Role-based access control
- [ ] Team member management
- [ ] Permission policies
- [ ] Audit logging

#### Week 4-6: Custom Fields & Advanced Builder

**Tasks:**
- [ ] Custom fields system
- [ ] Field validation rules
- [ ] Advanced template options
- [ ] Conditional field display

#### Week 7-10: Analytics & Reporting

**Tasks:**
- [ ] Document analytics dashboard
- [ ] Revenue reports
- [ ] Customer insights
- [ ] Export functionality
- [ ] Additional document types (Expense Voucher, Payment Voucher, etc.)

### 6.4 Phase 4: Ecosystem Integration (10-12 weeks)

**Goal:** Full MyGrowNet integration

#### Week 1-3: GrowFinance Integration

**Tasks:**
- [ ] Automatic journal entries
- [ ] Chart of accounts mapping
- [ ] Financial reports integration

#### Week 4-6: Inventory Integration

**Tasks:**
- [ ] Stock reduction on invoice
- [ ] Stock tracking via delivery notes
- [ ] Low stock alerts

#### Week 7-9: GrowMarket Integration

**Tasks:**
- [ ] Order to invoice conversion
- [ ] Auto-invoice generation
- [ ] Payment synchronization

#### Week 10-12: Advanced Features

**Tasks:**
- [ ] Template marketplace
- [ ] Mobile app integration
- [ ] WhatsApp Business API
- [ ] CRM purchase history

### 6.5 Development Best Practices

**Code Quality:**
- Follow PSR-12 coding standards
- Write unit tests for domain logic (>80% coverage)
- Write feature tests for use cases
- Use Laravel Pint for code formatting
- Use PHPStan for static analysis

**Git Workflow:**
- Feature branches from `develop`
- Pull requests with code review
- Automated tests in CI/CD
- Semantic versioning

**Documentation:**
- Update this document as features evolve
- Document API endpoints
- Create user guides
- Maintain changelog



---

## 7. Development Phases Summary

### Phase 1: MVP (8-10 weeks)
**Status:** Planning  
**Target:** Q2 2026

**Features:**
- ✓ Invoice, Receipt, Quotation, Delivery Note
- ✓ Customer database
- ✓ System and industry templates
- ✓ PDF generation
- ✓ WhatsApp sharing (wa.me)
- ✓ Document dashboard
- ✓ Business profile setup
- ✓ Document numbering system

**Success Criteria:**
- Can create and send invoices
- Can generate receipts
- Can create quotations
- PDF generation works reliably
- WhatsApp sharing functional
- 10+ businesses using the system

### Phase 2: Self-Service (6-8 weeks)
**Status:** Planned  
**Target:** Q3 2026

**Features:**
- ✓ Custom template builder
- ✓ Payment tracking and recording
- ✓ Document conversions (Quotation→Invoice, Invoice→Receipt)
- ✓ Print stationery generator
- ✓ Proforma Invoice, Credit Note, Debit Note, Purchase Order

**Success Criteria:**
- 20% of businesses create custom templates
- Payment tracking used by 50% of businesses
- 100+ stationery PDFs generated

### Phase 3: Enterprise (8-10 weeks)
**Status:** Planned  
**Target:** Q4 2026

**Features:**
- ✓ Custom fields system
- ✓ Advanced template builder
- ✓ Multi-user roles and permissions
- ✓ Document analytics
- ✓ Expense Voucher, Payment Voucher, Stock Issue Note, Service Report

**Success Criteria:**
- 30% of businesses have multiple users
- Analytics used by 40% of businesses
- Custom fields used by 25% of businesses

### Phase 4: Ecosystem Integration (10-12 weeks)
**Status:** Planned  
**Target:** Q1 2027

**Features:**
- ✓ Template marketplace
- ✓ Mobile app integration
- ✓ GrowFinance accounting entries
- ✓ Inventory stock reduction
- ✓ GrowMarket order-to-invoice
- ✓ CRM purchase history
- ✓ WhatsApp Business API

**Success Criteria:**
- 50% of businesses use at least one integration
- Template marketplace has 20+ templates
- Mobile app adoption by 30% of users

---

## 8. Migration Strategy

### 8.1 QuickInvoice to BizDocs Migration

#### 8.1.1 Data Migration

**Tables to Migrate:**

| QuickInvoice Table | BizDocs Table | Notes |
|-------------------|---------------|-------|
| `quick_invoice_businesses` | `business_profiles` | Map business data |
| `quick_invoice_customers` | `customers` | Preserve customer records |
| `quick_invoices` | `documents` | Set `document_type = 'invoice'` |
| `quick_invoice_items` | `document_items` | Preserve line items |
| `custom_templates` | `document_templates` | Convert template structure |

**Migration Command:**
```bash
php artisan bizdocs:migrate-quickinvoice
```

**Migration Steps:**
1. Create business profiles from QuickInvoice businesses
2. Migrate customers (with duplicate detection)
3. Migrate invoices as documents
4. Migrate line items
5. Convert custom templates to new structure
6. Generate document numbers for existing invoices
7. Update file paths for PDFs and images

#### 8.1.2 Feature Parity Checklist

Before migration, ensure BizDocs has:
- [x] Invoice creation
- [x] Customer management
- [x] Template system
- [x] PDF generation
- [x] WhatsApp sharing
- [x] Dashboard with filtering
- [x] Business profile setup

#### 8.1.3 Rollout Strategy

**Week 1: Soft Launch**
- Enable BizDocs for 5-10 pilot businesses
- Keep QuickInvoice running in parallel
- Gather feedback and fix critical bugs

**Week 2-3: Gradual Migration**
- Migrate 25% of businesses per week
- Monitor performance and errors
- Provide support for migrated businesses

**Week 4: Full Migration**
- Migrate remaining businesses
- Set QuickInvoice to read-only mode
- Redirect QuickInvoice routes to BizDocs

**Week 5+: Deprecation**
- Keep QuickInvoice accessible for historical data
- Plan full deprecation after 3 months
- Archive QuickInvoice codebase

#### 8.1.4 Rollback Plan

If critical issues arise:
1. Revert route changes to restore QuickInvoice
2. Disable BizDocs temporarily
3. Fix issues in staging environment
4. Resume migration when stable

### 8.2 User Communication

**Pre-Migration (2 weeks before):**
- Email announcement about new BizDocs system
- Highlight new features and benefits
- Provide migration timeline
- Offer training webinar

**During Migration:**
- In-app notification about migration
- Link to user guide and video tutorials
- Support contact information

**Post-Migration:**
- Welcome email to BizDocs
- Quick start guide
- Feedback survey
- Support availability

### 8.3 Training Materials

**Create:**
- Video tutorial: Creating your first invoice
- Video tutorial: Setting up business profile
- Video tutorial: Using templates
- PDF guide: BizDocs quick reference
- FAQ document

---

## 9. Template System Details

### 9.1 System Templates (Phase 1)

#### Modern Invoice
- Clean, professional design
- Blue accent color
- Logo left, business info right
- Full-width items table
- Signature and stamp areas

#### Compact Receipt
- Half-page size
- Minimal design
- Essential information only
- Suitable for thermal printers

#### Professional Quotation
- Formal layout
- Validity period prominent
- Terms and conditions section
- Acceptance signature area

#### Standard Delivery Note
- Clear item listing
- Delivery address prominent
- Recipient signature area
- Driver/carrier information

### 9.2 Industry Templates (Phase 1)

#### Retail Shop Invoice
- Product-focused layout
- Barcode/SKU column
- Cashier name field
- Payment method section

#### Construction Quotation
- Project site field
- Material and labor breakdown
- Payment milestone section
- Validity and terms prominent

#### School Fee Receipt
- Student name and number
- Term/semester field
- Fee breakdown by category
- Balance carried forward

#### Pharmacy Invoice
- Medicine name and dosage
- Prescription number field
- Pharmacist name
- Regulatory compliance text

#### Vehicle Spare Parts Invoice
- Part number column
- Vehicle registration field
- Warranty information
- Fitment instructions

#### Transport Delivery Note
- Vehicle registration
- Driver name and license
- Route information
- Delivery time tracking

#### Salon Receipt
- Service description
- Stylist name
- Duration field
- Next appointment reminder

#### Freelancer Invoice
- Hourly rate breakdown
- Project/task description
- Time tracking
- Payment terms

### 9.3 Template Builder (Phase 2)

**Sections:**
1. Header Configuration
2. Customer Block Configuration
3. Document Title Configuration
4. Items Table Configuration
5. Totals Block Configuration
6. Footer Configuration
7. Watermark Configuration
8. Global Styles

**Live Preview:**
- Real-time preview as you edit
- Sample data for preview
- Mobile/desktop preview toggle
- Print preview mode

---

## 10. Security & Compliance

### 10.1 Data Security

**Encryption:**
- All PDFs stored with encryption at rest
- Signed URLs for temporary access
- HTTPS for all data transmission

**Access Control:**
- Business data scoped to `business_id`
- Row-level security in queries
- Policy-based authorization

**Audit Trail:**
- Log all document status changes
- Track who created/modified documents
- Soft deletes preserve history

### 10.2 Compliance

**Tax Compliance:**
- TPIN field for tax identification
- Tax calculation per line item
- Tax summary in totals

**Data Privacy:**
- GDPR-compliant data handling
- Customer data deletion on request
- Data export functionality

**Financial Records:**
- Immutable finalized documents
- Sequential numbering (no gaps)
- Audit trail for all changes

---

## 11. Performance Considerations

### 11.1 PDF Generation

**Strategy:**
- Queue PDF generation (don't block HTTP requests)
- Cache generated PDFs
- Use Snappy (faster) as primary, Browsershot as fallback
- Optimize Blade templates for rendering speed

**Monitoring:**
- Track PDF generation time
- Alert on failures
- Monitor queue depth

### 11.2 Database Optimization

**Indexes:**
- Composite indexes on frequently queried columns
- Index on `business_id` for all business-scoped tables
- Index on `document_number` for quick lookups

**Query Optimization:**
- Eager load relationships
- Use pagination for large lists
- Cache frequently accessed data (templates, business profiles)

### 11.3 File Storage

**Strategy:**
- Store PDFs in S3 or equivalent (not local filesystem)
- Use CDN for serving PDFs
- Implement file cleanup for expired signed URLs

---

## 12. Testing Strategy

### 12.1 Unit Tests

**Domain Layer:**
- Value object validation
- Entity business rules
- Domain service logic

**Target:** >80% code coverage

### 12.2 Integration Tests

**Infrastructure Layer:**
- Repository implementations
- PDF generation
- File storage
- External services (WhatsApp)

### 12.3 Feature Tests

**Application Layer:**
- Complete use case flows
- Document creation to PDF generation
- Payment recording to receipt generation
- Template rendering

### 12.4 End-to-End Tests

**User Workflows:**
- Create invoice → Send via WhatsApp → Record payment → Generate receipt
- Create quotation → Accept → Convert to invoice
- Create custom template → Use in document

---

## 13. Monitoring & Analytics

### 13.1 Application Metrics

**Track:**
- Documents created per day/week/month
- PDF generation success rate
- WhatsApp share rate
- Template usage distribution
- Average document value

### 13.2 Business Metrics

**Track:**
- Active businesses using BizDocs
- Documents per business
- Custom templates created
- Payment tracking adoption
- Feature usage rates

### 13.3 Error Monitoring

**Monitor:**
- PDF generation failures
- Queue job failures
- Database errors
- File storage errors

**Alerts:**
- Critical errors (email/Slack)
- Performance degradation
- Queue backlog

---

## 14. Future Enhancements

### 14.1 Phase 5+ Ideas

**Advanced Features:**
- Recurring invoices
- Subscription billing
- Multi-currency support
- Multi-language documents
- E-signature integration
- Document versioning
- Bulk operations
- API for third-party integrations

**AI/ML Features:**
- Smart document categorization
- Predictive payment dates
- Fraud detection
- Automated data entry from images

**Mobile Features:**
- Native mobile app
- Offline document creation
- Mobile receipt scanning
- Push notifications

---

## Changelog

### March 15, 2026
- Initial specification document created
- Defined domain model and bounded contexts
- Outlined 4-phase development plan
- Detailed database schema
- Created migration strategy from QuickInvoice

---

**Document Status:** Ready for Development  
**Next Steps:**
1. Review and approve specification
2. Set up development environment
3. Begin Phase 1 implementation
4. Schedule weekly progress reviews
