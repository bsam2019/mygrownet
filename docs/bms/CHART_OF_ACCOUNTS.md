# Chart of Accounts System

**Last Updated:** February 13, 2026  
**Status:** Complete

## Overview

Full double-entry accounting system with Chart of Accounts, journal entries, and trial balance reporting. Provides proper accounting foundation for the CMS platform.

## Features

- Standard chart of accounts (Assets, Liabilities, Equity, Income, Expenses)
- Custom account creation with validation
- Journal entry system with double-entry bookkeeping
- Automatic balance updates when entries are posted
- Trial balance reporting
- Account transaction history
- System vs custom account protection

## Database Structure

### Tables

1. **cms_accounts** - Chart of accounts
   - Account code, name, type, category
   - Current balance tracking
   - System account protection flag

2. **cms_journal_entries** - Journal entry headers
   - Entry number, date, description
   - Posted status and timestamp
   - Reference field for linking

3. **cms_journal_lines** - Journal entry lines
   - Debit and credit amounts
   - Links to accounts
   - Line-level descriptions

## Default Accounts

### Assets (1000-1999)
- 1000: Cash on Hand
- 1010: Bank Account
- 1020: Mobile Money
- 1100: Accounts Receivable
- 1200: Inventory
- 1300: Prepaid Expenses
- 1400: Fixed Assets
- 1410: Accumulated Depreciation

### Liabilities (2000-2999)
- 2000: Accounts Payable
- 2100: Accrued Expenses
- 2200: Short-term Loans
- 2300: VAT Payable
- 2310: Withholding Tax Payable
- 2400: Payroll Liabilities

### Equity (3000-3999)
- 3000: Owner's Capital
- 3100: Retained Earnings
- 3200: Owner's Drawings

### Income (4000-4999)
- 4000: Sales Revenue
- 4100: Service Revenue
- 4200: Other Income
- 4300: Interest Income

### Expenses (5000-5999)
- 5000: Cost of Goods Sold
- 5100: Salaries & Wages
- 5200: Rent Expense
- 5300: Utilities
- 5400: Transport & Fuel
- 5500: Office Supplies
- 5600: Marketing & Advertising
- 5700: Bank Charges
- 5800: Depreciation
- 5900: Professional Fees
- 5950: Miscellaneous Expenses

## Implementation

### Backend Files

**Domain Layer:**
- `app/Domain/CMS/Core/ValueObjects/AccountType.php` - Account type enum
- `app/Domain/CMS/Core/Services/ChartOfAccountsService.php` - Core accounting logic

**Infrastructure Layer:**
- `app/Infrastructure/Persistence/Eloquent/CMS/AccountModel.php`
- `app/Infrastructure/Persistence/Eloquent/CMS/JournalEntryModel.php`
- `app/Infrastructure/Persistence/Eloquent/CMS/JournalLineModel.php`

**Presentation Layer:**
- `app/Http/Controllers/CMS/AccountingController.php` - CRUD and reports

**Database:**
- `database/migrations/2026_02_13_100000_create_cms_chart_of_accounts.php`
- `database/seeders/ChartOfAccountsSeeder.php`

### Frontend Files

**Pages:**
- `resources/js/Pages/CMS/Accounting/Index.vue` - Chart of accounts list
- `resources/js/Pages/CMS/Accounting/Create.vue` - Create new account

**Routes:**
- `routes/cms.php` - Accounting routes group

## Usage

### Initialize Chart of Accounts

For new companies, the chart of accounts is automatically initialized during onboarding. For existing companies:

```bash
php artisan db:seed --class=ChartOfAccountsSeeder
```

Or via the UI:
```php
POST /cms/accounting/initialize
```

### Create Custom Account

```php
POST /cms/accounting
{
    "code": "1500",
    "name": "Equipment",
    "type": "asset",
    "category": "Fixed Assets",
    "description": "Office and production equipment",
    "opening_balance": 50000.00
}
```

### View Trial Balance

```
GET /cms/accounting/trial-balance
```

Returns:
- List of all accounts with debit/credit balances
- Total debits and credits
- Balance verification status

### Create Journal Entry

```php
$chartService->createJournalEntry(
    companyId: 1,
    description: 'Record equipment purchase',
    lines: [
        [
            'account_id' => 15, // Equipment
            'debit_amount' => 5000,
            'credit_amount' => 0,
        ],
        [
            'account_id' => 2, // Bank Account
            'debit_amount' => 0,
            'credit_amount' => 5000,
        ],
    ],
    reference: 'INV-2024-001'
);
```

### Post Journal Entry

```php
$chartService->postJournalEntry($entry);
```

This will:
1. Validate the entry is balanced
2. Update all account balances
3. Mark entry as posted
4. Set posted timestamp

## Routes

| Method | Route | Name | Description |
|--------|-------|------|-------------|
| GET | `/cms/accounting` | `cms.accounting.index` | List all accounts |
| GET | `/cms/accounting/create` | `cms.accounting.create` | Create account form |
| POST | `/cms/accounting` | `cms.accounting.store` | Store new account |
| GET | `/cms/accounting/{id}` | `cms.accounting.show` | View account details |
| PUT | `/cms/accounting/{id}` | `cms.accounting.update` | Update account |
| DELETE | `/cms/accounting/{id}` | `cms.accounting.destroy` | Delete account |
| GET | `/cms/accounting/trial-balance` | `cms.accounting.trial-balance` | Trial balance report |
| GET | `/cms/accounting/journal-entries` | `cms.accounting.journal-entries` | List journal entries |
| POST | `/cms/accounting/initialize` | `cms.accounting.initialize` | Initialize default accounts |

## Business Rules

1. **System Accounts**
   - Cannot be modified or deleted
   - Automatically created during initialization
   - Marked with `is_system = true`

2. **Account Codes**
   - Must be unique per company
   - Recommended ranges by type (1000s, 2000s, etc.)
   - No strict format enforcement

3. **Journal Entries**
   - Must be balanced (debits = credits)
   - Cannot be posted if unbalanced
   - Cannot be modified after posting

4. **Account Deletion**
   - Only custom accounts can be deleted
   - Cannot delete accounts with transactions
   - Soft delete recommended for audit trail

5. **Balance Calculation**
   - Assets and Expenses: Debit normal (increase with debits)
   - Liabilities, Equity, Income: Credit normal (increase with credits)

## Integration Points

### Invoice System
When an invoice is created and paid:
```php
// Debit: Bank Account (increase asset)
// Credit: Sales Revenue (increase income)
```

### Expense System
When an expense is recorded:
```php
// Debit: Expense Account (increase expense)
// Credit: Cash/Bank (decrease asset)
```

### Payroll System
When payroll is processed:
```php
// Debit: Salaries & Wages (increase expense)
// Credit: Bank Account (decrease asset)
// Credit: Payroll Liabilities (increase liability for taxes)
```

### Asset Depreciation
Monthly depreciation:
```php
// Debit: Depreciation Expense (increase expense)
// Credit: Accumulated Depreciation (contra-asset)
```

## Future Enhancements

- General ledger report
- Income statement generation
- Balance sheet generation
- Cash flow statement
- Budget vs actual comparison
- Multi-period comparative reports
- Account reconciliation
- Closing entries automation
- Fiscal year management

## Changelog

### February 13, 2026
- Initial implementation
- Created database migrations
- Implemented domain services
- Built UI pages
- Added navigation integration
- Created seeder for initialization
