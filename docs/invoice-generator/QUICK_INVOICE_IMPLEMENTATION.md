# Quick Invoice Generator - Implementation Document

**Last Updated:** January 2, 2026
**Status:** Development
**Feature:** Quick Invoice/Delivery Note Generator

## Overview

A lightweight, no-account-required invoice and delivery note generator for MyGrowNet. Users can create professional documents instantly, upload their logo, and share via WhatsApp, Email, or download as PDF.

## Business Value

- **Lead Generation**: Attracts potential MyGrowNet members through free tool
- **Brand Exposure**: MyGrowNet branding on generated documents
- **User Conversion**: Prompt to register for advanced features
- **Community Value**: Free tool for small businesses and entrepreneurs

## Features

### Core Features (MVP)
1. **No Account Required** - Guest users can create documents instantly
2. **Document Types** - Invoice, Delivery Note, Quotation, Receipt
3. **Logo Upload** - Custom branding with temporary storage
4. **Line Items** - Add/remove items with quantity, price, description
5. **Auto Calculations** - Subtotal, tax, discount, total
6. **Share Options** - WhatsApp, Email, Download PDF
7. **Live Preview** - Real-time document preview
8. **5 PDF Templates** - Classic, Modern, Minimal, Professional, Bold
9. **Custom Colors** - Match brand colors (primary, secondary, accent)
10. **Signature Upload** - Add authorized signature to documents

### Enhanced Features (Registered Users)
1. **Save Templates** - Reusable business info templates
2. **Document History** - Access past documents
3. **Client Directory** - Save frequent clients

## Technical Architecture (DDD)

### Domain Layer (`app/Domain/QuickInvoice/`)

```
app/Domain/QuickInvoice/
├── Entities/
│   ├── Document.php              # Aggregate root
│   └── LineItem.php              # Line item entity
├── ValueObjects/
│   ├── BusinessInfo.php          # Issuer company details
│   ├── ClientInfo.php            # Recipient details
│   ├── DocumentId.php            # UUID identifier
│   ├── DocumentNumber.php        # Reference number
│   ├── DocumentType.php          # Enum: Invoice/Delivery/Quote/Receipt
│   └── Money.php                 # Currency amount handling
├── Services/
│   ├── DocumentService.php       # Document business logic
│   ├── PdfGeneratorService.php   # PDF generation
│   └── ShareService.php          # WhatsApp/Email sharing
├── Repositories/
│   └── DocumentRepositoryInterface.php  # Repository contract
└── Exceptions/
    ├── QuickInvoiceException.php
    ├── DocumentNotFoundException.php
    ├── InvalidDocumentDataException.php
    └── UnauthorizedAccessException.php
```

### Infrastructure Layer (`app/Infrastructure/QuickInvoice/`)

```
app/Infrastructure/QuickInvoice/
└── Repositories/
    └── EloquentDocumentRepository.php  # Repository implementation
```

### Presentation Layer

```
app/Http/Controllers/QuickInvoiceController.php
app/Http/Requests/QuickInvoice/
├── CreateDocumentRequest.php
├── SendEmailRequest.php
└── UploadLogoRequest.php
```

### Data Layer

```
app/Models/
├── QuickInvoiceDocument.php
├── QuickInvoiceItem.php
└── QuickInvoiceProfile.php    # Saved business profiles

database/migrations/
├── 2026_01_02_000001_create_quick_invoice_tables.php
└── 2026_01_02_000002_create_quick_invoice_profiles_table.php
```

### Frontend

```
resources/js/Pages/QuickInvoice/
├── Index.vue          # Landing/type selection
├── Create.vue         # Document builder
└── History.vue        # Document history
```

## Database Schema

### quick_invoice_documents
| Column | Type | Description |
|--------|------|-------------|
| id | uuid | Primary key |
| user_id | bigint nullable | Optional user reference |
| session_id | string | Guest session identifier |
| document_type | enum | invoice/delivery_note/quotation/receipt |
| document_number | string | Reference number |
| business_* | various | Business info fields |
| client_* | various | Client info fields |
| issue_date | date | Document date |
| due_date | date nullable | Payment due date |
| currency | string | Currency code (ZMW default) |
| subtotal, tax_*, discount_*, total | decimal | Financial fields |
| notes, terms | text nullable | Additional info |
| status | enum | draft/sent/paid/cancelled |

### quick_invoice_items
| Column | Type | Description |
|--------|------|-------------|
| id | uuid | Primary key |
| document_id | uuid | Foreign key |
| description | string | Item description |
| quantity | decimal | Item quantity |
| unit | string nullable | Unit of measure |
| unit_price | decimal | Price per unit |
| amount | decimal | Line total |
| sort_order | integer | Display order |

## API Routes

```
GET    /quick-invoice              # Landing page
GET    /quick-invoice/create       # Create form
GET    /quick-invoice/history      # Document history
POST   /quick-invoice/generate     # Generate document
POST   /quick-invoice/upload-logo  # Upload logo
POST   /quick-invoice/upload-signature  # Upload signature
POST   /quick-invoice/send-email   # Send via email
GET    /quick-invoice/pdf/{id}     # Download PDF
DELETE /quick-invoice/{id}         # Delete document

# Authenticated routes
POST   /quick-invoice/save-profile # Save business profile
GET    /quick-invoice/profile      # Get saved profile
```

## Key Files

| File | Purpose |
|------|---------|
| `app/Domain/QuickInvoice/Entities/Document.php` | Aggregate root with business logic |
| `app/Domain/QuickInvoice/Services/DocumentService.php` | Document operations |
| `app/Infrastructure/QuickInvoice/Repositories/EloquentDocumentRepository.php` | Persistence |
| `app/Providers/QuickInvoiceServiceProvider.php` | DI bindings |
| `routes/quick-invoice.php` | Route definitions |
| `resources/views/pdf/quick-invoice/*.blade.php` | PDF templates (5 styles) |
| `resources/js/Pages/QuickInvoice/*.vue` | Frontend pages |

## Navigation Placement

Quick Invoice is accessible from:

1. **Public Navigation** - Services dropdown menu (`/quick-invoice`)
2. **MyGrowNet Tools** - Tools page for logged-in members
3. **Direct URL** - `/quick-invoice` (no auth required)

## Security

1. **Rate Limiting** - Max 10 documents per hour for guests
2. **File Validation** - Strict logo file validation (2MB, PNG/JPG/SVG)
3. **Session Binding** - Documents tied to session for guests
4. **Access Control** - Users can only access their own documents

## Currency Support

- ZMW (Zambian Kwacha) - Default
- USD, EUR, GBP, ZAR

## Changelog

### January 2, 2026
- Initial implementation with DDD architecture
- Domain entities: Document, LineItem
- Value objects: BusinessInfo, ClientInfo, Money, DocumentType, DocumentId, DocumentNumber, TemplateStyle, ThemeColors
- Repository pattern with interface and Eloquent implementation
- PDF generation with DomPDF
- WhatsApp and Email sharing
- Vue 3 frontend with TypeScript
- Added 5 PDF templates: Classic, Modern, Minimal, Professional, Bold
- Added custom color theming support
- Added signature upload capability
- Added business profile save/load for registered users
- Complete Create.vue form with validation and error feedback
