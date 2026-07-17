# StockFlow — World-Class Gap Analysis

Comprehensive audit of the StockFlow module against world-class inventory management systems (e.g., TradeGecko/Zoho Inventory/DEAR/Cinch). Current state: **76 pages, 37 domain entities, 30 services, 41 tables, ~150 routes**.

---

## HIGH PRIORITY — Core Functional Gaps

| # | Feature | Why It Matters | Current State |
|---|---------|---------------|---------------|
| 1 | **Barcode scanning & label printing** | Warehouse operations rely on scanning. No system is world-class without it. | No scanning support. Items have a `barcode` field but it's never populated or used. No label generation (ZPL/PDF). |
| 2 | **Inter-warehouse / inter-branch transfers** | Stock moves between locations constantly. Transfer orders with in-transit status are table stakes. | Warehouses exist but are isolated. No `TransferOrder` entity, no in-transit stock status, no transfer workflow. Items don't link to a warehouse. |
| 3 | **Multi-currency transactions** | Businesses deal in multiple currencies daily. Exchange rates exist but aren't applied to sales/purchases. | `ExchangeRate` entity exists, `CurrencyService` can convert, but no sale/purchase/invoice stores amounts in a foreign currency with a rate reference. |
| 4 | **Bulk operations** | Editing 200 items one-by-one is not viable. Batch price updates, bulk delete, bulk status change are essential. | All CRUD is single-item. No `POST /items/bulk-update`, no bulk select in tables, no batch stock adjustment. |
| 5 | **Global search** | Users need to find items, customers, orders instantly from anywhere. | No search bar. Each page has its own filter, but no cross-entity quick search. |
| 6 | **Advanced reporting** | Margin analysis, ABC classification, stock aging, turnover, supplier performance — these drive business decisions. | Reports page exists but has minimal exports (inventory report, sales/purchase PDFs). No ABC analysis, no aging report, no profitability by customer/item. |
| 7 | **Batch / serial number tracking** | Traceability is non-negotiable for pharma/food/manufacturing. | Lot entity exists but doesn't integrate with sales (a sale doesn't record which lot was sold). No full lot traceability. |
| 8 | **Supplier-item linking** | Each item typically has a preferred supplier with supplier-specific SKU, lead time, and cost. | No link between items and suppliers. Purchases select items manually. |
| 9 | **Product images** | Visual identification is critical in warehousing. | Item has no image field. No media upload for products. |
| 10 | **Activity log viewer** | Who changed what and when — essential for compliance and debugging. | `sa_activity_log` table exists, `ActivityLogService` records events, but there's **no UI** to browse the log. |

---

## MEDIUM PRIORITY — Feature Depth

| # | Feature | Why It Matters | Current State |
|---|---------|---------------|---------------|
| 11 | **Pick / Pack / Ship workflow** | Fulfillment centers need picking waves, packing verification, and shipment tracking. | Not present. Sales record items but there's no fulfillment workflow. No shipment tracking. |
| 12 | **Cycle counting (ABC analysis)** | Full physical counts are disruptive. ABC analysis enables daily cycle counts of high-value items. | Only full physical counts exist. No ABC classification on items. No cycle count scheduling. |
| 13 | **Automated reorder points & POs** | "Auto-reorder" when stock hits minimum level saves buyer time. | Items have `reorderLevel` but no automation triggers it. No suggested PO generation. |
| 14 | **Backorder / preorder management** | Handle demand exceeding supply without losing sales. | Not supported. Sales fail if stock is insufficient (InsufficientStockException). |
| 15 | **Customer portal** | Let customers view their orders, invoices, and account status. | Only a supplier portal exists. No customer self-service portal. |
| 16 | **Product variants** | Same product in multiple sizes/colors needs variant linking. | `ProductVariantModel` exists but is unused — no UI, no controller, no workflow. |
| 17 | **Bin-to-bin transfers** | Stock moves between bins daily without a purchase/sale. | No bin transfer workflow. Users must do a stock adjustment instead. |
| 18 | **Column customization & saved filters** | Power users need to customize table views and save filter presets. | All tables are fixed-column. No drag-to-reorder columns, no show/hide, no saved filter presets. |
| 19 | **Kit / bundle management** | Sell product kits (e.g., "Starter Pack") that auto-deduct component stock. | No bundle/kit entity or workflow. |
| 20 | **Payment tracking & aging** | Track which invoices are overdue, send reminders, apply late fees. | Invoices track `amountPaid`, `balanceDue`, and `isOverdue()` but there's no automated dunning or aging report. |
| 21 | **Dark mode** | Standard expectation for modern SaaS. | Tailwind dark mode classes exist project-wide but StockFlow doesn't use them. |
| 22 | **Keyboard shortcuts** | Power users navigate by keyboard (e.g., `g+i` → Items, `g+s` → Sales). | No shortcut system. |
| 23 | **E-commerce integration** | Sync inventory & orders with Shopify/WooCommerce. | No integration. API exists but no e-commerce connector. |
| 24 | **Accounting integration** | Sync invoices/bills with QuickBooks/Xero. | No integration. |

---

## LOW PRIORITY — Differentiators

| # | Feature | Why It Matters |
|---|---------|---------------|
| 25 | **Demand forecasting** | ML-based prediction of future stock needs. |
| 26 | **Drop-shipping workflow** | Route orders directly to suppliers. |
| 27 | **Consignment inventory** | Supplier-owned stock at your location, pay when sold. |
| 28 | **Variable-weight items** | Items sold by weight (e.g., produce, meat). |
| 29 | **Gift cards / store credit** | Internal value system. |
| 30 | **Loyalty program integration** | Points-based customer rewards. |
| 31 | **SMS notifications** | Order/shipping alerts via SMS. |
| 32 | **Slack / Teams integration** | Real-time alerts to team channels. |
| 33 | **Workflow / approval engine** | Custom approval chains (e.g., PO > $1000 needs manager). |
| 34 | **EDI integration** | Electronic Data Interchange for enterprise customers. |
| 35 | **3PL integration** | Connect with third-party logistics providers. |
| 36 | **Tax automation (Avalara/TaxJar)** | Automatic tax calculation per address. |
| 37 | **Shipping carrier integration** | Rate comparison, label printing (FedEx/UPS/DHL). |
| 38 | **Webhook / event system** | Let external systems subscribe to stock changes. |
| 39 | **Native mobile app** | Offline-capable mobile app for warehouse scanning. |
| 40 | **Two-factor authentication** | Security requirement for enterprise. |
| 41 | **i18n / multi-language** | Serve non-English markets. |
| 42 | **In-app onboarding tour** | Guide new users through features. |
| 43 | **Data retention / archival** | Purge old data per policy. |
| 44 | **Undo / soft-delete restore** | Recover from mistakes. |
| 45 | **Realtime dashboard via WebSockets** | Live metrics without page refresh. |

---

## WHAT ALREADY EXISTS (Strength Inventory)

These areas are already strong and don't need immediate investment:

- **DDD architecture** — Clean separation Domain/Infrastructure/Controllers with typed IDs, value objects, repository pattern
- **Multi-tenant isolation** — `sa_company_id` on every table, feature flags per company
- **Extension system** — Pharmacy, Manufacturing, Restaurant extensions with subscription gating
- **Core workflows** — Purchase → Receive → Stock+ → Sale → Stock− → Cash Register → Count → Audit is complete
- **CSV import** — Items, Suppliers, Bins bulk import
- **PDF export** — Inventory report, sales report, purchase report, cash summary, audit export
- **Role-based access** — 46 permissions across 12 categories
- **Notification system** — In-app + email notifications with real-time via Laravel Echo
- **Comments & messaging** — Threaded comments on entities, internal messaging
- **Supplier portal** — Suppliers can view orders and manage profile
- **Backup system** — Automated daily CSV email backup with full extension coverage
- **Help page** — In-app documentation with accordion sections
- **Pagination, loading skeletons, confirm dialogs** — Consistent UX patterns
- **Responsive layout** — Mobile sidebar, bottom nav, collapsible desktop sidebar

---

## RECOMMENDED ROADMAP

### Phase 1 — Quick Wins (1-2 weeks each)
1. Activity log viewer (UI is missing, data is already recorded)
2. Product images (add `image_url` to items, media upload)
3. Global search bar (cross-entity quick search)
4. Bulk operations (multi-select + batch actions on list pages)
5. Dark mode toggle

### Phase 2 — Core Warehouse (2-4 weeks each)
6. Inter-warehouse transfers
7. Barcode scanning (camera-based, plus label generation)
8. Supplier-item linking
9. Bin-to-bin transfers
10. Backorder/preorder support

### Phase 3 — Advanced Operations (3-6 weeks each)
11. Pick/Pack/Ship workflow
12. Cycle counting with ABC analysis
13. Automated reorder suggestions
14. Customer portal
15. Multi-currency transactions

### Phase 4 — Ecosystem (ongoing)
16. E-commerce integration (Shopify)
17. Accounting integration (QuickBooks)
18. Advanced reporting (BI-style dashboards)
19. Webhook system
20. Mobile app (Capacitor-based)
