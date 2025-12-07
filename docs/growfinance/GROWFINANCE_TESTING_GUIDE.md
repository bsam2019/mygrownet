# GrowFinance Testing Guide

**Last Updated:** December 3, 2025
**Status:** Active

## Overview

This guide covers how to test the GrowFinance module comprehensively, including backend (PHP/Laravel) tests and frontend (user-facing) testing.

---

## Quick Start

### Prerequisites

1. Create a test database:
```sql
CREATE DATABASE mygrownet_test;
```

2. Run migrations on test database:
```bash
php artisan migrate --database=mysql --env=testing
```

### Run All GrowFinance Tests
```bash
# Run all GrowFinance feature tests
php vendor/bin/pest tests/Feature/GrowFinance

# Run specific test file
php vendor/bin/pest tests/Feature/GrowFinance/DashboardTest.php

# Run with filter
php vendor/bin/pest --filter="dashboard"
```

### Seed Demo Data (for manual testing)
```bash
# Seed demo data for manual testing
php artisan db:seed --class=GrowFinanceDemoSeeder
```

---

## Backend Testing (Pest PHP)

### Test Structure
```
tests/Feature/GrowFinance/
├── GrowFinanceTestCase.php    # Base test class
├── DashboardTest.php          # Dashboard tests
├── AccountsTest.php           # Chart of accounts tests
├── CustomersTest.php          # Customer CRUD tests
├── ExpensesTest.php           # Expense + receipt tests
├── InvoicesTest.php           # Invoice workflow tests
├── ReportsTest.php            # Financial reports tests
├── SalesTest.php              # Quick sales tests
├── BankingTest.php            # Banking operations tests
└── SubscriptionTest.php       # Subscription/tier tests
```

### Running Tests

```bash
# All GrowFinance tests
./vendor/bin/pest tests/Feature/GrowFinance

# Specific test class
./vendor/bin/pest tests/Feature/GrowFinance/InvoicesTest.php

# Specific test method
./vendor/bin/pest --filter="test_can_create_invoice"

# With coverage (requires Xdebug)
./vendor/bin/pest tests/Feature/GrowFinance --coverage
```

### Test Categories

| Category | File | Tests |
|----------|------|-------|
| Dashboard | DashboardTest.php | Auth, stats display |
| Accounts | AccountsTest.php | CRUD, system accounts |
| Customers | CustomersTest.php | CRUD, validation |
| Vendors | VendorsTest.php | CRUD, validation |
| Expenses | ExpensesTest.php | CRUD, receipt upload |
| Invoices | InvoicesTest.php | CRUD, payments, PDF |
| Reports | ReportsTest.php | All 5 reports, export |
| Sales | SalesTest.php | Quick sales |
| Banking | BankingTest.php | Deposits, withdrawals, transfers |
| Subscription | SubscriptionTest.php | Tiers, limits, upgrade |

---

## Frontend/User Testing Checklist

### 1. Initial Setup Flow
- [ ] Navigate to `/growfinance`
- [ ] Complete setup wizard (if first time)
- [ ] Verify dashboard loads with stats

### 2. Dashboard
- [ ] Stats cards show correct values
- [ ] Recent transactions list displays
- [ ] Quick action buttons work
- [ ] Mobile responsive layout

### 3. Customers
- [ ] List customers
- [ ] Create new customer
- [ ] Edit customer details
- [ ] Delete customer
- [ ] Search/filter customers

### 4. Vendors
- [ ] List vendors
- [ ] Create new vendor
- [ ] Edit vendor details
- [ ] Delete vendor

### 5. Expenses
- [ ] List expenses
- [ ] Create expense with vendor
- [ ] Upload receipt (image/PDF)
- [ ] View receipt
- [ ] Delete receipt
- [ ] Edit expense
- [ ] Delete expense

### 6. Invoices
- [ ] List invoices
- [ ] Create invoice with line items
- [ ] Verify totals calculate correctly
- [ ] Send invoice (status changes to "sent")
- [ ] Record payment (partial and full)
- [ ] Download PDF invoice
- [ ] Preview PDF invoice

### 7. Sales (Quick Add)
- [ ] Record quick sale
- [ ] Select payment method
- [ ] Verify transaction recorded

### 8. Banking
- [ ] View bank accounts
- [ ] Make deposit
- [ ] Make withdrawal
- [ ] Transfer between accounts
- [ ] Reconciliation page loads

### 9. Reports
- [ ] Profit & Loss report
- [ ] Balance Sheet report
- [ ] Cash Flow report
- [ ] Trial Balance report
- [ ] General Ledger report
- [ ] Date range filtering
- [ ] CSV export
- [ ] PDF export (Professional+ tier)

### 10. Subscription
- [ ] View upgrade page
- [ ] See current tier
- [ ] View pricing tiers
- [ ] Checkout flow
- [ ] Usage stats display

---

## Manual Testing Scenarios

### Scenario 1: Complete Invoice Workflow
```
1. Create a customer
2. Create an invoice for that customer
3. Add 2-3 line items
4. Save as draft
5. Send invoice
6. Record partial payment
7. Record remaining payment
8. Verify status = "paid"
9. Download PDF
```

### Scenario 2: Expense with Receipt
```
1. Create a vendor
2. Create an expense
3. Upload receipt image
4. View receipt
5. Edit expense amount
6. Delete receipt
7. Upload new receipt
```

### Scenario 3: Monthly Reconciliation
```
1. Record several sales
2. Record several expenses
3. View Profit & Loss report
4. Verify totals match
5. Export to CSV
6. Check Trial Balance (debits = credits)
```

### Scenario 4: Subscription Limits (Free Tier)
```
1. Create 100 transactions
2. Try to create 101st
3. Verify limit warning appears
4. Navigate to upgrade page
5. Verify upgrade CTA shown
```

---

## API Testing (if using API tokens)

### Get API Token
1. Navigate to `/growfinance/api`
2. Create new token with desired permissions
3. Copy token (shown once)

### Test Endpoints
```bash
# List customers
curl -H "Authorization: Bearer YOUR_TOKEN" \
     https://your-domain.com/api/growfinance/customers

# Create expense
curl -X POST \
     -H "Authorization: Bearer YOUR_TOKEN" \
     -H "Content-Type: application/json" \
     -d '{"amount": 500, "category": "Office", "description": "Test"}' \
     https://your-domain.com/api/growfinance/expenses
```

---

## Common Issues & Debugging

### Issue: Tests fail with "could not find driver"
```bash
# SQLite driver not installed - use MySQL instead
# Create test database:
mysql -u root -p -e "CREATE DATABASE mygrownet_test;"

# phpunit.xml is configured to use mygrownet_test database
```

### Issue: Tests fail with "Table not found"
```bash
# Run migrations on test database
php artisan migrate --database=mysql --env=testing

# Or refresh database
php artisan migrate:fresh --env=testing
```

### Issue: Chart of accounts not initialized
```bash
# The test base class handles this, but manually:
php artisan tinker
>>> app(AccountingService::class)->initializeChartOfAccounts($userId);
```

### Issue: PDF generation fails
```bash
# Ensure dompdf is installed
composer require barryvdh/laravel-dompdf
```

### Issue: Receipt upload fails
```bash
# Check storage permissions (Linux/Mac)
chmod -R 775 storage
php artisan storage:link

# Windows - run as administrator
php artisan storage:link
```

---

## Performance Testing

### Load Test Endpoints
```bash
# Using Apache Bench
ab -n 100 -c 10 -H "Cookie: your_session_cookie" \
   http://localhost/growfinance

# Key endpoints to test:
# - Dashboard (complex queries)
# - Reports (aggregations)
# - Invoice list (pagination)
```

### Database Query Optimization
```bash
# Enable query logging in .env
DB_LOG_QUERIES=true

# Check for N+1 queries in:
# - Customer list with invoices
# - Expense list with vendors
# - Report generation
```

---

## Test Data

### Demo Seeder Creates:
- 1 demo user (demo@mygrownet.com / password)
- 5 customers (Shoprite, Pick n Pay, individuals)
- 5 vendors (Trade Kings, Zambeef, ZESCO, etc.)
- 10-15 invoices with line items
- 7-21 expenses across categories
- Initialized chart of accounts

### Reset Test Data
```bash
# Fresh database with demo data
php artisan migrate:fresh --seed
php artisan db:seed --class=GrowFinanceDemoSeeder
```

---

## Continuous Integration

### GitHub Actions Example
```yaml
name: GrowFinance Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      - name: Install Dependencies
        run: composer install
      - name: Run Tests
        run: ./vendor/bin/pest tests/Feature/GrowFinance
```

---

## Related Documentation

- `GROWFINANCE_IMPROVEMENTS.md` - Feature implementation details
- `GROWFINANCE_MODULE.md` - Module overview
- `PROFILE_SETUP_WIZARD.md` - Setup wizard documentation
