# StockFlow — Business Inventory & Cash Management System

StockFlow is a complete inventory management, sales processing, and cash register system built for small to medium businesses. It is part of the MyGrowNet platform and runs on dedicated subdomains (e.g., `taradasi.mygrownet.com`).

## How It Works

Each business (company) gets its own subdomain. After logging in, users can manage:

- **Items** — Products tracked with SKU, category, unit, and quantity
- **Purchases** — Purchase orders from suppliers, with stock receipt
- **Sales** — Point-of-sale with receipt generation and automatic stock deduction
- **Quotations** — Create and send quotes; convert to invoices
- **Invoices** — Bill customers; track sent/pending/paid/cancelled status
- **Receipts** — Payment receipts linked to sales and invoices
- **Cash Register** — Open/close register, record expenses and banking
- **Physical Counts** — Count inventory manually; system auto-fills expected quantities
- **Audits** — Full reconciliation: physical vs system vs recorded sales
- **Departments & Bins** — Organise inventory by location
- **Suppliers** — Vendor management
- **Stock Movements** — Audit trail of every quantity change
- **Reports** — Sales, purchases, inventory, cash summaries (printable/PDF)

## Architecture (Domain-Driven Design)

```
app/Domain/StockFlow/          ← Pure PHP domain (no Laravel dependencies)
├── ValueObjects/              Typed IDs, enums, Money
├── Entities/                  Rich domain models with behaviour
├── Repositories/              Interface contracts
├── Services/                  Business logic orchestration
└── Exceptions/                Domain exceptions

app/Infrastructure/Persistence/
├── Eloquent/StockFlow/        Eloquent models (thin data mappers)
└── Repositories/StockFlow/    Interface implementations

app/Http/Controllers/StockFlow/  HTTP layer (validation + service delegation)
routes/stockflow.php              Main domain routes (/stockflow prefix)
routes/stockflow-subdomain.php    Subdomain routes (no prefix)
```

### Tables

All tables use the `sa_` prefix and `sa_company_id` for tenant isolation:

`sa_companies`, `sa_departments`, `sa_bins`, `sa_items`, `sa_suppliers`, `sa_purchase_orders`, `sa_purchase_order_items`, `sa_sales`, `sa_sale_items`, `sa_stock_movements`, `sa_physical_counts`, `sa_count_items`, `sa_audits`, `sa_audit_items`, `sa_audit_reconciliations`, `sa_cash_registers`, `sa_cash_movements`, `sa_quotations`, `sa_quotation_items`, `sa_invoices`, `sa_invoice_items`, `sa_receipts`, `sa_receipt_items`, `sa_expiry_checks`, `sa_expiry_check_items`, `sa_subscription_plans`, `sa_company_subscriptions`

### Key Invariants

- Every stock change records quantity before/after (`StockMovement`)
- Item quantity can never go below zero
- Cash register expected-closing auto-calculates from recorded sales, expenses, and banking
- Audit finalization computes unaccounted value = total variance − total recorded sales
- All mutations run inside `DB::transaction()`

## Numbering Formats

| Document | Format |
|----------|--------|
| Purchase Orders | PO-XXXXXX |
| Sales | RECEIPT-XXXXXX |
| Quotations | QTN-XXXXXX |
| Invoices | INV-XXXXXX |
| Receipts | RCT-XXXXXX |

## Feature Flags

Each module can be enabled/disabled per company via company settings (`settings.features_enabled`). Features default to enabled unless explicitly disabled.

Available flags: `items`, `purchases`, `sales`, `quotations`, `invoices`, `receipts`, `cash`, `movements`, `counts`, `audits`, `departments`, `bins`, `suppliers`, `roles`, `employees`
