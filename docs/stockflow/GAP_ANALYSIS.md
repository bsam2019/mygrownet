# StockFlow — World-Class Gap Analysis

Comprehensive audit of the StockFlow module against world-class inventory management systems (e.g., TradeGecko/Zoho Inventory/DEAR/Cinch). Current state: **76 pages, 37 domain entities, 30 services, 41 tables, ~150 routes**.

---

## COMPLETED — High Priority Must-Fix Items ✓

All 10 high-priority gaps identified in the original audit have been implemented (commit `ba0fba7`):

| # | Feature | What Was Built |
|---|---------|---------------|
| 1 | **Barcode scanning & label printing** | Camera scanner (`BarcodeDetector` API + manual fallback), printable label view (PDF/HTML), scan buttons on Items pages |
| 2 | **Inter-warehouse transfers** | Full DDD entity + service + controller + migrations + Vue pages (Index/Create/Show). In-transit/received workflow with stock movement tracking |
| 3 | **Multi-currency transactions** | `currency` + `exchange_rate` columns on `sa_sales` / `sa_purchase_orders`, currency dropdown on create forms, dual-currency display |
| 4 | **Bulk operations** | `ItemBulkController` (bulk delete, bulk adjust stock, bulk update price), checkbox multi-select + floating action bar on Items Index |
| 5 | **Global search** | `SearchController` (Items/Customers/Suppliers/POs), `GlobalSearch.vue` component with debounced input + grouped dropdown in top bar |
| 6 | **Advanced reporting** | ABC analysis (A/B/C classification), stock aging (expiry buckets), inventory turnover — dedicated Vue pages linked from Reports |
| 7 | **Batch / serial traceability** | `sa_lot_id` on `sale_items` / `purchase_order_items`, `Traceability.vue` for full lot chain (purchase → stock → sale) |
| 8 | **Supplier-item linking** | Pivot table `sa_item_suppliers` (supplier_sku, price, lead_time, is_preferred), `ItemSuppliers` component in Items Show |
| 9 | **Product images** | `image_url` column on `sa_items`, file upload in controller, thumbnails in Index/Show/Create with lightbox |
| 10 | **Activity log viewer** | `ActivityLogController` + `ActivityLog/Index.vue` — filterable table of all system events with user, event badge, details |

---

## HIGH PRIORITY — Core Functional Gaps

*All 10 original high-priority items are now implemented. Next highest priorities:*

| # | Feature | Why It Matters | Current State |
|---|---------|---------------|---------------|
| 11 | **Pick / Pack / Ship workflow** | Fulfillment centers need picking waves, packing verification, and shipment tracking. | Not present. Sales record items but there's no fulfillment workflow. No shipment tracking. |
| 12 | **Cycle counting (ABC analysis)** | Full physical counts are disruptive. ABC analysis enables daily cycle counts of high-value items. | ABC report exists but no automated cycle count scheduling. |
| 13 | **Automated reorder points & POs** | "Auto-reorder" when stock hits minimum level saves buyer time. | Items have `reorderLevel` but no automation triggers it. No suggested PO generation. |
| 14 | **Backorder / preorder management** | Handle demand exceeding supply without losing sales. | Not supported. Sales fail if stock is insufficient (InsufficientStockException). |
| 15 | **Customer portal** | Let customers view their orders, invoices, and account status. | Only a supplier portal exists. No customer self-service portal. |

---

## MEDIUM PRIORITY — Feature Depth

| # | Feature | Why It Matters | Current State |
|---|---------|---------------|---------------|
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

These areas are already strong:

- **DDD architecture** — Clean separation Domain/Infrastructure/Controllers with typed IDs, value objects, repository pattern
- **Multi-tenant isolation** — `sa_company_id` on every table, feature flags per company
- **Extension system** — Pharmacy, Manufacturing, Restaurant extensions with subscription gating
- **Core workflows** — Purchase → Receive → Stock+ → Sale → Stock− → Cash Register → Count → Audit → Transfer
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
- **Global search** — Cross-entity quick search in top bar
- **Activity log** — Full audit trail viewer
- **Product images** — Upload and display
- **Barcode scanning** — Camera-based scanner
- **Barcode labels** — Printable label generation
- **Supplier-item linking** — Preferred suppliers with SKU/price/lead-time per item
- **Bulk operations** — Multi-select delete/stock/price updates
- **Multi-currency** — Currency + exchange rate on sales/purchases
- **Inter-warehouse transfers** — Transfer orders with in-transit/received workflow
- **Advanced reports** — ABC analysis, stock aging, inventory turnover
- **Lot traceability** — Full chain from purchase through sale

---

## RECOMMENDED ROADMAP

### Phase 1 — Quick Wins (1-2 weeks each)
1. Dark mode toggle
2. Bin-to-bin transfers
3. Product variants (model already exists)

### Phase 2 — Core Operations (2-4 weeks each)
4. Pick/Pack/Ship workflow
5. Cycle counting with ABC scheduling
6. Automated reorder suggestions
7. Backorder/preorder support

### Phase 3 — Ecosystem (3-6 weeks each)
8. Customer portal
9. E-commerce integration (Shopify)
10. Accounting integration (QuickBooks)

### Phase 4 — Enterprise (ongoing)
11. Webhook system
12. Mobile app (Capacitor-based)
13. Advanced BI dashboards
