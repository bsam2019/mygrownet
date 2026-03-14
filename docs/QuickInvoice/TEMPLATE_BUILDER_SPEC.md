# Quick Invoice Template Builder Specification

**Last Updated:** March 12, 2026
**Status:** In Development

## Overview

A comprehensive visual template builder that allows users to create fully customized invoice templates with drag-and-drop layout control, field management, and brand customization.

## Core Features

### 1. Visual Customization
- **Logo Upload**: Users can upload and position their company logo
- **Brand Colors**: Color picker for primary, secondary, and accent colors
- **Typography**: Font selection for headings and body text
- **Spacing & Sizing**: Control padding, margins, and element sizes

### 2. Layout Structure Control
- **Drag-and-Drop Builder**: Visual editor for positioning elements
- **Layout Blocks**:
  - Header block (logo, company info, invoice title)
  - Company details block
  - Customer details block
  - Invoice metadata block (number, date, due date)
  - Items table block
  - Totals block
  - Footer block (payment terms, notes, bank details)
  - Custom text blocks
  - Image blocks
  - Divider blocks

### 3. Field Management
**Required Fields:**
- Invoice number
- Date
- Customer name
- Items table (description, quantity, price)
- Total amount

**Optional Fields (Enable/Disable):**
- Company name, address, phone, email, website
- Tax number / Registration number
- Customer address, phone, email
- Due date
- Purchase order number
- Payment terms
- Bank details
- Tax breakdown
- Discount
- Shipping
- Notes section
- Terms and conditions

### 4. Drag-and-Drop Layout Builder

**Canvas:**
- Visual representation of invoice
- Grid-based layout system
- Snap-to-grid functionality
- Responsive preview (desktop, tablet, mobile)

**Component Library:**
- Text blocks
- Image blocks
- Table blocks
- Divider lines
- Spacer blocks
- Custom HTML blocks

**Actions:**
- Drag components from library to canvas
- Reorder components
- Resize components
- Delete components
- Duplicate components
- Configure component properties

### 5. Preview & Testing
- **Live Preview**: Real-time preview with sample data
- **Test Data**: Generate preview with realistic sample data
- **PDF Preview**: See how it renders in PDF format
- **Print Preview**: Check print layout
- **Responsive Preview**: View on different screen sizes

### 6. Template Management
- **Save Multiple Templates**: Unlimited custom templates
- **Set Default Template**: Choose default for new documents
- **Edit Templates**: Modify existing templates
- **Duplicate Templates**: Clone and modify templates
- **Delete Templates**: Remove unwanted templates
- **Template Categories**: Organize by type (invoice, quote, receipt)
- **Template Versioning**: Track changes and revert if needed

### 7. Export Compatibility
- **PDF Rendering**: Consistent PDF output
- **Print Optimization**: Print-friendly layouts
- **Email Templates**: HTML email versions
- **Cross-browser Compatibility**: Works in all modern browsers

### 8. Advanced Features (Future)
- **Template Marketplace**: Share/sell templates
- **Template Import/Export**: JSON format for portability
- **Conditional Fields**: Show/hide based on conditions
- **Multi-language Support**: Templates in different languages
- **Formula Fields**: Calculated fields
- **QR Code Integration**: Add QR codes for payments

## Technical Implementation

### Database Schema

```sql
-- Enhanced custom_templates table
ALTER TABLE quick_invoice_custom_templates ADD COLUMN layout_json JSON;
ALTER TABLE quick_invoice_custom_templates ADD COLUMN field_config JSON;
ALTER TABLE quick_invoice_custom_templates ADD COLUMN logo_url VARCHAR(255);
ALTER TABLE quick_invoice_custom_templates ADD COLUMN version INT DEFAULT 1;
```

**Layout JSON Structure:**
```json
{
  "version": "1.0",
  "canvas": {
    "width": "210mm",
    "height": "297mm",
    "padding": "20mm",
    "background": "#ffffff"
  },
  "blocks": [
    {
      "id": "header-1",
      "type": "header",
      "position": { "x": 0, "y": 0 },
      "size": { "width": "100%", "height": "80px" },
      "config": {
        "logo": { "enabled": true, "position": "left", "size": "60px" },
        "companyInfo": { "enabled": true, "position": "right" },
        "invoiceTitle": { "enabled": true, "position": "center" }
      }
    },
    {
      "id": "table-1",
      "type": "table",
      "position": { "x": 0, "y": 200 },
      "size": { "width": "100%", "height": "auto" },
      "config": {
        "columns": ["description", "quantity", "price", "total"],
        "style": "striped",
        "borders": true
      }
    }
  ]
}
```

**Field Config JSON:**
```json
{
  "fields": {
    "company_name": { "enabled": true, "required": true, "label": "Company Name" },
    "company_address": { "enabled": true, "required": false, "label": "Address" },
    "tax_number": { "enabled": true, "required": false, "label": "Tax ID" },
    "invoice_number": { "enabled": true, "required": true, "label": "Invoice #" },
    "po_number": { "enabled": false, "required": false, "label": "PO Number" },
    "payment_terms": { "enabled": true, "required": false, "label": "Payment Terms" },
    "bank_details": { "enabled": false, "required": false, "label": "Bank Details" }
  }
}
```

### Frontend Architecture

**Components:**
- `TemplateBuilder.vue` - Main builder interface
- `BuilderCanvas.vue` - Drag-and-drop canvas
- `ComponentLibrary.vue` - Available blocks
- `PropertyPanel.vue` - Block configuration
- `PreviewPanel.vue` - Live preview
- `FieldManager.vue` - Field enable/disable
- `BrandingPanel.vue` - Logo, colors, fonts

**State Management:**
- Use Pinia or Vuex for template state
- Undo/redo functionality
- Auto-save drafts

**Drag-and-Drop:**
- Use Vue Draggable or VueDraggableNext
- Grid system for alignment
- Snap-to-grid functionality

### Backend Architecture

**Controllers:**
- `TemplateBuilderController` - CRUD operations
- `TemplatePreviewController` - Generate previews
- `TemplateExportController` - Export to PDF/JSON

**Services:**
- `TemplateRenderService` - Render templates to HTML/PDF
- `TemplateValidationService` - Validate template structure
- `TemplateVersioningService` - Track versions

## Implementation Phases

### Phase 1: Foundation (Current)
- ✅ Basic template CRUD
- ✅ Simple customization (colors, fonts)
- ⏳ Database schema for layout JSON

### Phase 2: Visual Builder
- Drag-and-drop canvas
- Component library
- Property panel
- Live preview

### Phase 3: Field Management
- Field enable/disable
- Custom field labels
- Required/optional fields
- Field validation

### Phase 4: Advanced Features
- Template versioning
- Import/export
- PDF rendering engine
- Print optimization

### Phase 5: Marketplace (Future)
- Template sharing
- Template marketplace
- Community templates
- Template ratings

## User Workflow

1. **Create New Template**
   - Click "Create Template" in Design Studio
   - Choose starting point (blank or system template)
   - Enter template name and description

2. **Design Layout**
   - Drag blocks from component library to canvas
   - Position and resize blocks
   - Configure block properties
   - Add/remove fields

3. **Customize Branding**
   - Upload logo
   - Set brand colors
   - Choose fonts
   - Adjust spacing

4. **Preview & Test**
   - View live preview with sample data
   - Test PDF export
   - Check print layout
   - Verify responsive design

5. **Save & Use**
   - Save template
   - Set as default (optional)
   - Use in document creation
   - Edit anytime

## Success Metrics

- Number of custom templates created
- Template usage frequency
- User satisfaction with customization options
- PDF export quality
- Template creation time
- Template marketplace engagement (future)

## Next Steps

1. Update database schema for layout JSON
2. Build drag-and-drop canvas component
3. Create component library
4. Implement property panel
5. Add live preview functionality
6. Build field management interface
7. Integrate with document creation flow
8. Add PDF rendering engine
