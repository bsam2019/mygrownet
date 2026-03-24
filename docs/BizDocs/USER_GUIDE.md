# BizDocs User Guide

**Version:** 1.0  
**Last Updated:** March 19, 2026  
**Status:** Production Ready

---

## Table of Contents

1. [Getting Started](#getting-started)
2. [Business Profile Setup](#business-profile-setup)
3. [Managing Customers](#managing-customers)
4. [Creating Documents](#creating-documents)
5. [Document Types](#document-types)
6. [Sharing Documents](#sharing-documents)
7. [Tips & Best Practices](#tips--best-practices)
8. [Troubleshooting](#troubleshooting)

---

## Getting Started

BizDocs is a comprehensive document management system that helps you create professional invoices, receipts, quotations, and delivery notes for your business.

### First Time Setup

When you first access BizDocs, you'll be prompted to set up your business profile. This is a one-time setup that captures your business information for all future documents.

**Access BizDocs:**
- Navigate to `/bizdocs/dashboard` from your MyGrowNet account
- Or click on "BizDocs" in the main navigation menu

---

## Business Profile Setup

Your business profile appears on all documents you create. Complete this setup carefully.

### Required Information

1. **Business Name** (required)
   - Your registered business name
   - This appears prominently on all documents

2. **Business Address** (required)
   - Complete physical address
   - Include city and postal code

3. **Phone Number** (required)
   - Primary business contact number
   - Format: +260 XXX XXX XXX

### Optional Information

4. **Email Address**
   - Business email for customer correspondence

5. **TPIN (Tax ID)**
   - Your Tax Payer Identification Number
   - Required for tax compliance

6. **Website**
   - Your business website URL

7. **Bank Details** (optional but recommended)
   - Bank Name (e.g., Zanaco, FNB)
   - Account Number
   - Branch Name or Code
   - These appear on invoices for payment instructions

8. **Default Currency**
   - Choose your primary currency
   - Options: ZMW (Zambian Kwacha), USD, EUR, GBP
   - Can be changed later

### Editing Your Profile

- Access: BizDocs Dashboard → Business Profile
- You can update your profile anytime
- Changes apply to new documents only (existing documents remain unchanged)

---

## Managing Customers

Before creating documents, add your customers to the system.

### Adding a New Customer

1. Navigate to **BizDocs → Customers**
2. Click **"Add Customer"** button
3. Fill in customer details:
   - **Name** (required) - Customer or company name
   - **Phone** - Contact number
   - **Email** - Email address
   - **Address** - Physical address
   - **TPIN** - Customer's tax ID (if applicable)
   - **Notes** - Any additional information
4. Click **"Add Customer"**

### Editing a Customer

1. Go to **Customers** page
2. Click on any customer card
3. Update the information
4. Click **"Update Customer"**

### Searching Customers

- Use the search bar to find customers by:
  - Name
  - Phone number
  - Email address

### Customer Best Practices

- Add customers before creating documents
- Keep contact information up-to-date
- Use the Notes field for important details (e.g., "Prefers WhatsApp", "Net 30 payment terms")
- Include TPIN for business customers

---

## Creating Documents

BizDocs supports four document types: Invoices, Receipts, Quotations, and Delivery Notes.

### Step-by-Step: Creating an Invoice

1. **Navigate to Documents**
   - Go to **BizDocs → Documents**
   - Click **"New Document"** button
   - Or go directly to **Documents → Create**

2. **Select Document Type**
   - Choose "Invoice" from the type selector
   - (You can also create Receipt, Quotation, or Delivery Note)

3. **Select Customer**
   - Choose from your customer list
   - If customer doesn't exist, add them first

4. **Set Document Dates**
   - **Issue Date** (required) - Date the invoice is created
   - **Due Date** (optional) - Payment deadline
   - Tip: Set due date 7-30 days from issue date

5. **Add Line Items**
   - Click **"Add Item"** to add products/services
   - For each item, enter:
     - **Description** (required) - What you're selling
     - **Quantity** (required) - How many units
     - **Unit Price** (required) - Price per unit
     - **Tax Rate** (optional) - VAT percentage (e.g., 16%)
     - **Discount** (optional) - Discount amount
   - Click the trash icon to remove an item
   - Add multiple items as needed

6. **Review Totals**
   - Subtotal, tax, and grand total calculate automatically
   - Verify amounts before proceeding

7. **Add Additional Information** (optional)
   - **Notes** - Special instructions or thank you message
   - **Payment Instructions** - How customer should pay
   - **Terms & Conditions** - Your business terms

8. **Create Document**
   - Click **"Create Invoice"** button
   - Document is saved as "Draft" status

### After Creating a Document

Once created, you can:
- **View** - Preview the document
- **Finalize** - Lock the document (can't be edited after)
- **Generate PDF** - Create downloadable PDF
- **Share via WhatsApp** - Send to customer instantly
- **Download** - Save PDF to your device

---

## Document Types

### 1. Invoice

**Purpose:** Bill customers for goods or services

**When to Use:**
- After delivering products or completing services
- When payment is due

**Key Fields:**
- Issue Date (when invoice is created)
- Due Date (payment deadline)
- Line items with pricing
- Payment instructions

**Status Flow:**
- Draft → Sent → Paid (or Overdue)

**Tips:**
- Set clear due dates
- Include payment instructions
- Add your bank details in business profile
- Finalize before sending to customer

### 2. Receipt

**Purpose:** Proof of payment received

**When to Use:**
- After receiving payment from customer
- For cash transactions
- To acknowledge payment

**Key Fields:**
- Issue Date (payment date)
- Payment method (cash, mobile money, bank transfer)
- Amount received
- Reference to invoice (optional)

**Status Flow:**
- Draft → Issued

**Tips:**
- Issue immediately after receiving payment
- Keep payment method clear
- Reference the original invoice if applicable
- Receipts cannot be edited after issuing

### 3. Quotation

**Purpose:** Formal price offer to prospective customers

**When to Use:**
- Customer requests pricing
- Before starting a project
- For competitive bidding

**Key Fields:**
- Issue Date
- Validity Date (how long quote is valid)
- Line items with pricing
- Terms and conditions

**Status Flow:**
- Draft → Sent → Accepted/Rejected/Expired

**Tips:**
- Set realistic validity periods (7-30 days)
- Include detailed descriptions
- Add terms and conditions
- Can convert to invoice when accepted (Phase 2 feature)

### 4. Delivery Note

**Purpose:** Document accompanying goods during delivery

**When to Use:**
- When delivering products to customer
- For inventory tracking
- Proof of delivery

**Key Fields:**
- Delivery Date
- Delivery Address
- Items delivered (description and quantity)
- Recipient signature area

**Status Flow:**
- Draft → Sent → Delivered → Acknowledged

**Tips:**
- Print before delivery
- Get customer signature
- Keep copy for records
- Can link to invoice

---

## Sharing Documents

### Generating PDF

1. Open the document
2. Click **"Generate PDF"** button
3. Wait for generation (usually 2-5 seconds)
4. PDF is stored and ready for download/sharing

### Downloading PDF

1. Open the document
2. Click **"Download PDF"** button
3. PDF downloads to your device
4. You can print or email from your device

### Sharing via WhatsApp

**Requirements:**
- Customer must have a phone number in their profile
- PDF must be generated first

**Steps:**
1. Open the document
2. Click **"Generate PDF"** (if not already generated)
3. Click **"Share via WhatsApp"** button
4. WhatsApp opens with pre-filled message
5. Message includes:
   - Customer name
   - Document type and number
   - Total amount
   - Download link for PDF
6. Review message and send

**WhatsApp Message Format:**
```
Hello [Customer Name], please find your Invoice #INV-2026-0001 attached.
Total: ZMW 1,500.00. Thank you for your business.
[PDF Download Link]
```

**Tips:**
- Ensure customer phone number is correct
- PDF link expires after 24 hours
- Customer can download PDF from link
- Works on both WhatsApp mobile and web

---

## Tips & Best Practices

### Document Numbering

- Numbers are generated automatically
- Format: `INV-2026-0001`, `RCPT-2026-0001`, etc.
- Sequential and unique
- Cannot be changed or duplicated

### Status Management

**Draft Status:**
- Document can be edited
- Not visible to customers
- Use for work-in-progress

**Finalized Status:**
- Document is locked (cannot edit)
- Ready to send to customer
- Professional and official

**Best Practice:**
- Keep as draft while working
- Finalize only when ready to send
- Double-check all details before finalizing

### Line Items

**Description Tips:**
- Be specific and clear
- Include model numbers or specifications
- Use consistent naming

**Pricing Tips:**
- Use consistent units (per item, per hour, per kg)
- Include tax in unit price or add as separate tax rate
- Round to 2 decimal places

**Tax Handling:**
- Enter tax rate as percentage (e.g., 16 for 16% VAT)
- Tax calculates automatically
- Shows separately in totals

### Payment Instructions

**Include:**
- Accepted payment methods
- Bank account details
- Mobile money numbers
- Payment reference format

**Example:**
```
Payment Options:
1. Bank Transfer: Zanaco Account 1234567890
2. Mobile Money: MTN +260 XXX XXX XXX
3. Cash: Visit our office

Please reference Invoice #INV-2026-0001 in payment
```

### Terms & Conditions

**Recommended Terms:**
- Payment terms (Net 7, Net 30, etc.)
- Late payment penalties
- Return/refund policy
- Warranty information
- Dispute resolution

**Example:**
```
Payment Terms: Net 30 days
Late Payment: 2% per month after due date
Returns: Within 7 days with receipt
All prices in ZMW unless stated otherwise
```

---

## Troubleshooting

### Common Issues

**Issue: Can't create document**
- **Solution:** Ensure you've completed business profile setup
- **Solution:** Add at least one customer first

**Issue: PDF not generating**
- **Solution:** Check internet connection
- **Solution:** Refresh page and try again
- **Solution:** Contact support if persists

**Issue: WhatsApp share not working**
- **Solution:** Ensure customer has phone number
- **Solution:** Generate PDF first
- **Solution:** Check phone number format (+260...)

**Issue: Can't edit document**
- **Solution:** Document may be finalized (locked)
- **Solution:** Create a new document instead
- **Solution:** Contact support to void and recreate

**Issue: Wrong currency showing**
- **Solution:** Update default currency in business profile
- **Solution:** Applies to new documents only

**Issue: Customer not found**
- **Solution:** Add customer first before creating document
- **Solution:** Check spelling in search

**Issue: Totals not calculating**
- **Solution:** Ensure quantity and price are numbers
- **Solution:** Refresh page
- **Solution:** Remove and re-add line item

### Getting Help

**Support Channels:**
- Email: support@mygrownet.com
- Phone: +260 XXX XXX XXX
- In-app: Help → Contact Support

**Before Contacting Support:**
- Note the document number
- Take a screenshot of the issue
- Describe what you were trying to do
- Mention any error messages

---

## Keyboard Shortcuts

| Action | Shortcut |
|--------|----------|
| New Document | `Ctrl + N` (coming soon) |
| Save Draft | `Ctrl + S` (coming soon) |
| Search | `Ctrl + F` (coming soon) |

---

## Frequently Asked Questions

**Q: Can I delete a document?**
A: Documents are archived, not deleted, for audit purposes. Contact support if you need to void a document.

**Q: Can I customize document templates?**
A: Custom templates are coming in Phase 2. Currently, professional templates are used automatically.

**Q: How long are PDF links valid?**
A: PDF download links expire after 24 hours for security. Generate a new link if needed.

**Q: Can I send documents via email?**
A: Email sending is coming in Phase 2. Currently, download PDF and email manually, or use WhatsApp sharing.

**Q: Is my data secure?**
A: Yes. All data is encrypted, and PDFs use signed URLs. Only you and your customers can access documents.

**Q: Can I use BizDocs offline?**
A: No, BizDocs requires internet connection. Offline mode is planned for future.

**Q: How many documents can I create?**
A: Unlimited documents. No restrictions on document creation.

**Q: Can I export my data?**
A: Data export feature is coming in Phase 3. Contact support for manual export if needed.

---

## What's Coming Next

**Phase 2 Features (Coming Soon):**
- Custom template builder
- Payment tracking and recording
- Document conversions (Quotation → Invoice)
- Print stationery generator
- Email sending

**Phase 3 Features (Planned):**
- Multi-user access
- Custom fields
- Advanced analytics
- Recurring invoices
- Bulk operations

---

## Changelog

### March 19, 2026
- Initial user guide created
- Covers Phase 1 MVP features
- All core functionality documented

---

**Need more help?** Contact support@mygrownet.com or visit our help center.
