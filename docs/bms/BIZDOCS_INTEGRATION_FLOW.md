# BizDocs Integration Flow with CMS Invoice System

## Overview

BizDocs integrates with the CMS invoice system as an **optional enhancement layer** that sits between the CMS controllers and PDF generation. The existing invoice system continues to work exactly as before, but when BizDocs is enabled, it provides professional PDF generation with custom templates.

## Integration Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                    USER CLICKS "DOWNLOAD PDF"                    │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│              InvoiceController::downloadPdf()                    │
│  - Fetches invoice from database                                │
│  - Loads relationships (customer, items, company)                │
└─────────────────────────────────────────────────────────────────┘
                              ↓
                    ┌─────────────────┐
                    │ Check if BizDocs│
                    │ module enabled? │
                    └─────────────────┘
                         ↙         ↘
                    YES              NO
                     ↓                ↓
    ┌──────────────────────┐   ┌──────────────────────┐
    │   BizDocs Path       │   │   Fallback Path      │
    │   (Professional)     │   │   (Basic)            │
    └──────────────────────┘   └──────────────────────┘
              ↓                          ↓
    ┌──────────────────────┐   ┌──────────────────────┐
    │  BizDocsAdapter      │   │  PdfInvoiceService   │
    │  generateInvoicePdf()│   │  (existing service)  │
    └──────────────────────┘   └──────────────────────┘
              ↓                          ↓
    ┌──────────────────────┐   ┌──────────────────────┐
    │ DocumentConversion   │   │  Basic Blade         │
    │ Service              │   │  Template            │
    │ - Converts CMS       │   │  invoice-basic.blade │
    │   Invoice to BizDocs │   └──────────────────────┘
    │   Document format    │            ↓
    └──────────────────────┘   ┌──────────────────────┐
              ↓                │  DomPDF Library      │
    ┌──────────────────────┐   │  Generates PDF       │
    │ BizDocs              │   └──────────────────────┘
    │ PdfGenerationService │            ↓
    │ - Uses custom        │            ↓
    │   templates          │            ↓
    │ - Professional       │            ↓
    │   formatting         │            ↓
    └──────────────────────┘            ↓
              ↓                          ↓
              ↓         ┌────────────────┘
              ↓         ↓
    ┌──────────────────────────────────┐
    │  PDF Content (binary)            │
    └──────────────────────────────────┘
              ↓
    ┌──────────────────────────────────┐
    │  Return PDF to Browser           │
    │  - Content-Type: application/pdf │
    │  - Content-Disposition: download │
    └──────────────────────────────────┘
```

## Detailed Flow Explanation

### Step 1: User Action
```
User clicks "Download PDF" button on invoice show page
↓
Frontend makes request to: GET /cms/invoices/{id}/pdf
```

### Step 2: Controller Entry Point
```php
// InvoiceController.php
public function downloadPdf(Request $request, int $id)
{
    // 1. Authenticate and get company
    $cmsUser = $this->getCmsUserOrFail($request);
    
    // 2. Fetch invoice with relationships
    $invoice = InvoiceModel::where('company_id', $cmsUser->company_id)
        ->with(['customer', 'items', 'company'])
        ->findOrFail($id);
    
    // 3. Check if BizDocs is enabled
    if ($invoice->company->hasBizDocsModule()) {
        // Use BizDocs path
    } else {
        // Use fallback path
    }
}
```

### Step 3A: BizDocs Path (When Enabled)

```php
// Check if company has BizDocs module enabled
if ($invoice->company->hasBizDocsModule()) {
    try {
        // 1. Get BizDocs adapter from service container
        $adapter = app(\App\Domain\CMS\BizDocs\Contracts\DocumentGeneratorInterface::class);
        
        // 2. Generate PDF using BizDocs
        $pdfContent = $adapter->generateInvoicePdf($invoice);
        
        // 3. Return PDF response
        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="invoice-' . $invoice->invoice_number . '.pdf"');
            
    } catch (\Exception $e) {
        // Log error and fall through to fallback
        \Log::error('BizDocs invoice PDF generation failed', [
            'invoice_id' => $id,
            'error' => $e->getMessage(),
        ]);
        // Falls through to fallback below
    }
}
```

**Inside BizDocsAdapter:**
```php
public function generateInvoicePdf(InvoiceModel $invoice): string
{
    // 1. Convert CMS invoice to BizDocs Document format
    $document = $this->conversionService->invoiceToDocument($invoice);
    
    // 2. Generate PDF using existing BizDocs service
    return $this->pdfService->generatePdfContent($document);
}
```

**Inside DocumentConversionService:**
```php
public function invoiceToDocument(InvoiceModel $invoice): Document
{
    // Create BizDocs Document entity
    $document = Document::create(
        businessId: $invoice->company_id,
        customerId: $invoice->customer_id,
        type: DocumentType::fromString('invoice'),
        number: DocumentNumber::fromString($invoice->invoice_number),
        issueDate: new \DateTimeImmutable($invoice->invoice_date),
        currency: 'ZMW',
        dueDate: $invoice->due_date ? new \DateTimeImmutable($invoice->due_date) : null,
        notes: $invoice->notes,
        terms: $invoice->terms,
    );

    // Add line items
    foreach ($invoice->items as $index => $item) {
        $document->addItem(DocumentItem::create(
            description: $item->description,
            quantity: $item->quantity,
            unitPrice: Money::fromAmount((int)($item->unit_price * 100), 'ZMW'),
            taxRate: $item->tax_rate ?? 0,
            sortOrder: $index
        ));
    }

    return $document;
}
```

### Step 3B: Fallback Path (When Disabled or Error)

```php
// Fallback: Use existing PDF service
return $this->pdfService->download($invoice);

// OR if that doesn't exist, use basic template:
$pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('cms.pdf.invoice-basic', ['invoice' => $invoice])
    ->setPaper('a4', 'portrait');

return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf');
```

## Data Flow

### CMS Invoice Model → BizDocs Document

```php
// CMS Invoice Structure
InvoiceModel {
    id: 123
    company_id: 1
    customer_id: 45
    invoice_number: "INV-2026-001"
    invoice_date: "2026-04-20"
    due_date: "2026-05-20"
    subtotal: 1000.00
    total_amount: 1160.00
    notes: "Thank you for your business"
    items: [
        {
            description: "Web Development Services"
            quantity: 10
            unit_price: 100.00
            line_total: 1000.00
        }
    ]
    customer: {
        name: "Acme Corp"
        email: "billing@acme.com"
        phone: "+260 123 456 789"
    }
    company: {
        name: "My Company Ltd"
        address: "123 Main St, Lusaka"
        phone: "+260 987 654 321"
        email: "info@mycompany.com"
        tax_number: "TPIN123456"
    }
}

        ↓ (DocumentConversionService)

// BizDocs Document Structure
Document {
    businessId: 1
    customerId: 45
    type: DocumentType::INVOICE
    number: DocumentNumber("INV-2026-001")
    issueDate: DateTimeImmutable("2026-04-20")
    dueDate: DateTimeImmutable("2026-05-20")
    currency: "ZMW"
    notes: "Thank you for your business"
    items: [
        DocumentItem {
            description: "Web Development Services"
            quantity: 10
            unitPrice: Money(10000, "ZMW") // stored in cents
            taxRate: 0
            sortOrder: 0
        }
    ]
}
```

## Key Integration Points

### 1. Company Model Extension
```php
// CompanyModel.php
public function hasBizDocsModule(): bool
{
    return $this->settings['bizdocs_module'] ?? false;
}

public function getBizDocsFeatures(): array
{
    return $this->settings['bizdocs_features'] ?? [
        'invoices' => true,
        'quotations' => true,
        'receipts' => true,
        'stationery' => false,
    ];
}
```

### 2. Service Provider Registration
```php
// BizDocsIntegrationServiceProvider.php
public function register()
{
    $this->app->singleton(
        \App\Domain\CMS\BizDocs\Contracts\DocumentGeneratorInterface::class,
        \App\Domain\CMS\BizDocs\Adapters\BizDocsAdapter::class
    );
}
```

### 3. Controller Integration
```php
// InvoiceController.php - BEFORE
public function downloadPdf(Request $request, int $id)
{
    $invoice = InvoiceModel::findOrFail($id);
    return $this->pdfService->download($invoice);
}

// InvoiceController.php - AFTER
public function downloadPdf(Request $request, int $id)
{
    $invoice = InvoiceModel::with(['customer', 'items', 'company'])->findOrFail($id);
    
    // Try BizDocs first if enabled
    if ($invoice->company->hasBizDocsModule()) {
        try {
            $adapter = app(DocumentGeneratorInterface::class);
            $pdfContent = $adapter->generateInvoicePdf($invoice);
            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="invoice-' . $invoice->invoice_number . '.pdf"');
        } catch (\Exception $e) {
            \Log::error('BizDocs failed', ['error' => $e->getMessage()]);
            // Fall through to existing service
        }
    }
    
    // Fallback to existing service
    return $this->pdfService->download($invoice);
}
```

## Benefits of This Integration

### 1. **Non-Breaking**
- Existing invoice system continues to work
- No changes to invoice creation, editing, or management
- Only PDF generation is enhanced

### 2. **Optional**
- Companies can enable/disable BizDocs
- Fallback ensures PDFs always work
- No dependency on BizDocs for core functionality

### 3. **Isolated**
- CMS code doesn't know about BizDocs internals
- BizDocs changes don't affect CMS
- Adapter pattern provides clean separation

### 4. **Graceful Degradation**
- If BizDocs fails, fallback to basic PDF
- Errors are logged but don't break user experience
- Users always get a PDF

### 5. **Feature Flags**
- Enable BizDocs for invoices but not quotations
- Granular control per document type
- Easy to test and roll out gradually

## Example User Journey

### Scenario: Company Enables BizDocs

1. **Admin goes to Settings > Modules**
2. **Toggles BizDocs Integration ON**
3. **Selects features: ☑ Invoices, ☑ Quotations, ☑ Receipts**
4. **Clicks "Save Settings"**

Backend saves:
```php
$company->settings = [
    'bizdocs_module' => true,
    'bizdocs_features' => [
        'invoices' => true,
        'quotations' => true,
        'receipts' => true,
        'stationery' => false,
    ]
];
```

5. **User views an invoice**
6. **Clicks "Download PDF"**
7. **System checks: `$company->hasBizDocsModule()` → true**
8. **BizDocs generates professional PDF with custom template**
9. **User receives beautifully formatted PDF**

### Scenario: BizDocs Disabled or Fails

1. **User views an invoice**
2. **Clicks "Download PDF"**
3. **System checks: `$company->hasBizDocsModule()` → false**
4. **OR BizDocs throws exception**
5. **System uses fallback: basic PDF template**
6. **User receives simple but functional PDF**

## Technical Details

### Database Schema
No changes to existing CMS tables! BizDocs settings stored in `companies.settings` JSON column:

```json
{
  "bizdocs_module": true,
  "bizdocs_features": {
    "invoices": true,
    "quotations": true,
    "receipts": true,
    "stationery": false
  }
}
```

### Dependencies
- **CMS depends on:** `DocumentGeneratorInterface` (contract only)
- **BizDocs depends on:** Nothing from CMS
- **Adapter depends on:** Both CMS models and BizDocs services

### Error Handling
```php
try {
    // Try BizDocs
    $pdfContent = $adapter->generateInvoicePdf($invoice);
} catch (\App\Domain\CMS\BizDocs\Exceptions\DocumentConversionException $e) {
    // Conversion failed - log and fallback
    \Log::error('BizDocs conversion failed', ['error' => $e->getMessage()]);
} catch (\Exception $e) {
    // Any other error - log and fallback
    \Log::error('BizDocs generation failed', ['error' => $e->getMessage()]);
}

// Fallback always works
return $this->pdfService->download($invoice);
```

## Summary

BizDocs integrates with CMS invoices as an **optional enhancement layer**:

1. **Existing system unchanged** - Invoices work exactly as before
2. **Optional module** - Enable/disable per company
3. **Adapter pattern** - Clean separation of concerns
4. **Graceful fallback** - Always generates PDFs
5. **Feature flags** - Granular control per document type
6. **Error resilient** - Failures don't break user experience

The integration is **transparent to users** - they just get better PDFs when BizDocs is enabled!
