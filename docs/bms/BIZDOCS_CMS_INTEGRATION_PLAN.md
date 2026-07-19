# BizDocs CMS Integration - Implementation Complete

**Last Updated:** April 20, 2026  
**Status:** ✅ COMPLETE

## Overview

The BizDocs integration for CMS is now fully implemented as an **optional module** that can be enabled/disabled in company settings. When enabled, it provides professional PDF generation for invoices, quotations, and receipts using the existing BizDocs system.

## Implementation Status

### ✅ COMPLETED

1. **Module Toggle System**
   - Added BizDocs module to Settings > Modules tab
   - Toggle switch to enable/disable module
   - Feature-level toggles (invoices, quotations, receipts, stationery)
   - Backend route and controller method for saving settings

2. **Backend Integration**
   - Created `DocumentGeneratorInterface` contract
   - Created `DocumentConversionService` (converts CMS → BizDocs format)
   - Created `BizDocsAdapter` (calls existing BizDocs services)
   - Created `BizDocsIntegrationServiceProvider`
   - Registered provider in `bootstrap/providers.php`
   - Added `hasBizDocsModule()` and `getBizDocsFeatures()` to CompanyModel

3. **PDF Download Routes**
   - Added `/invoices/{invoice}/pdf` route
   - Added `/quotations/{quotation}/pdf` route  
   - Added `/payments/{payment}/receipt/download` route
   - Added preview routes for all document types

4. **Controller Updates**
   - Updated `InvoiceController::downloadPdf()` to use BizDocsAdapter
   - Updated `InvoiceController::previewPdf()` to use BizDocsAdapter
   - Updated `QuotationController::downloadPdf()` to use BizDocsAdapter
   - Updated `QuotationController::previewPdf()` to use BizDocsAdapter
   - Updated `PaymentController::downloadReceipt()` to use BizDocsAdapter
   - Updated `PaymentController::previewReceipt()` to use BizDocsAdapter

5. **Frontend UI**
   - Added PDF download button to `Invoices/Show.vue`
   - Added PDF download button to `Quotations/Show.vue`
   - Payment receipts already had download buttons
   - Added BizDocs module card to Settings > Modules tab

6. **Fallback PDF Templates**
   - Created `resources/views/cms/pdf/invoice-basic.blade.php`
   - Created `resources/views/cms/pdf/quotation-basic.blade.php`
   - Created `resources/views/cms/pdf/receipt-basic.blade.php`

7. **Error Handling**
   - Graceful fallback to basic PDFs if BizDocs fails
   - Logging of BizDocs errors
   - No disruption to user experience on failure

## Architecture

### Adapter Pattern

```
CMS Controllers
      ↓
DocumentGeneratorInterface (Contract)
      ↓
BizDocsAdapter
      ↓
┌─────────────────────────────────┐
│  DocumentConversionService      │ ← Converts CMS models to BizDocs format
└─────────────────────────────────┘
      ↓
┌─────────────────────────────────┐
│  Existing BizDocs Services      │
│  - PdfGenerationService         │
│  - StationeryGeneratorService   │
└─────────────────────────────────┘
```

### Isolation Strategy

- **CMS code** only depends on `DocumentGeneratorInterface`
- **BizDocs changes** don't affect CMS (adapter absorbs changes)
- **Fallback system** ensures CMS works even if BizDocs fails
- **Module toggle** allows companies to opt-in/out

## Usage

### For Companies

1. Go to **Settings > Modules** tab
2. Toggle **BizDocs Integration** ON
3. Select which features to enable:
   - ☑ Invoice PDFs
   - ☑ Quotation PDFs
   - ☑ Payment Receipts
   - ☐ Print Stationery (Advanced)

### For Developers

**Check if module is enabled:**
```php
if ($company->hasBizDocsModule()) {
    // Use BizDocs
}
```

**Generate PDF:**
```php
$adapter = app(DocumentGeneratorInterface::class);
$pdfContent = $adapter->generateInvoicePdf($invoice);
```

**Fallback is automatic:**
```php
try {
    $pdfContent = $adapter->generateInvoicePdf($invoice);
} catch (\Exception $e) {
    // Adapter automatically falls back to basic PDF
    // Error is logged, user gets PDF either way
}
```

## Files Modified/Created

### Backend
- `app/Domain/CMS/BizDocs/Contracts/DocumentGeneratorInterface.php` ✅
- `app/Domain/CMS/BizDocs/Exceptions/DocumentConversionException.php` ✅
- `app/Domain/CMS/BizDocs/Services/DocumentConversionService.php` ✅
- `app/Domain/CMS/BizDocs/Adapters/BizDocsAdapter.php` ✅
- `app/Providers/BizDocsIntegrationServiceProvider.php` ✅
- `app/Infrastructure/Persistence/Eloquent/CMS/CompanyModel.php` ✅
- `app/Http/Controllers/CMS/InvoiceController.php` ✅
- `app/Http/Controllers/CMS/QuotationController.php` ✅
- `app/Http/Controllers/CMS/PaymentController.php` ✅
- `app/Http/Controllers/CMS/SettingsController.php` ✅
- `bootstrap/providers.php` ✅
- `routes/cms.php` ✅

### Frontend
- `resources/js/pages/CMS/Invoices/Show.vue` ✅
- `resources/js/pages/CMS/Quotations/Show.vue` ✅
- `resources/js/pages/CMS/Settings/Index.vue` ✅

### Templates
- `resources/views/cms/pdf/invoice-basic.blade.php` ✅
- `resources/views/cms/pdf/quotation-basic.blade.php` ✅
- `resources/views/cms/pdf/receipt-basic.blade.php` ✅

## Testing Checklist

- [ ] Enable BizDocs module in Settings
- [ ] Download invoice PDF (should use BizDocs)
- [ ] Download quotation PDF (should use BizDocs)
- [ ] Download payment receipt (should use BizDocs)
- [ ] Disable BizDocs module
- [ ] Download invoice PDF (should use fallback)
- [ ] Test with company that has no BizDocs templates
- [ ] Verify fallback works when BizDocs service fails
- [ ] Check error logging when BizDocs fails

## Benefits

✅ **Optional** - Companies can enable/disable as needed  
✅ **Isolated** - CMS doesn't break if BizDocs changes  
✅ **Fallback** - Always generates PDFs, even if BizDocs fails  
✅ **Professional** - Uses BizDocs templates when available  
✅ **Simple** - Basic PDFs when BizDocs is disabled  
✅ **Flexible** - Feature-level control (invoices, quotations, receipts)

## Future Enhancements

- [ ] Document Templates management page
- [ ] Print Stationery generation page
- [ ] Email integration with BizDocs PDFs
- [ ] WhatsApp sharing with BizDocs PDFs
- [ ] Custom template upload
- [ ] Template preview in settings

## Notes

- Module is **disabled by default** for new companies
- Existing companies need to manually enable it
- Fallback templates are always available
- BizDocs errors are logged but don't disrupt user experience
- Feature toggles allow granular control

**Date:** April 19, 2026  
**Status:** 📋 Planning

## Overview

Integrate BizDocs professional document generation capabilities into the CMS to provide:
- **Formatted PDF documents** (invoices, quotations, receipts)
- **Print-ready stationery** (bulk document generation)
- **Customizable templates** per company
- **Professional branding** with logos and signatures

## Current State Analysis

### BizDocs Capabilities
✅ **PDF Generation Service** (`PdfGenerationService.php`)
- Generates professional PDFs from documents
- Supports custom templates
- Handles logos and signatures
- Uses DomPDF for rendering

✅ **Stationery Generator** (`StationeryGeneratorService.php`)
- Bulk document generation (1-10 per page)
- Pre-numbered documents for printing
- Multiple layouts (A4, half-page, quarter-page, etc.)
- Perfect for printing physical invoices/receipts

✅ **Template System**
- Custom Blade templates
- Business profile integration
- Customer data integration
- Flexible layouts

### CMS Current State
✅ **Document Management**
- Invoices (`InvoiceModel`)
- Quotations (`QuotationModel`)
- Jobs with measurements
- Customer management

❌ **Missing**
- PDF generation for documents
- Print format generation
- Template customization per company
- Professional document branding

## Module Architecture

### BizDocs as an Optional Module

Following the Fabrication Module pattern, BizDocs will be an **optional module** that companies can enable/disable:

**Module Settings:**
```json
{
  "settings": {
    "bizdocs_module": true,  // Enable/disable module
    "bizdocs_features": {
      "pdf_generation": true,
      "print_stationery": true,
      "email_documents": true,
      "whatsapp_sharing": false,
      "qr_codes": false
    }
  }
}
```

**Benefits of Module Approach:**
- ✅ **Optional** - Companies choose if they need it
- ✅ **Flexible** - Enable only needed features
- ✅ **Performance** - No overhead for companies not using it
- ✅ **Scalable** - Easy to add/remove features
- ✅ **Monetization** - Can be a premium feature

**Module Detection:**
```php
// app/Infrastructure/Persistence/Eloquent/CMS/CompanyModel.php
public function hasBizDocsModule(): bool
{
    // Explicit toggle in settings takes precedence
    if (isset($this->settings['bizdocs_module'])) {
        return (bool) $this->settings['bizdocs_module'];
    }
    
    // Default: enabled for all companies (can change to false)
    return true;
}

public function getBizDocsFeatures(): array
{
    return $this->settings['bizdocs_features'] ?? [
        'pdf_generation' => true,
        'print_stationery' => true,
        'email_documents' => true,
        'whatsapp_sharing' => false,
        'qr_codes' => false,
    ];
}
```

**UI Conditional Rendering:**
```vue
<!-- Only show PDF button if module enabled -->
<button v-if="company.has_bizdocs_module" @click="downloadPdf">
  <DocumentArrowDownIcon class="h-5 w-5" />
  Download PDF
</button>

<!-- Only show stationery link if feature enabled -->
<NavItem
  v-if="company.bizdocs_features?.print_stationery"
  icon="PrinterIcon"
  label="Print Stationery"
  route-name="cms.settings.print-stationery"
/>
```

## Integration Strategy

### Phase 0: Module Setup (Day 1)

#### 0.1 Database Schema

```php
// Migration: Add BizDocs module settings
Schema::table('cms_companies', function (Blueprint $table) {
    // Module is controlled via settings JSON column (already exists)
    // No new columns needed
});

// Default settings in CompanyModel
protected $casts = [
    'settings' => 'array',
];

protected static function boot()
{
    parent::boot();
    
    static::creating(function ($company) {
        // Set default module settings for new companies
        $company->settings = array_merge([
            'bizdocs_module' => true,
            'bizdocs_features' => [
                'pdf_generation' => true,
                'print_stationery' => true,
                'email_documents' => true,
                'whatsapp_sharing' => false,
                'qr_codes' => false,
            ],
        ], $company->settings ?? []);
    });
}
```

#### 0.2 Module Toggle UI

```vue
<!-- resources/js/pages/CMS/Settings/Modules.vue -->
<template>
  <div class="max-w-4xl mx-auto space-y-6">
    <h1>Modules & Features</h1>
    <p class="text-gray-600">Enable or disable optional modules for your company</p>
    
    <!-- BizDocs Module Card -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
      <div class="flex items-start justify-between">
        <div class="flex items-start gap-4">
          <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
            <DocumentTextIcon class="h-6 w-6 text-blue-600" />
          </div>
          <div>
            <h3 class="text-lg font-semibold text-gray-900">BizDocs</h3>
            <p class="text-sm text-gray-600 mt-1">
              Professional PDF documents, print stationery, and document sharing
            </p>
            
            <!-- Feature List -->
            <ul class="mt-3 space-y-1 text-sm text-gray-600">
              <li class="flex items-center gap-2">
                <CheckIcon class="h-4 w-4 text-green-500" />
                Generate professional PDF invoices & quotations
              </li>
              <li class="flex items-center gap-2">
                <CheckIcon class="h-4 w-4 text-green-500" />
                Print pre-numbered stationery
              </li>
              <li class="flex items-center gap-2">
                <CheckIcon class="h-4 w-4 text-green-500" />
                Email documents to customers
              </li>
              <li class="flex items-center gap-2">
                <CheckIcon class="h-4 w-4 text-green-500" />
                Custom templates & branding
              </li>
            </ul>
          </div>
        </div>
        
        <!-- Toggle Switch -->
        <Switch
          v-model="form.bizdocs_module"
          :class="form.bizdocs_module ? 'bg-blue-600' : 'bg-gray-200'"
          class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
        >
          <span class="sr-only">Enable BizDocs</span>
          <span
            :class="form.bizdocs_module ? 'translate-x-6' : 'translate-x-1'"
            class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
          />
        </Switch>
      </div>
      
      <!-- Feature Toggles (shown when module enabled) -->
      <div v-if="form.bizdocs_module" class="mt-6 pt-6 border-t border-gray-200">
        <h4 class="text-sm font-medium text-gray-900 mb-4">Features</h4>
        <div class="space-y-3">
          <label class="flex items-center justify-between">
            <span class="text-sm text-gray-700">PDF Generation</span>
            <Switch v-model="form.bizdocs_features.pdf_generation" />
          </label>
          <label class="flex items-center justify-between">
            <span class="text-sm text-gray-700">Print Stationery</span>
            <Switch v-model="form.bizdocs_features.print_stationery" />
          </label>
          <label class="flex items-center justify-between">
            <span class="text-sm text-gray-700">Email Documents</span>
            <Switch v-model="form.bizdocs_features.email_documents" />
          </label>
          <label class="flex items-center justify-between">
            <span class="text-sm text-gray-700">WhatsApp Sharing</span>
            <Switch v-model="form.bizdocs_features.whatsapp_sharing" />
          </label>
          <label class="flex items-center justify-between">
            <span class="text-sm text-gray-700">QR Codes</span>
            <Switch v-model="form.bizdocs_features.qr_codes" />
          </label>
        </div>
      </div>
    </div>
    
    <!-- Fabrication Module Card -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
      <!-- Similar structure for Fabrication Module -->
    </div>
    
    <button @click="save" class="btn-primary">
      Save Module Settings
    </button>
  </div>
</template>
```

#### 0.3 Navigation Integration

```vue
<!-- resources/js/Layouts/CMSLayout.vue -->

<!-- Add to Settings section -->
<NavItem
  v-if="shouldShowNavItem('cms.settings.modules')"
  icon="PuzzlePieceIcon"
  label="Modules & Features"
  route-name="cms.settings.modules"
  :collapsed="sidebarCollapsed"
  :active="isActive('cms.settings.modules')"
  @click="navigateTo('cms.settings.modules')"
/>

<!-- Document Templates (only if BizDocs enabled) -->
<NavItem
  v-if="shouldShowNavItem('cms.settings.document-templates') && hasBizDocsModule"
  icon="DocumentTextIcon"
  label="Document Templates"
  route-name="cms.settings.document-templates"
  :collapsed="sidebarCollapsed"
  :active="isActive('cms.settings.document-templates')"
  @click="navigateTo('cms.settings.document-templates')"
/>

<!-- Print Stationery (only if BizDocs enabled) -->
<NavItem
  v-if="shouldShowNavItem('cms.settings.print-stationery') && hasBizDocsModule"
  icon="PrinterIcon"
  label="Print Stationery"
  route-name="cms.settings.print-stationery"
  :collapsed="sidebarCollapsed"
  :active="isActive('cms.settings.print-stationery')"
  @click="navigateTo('cms.settings.print-stationery')"
/>

<script setup>
// Add computed property
const hasBizDocsModule = computed(() => {
  const c = company.value as any
  if (!c) return false
  return c.settings?.bizdocs_module ?? true // Default enabled
})
</script>
```

### Phase 1: Core PDF Generation (Week 1)

#### 1.1 Add PDF Generation to Invoices

**Backend Changes:**

```php
// app/Domain/CMS/Core/Services/InvoiceService.php
public function generatePdf(InvoiceModel $invoice): string
{
    // Convert CMS invoice to BizDocs format
    $document = $this->convertToDocument($invoice);
    
    // Use BizDocs PDF service
    return app(PdfGenerationService::class)->generatePdfContent($document);
}
```

**Controller Addition:**

```php
// app/Http/Controllers/CMS/InvoiceController.php
public function downloadPdf(InvoiceModel $invoice)
{
    $pdfContent = $this->invoiceService->generatePdf($invoice);
    
    return response($pdfContent)
        ->header('Content-Type', 'application/pdf')
        ->header('Content-Disposition', 'attachment; filename="invoice-' . $invoice->invoice_number . '.pdf"');
}
```

**Frontend Addition:**

```vue
<!-- resources/js/pages/CMS/Invoices/Show.vue -->
<!-- Only show if BizDocs module enabled -->
<button v-if="company.has_bizdocs_module" @click="downloadPdf" class="btn-primary">
  <DocumentArrowDownIcon class="h-5 w-5" />
  Download PDF
</button>

<!-- Or in actions menu -->
<Menu v-if="company.has_bizdocs_module" as="div" class="relative">
  <MenuButton class="btn-secondary">
    <EllipsisVerticalIcon class="h-5 w-5" />
  </MenuButton>
  <MenuItems class="menu-dropdown">
    <MenuItem v-if="company.bizdocs_features?.pdf_generation">
      <button @click="downloadPdf">
        <DocumentArrowDownIcon class="h-5 w-5" />
        Download PDF
      </button>
    </MenuItem>
    <MenuItem v-if="company.bizdocs_features?.email_documents">
      <button @click="emailDocument">
        <EnvelopeIcon class="h-5 w-5" />
        Email to Customer
      </button>
    </MenuItem>
    <MenuItem v-if="company.bizdocs_features?.whatsapp_sharing">
      <button @click="shareWhatsApp">
        <DevicePhoneMobileIcon class="h-5 w-5" />
        Share via WhatsApp
      </button>
    </MenuItem>
  </MenuItems>
</Menu>
```

#### 1.2 Add PDF Generation to Quotations

Same pattern as invoices:
- `QuotationService::generatePdf()`
- `QuotationController::downloadPdf()`
- UI button in quotation show page

#### 1.3 Add PDF Generation to Receipts

Create receipt generation from payments:
- `PaymentService::generateReceipt()`
- `PaymentController::downloadReceipt()`
- UI button in payment show page

### Phase 2: Template Management (Week 2)

#### 2.1 Company Template Settings

**Database Migration:**

```php
// Add to cms_companies table
Schema::table('cms_companies', function (Blueprint $table) {
    $table->string('invoice_template')->nullable();
    $table->string('quotation_template')->nullable();
    $table->string('receipt_template')->nullable();
    $table->text('document_footer')->nullable();
    $table->json('template_settings')->nullable();
});
```

**Settings Page:**

```vue
<!-- resources/js/pages/CMS/Settings/DocumentTemplates.vue -->
<template>
  <div>
    <h2>Document Templates</h2>
    
    <!-- Template Selection -->
    <select v-model="form.invoice_template">
      <option value="default">Default Professional</option>
      <option value="modern">Modern Minimal</option>
      <option value="classic">Classic Formal</option>
      <option value="creative">Creative Bold</option>
    </select>
    
    <!-- Preview -->
    <div class="template-preview">
      <img :src="`/templates/previews/${form.invoice_template}.png`" />
    </div>
    
    <!-- Custom Footer -->
    <textarea v-model="form.document_footer" 
      placeholder="Custom footer text (payment terms, thank you message, etc.)">
    </textarea>
  </div>
</template>
```

#### 2.2 Template Customization Options

**Settings Structure:**

```json
{
  "template_settings": {
    "color_scheme": "blue",
    "font_family": "Arial",
    "show_logo": true,
    "show_signature": true,
    "show_qr_code": false,
    "header_style": "centered",
    "footer_style": "minimal",
    "line_item_style": "detailed"
  }
}
```

### Phase 3: Print Stationery (Week 3)

#### 3.1 Stationery Generator Integration

**New Controller:**

```php
// app/Http/Controllers/CMS/StationeryController.php
class StationeryController extends Controller
{
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'document_type' => 'required|in:invoice,quotation,receipt',
            'quantity' => 'required|integer|min:1|max:1000',
            'documents_per_page' => 'required|in:1,2,4,6,8,10',
            'starting_number' => 'required|string',
            'page_size' => 'required|in:A4,Letter',
        ]);
        
        $companyId = $request->user()->cmsUser->company_id;
        $company = CompanyModel::findOrFail($companyId);
        
        // Convert CMS company to BizDocs BusinessProfile
        $businessProfile = $this->convertToBusinessProfile($company);
        
        // Generate stationery PDF
        $pdfContent = app(StationeryGeneratorService::class)->generate(
            $businessProfile,
            null, // template
            $validated['document_type'],
            $validated['quantity'],
            $validated['documents_per_page'],
            $validated['starting_number'],
            $validated['page_size']
        );
        
        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="stationery-' . $validated['document_type'] . '.pdf"');
    }
}
```

**Frontend Page:**

```vue
<!-- resources/js/pages/CMS/Settings/PrintStationery.vue -->
<template>
  <div class="max-w-4xl mx-auto">
    <h1>Generate Print Stationery</h1>
    <p class="text-gray-600">Generate pre-numbered documents for printing</p>
    
    <form @submit.prevent="generate">
      <!-- Document Type -->
      <div>
        <label>Document Type</label>
        <select v-model="form.document_type">
          <option value="invoice">Invoice</option>
          <option value="quotation">Quotation</option>
          <option value="receipt">Receipt</option>
        </select>
      </div>
      
      <!-- Quantity -->
      <div>
        <label>Quantity</label>
        <input type="number" v-model="form.quantity" min="1" max="1000" />
        <p class="text-sm text-gray-500">How many documents to generate</p>
      </div>
      
      <!-- Layout -->
      <div>
        <label>Documents Per Page</label>
        <div class="grid grid-cols-3 gap-4">
          <button type="button" @click="form.documents_per_page = 1"
            :class="form.documents_per_page === 1 ? 'selected' : ''">
            <div class="layout-preview">1 per page</div>
            <span>Full Page</span>
          </button>
          <button type="button" @click="form.documents_per_page = 2"
            :class="form.documents_per_page === 2 ? 'selected' : ''">
            <div class="layout-preview">2 per page</div>
            <span>Half Page</span>
          </button>
          <button type="button" @click="form.documents_per_page = 4"
            :class="form.documents_per_page === 4 ? 'selected' : ''">
            <div class="layout-preview">4 per page</div>
            <span>Quarter Page</span>
          </button>
        </div>
      </div>
      
      <!-- Starting Number -->
      <div>
        <label>Starting Number</label>
        <input type="text" v-model="form.starting_number" 
          placeholder="INV-2026-0001" />
        <p class="text-sm text-gray-500">First document number</p>
      </div>
      
      <!-- Preview -->
      <div class="bg-gray-50 p-4 rounded-lg">
        <h3>Preview</h3>
        <p>Generating {{ form.quantity }} {{ form.document_type }}s</p>
        <p>Layout: {{ form.documents_per_page }} per page</p>
        <p>Numbers: {{ form.starting_number }} to {{ calculateEndNumber() }}</p>
        <p>Total pages: {{ Math.ceil(form.quantity / form.documents_per_page) }}</p>
      </div>
      
      <button type="submit" class="btn-primary">
        <PrinterIcon class="h-5 w-5" />
        Generate PDF
      </button>
    </form>
  </div>
</template>
```

### Phase 4: Advanced Features (Week 4)

#### 4.1 Email Integration

Send PDFs via email:

```php
// app/Notifications/CMS/InvoiceSentNotification.php
public function toMail($notifiable)
{
    $pdfContent = $this->invoiceService->generatePdf($this->invoice);
    
    return (new MailMessage)
        ->subject('Invoice ' . $this->invoice->invoice_number)
        ->line('Please find your invoice attached.')
        ->attachData($pdfContent, 'invoice.pdf', [
            'mime' => 'application/pdf',
        ]);
}
```

#### 4.2 WhatsApp Sharing

```php
// Use BizDocs WhatsAppSharingService
public function shareViaWhatsApp(InvoiceModel $invoice, string $phoneNumber)
{
    $pdfUrl = $this->uploadPdfToStorage($invoice);
    
    return app(WhatsAppSharingService::class)->shareDocument(
        $phoneNumber,
        $pdfUrl,
        "Invoice {$invoice->invoice_number}"
    );
}
```

#### 4.3 QR Code Integration

Add QR codes to documents for payment/verification:

```php
// In template settings
'show_qr_code' => true,
'qr_code_content' => 'payment_link', // or 'verification_url'
```

## Data Mapping

### CMS → BizDocs Conversion

```php
// app/Domain/CMS/Core/Services/DocumentConversionService.php
class DocumentConversionService
{
    public function invoiceToDocument(InvoiceModel $invoice): Document
    {
        return Document::create(
            id: $invoice->id,
            businessId: $invoice->company_id,
            customerId: $invoice->customer_id,
            number: DocumentNumber::fromString($invoice->invoice_number),
            type: DocumentType::INVOICE(),
            issueDate: $invoice->invoice_date,
            dueDate: $invoice->due_date,
            items: $this->convertLineItems($invoice->items),
            // ... other fields
        );
    }
    
    public function companyToBusinessProfile(CompanyModel $company): BusinessProfile
    {
        return BusinessProfile::create(
            id: $company->id,
            businessName: $company->name,
            address: $company->address,
            phone: $company->phone,
            email: $company->email,
            tpin: $company->tax_number,
            logo: $company->logo_path,
            signatureImage: $company->settings['signature_image'] ?? null,
            defaultCurrency: $company->settings['currency'] ?? 'ZMW',
        );
    }
}
```

## UI/UX Design

### Document Actions Menu

```vue
<!-- On Invoice/Quotation/Receipt Show Pages -->
<Menu as="div" class="relative">
  <MenuButton class="btn-secondary">
    <EllipsisVerticalIcon class="h-5 w-5" />
  </MenuButton>
  <MenuItems class="menu-dropdown">
    <MenuItem>
      <button @click="downloadPdf">
        <DocumentArrowDownIcon class="h-5 w-5" />
        Download PDF
      </button>
    </MenuItem>
    <MenuItem>
      <button @click="emailDocument">
        <EnvelopeIcon class="h-5 w-5" />
        Email to Customer
      </button>
    </MenuItem>
    <MenuItem>
      <button @click="shareWhatsApp">
        <DevicePhoneMobileIcon class="h-5 w-5" />
        Share via WhatsApp
      </button>
    </MenuItem>
    <MenuItem>
      <button @click="printDocument">
        <PrinterIcon class="h-5 w-5" />
        Print
      </button>
    </MenuItem>
  </MenuItems>
</Menu>
```

### Settings Navigation

Add new section in CMS Settings:

```
Settings
├── Company Settings
├── Modules & Features      ← NEW (Module toggle)
├── Pricing Rules
├── Industry Presets
├── Document Templates      ← NEW (Only if BizDocs enabled)
├── Print Stationery        ← NEW (Only if BizDocs enabled)
├── Email Settings
├── SMS Settings
└── Security
```

**Module Badge:**
Show module status in navigation:
```vue
<NavItem
  icon="DocumentTextIcon"
  label="Document Templates"
  badge="Module"
  badge-color="blue"
/>
```

## Technical Considerations

### 1. Isolation & Loose Coupling

**Challenge:** BizDocs updates could break CMS integration

**Solution: Adapter Pattern with Interface Isolation**

```php
// app/Domain/CMS/BizDocs/Contracts/DocumentGeneratorInterface.php
interface DocumentGeneratorInterface
{
    public function generateInvoicePdf(InvoiceModel $invoice): string;
    public function generateQuotationPdf(QuotationModel $quotation): string;
    public function generateReceiptPdf(PaymentModel $payment): string;
    public function generateStationery(array $config): string;
}

// app/Domain/CMS/BizDocs/Adapters/BizDocsAdapter.php
class BizDocsAdapter implements DocumentGeneratorInterface
{
    public function __construct(
        private PdfGenerationService $pdfService,
        private DocumentConversionService $conversionService
    ) {}
    
    public function generateInvoicePdf(InvoiceModel $invoice): string
    {
        try {
            // Convert CMS data to BizDocs format
            $document = $this->conversionService->invoiceToDocument($invoice);
            
            // Generate PDF using BizDocs
            return $this->pdfService->generatePdfContent($document);
        } catch (\Exception $e) {
            // Log error and fallback
            \Log::error('BizDocs PDF generation failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
            
            // Fallback to basic PDF generation
            return $this->generateFallbackPdf($invoice);
        }
    }
    
    private function generateFallbackPdf(InvoiceModel $invoice): string
    {
        // Simple fallback using basic Blade template
        return Pdf::loadView('cms.pdf.invoice-basic', ['invoice' => $invoice])
            ->output();
    }
}
```

**Benefits:**
- ✅ **Isolated** - CMS only depends on interface, not BizDocs directly
- ✅ **Testable** - Can mock the interface for testing
- ✅ **Swappable** - Can replace BizDocs with another service
- ✅ **Fallback** - Graceful degradation if BizDocs fails
- ✅ **Version-proof** - Interface stays stable even if BizDocs changes

### 2. Version Compatibility Strategy

**Approach: Semantic Versioning with Compatibility Layer**

```php
// app/Domain/CMS/BizDocs/BizDocsCompatibility.php
class BizDocsCompatibility
{
    private const SUPPORTED_VERSIONS = ['1.0', '1.1', '1.2', '2.0'];
    private const MIN_VERSION = '1.0';
    private const MAX_VERSION = '2.0';
    
    public static function checkCompatibility(): array
    {
        $version = self::getBizDocsVersion();
        
        return [
            'compatible' => self::isCompatible($version),
            'version' => $version,
            'warnings' => self::getWarnings($version),
            'requires_update' => self::requiresUpdate($version),
        ];
    }
    
    private static function getBizDocsVersion(): string
    {
        // Check BizDocs version from composer or config
        return config('bizdocs.version', '1.0');
    }
    
    private static function isCompatible(string $version): bool
    {
        return version_compare($version, self::MIN_VERSION, '>=') &&
               version_compare($version, self::MAX_VERSION, '<=');
    }
    
    public static function getAdapter(string $version): DocumentGeneratorInterface
    {
        // Return version-specific adapter
        return match(true) {
            version_compare($version, '2.0', '>=') => new BizDocsV2Adapter(),
            version_compare($version, '1.0', '>=') => new BizDocsV1Adapter(),
            default => new FallbackAdapter(),
        };
    }
}
```

**Usage:**
```php
// In service provider
$compatibility = BizDocsCompatibility::checkCompatibility();

if (!$compatibility['compatible']) {
    \Log::warning('BizDocs version incompatible', $compatibility);
    // Use fallback adapter
}

$adapter = BizDocsCompatibility::getAdapter($compatibility['version']);
$this->app->singleton(DocumentGeneratorInterface::class, fn() => $adapter);
```

### 3. Data Conversion Layer

**Challenge:** BizDocs data structures might change

**Solution: Dedicated Conversion Service with Validation**

```php
// app/Domain/CMS/BizDocs/Services/DocumentConversionService.php
class DocumentConversionService
{
    /**
     * Convert CMS Invoice to BizDocs Document
     * Validates data and handles missing fields gracefully
     */
    public function invoiceToDocument(InvoiceModel $invoice): Document
    {
        try {
            // Validate required fields
            $this->validateInvoice($invoice);
            
            // Convert with explicit field mapping
            return Document::create(
                id: $invoice->id,
                businessId: $invoice->company_id,
                customerId: $invoice->customer_id,
                number: $this->convertNumber($invoice->invoice_number),
                type: $this->convertType('invoice'),
                issueDate: $this->convertDate($invoice->invoice_date),
                dueDate: $this->convertDate($invoice->due_date),
                items: $this->convertLineItems($invoice->items),
                notes: $invoice->notes ?? '',
                terms: $invoice->terms ?? '',
            );
        } catch (\Exception $e) {
            throw new DocumentConversionException(
                "Failed to convert invoice {$invoice->id}: " . $e->getMessage(),
                previous: $e
            );
        }
    }
    
    private function validateInvoice(InvoiceModel $invoice): void
    {
        if (!$invoice->company_id) {
            throw new \InvalidArgumentException('Invoice missing company_id');
        }
        
        if (!$invoice->customer_id) {
            throw new \InvalidArgumentException('Invoice missing customer_id');
        }
        
        // Add more validations
    }
    
    private function convertNumber(string $number): DocumentNumber
    {
        // Handle different number formats
        return DocumentNumber::fromString($number);
    }
    
    private function convertDate($date): \DateTimeImmutable
    {
        if ($date instanceof \DateTimeImmutable) {
            return $date;
        }
        
        if ($date instanceof \DateTime) {
            return \DateTimeImmutable::createFromMutable($date);
        }
        
        if (is_string($date)) {
            return new \DateTimeImmutable($date);
        }
        
        throw new \InvalidArgumentException('Invalid date format');
    }
}
```

### 4. Dependency Management

**Strategy: Pin BizDocs Version with Controlled Updates**

```json
// composer.json
{
  "require": {
    "app/bizdocs": "^1.0",  // Allow minor updates, not major
  }
}
```

**Update Process:**
1. **Test in staging** - Always test BizDocs updates in staging first
2. **Run compatibility check** - Automated compatibility tests
3. **Review changelog** - Check for breaking changes
4. **Update adapter if needed** - Modify adapter for new version
5. **Deploy gradually** - Canary deployment

### 5. Fallback Mechanisms

**Multiple Layers of Fallback:**

```php
// app/Domain/CMS/BizDocs/Services/ResilientPdfGenerator.php
class ResilientPdfGenerator
{
    public function generate(InvoiceModel $invoice): string
    {
        // Try BizDocs first
        try {
            return $this->bizDocsAdapter->generateInvoicePdf($invoice);
        } catch (BizDocsException $e) {
            \Log::warning('BizDocs failed, trying fallback', [
                'error' => $e->getMessage(),
            ]);
        }
        
        // Fallback 1: Simple BizDocs template
        try {
            return $this->generateSimpleBizDocsPdf($invoice);
        } catch (\Exception $e) {
            \Log::warning('Simple BizDocs failed, trying basic', [
                'error' => $e->getMessage(),
            ]);
        }
        
        // Fallback 2: Basic CMS template
        try {
            return $this->generateBasicCmsPdf($invoice);
        } catch (\Exception $e) {
            \Log::error('All PDF generation failed', [
                'error' => $e->getMessage(),
            ]);
        }
        
        // Fallback 3: Plain text
        return $this->generatePlainTextPdf($invoice);
    }
}
```

### 6. Testing Strategy

**Comprehensive Test Suite:**

```php
// tests/Feature/CMS/BizDocsIntegrationTest.php
class BizDocsIntegrationTest extends TestCase
{
    /** @test */
    public function it_generates_invoice_pdf_successfully()
    {
        $invoice = InvoiceModel::factory()->create();
        
        $pdf = app(DocumentGeneratorInterface::class)
            ->generateInvoicePdf($invoice);
        
        $this->assertNotEmpty($pdf);
        $this->assertStringStartsWith('%PDF', $pdf); // PDF header
    }
    
    /** @test */
    public function it_handles_bizdocs_failure_gracefully()
    {
        // Mock BizDocs to throw exception
        $this->mock(PdfGenerationService::class)
            ->shouldReceive('generatePdfContent')
            ->andThrow(new \Exception('BizDocs error'));
        
        $invoice = InvoiceModel::factory()->create();
        
        // Should still generate PDF using fallback
        $pdf = app(DocumentGeneratorInterface::class)
            ->generateInvoicePdf($invoice);
        
        $this->assertNotEmpty($pdf);
    }
    
    /** @test */
    public function it_validates_data_before_conversion()
    {
        $invoice = InvoiceModel::factory()->create([
            'company_id' => null, // Invalid
        ]);
        
        $this->expectException(DocumentConversionException::class);
        
        app(DocumentConversionService::class)
            ->invoiceToDocument($invoice);
    }
}
```

### 7. Monitoring & Alerts

**Track Integration Health:**

```php
// app/Domain/CMS/BizDocs/Monitoring/BizDocsHealthCheck.php
class BizDocsHealthCheck
{
    public function check(): array
    {
        return [
            'bizdocs_available' => $this->isBizDocsAvailable(),
            'version_compatible' => $this->isVersionCompatible(),
            'pdf_generation_working' => $this->testPdfGeneration(),
            'fallback_working' => $this->testFallback(),
            'last_error' => $this->getLastError(),
            'success_rate_24h' => $this->getSuccessRate(),
        ];
    }
    
    private function testPdfGeneration(): bool
    {
        try {
            $testInvoice = $this->createTestInvoice();
            $pdf = app(DocumentGeneratorInterface::class)
                ->generateInvoicePdf($testInvoice);
            return !empty($pdf);
        } catch (\Exception $e) {
            \Log::error('BizDocs health check failed', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
```

**Dashboard Widget:**
```vue
<!-- Show BizDocs health in admin dashboard -->
<div class="health-widget">
  <h3>BizDocs Integration</h3>
  <div :class="health.bizdocs_available ? 'status-ok' : 'status-error'">
    {{ health.bizdocs_available ? '✓ Available' : '✗ Unavailable' }}
  </div>
  <div>Success Rate: {{ health.success_rate_24h }}%</div>
</div>
```

### 8. Update Impact Mitigation

**Strategies to Minimize Impact:**

1. **Interface Stability** - CMS depends on stable interface, not implementation
2. **Version Pinning** - Control when to update BizDocs
3. **Automated Tests** - Catch breaking changes early
4. **Fallback System** - Continue working even if BizDocs breaks
5. **Gradual Rollout** - Test updates with subset of companies first
6. **Feature Flags** - Disable BizDocs features if issues detected
7. **Monitoring** - Alert on failures immediately

**Feature Flag Example:**
```php
// Disable BizDocs if too many failures
if ($failureRate > 0.1) { // 10% failure rate
    Cache::put('bizdocs_disabled', true, now()->addHours(1));
    
    // Notify admins
    Notification::send($admins, new BizDocsDisabledNotification());
}

// Check before using
if (Cache::get('bizdocs_disabled')) {
    return $this->fallbackGenerator->generate($invoice);
}
```

### Summary: Update-Proof Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                         CMS Layer                            │
│  (InvoiceService, QuotationService, PaymentService)         │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ↓
┌─────────────────────────────────────────────────────────────┐
│              DocumentGeneratorInterface                      │
│  (Stable contract - never changes)                           │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ↓
┌─────────────────────────────────────────────────────────────┐
│                   BizDocsAdapter                             │
│  (Handles version differences & conversions)                 │
│  • Version detection                                         │
│  • Data conversion                                           │
│  • Error handling                                            │
│  • Fallback logic                                            │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ↓
┌─────────────────────────────────────────────────────────────┐
│                  BizDocs Services                            │
│  (Can change without affecting CMS)                          │
│  • PdfGenerationService                                      │
│  • StationeryGeneratorService                                │
│  • TemplateDataWrapper                                       │
└─────────────────────────────────────────────────────────────┘
```

**Result:** BizDocs updates have **minimal to zero impact** on CMS functionality.

### 9. Performance

**Challenge:** PDF generation can be slow for large documents

**Solutions:**
- Queue PDF generation for bulk operations
- Cache generated PDFs (with invalidation on document update)
- Use job batching for stationery generation

```php
// Queue PDF generation
GenerateInvoicePdfJob::dispatch($invoice);

// Cache PDFs
Cache::remember("invoice-pdf-{$invoice->id}", 3600, function() use ($invoice) {
    return $this->invoiceService->generatePdf($invoice);
});
```

### 10. Storage

**Challenge:** PDFs take up storage space

**Solutions:**
- Store PDFs in S3/DigitalOcean Spaces (already configured)
- Implement automatic cleanup of old PDFs
- Generate PDFs on-demand instead of storing

```php
// On-demand generation (recommended)
public function downloadPdf(InvoiceModel $invoice)
{
    $pdfContent = $this->invoiceService->generatePdf($invoice);
    return response($pdfContent)->header('Content-Type', 'application/pdf');
}
```

### 11. Template Compatibility

**Challenge:** BizDocs uses different data structures

**Solutions:**
- Create adapter/converter services
- Use TemplateDataWrapper for flexible access
- Maintain separate template sets for CMS

### 12. Company Branding

**Challenge:** Each company needs custom branding

**Solutions:**
- Store logo/signature in company settings
- Use company-specific templates
- Allow custom colors/fonts per company

## Implementation Checklist

### Phase 0: Module Setup
- [ ] Add `hasBizDocsModule()` method to `CompanyModel`
- [ ] Add `getBizDocsFeatures()` method to `CompanyModel`
- [ ] Create `Modules.vue` settings page
- [ ] Add module toggle UI with feature switches
- [ ] Add navigation items (conditional on module)
- [ ] Update `CMSLayout.vue` with `hasBizDocsModule` computed
- [ ] Test module enable/disable functionality
- [ ] Set default module state for new companies

### Phase 1: Core PDF Generation
- [ ] Create `DocumentConversionService`
- [ ] Add PDF generation to `InvoiceService`
- [ ] Add PDF generation to `QuotationService`
- [ ] Add PDF generation to `PaymentService` (receipts)
- [ ] Create download PDF routes
- [ ] Add "Download PDF" buttons to show pages
- [ ] Test PDF generation with sample data

### Phase 2: Template Management
- [ ] Add template columns to `cms_companies` table
- [ ] Create `DocumentTemplates.vue` settings page
- [ ] Design 4-5 professional templates
- [ ] Create template preview images
- [ ] Implement template selection logic
- [ ] Add custom footer support
- [ ] Test template switching

### Phase 3: Print Stationery
- [ ] Create `StationeryController`
- [ ] Create `PrintStationery.vue` page
- [ ] Add stationery route to settings
- [ ] Implement layout previews
- [ ] Add number range calculation
- [ ] Test bulk generation (100+ documents)
- [ ] Optimize PDF generation performance

### Phase 4: Advanced Features
- [ ] Email PDF integration
- [ ] WhatsApp sharing integration
- [ ] QR code generation
- [ ] PDF caching system
- [ ] Automatic cleanup jobs
- [ ] Analytics (PDFs generated, downloaded, etc.)

## File Structure

```
app/
├── Domain/CMS/Core/Services/
│   ├── DocumentConversionService.php      # NEW
│   ├── InvoiceService.php                 # UPDATED
│   ├── QuotationService.php               # UPDATED
│   └── PaymentService.php                 # UPDATED
├── Http/Controllers/CMS/
│   ├── InvoiceController.php              # UPDATED
│   ├── QuotationController.php            # UPDATED
│   ├── PaymentController.php              # UPDATED
│   └── StationeryController.php           # NEW
└── Jobs/
    └── GenerateDocumentPdfJob.php         # NEW

resources/
├── js/pages/CMS/
│   ├── Invoices/Show.vue                  # UPDATED
│   ├── Quotations/Show.vue                # UPDATED
│   ├── Payments/Show.vue                  # UPDATED
│   └── Settings/
│       ├── DocumentTemplates.vue          # NEW
│       └── PrintStationery.vue            # NEW
└── views/bizdocs/
    └── cms/                               # NEW
        ├── invoice.blade.php
        ├── quotation.blade.php
        └── receipt.blade.php

routes/
└── cms.php                                # UPDATED

database/migrations/
└── 2026_04_19_add_template_settings_to_cms_companies.php  # NEW
```

## Benefits

### For Companies
✅ **Professional Documents** - Branded invoices, quotations, receipts
✅ **Print Ready** - Generate bulk stationery for offline use
✅ **Customizable** - Choose templates and branding
✅ **Time Saving** - Automated PDF generation
✅ **Customer Experience** - Professional-looking documents
✅ **Optional** - Enable only if needed
✅ **Flexible** - Choose which features to use

### For Users
✅ **Easy Sharing** - Download, email, or WhatsApp PDFs
✅ **Consistent Branding** - All documents match company identity
✅ **Flexible Layouts** - Multiple template options
✅ **Offline Capability** - Print stationery for field use

### For Platform
✅ **Monetization** - Can be a premium/paid module
✅ **Differentiation** - Professional feature set
✅ **Scalability** - Easy to add/remove features
✅ **Performance** - No overhead for non-users

## Monetization Strategy (Optional)

### Free Tier
- Basic PDF generation (1 template)
- Standard branding
- Download only

### Premium Tier (Paid Module)
- Multiple professional templates
- Custom branding & colors
- Print stationery generation
- Email & WhatsApp sharing
- QR codes
- Priority support

### Industry Preset Integration

BizDocs can be **auto-enabled** for certain industries:

```php
// database/seeders/IndustryPresetsSeeder.php
[
    'name' => 'Aluminium Fabrication',
    'modules' => [
        'fabrication_module' => true,
        'bizdocs_module' => true,  // Auto-enable for professional docs
    ],
    'bizdocs_features' => [
        'pdf_generation' => true,
        'print_stationery' => true,  // Important for field work
        'email_documents' => true,
    ],
],
[
    'name' => 'Accounting & Bookkeeping',
    'modules' => [
        'bizdocs_module' => true,  // Essential for accountants
    ],
    'bizdocs_features' => [
        'pdf_generation' => true,
        'print_stationery' => true,
        'email_documents' => true,
        'qr_codes' => true,  // For payment tracking
    ],
],
```

**Industries that benefit most:**
- Accounting & Bookkeeping
- Legal Services
- Consulting
- Construction & Fabrication
- Retail & Wholesale
- Professional Services

## Next Steps

1. **Review & Approve** this integration plan
2. **Phase 1 Implementation** - Start with core PDF generation
3. **User Testing** - Test with real companies
4. **Iterate** - Refine based on feedback
5. **Phase 2-4** - Roll out additional features

---

**Questions to Consider:**

1. ~~Should PDFs be generated on-demand or stored?~~ **On-demand (decided)**
2. How many template options should we provide initially? **4-5 professional templates**
3. ~~Should stationery generation be a premium feature?~~ **Yes, part of module**
4. Do we need multi-language support for documents? **Future consideration**
5. Should we allow custom template uploads? **Phase 5 (future)**
6. **Should BizDocs be enabled by default?** **Yes, but can be disabled**
7. **Should it be a paid module?** **Optional - can monetize later**

**Estimated Timeline:** 
- Phase 0 (Module Setup): 1-2 days
- Phase 1 (Core PDF): 3-4 days
- Phase 2 (Templates): 5-7 days
- Phase 3 (Stationery): 5-7 days
- Phase 4 (Advanced): 5-7 days
- **Total: 3-4 weeks**

**Priority:** High - Professional documents are essential for business credibility

## Module Architecture Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                     CMS Company Settings                     │
├─────────────────────────────────────────────────────────────┤
│  Modules & Features                                          │
│  ┌────────────────────────────────────────────────────────┐ │
│  │ ☑ BizDocs Module                          [ENABLED]    │ │
│  │                                                          │ │
│  │ Features:                                                │ │
│  │   ☑ PDF Generation                                      │ │
│  │   ☑ Print Stationery                                    │ │
│  │   ☑ Email Documents                                     │ │
│  │   ☐ WhatsApp Sharing                                    │ │
│  │   ☐ QR Codes                                            │ │
│  └────────────────────────────────────────────────────────┘ │
│                                                               │
│  ┌────────────────────────────────────────────────────────┐ │
│  │ ☑ Fabrication Module                      [ENABLED]    │ │
│  └────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘

                            ↓

┌─────────────────────────────────────────────────────────────┐
│                    Navigation (Conditional)                  │
├─────────────────────────────────────────────────────────────┤
│  Settings                                                     │
│    ├── Company Settings                                      │
│    ├── Modules & Features                                    │
│    ├── Pricing Rules (if Fabrication enabled)               │
│    ├── Document Templates (if BizDocs enabled)              │
│    ├── Print Stationery (if BizDocs enabled)                │
│    └── Email Settings                                        │
└─────────────────────────────────────────────────────────────┘

                            ↓

┌─────────────────────────────────────────────────────────────┐
│                  Invoice/Quotation Show Page                 │
├─────────────────────────────────────────────────────────────┤
│  Actions Menu (if BizDocs enabled):                          │
│    • Download PDF (if pdf_generation enabled)                │
│    • Email Document (if email_documents enabled)             │
│    • Share WhatsApp (if whatsapp_sharing enabled)            │
│    • Print                                                    │
└─────────────────────────────────────────────────────────────┘
```

## Quick Start Guide (For Developers)

### 1. Enable Module for a Company

```php
$company = CompanyModel::find(1);
$company->setBizDocsModule(true);
```

### 2. Check if Module is Enabled

```php
if ($company->hasBizDocsModule()) {
    // Show PDF generation options
}
```

### 3. Check Specific Feature

```php
$features = $company->getBizDocsFeatures();
if ($features['print_stationery']) {
    // Show stationery generation
}
```

### 4. Generate PDF

```php
$invoice = InvoiceModel::find(1);
$pdfContent = app(InvoiceService::class)->generatePdf($invoice);
return response($pdfContent)->header('Content-Type', 'application/pdf');
```

### 5. Frontend Check

```vue
<button v-if="company.has_bizdocs_module" @click="downloadPdf">
  Download PDF
</button>
```
