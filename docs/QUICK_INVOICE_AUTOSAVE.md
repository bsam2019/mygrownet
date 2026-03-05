# Quick Invoice Auto-Save Feature

**Last Updated:** March 5, 2026
**Status:** Production

## Changelog

### March 5, 2026
- **UI REFACTORING:** Simplified business info section on create page
  - Removed logo and signature upload controls from create page
  - Now shows small preview thumbnails only (non-editable)
  - Added helpful prompt to set up profile for new users
  - "Change" button links to profile settings for quick edits
  - Cleaner, more focused interface for document creation
  - Logo and signature management moved exclusively to profile settings
- **NEW:** Document Numbering System with auto-increment functionality
  - Configure numbering format for each document type (Invoice, Quotation, Receipt, Delivery Note)
  - Set custom prefix (e.g., "INV", "QUO"), starting number, and padding
  - System auto-generates sequential numbers (e.g., INV-0001, INV-0002)
  - Preview shows what next document number will look like
  - Auto-increment happens when document is generated
  - Numbering settings saved in profile (4 tabs: Business Info, Default Settings, Numbering, Attachment Library)
- **REMOVED:** Style & Template section from create page
  - Template and color preferences moved to profile settings
  - Cleaner, more focused document creation interface
- **NEW:** Profile Settings now includes Attachment Library management tab
- Users can manage their reusable attachments directly from Edit Profile modal
- Four organized tabs: Business Info, Default Settings, Numbering, and Attachment Library
- Upload, view, and delete library attachments without creating a document
- Improved UX - centralized management of all profile-related settings
- "Prepared By" field moved to Business Info tab (saved with profile)
- **NEW:** Reusable Attachment Library for authenticated users
- Users can save frequently-used attachments (certificates, T&Cs, etc.) to their library
- Select from library with checkboxes instead of re-uploading
- Manage library: add, delete, and organize saved attachments
- **OPTIMIZED:** Library files stored once and referenced (not duplicated)
- Documents store references to library files, not copies
- Saves storage space and reduces S3 costs
- Fixed image attachment processing - explicitly specify image type (PNG/JPG) to FPDF
- Resolved "Unsupported image type: tmp" error by detecting file extension
- Images now successfully merge into final PDF documents

### March 4, 2026
- Updated attachment storage to use S3 (DigitalOcean Spaces) instead of local disk
- Added temporary file handling for S3 retrieval during PDF merging
- Improved error handling for S3 attachment loading
- Initial implementation of auto-save feature
- Added document attachments feature

## Overview

The Quick Invoice generator now includes automatic draft saving to prevent data loss. Form data is automatically saved to browser localStorage as users type, with a restore prompt appearing when they return to an incomplete form.

## Implementation

### Files Modified

1. **resources/js/composables/useAutoSave.ts** (NEW)
   - Reusable composable for auto-save functionality
   - Debounced saving (3 seconds after changes stop)
   - 24-hour data retention
   - Configurable exclude fields

2. **resources/js/pages/QuickInvoice/Create.vue**
   - Integrated auto-save composable
   - Restore prompt UI
   - Auto-saving indicator in header
   - Clears saved data on successful generation

## Features

### Auto-Save Behavior
- Saves form data every 3 seconds after user stops typing
- Stores data in browser localStorage (client-side only)
- Excludes uploaded files (logo, signature) from auto-save
- Data expires after 24 hours
- Unique storage key per document type and edit mode

### Restore Prompt
- Appears when saved data is found (not in edit mode)
- Shows last save timestamp
- Two options:
  - "Restore Draft" - loads saved data
  - "Start Fresh" - discards saved data

### Auto-Saving Indicator
- Subtle "Auto-saving" text with clock icon in header
- Always visible to reassure users their work is being saved

### Data Cleared
- Automatically cleared on successful document generation
- Manually cleared when user clicks "Start Fresh"
- Automatically cleared if data is older than 24 hours

## Saved Fields

The following fields are auto-saved:
- Template selection and brand color
- Business information (name, address, phone, email, tax number)
- Prepared by name
- Client information (name, address, phone, email)
- Document details (number, dates, currency)
- Tax and discount rates
- Line items (description, quantity, unit, price, area calculations)
- Notes and terms

**Excluded:** Logo and signature uploads (too large for localStorage)

## Technical Details

### Storage Keys
- Format: `autosave_invoice_{documentType}_{documentId}`
- Timestamp: `autosave_invoice_{documentType}_{documentId}_timestamp`

### Debounce
- 3-second delay after last change before saving
- Prevents excessive localStorage writes
- Cancelled on component unmount

### Data Retention
- Maximum: 24 hours
- Checked on restore attempt
- Old data automatically cleared

## User Experience

1. User starts filling invoice form
2. After 3 seconds of inactivity, data is saved
3. "Auto-saving" indicator visible in header
4. User closes browser/tab
5. User returns to form
6. Restore prompt appears with timestamp
7. User chooses to restore or start fresh
8. On successful generation, saved data is cleared

## Browser Compatibility

Works in all modern browsers that support:
- localStorage API
- ES6+ JavaScript features
- Vue 3 Composition API

## Limitations

- Client-side only (not synced across devices)
- Limited to ~5-10MB localStorage per domain
- Uploaded files not saved (would exceed storage limits)
- Only works when not editing existing documents

## Future Enhancements

Potential improvements:
- Server-side draft saving for cross-device sync
- Periodic background saves (every 30 seconds)
- Multiple draft management
- Draft list view
- Auto-save for other forms (quotations, receipts, etc.)

---

# Document Numbering System

**Added:** March 5, 2026
**Status:** Production

## Overview

The Quick Invoice system now includes automatic document numbering with sequential auto-increment functionality. Users can configure custom numbering formats for each document type (Invoice, Quotation, Receipt, Delivery Note) and the system automatically generates sequential numbers.

## Features

### Per-Document-Type Configuration

Each document type has independent numbering settings:
- **Invoice** - Default: INV-0001, INV-0002, etc.
- **Quotation** - Default: QUO-0001, QUO-0002, etc.
- **Receipt** - Default: REC-0001, REC-0002, etc.
- **Delivery Note** - Default: DN-0001, DN-0002, etc.

### Configurable Components

For each document type, users can customize:

1. **Prefix** - Text before the number (e.g., "INV", "QUO", "2026-INV")
2. **Next Number** - The next sequential number to use (e.g., 1, 100, 1000)
3. **Padding** - Number of digits with leading zeros (e.g., 4 = "0001", 5 = "00001")

### Live Preview

The settings interface shows a real-time preview of what the next document number will look like:
- Example: `INV-0001` (prefix: "INV", next: 1, padding: 4)
- Example: `2026-QUO-00123` (prefix: "2026-QUO", next: 123, padding: 5)

## Implementation

### Database Schema

Added to `quick_invoice_profiles` table:

```sql
-- Invoice numbering
invoice_prefix VARCHAR(20) DEFAULT 'INV'
invoice_next_number INT DEFAULT 1
invoice_number_padding INT DEFAULT 4

-- Quotation numbering
quotation_prefix VARCHAR(20) DEFAULT 'QUO'
quotation_next_number INT DEFAULT 1
quotation_number_padding INT DEFAULT 4

-- Receipt numbering
receipt_prefix VARCHAR(20) DEFAULT 'REC'
receipt_next_number INT DEFAULT 1
receipt_number_padding INT DEFAULT 4

-- Delivery Note numbering
delivery_note_prefix VARCHAR(20) DEFAULT 'DN'
delivery_note_next_number INT DEFAULT 1
delivery_note_number_padding INT DEFAULT 4
```

### Model Methods

**QuickInvoiceProfile.php** includes two key methods:

```php
// Generate document number based on current settings
public function generateDocumentNumber(string $type): string
{
    $prefix = $this->{$type . '_prefix'} ?? strtoupper(substr($type, 0, 3));
    $nextNumber = $this->{$type . '_next_number'} ?? 1;
    $padding = $this->{$type . '_number_padding'} ?? 4;
    
    return $prefix . '-' . str_pad($nextNumber, $padding, '0', STR_PAD_LEFT);
}

// Increment counter after document generation
public function incrementDocumentNumber(string $type): void
{
    $field = $type . '_next_number';
    $this->increment($field);
}
```

### Auto-Generation Logic

In **QuickInvoiceController.php** `generate()` method:

```php
// Auto-generate document number if not provided and user is authenticated
if (empty($data['document_number']) && auth()->check()) {
    $profile = QuickInvoiceProfile::where('user_id', auth()->id())->first();
    
    if ($profile) {
        // Map document types to profile fields
        $typeMap = [
            'invoice' => 'invoice',
            'quotation' => 'quotation',
            'receipt' => 'receipt',
            'delivery_note' => 'delivery_note',
        ];
        
        $documentType = $typeMap[$data['document_type']] ?? 'invoice';
        
        // Generate document number
        $data['document_number'] = $profile->generateDocumentNumber($documentType);
        
        // Increment the counter for next time
        $profile->incrementDocumentNumber($documentType);
    }
}
```

### Files Modified

1. **database/migrations/2026_03_05_074830_add_numbering_settings_to_quick_invoice_profiles_table.php** (NEW)
   - Added 12 new columns for numbering settings (3 per document type)

2. **app/Models/QuickInvoiceProfile.php**
   - Added numbering fields to `$fillable` array
   - Added `$casts` for integer fields
   - Implemented `generateDocumentNumber()` method
   - Implemented `incrementDocumentNumber()` method

3. **app/Http/Controllers/QuickInvoiceController.php**
   - Updated `saveProfile()` to validate and save numbering settings
   - Updated `generate()` to auto-generate document numbers
   - Auto-increment logic for sequential numbering

4. **resources/js/pages/QuickInvoice/Create.vue**
   - Removed "Style & Template" section from create page
   - Added numbering reactive variables (prefix, next_number, padding for each type)
   - Updated `SavedProfile` interface with numbering fields
   - Updated `loadSavedProfile()` to load numbering settings
   - Updated `saveBusinessProfile()` to save numbering settings
   - Added "Numbering" tab to Edit Profile modal with UI for all 4 document types
   - Live preview of generated numbers using computed values

## User Interface

### Profile Settings Modal

The Edit Profile modal now has 4 tabs:
1. **Business Info** - Company details, logo, signature, prepared by
2. **Default Settings** - Default tax rate, discount rate, notes, terms
3. **Numbering** - Document numbering configuration (NEW)
4. **Attachment Library** - Manage reusable attachments

### Numbering Tab Layout

Each document type has its own section with:
- **Prefix input** - Text field for custom prefix
- **Next Number input** - Number field for starting/current number
- **Padding input** - Number field (1-10) for digit padding
- **Live Preview** - Shows what the next document number will look like

Example UI for Invoice:
```
Invoice Numbering
┌─────────────────────────────────────────┐
│ Prefix: [INV    ]                       │
│ Next Number: [1    ]                    │
│ Padding: [4    ]                        │
│                                         │
│ Preview: INV-0001                       │
└─────────────────────────────────────────┘
```

### How It Works

1. **First Time Setup**
   - User opens Edit Profile modal
   - Switches to "Numbering" tab
   - Configures prefix, starting number, and padding for each document type
   - Clicks "Save Changes"

2. **Creating Documents**
   - User creates a new invoice/quotation/receipt/delivery note
   - Leaves "Document #" field empty (or enters custom number)
   - System auto-generates number based on profile settings
   - Counter automatically increments for next document

3. **Manual Override**
   - User can still manually enter document numbers
   - Manual numbers don't affect auto-increment counter
   - Useful for importing existing document sequences

## Usage Examples

### Example 1: Standard Sequential Numbering
```
Settings:
- Prefix: "INV"
- Next Number: 1
- Padding: 4

Generated Numbers:
INV-0001, INV-0002, INV-0003, ...
```

### Example 2: Year-Based Numbering
```
Settings:
- Prefix: "2026-INV"
- Next Number: 1
- Padding: 5

Generated Numbers:
2026-INV-00001, 2026-INV-00002, 2026-INV-00003, ...
```

### Example 3: Department-Based Numbering
```
Settings:
- Prefix: "SALES"
- Next Number: 100
- Padding: 3

Generated Numbers:
SALES-100, SALES-101, SALES-102, ...
```

### Example 4: Minimal Padding
```
Settings:
- Prefix: "Q"
- Next Number: 1
- Padding: 2

Generated Numbers:
Q-01, Q-02, Q-03, ..., Q-99, Q-100 (auto-expands)
```

## Benefits

1. **Consistency** - All documents follow the same numbering pattern
2. **Automation** - No need to remember the last number used
3. **Flexibility** - Customize format per document type
4. **Professional** - Clean, sequential numbering for business documents
5. **Time-Saving** - One less field to fill when creating documents
6. **Audit Trail** - Sequential numbers help track document history

## Important Notes

### Auto-Increment Behavior
- Counter increments ONLY when document is successfully generated
- Failed generations don't increment the counter
- Editing existing documents doesn't increment the counter
- Manual document numbers don't affect the counter

### Manual Override
- Users can still manually enter document numbers
- Leave field empty for auto-generation
- Manual numbers useful for:
  - Importing existing document sequences
  - Special numbering requirements
  - One-off custom numbers

### Resetting Numbering
- Users can manually change "Next Number" to reset sequence
- Example: Start new year with "Next Number: 1"
- No automatic reset functionality (by design)

### Multiple Users
- Each user has their own numbering sequence
- Sequences are independent per user
- No conflicts between users

## Troubleshooting

### Numbers not auto-generating
- Ensure you're logged in (feature requires authentication)
- Check that profile settings are saved
- Verify "Document #" field is left empty
- Check browser console for errors

### Numbers skipping
- This is normal if document generation failed
- Counter increments on successful generation only
- Check that documents are being saved successfully

### Want to restart numbering
- Open Edit Profile → Numbering tab
- Change "Next Number" to desired starting number
- Example: Set to 1 to restart from beginning

### Different format needed
- Adjust prefix to include year, department, etc.
- Example: "2026-SALES-INV" for year + department + type
- Padding can be 1-10 digits

## Testing

To test the numbering system:

1. **Setup**
   - Log in to your account
   - Click "Edit Profile" button
   - Switch to "Numbering" tab
   - Configure settings for invoice (e.g., prefix: "TEST", next: 1, padding: 3)
   - Save changes

2. **Generate Documents**
   - Create a new invoice
   - Leave "Invoice #" field empty
   - Fill other required fields
   - Generate document
   - Verify document number is "TEST-001"

3. **Verify Auto-Increment**
   - Create another invoice
   - Leave "Invoice #" field empty
   - Generate document
   - Verify document number is "TEST-002"

4. **Test Manual Override**
   - Create another invoice
   - Manually enter "CUSTOM-999"
   - Generate document
   - Create another invoice with empty number field
   - Verify it generates "TEST-003" (not affected by manual number)

5. **Test Preview**
   - Open Edit Profile → Numbering tab
   - Change prefix to "2026-INV"
   - Change next number to 50
   - Change padding to 5
   - Verify preview shows "2026-INV-00050"

## Deployment

Deployed to production on March 4, 2026 via:
```bash
bash deployment/deploy-with-assets.sh
```

## Testing

To test the feature:
1. Navigate to Quick Invoice generator
2. Fill in some form fields
3. Wait 3 seconds (watch for auto-save indicator)
4. Close the browser tab
5. Return to the form
6. Verify restore prompt appears
7. Click "Restore Draft" to verify data loads correctly
8. Generate document and verify saved data is cleared

## Changelog

### March 4, 2026
- Initial implementation of auto-save feature
- Created reusable useAutoSave composable
- Added restore prompt UI
- Added auto-saving indicator
- Deployed to production

---

# Document Attachments Feature

**Added:** March 4, 2026
**Status:** Production - Fully Working

## Overview

Users can now attach supporting documents (PDFs, images) to invoices and quotations. Attachments are automatically merged into the final PDF document, creating a single comprehensive file.

**Latest Update (March 5, 2026):** Image attachments now work correctly. The issue with "Unsupported image type" has been resolved by explicitly specifying the image format (PNG/JPG) to FPDF based on file extension.

**New Feature (March 5, 2026):** Reusable Attachment Library - Authenticated users can now save frequently-used attachments to their personal library and reuse them across multiple documents without re-uploading.

## Reusable Attachment Library

**For Authenticated Users Only**

The attachment library allows users to save commonly-used documents (company certificates, terms & conditions, product specifications, etc.) and reuse them across multiple invoices and quotations.

### Accessing the Library

**Two Ways to Access:**
1. **From Document Creation**: Click "From Library" button in the attachments section
2. **From Profile Settings**: Click "Edit Profile" → "Attachment Library" tab (recommended for management)

### Key Features

1. **Save to Library** - Save any uploaded attachment to your personal library with a custom name and description
2. **Select from Library** - Browse your saved attachments and select multiple files with checkboxes
3. **Manage Library** - View, organize, and delete saved attachments
4. **No Re-uploading** - Reuse the same files across unlimited documents

### How It Works

**Method 1: Upload Directly to Library (Recommended)**
1. Open Edit Profile modal (click "Edit Profile" button)
2. Switch to "Attachment Library" tab
3. Click "Upload to Library" button
4. Select a file from your computer
5. File is automatically saved to your library
6. File appears in your library list immediately

**Method 2: Upload from Library Modal During Document Creation**
1. Click "From Library" button in the attachments section
2. In the library modal, click "Upload to Library" button at the top
3. Select a file from your computer
4. File is automatically saved to your library with its filename
5. File appears in your library list immediately

**Method 3: Save from Document Attachments**
1. Upload a file as normal in the attachments section
2. Click the bookmark icon next to the uploaded file
3. Give it a memorable name (e.g., "Company Certificate 2026")
4. Add an optional description
5. Click "Save to Library"

**Using Library Attachments:**
1. When creating a document, click "From Library" button in the attachments section
2. Browse your saved attachments
3. Check the boxes next to files you want to include
4. Click "Add Selected"
5. Selected files are added to your current document

**Managing Library from Profile Settings:**
1. Click "Edit Profile" button (top right of Business Info section)
2. Switch to "Attachment Library" tab
3. View all saved attachments with names, sizes, and descriptions
4. Upload new files directly to library
5. Delete attachments you no longer need (trash icon)
6. Library is personal - only you can see your saved attachments

### Storage

**Optimized Storage - No Duplication:**
- Library attachments stored ONCE in S3: `quick-invoice/library/{user_id}/`
- Documents store REFERENCES to library attachments (not copies)
- Uploaded files (non-library) stored in: `quick-invoice/attachments/{session_id}/`
- Saves storage space and costs
- Single source of truth for library files

**Document Attachment Structure:**
```json
{
  "attachments": [
    {
      "name": "invoice.pdf",
      "path": "quick-invoice/attachments/abc123/file.pdf",
      "size": 245760,
      "type": "application/pdf",
      "source": "upload"
    },
    {
      "name": "Company Certificate.pdf",
      "path": "quick-invoice/library/1/cert.pdf",
      "size": 512000,
      "type": "application/pdf",
      "source": "library",
      "library_id": 5
    }
  ]
}
```

**Benefits:**
- Library files stored once, referenced many times
- Deleting a document doesn't delete library files
- Updating library file updates all documents using it
- Efficient storage usage

### Database Schema

```sql
CREATE TABLE quick_invoice_attachment_library (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    path VARCHAR(255) NOT NULL,
    type VARCHAR(255) NOT NULL,
    size INT NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX(user_id)
);
```

### API Endpoints

- `GET /quick-invoice/attachment-library` - Get user's library
- `GET /quick-invoice/attachment-library/{id}/download` - Download attachment
- `POST /quick-invoice/attachment-library` - Save to library
- `PUT /quick-invoice/attachment-library/{id}` - Update attachment details
- `DELETE /quick-invoice/attachment-library/{id}` - Delete from library

### Use Cases

1. **Company Documents** - Save company certificate, registration docs once, use everywhere
2. **Terms & Conditions** - Attach standard T&Cs to all quotations
3. **Product Specs** - Reuse product specification sheets across multiple quotes
4. **Certifications** - Include quality certifications, ISO certificates
5. **Branding Materials** - Company brochures, catalogs

### Benefits

- **Time Saving** - No need to find and upload the same files repeatedly
- **Consistency** - Ensure the same version of documents is used
- **Organization** - Keep frequently-used files organized in one place
- **Efficiency** - Faster document generation workflow

## Implementation

### Files Modified/Created

1. **app/Domain/QuickInvoice/Services/PdfMergerService.php** (NEW)
   - PDF merging logic using FPDI library
   - Handles both PDF and image attachments
   - Scales images to fit A4 pages
   - Error handling for corrupted files

2. **resources/js/pages/QuickInvoice/Create.vue**
   - Attachments UI section
   - File upload handling
   - File validation (type, size, count)
   - Attachment list display

3. **app/Http/Controllers/QuickInvoiceController.php**
   - Attachment storage to S3 (DigitalOcean Spaces)
   - PDF merging on download/view
   - Metadata storage with document
   - Temporary file handling for S3 retrieval

4. **app/Http/Requests/QuickInvoice/CreateDocumentRequest.php**
   - Attachment validation rules
   - FormData handling

### Dependencies
- **setasign/fpdi** (v2.6.4) - PDF manipulation library

## Features

### Supported File Types
- **PDF files** (.pdf) - Merged as additional pages
- **Images** (.jpg, .jpeg, .png) - Converted to PDF pages

### Limitations
- Maximum 5 attachments per document
- Maximum 5MB per file
- Supported formats only: PDF, JPG, JPEG, PNG

### User Interface

**Upload Section:**
- Located after "Terms & Conditions" section
- Drag-and-drop style upload area
- Clear file type and size restrictions
- Visual feedback for uploaded files

**Attachment List:**
- File name with truncation for long names
- File size in human-readable format (KB/MB)
- Type-specific icons (red for PDF, blue for images)
- Remove button for each attachment

### Storage Structure
```
storage/app/private/
└── invoice-attachments/
    └── {session_id}/
        ├── abc123.pdf
        ├── def456.jpg
        └── ghi789.png
```

### Metadata Storage
Attachments metadata stored in document JSON:
```json
{
  "attachments": [
    {
      "name": "specifications.pdf",
      "path": "invoice-attachments/session123/abc123.pdf",
      "size": 245760,
      "type": "application/pdf"
    }
  ]
}
```

## PDF Merging Process

1. Generate main invoice/quotation PDF using DomPDF
2. If attachments exist:
   - Load attachment files from storage
   - Create FPDI instance
   - Import all pages from main PDF
   - For each attachment:
     - **PDF**: Import all pages and add to document
     - **Image**: Scale to fit A4, center on page, add filename label
   - Return merged PDF as single file
3. If no attachments, return main PDF only

### Image Handling
- Images scaled to fit A4 page (210mm x 297mm)
- Maintains aspect ratio
- Centered on page
- Filename label added at bottom
- 300 DPI recommended for best quality

### Error Handling
- Corrupted PDFs: Error page added instead
- Invalid images: Error page added instead
- Missing files: Skipped silently
- All errors logged for debugging

## File Naming Convention
- **Without attachments:** `{document_number}.pdf`
- **With attachments:** `{document_number}_with_attachments.pdf`

## Usage

### For Users

1. Create invoice/quotation as normal
2. Scroll to "Attachments" section
3. Click "Add Attachments" button
4. Select up to 5 files (PDF, JPG, PNG under 5MB each)
5. Review uploaded files in the list
6. Remove any unwanted files using trash icon
7. Generate document
8. Download/view - attachments will be merged into final PDF

### For Developers

**Using PdfMergerService in other controllers:**

```php
use App\Domain\QuickInvoice\Services\PdfMergerService;

public function __construct(
    private readonly PdfMergerService $pdfMerger
) {}

public function generatePdf(Request $request)
{
    // Generate main PDF
    $mainPdf = $this->generateMainDocument($data);
    
    // Merge with attachments if present
    if ($request->hasFile('attachments')) {
        $attachments = $request->file('attachments');
        $mergedPdf = $this->pdfMerger->mergeWithAttachments(
            $mainPdf, 
            $attachments
        );
        
        return response($mergedPdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="document.pdf"');
    }
    
    return response($mainPdf)
        ->header('Content-Type', 'application/pdf');
}
```

## Troubleshooting

### "File too large" error
- Each file must be under 5MB
- Compress images before uploading
- Split large PDFs into smaller files

### "Maximum 5 attachments" error
- Remove unnecessary attachments
- Combine multiple images into a single PDF

### Attachment not in final PDF
- Check file is not corrupted
- Verify file type is supported
- Check server logs for processing errors

### Image quality poor
- Use higher resolution images (300 DPI recommended)
- Images are scaled to fit A4 pages
- Consider using PDF format for better quality

### PDF merge failed
- Ensure FPDI package is installed
- Check attachment file is valid PDF
- Review server logs: `storage/logs/laravel.log`

## Testing

To test the feature:

1. Navigate to Quick Invoice generator
2. Fill in required fields
3. Scroll to "Attachments" section
4. Upload a PDF file
5. Upload an image file (JPG/PNG)
6. Verify both appear in attachment list
7. Try uploading a 6th file (should show error)
8. Try uploading a file over 5MB (should show error)
9. Remove one attachment
10. Generate document
11. Download PDF and verify:
    - Main invoice pages present
    - PDF attachment pages present
    - Image attachment page present with filename label

## Performance Considerations

- PDF merging adds 1-3 seconds to generation time
- Larger attachments take longer to process
- Server timeout set to 120 seconds for PDF generation
- Consider async processing for very large documents

## Security

- Attachments stored in `storage/app/private` (not web-accessible)
- File type validation on upload
- File size limits enforced
- Session-based storage prevents cross-user access
- Files cleaned up when document is deleted

## Changelog - Attachments

### March 5, 2026
- **NEW FEATURE:** Reusable Attachment Library
  - Save frequently-used attachments to personal library
  - Select from library with checkboxes (no re-uploading needed)
  - Manage library: add descriptions, delete unused attachments
  - Perfect for company certificates, terms & conditions, specifications
- **FIXED:** Image attachment processing now works correctly
- Added explicit image type specification (PNG/JPG) based on file extension
- Resolved "Unsupported image type: tmp" error
- Both PDF and image attachments now merge successfully

### March 4, 2026
- Initial implementation of attachments feature
- Created PdfMergerService with FPDI
- Added attachment UI to Create.vue
- Implemented file validation and storage
- PDF and image merging support
- Deployed to production
