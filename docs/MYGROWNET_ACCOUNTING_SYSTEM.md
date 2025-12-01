# MyGrowNet Accounting System

**Last Updated:** November 30, 2025
**Status:** Development Ready
**Version:** 2.0

## Overview

The MyGrowNet Accounting System is a lightweight but powerful accounting module that integrates into the MyGrowNet ecosystem. The system supports SMEs and medium-sized businesses with essential financial features, easy reporting, and a modern, mobile-friendly interface.

This module runs inside the MyGrowNet PWA and follows the existing Laravel + Vue stack, providing basic to intermediate finance needs while being very easy to use for non-accountants.

## Problem Statement

### Current Challenges for Zambian SMEs

- **Poor income tracking** - No clear record of money coming in
- **Profitability uncertainty** - Difficulty determining if the business is making profit
- **Lost records** - Financial data scattered across notebooks or lost entirely
- **Debt management** - No systematic way to track who owes the business or what suppliers are owed
- **Decision-making gaps** - Lack of financial summaries to support business decisions or loan applications
- **Compliance issues** - No formal records for tax purposes or regulatory requirements

These gaps lead to unnecessary losses, missed opportunities, and stunted business growth.

## Purpose & Value Proposition

The MyGrowNet Accounting System empowers SMEs to:

1. **Record all financial activities** - Centralized, organized financial data
2. **Organize sales and expenses** - Categorized tracking for clarity
3. **Monitor debts** - Track debtors and creditors systematically
4. **Gain financial clarity** - Quick view of business health
5. **Make informed decisions** - Data-driven business management
6. **Support compliance** - Formal records for taxes and loans

## Core Objectives

1. Provide an accounting tool that covers basic to intermediate finance needs
2. Integrate seamlessly into the MyGrowNet Home Hub as a separate module
3. Support user permissions (owner, accountant, cashier, viewer)
4. Be very easy to use for non-accountants
5. Allow future expansion (inventory, payroll, POS, tax filings)

## Core Features (Phase 1)

### A. Dashboard

**Overview:**
- Income, expenses, profit/loss summary
- Simple charts for cash flow
- Outstanding invoices
- Quick shortcuts (Add Sale, Add Expense)

**User Benefit:** At-a-glance financial health monitoring

### B. Chart of Accounts

**Functionality:**
- Default accounts auto-created (assets, liabilities, equity, income, expenses)
- Ability to add custom accounts
- Account types and categories
- Status: active/inactive

**User Benefit:** Proper double-entry accounting foundation

### C. Journal Entries

**Functionality:**
- Manual double-entry journal
- Date, debit account, credit account, amount, description
- Auto-calculated trial balance

**User Benefit:** Full control over accounting entries for advanced users

### D. Sales & Income

**Functionality:**
- Create sales transactions
- Issue invoices
- Track paid/unpaid invoices
- Customer list management
- Receipt generation (PDF optional)

**User Benefit:** Professional sales tracking and invoicing

### E. Expenses

**Functionality:**
- Record expenses
- Vendor management
- Categorize expenses
- Upload receipt images (optional)

**User Benefit:** Organized expense tracking with documentation

### F. Banking

**Functionality:**
- Record deposits and withdrawals
- Reconcile bank statements
- Track cash account separately

**User Benefit:** Accurate bank and cash management

### G. Reports (Essential)

**Available Reports:**
- Profit & Loss Statement
- Balance Sheet
- Trial Balance
- General Ledger
- Cash Flow Summary

**Export Options:**
- Downloadable as PDF or CSV
- Mobile-friendly viewing

**User Benefit:** Professional financial reports for decision-making and compliance

## UI/UX Requirements

### Design Principles

| Principle | Implementation |
|-----------|-----------------|
| **Clean & Simple** | Non-cluttered interface, easy navigation |
| **Mobile-Friendly** | Navigation using tabs or sidebar |
| **Color Coding** | Green → Income, Red → Expenses, Blue → Balance |
| **Summary Widgets** | At the top of each section |
| **Mobile Reports** | Reports must be easily readable on mobile |

### Color Scheme

Following MyGrowNet design system:
- **Income/Success**: `#059669` (emerald-600) - Green for positive
- **Expenses/Alerts**: `#dc2626` (red-600) - Red for outgoing
- **Balance/Info**: `#2563eb` (blue-600) - Blue for neutral
- **Primary Actions**: `#2563eb` (blue-600) - Main CTAs
- **Background**: `#f9fafb` (gray-50) - Clean background
- **Surface**: `#ffffff` (white) - Cards and content areas

## User Personas

### Primary Users

1. **Small Shop Owner**
   - Runs a retail business (groceries, clothing, etc.)
   - Currently uses notebook or memory
   - Needs daily sales and expense tracking
   - Wants to know if business is profitable

2. **Service Provider**
   - Offers services (plumbing, hair salon, repairs, etc.)
   - Tracks client payments and outstanding debts
   - Needs expense tracking for supplies
   - Wants to understand monthly income

3. **Trader/Vendor**
   - Buys and sells goods (wholesale or retail)
   - Needs to track inventory costs and sales
   - Manages multiple payment methods
   - Wants to track supplier debts

### Secondary Users

- **Business Advisors** - Help clients understand financial position
- **Loan Officers** - Review financial records for loan applications
- **Family Members** - Help manage business finances

## User Roles & Permissions

Permissions handled from Laravel:

| Role | Access Level | Capabilities |
|------|--------------|--------------|
| **Owner** | Full access | All features, settings, user management |
| **Accountant** | Transactions + reports | Create entries, generate reports, no settings |
| **Cashier** | Record sales & expenses only | Limited to daily transactions |
| **Viewer** | Read-only access | View reports and data only |

Each permission controls module visibility inside the Home Hub.

## Technical Requirements

### Frontend

**Framework & Tools:**
- Vue 3 with TypeScript
- Tailwind CSS for styling
- Modular structure: `/modules/accounting/`
- Responsive layout for mobile-first
- Lazy loading in Vue Router

**Components:**
- Dashboard with charts
- Account management interface
- Journal entry forms
- Sales and invoice management
- Expense tracking
- Banking reconciliation
- Report viewers with export

### Backend

**Framework:**
- Laravel 12 (PHP 8.2+)

**Controllers:**
- AccountsController
- JournalController
- SalesController
- ExpensesController
- ReportsController

**Architecture:**
- Follow RESTful API design
- Use Laravel Policies for permissions
- Domain-driven design principles

**Database Tables:**
- `accounts` - Chart of accounts
- `journal_entries` - Journal header records
- `journal_lines` - Journal line items (debits/credits)
- `customers` - Customer management
- `vendors` - Vendor/supplier management
- `invoices` - Sales invoices
- `invoice_items` - Invoice line items
- `expenses` - Expense records
- `payments` - Payment tracking

### Integration Requirements

1. **Home Hub Integration:**
   - Add module card to Home Hub labeled "Accounting"
   - Only show if user has `access_accounting` permission
   - Use global authentication (same login session)

2. **PWA Compatibility:**
   - Make the module fully PWA-compatible
   - Offline caching for forms
   - Sync when connection restored

## Phase 2 (Future Expansion)

**Note:** Do not build now, but structure code to allow the following later:

### Advanced Features
- **Payroll module** - Employee salary management
- **Inventory & stock management** - Track stock levels and costs
- **POS integration** - Connect with point-of-sale systems
- **VAT compliance & ZRA reporting** - Automated tax calculations and filing
- **Multi-currency** - Support for multiple currencies
- **Recurring invoices** - Automated invoice generation
- **Project accounting** - Track finances by project
- **Supplier credit & purchase orders** - Advanced procurement management

## Deliverables

Kiro should provide:

### Frontend Deliverables
- [ ] Vue file structure for the module (`/modules/accounting/`)
- [ ] UI components for each feature
- [ ] Router integration for `/accounting/*` routes
- [ ] Home Hub tile for "Accounting System"
- [ ] Responsive layouts for mobile and desktop

### Backend Deliverables
- [ ] All necessary Laravel models
- [ ] Controllers (Accounts, Journal, Sales, Expenses, Reports)
- [ ] Database migrations for all tables
- [ ] API endpoints (RESTful)
- [ ] Permission handling example
- [ ] Demo data seeders

### Documentation
- [ ] API documentation
- [ ] Component usage guide
- [ ] Database schema documentation
- [ ] Permission setup guide

## Implementation Roadmap

### Phase 1: Core Accounting (Weeks 1-3)
- [ ] Chart of Accounts setup
- [ ] Journal entries system
- [ ] Trial balance calculation
- [ ] Basic dashboard
- [ ] User permissions

### Phase 2: Sales & Expenses (Weeks 4-5)
- [ ] Sales transactions
- [ ] Invoice management
- [ ] Customer management
- [ ] Expense recording
- [ ] Vendor management

### Phase 3: Banking & Reports (Weeks 6-7)
- [ ] Banking module
- [ ] Bank reconciliation
- [ ] Profit & Loss Statement
- [ ] Balance Sheet
- [ ] General Ledger
- [ ] Cash Flow Summary

### Phase 4: Polish & Integration (Week 8)
- [ ] Home Hub integration
- [ ] PWA optimization
- [ ] Report export (PDF/CSV)
- [ ] Mobile UI refinement
- [ ] Demo data and testing

## Success Metrics

### User Adoption
- Number of SMEs using the system
- Daily active users
- Feature usage rates

### Business Impact
- User satisfaction score (target: 4.5+/5)
- Time saved on financial management
- Improvement in business profitability (user-reported)

### Financial Health
- Accuracy of financial records
- Debt recovery rate
- Cash management improvement

## Pricing Strategy

### Tier 1: Free (MVP)
- Basic sales and expense tracking
- Cashbook view
- Limited reports
- Up to 100 transactions/month

### Tier 2: Basic (K99/month)
- Unlimited transactions
- Debtors/creditors management
- Advanced reports
- Receipt storage (100MB)
- Email support

### Tier 3: Professional (K299/month)
- All Basic features
- Receipt storage (1GB)
- Data export (PDF, email)
- Priority support
- Mobile money integration

### Tier 4: Business (K599/month)
- All Professional features
- Inventory management
- Invoicing system
- Multi-user access
- Dedicated support

## Security & Compliance

### Data Protection
- End-to-end encryption for sensitive data
- Secure password storage (bcrypt hashing)
- Regular security audits
- GDPR and local privacy compliance

### Backup & Recovery
- Automatic daily backups
- Cloud storage redundancy
- Data recovery options
- Export functionality for user data

### Compliance
- Bank of Zambia regulations
- Tax authority requirements
- Data protection laws
- Financial record retention policies

## User Onboarding

### Getting Started
1. Create account with phone number or email
2. Set up business profile (name, type, currency)
3. Record opening balance
4. Tutorial on main features
5. First transaction walkthrough

### Support Resources
- In-app help and tooltips
- Video tutorials
- FAQ section
- Email/chat support
- Community forum

## Changelog

### Version 2.0 (November 30, 2025)
- Updated with complete development brief
- Added technical specifications
- Defined user roles and permissions
- Detailed database schema
- Added deliverables checklist
- Restructured implementation roadmap
- Added UI/UX requirements with color coding
- Defined Phase 2 expansion features

### Version 1.0 (November 30, 2025)
- Initial concept documentation
- Core features defined
- Design principles established
- Implementation roadmap created
- Pricing strategy outlined

---

## Related Documentation

- `docs/MYGROWNET_PLATFORM_CONCEPT.md` - Main platform overview
- `docs/UNIFIED_PRODUCTS_SERVICES.md` - Products and services catalog
- `SME_BUSINESS_TOOLS_MVP_BRIEF.md` - SME tools strategy

## Next Steps

1. **Validate with Users** - Interview target SMEs about needs and pain points
2. **Prototype MVP** - Build basic sales and expense tracking
3. **User Testing** - Test with 10-20 SMEs and gather feedback
4. **Refine Features** - Adjust based on user feedback
5. **Develop Phase 1** - Full MVP implementation
6. **Launch Beta** - Limited release to gather real-world data
7. **Iterate** - Continuous improvement based on usage patterns
