# Document Numbering System

**Last Updated:** March 23, 2026  
**Status:** Production

## Overview

The Document Numbering System allows users to configure custom numbering formats for each document type in BizDocs. Each business can set their own prefix, padding, and sequence for invoices, receipts, quotations, and other document types.

## Features

- **Per Document Type Configuration**: Each of the 8 document types has independent numbering
- **Custom Prefix**: Set a unique prefix (e.g., "INV", "REC", "QUO")
- **Adjustable Padding**: Choose 1-6 digits with leading zeros
- **Year-based Sequences**: Automatic year inclusion in document numbers
- **Sequence Reset**: Optional ability to reset the counter
- **Live Preview**: See the next document number before saving

## Document Number Format

```
PREFIX + YEAR + PADDED_NUMBER
```

**Examples:**
- `INV202400001` = INV (prefix) + 2024 (year) + 00001 (5-digit padding)
- `REC2024001` = REC (prefix) + 2024 (year) + 001 (3-digit padding)
- `QUO20241` = QUO (prefix) + 2024 (year) + 1 (1-digit padding)

## Supported Document Types

1. Invoice
2. Receipt
3. Quotation
4. Delivery Note
5. Proforma Invoice
6. Credit Note
7. Debit Note
8. Purchase Order

## Implementation

### Database

**Table:** `bizdocs_document_sequences`

```sql
- id (primary key)
- business_id (foreign key to bizdocs_business_profiles)
- document_type (string: invoice, receipt, etc.)
- year (integer: current year)
- last_number (integer: last used number)
- prefix (string: custom prefix, max 50 chars)
- padding (integer: number of digits, 1-10)
- timestamps
- unique constraint on (business_id, document_type, year)
```

### Backend Files

1. **Controller:** `app/Http/Controllers/BizDocs/SettingsController.php`
   - `numbering()` - Display settings page
   - `updateNumbering()` - Save settings

2. **Service:** `app/Domain/BizDocs/DocumentManagement/Services/DocumentNumberingService.php`
   - `generateNextNumber()` - Generate next document number
   - `getLastNumber()` - Get current sequence
   - `resetSequence()` - Reset counter

3. **Model:** `app/Infrastructure/BizDocs/Persistence/Eloquent/DocumentSequenceModel.php`

### Frontend Files

1. **Settings Page:** `resources/js/pages/BizDocs/Settings/Numbering.vue`
   - Grid layout with 8 document type cards
   - Individual forms for each type
   - Live preview of next number
   - Validation and error handling

### Routes

```php
GET  /bizdocs/settings/numbering        - Display settings page
POST /bizdocs/settings/numbering        - Update settings
```

## Usage

### Accessing Settings

1. Navigate to BizDocs Dashboard
2. Click "Settings" in Quick Actions
3. Configure each document type individually

### Configuring a Document Type

1. **Set Prefix**: Enter a short code (e.g., "INV", "REC")
2. **Choose Padding**: Select number of digits (1-6)
3. **Preview**: See the next document number
4. **Optional Reset**: Enter a number to reset the sequence
5. **Save**: Click "Save Settings"

### How It Works

When creating a new document:
1. System checks for existing sequence for that document type and year
2. If none exists, creates new sequence starting at 1
3. Increments the last_number by 1
4. Generates document number using: `prefix + year + padded_number`
5. Saves the incremented number back to database

### Year Rollover

- Sequences are year-specific
- On January 1st, new sequences automatically start at 1
- Previous years' sequences are preserved

## Database Persistence

**Yes, all settings are saved in the database.**

The `bizdocs_document_sequences` table stores:
- Business-specific configurations
- Current sequence numbers
- Custom prefixes and padding
- Year-based tracking

Settings persist across sessions and are loaded when:
- Viewing the settings page
- Generating new documents
- Previewing document numbers

## Security

- Settings are business-specific (isolated by business_id)
- Only authenticated users can access settings
- Database transactions prevent race conditions
- Unique constraint prevents duplicate sequences

## Troubleshooting

### Settings Not Saving
- Check browser console for errors
- Verify business profile exists
- Ensure database connection is active

### Wrong Document Numbers
- Check the sequence in database
- Verify year is correct
- Use reset function if needed

### Duplicate Numbers
- System uses database locking to prevent duplicates
- If duplicates occur, check for concurrent requests

## Future Enhancements

- Custom format patterns (e.g., PREFIX-YEAR-NUMBER)
- Multiple prefix options per document type
- Automatic prefix suggestions
- Bulk reset for all document types
- Export/import settings

## Changelog

### March 23, 2026
- Initial implementation
- Added settings controller and page
- Integrated with existing DocumentNumberingService
- Added settings link to dashboard
- Created documentation
