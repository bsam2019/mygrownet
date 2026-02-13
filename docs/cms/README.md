# MyGrowNet CMS Documentation

**SME Operating System - Version 1**  
**Last Updated:** February 7, 2026

---

## Quick Navigation

| Document | Purpose | When to Use |
|----------|---------|-------------|
| **[DEVELOPMENT_BRIEF.md](./DEVELOPMENT_BRIEF.md)** | Business requirements & objectives | Understanding WHAT to build and WHY |
| **[COMPLETE_FEATURE_SPECIFICATION.md](./COMPLETE_FEATURE_SPECIFICATION.md)** | Detailed feature specs for all 18 modules | Understanding WHAT features are included |
| **[IMPLEMENTATION_PLAN.md](./IMPLEMENTATION_PLAN.md)** | Technical architecture & phased delivery | Understanding HOW to build it |
| **[MODULE_INTEGRATION.md](./MODULE_INTEGRATION.md)** | Integration with existing modules | Understanding HOW it connects & can be extracted |

---

## What is CMS?

MyGrowNet CMS (Company Management System) is an **SME Operating System** designed to help small and medium enterprises in Zambia operate formally with:

- ✅ Discipline - Business rules enforced at system level
- ✅ Accountability - All actions logged with user ID
- ✅ Traceability - Every transaction links back to a job

**Core Principle:** *If something is not linked in the system, it does not exist.*

---

## 18 Core Modules

### Critical Modules (Phase 1-2)
1. **Company & Administration** - Company setup, users, roles, permissions
2. **Customer Management** - Customer records, contacts, documents, notes
3. **Job/Operations Management** - Job creation, assignment, tracking, costing
4. **Quotations & Invoices** - Quote generation, invoice from jobs
5. **Payments & Cash Management** - Payment recording, allocation, credits
6. **Expense Management** - Expense tracking with approval workflows

### High Priority (Phase 2-5)
7. **Financial Reporting** - P&L, cashbook, outstanding invoices
8. **Payroll & Commission** - Worker management, commission calculation
9. **Dashboards & Analytics** - Owner, operations, finance dashboards
10. **Notifications & Audit Trail** - Activity logging, notifications
11. **Settings & Configuration** - Business hours, tax, approval thresholds
12. **Approval Workflows** - Configurable approvals for expenses, commissions

### Medium Priority (Phase 3-6)
13. **Inventory Management** - Stock tracking, usage per job, alerts
14. **Asset Management** - Equipment tracking, maintenance, assignment
15. **Document Management** - Secure storage, role-based access
16. **Industry Presets** - Geopamu printing preset, reusable templates
17. **Onboarding & Setup** - Company wizard, industry selection
18. **Security & Governance** - Data isolation, session management, backups

---

## Key Features (v1.1 Additions)

### Operational Safeguards
- ✅ **Approval Workflows** - Configurable approvals for expenses, assets, commissions
- ✅ **Data Locking** - Lock completed jobs and paid invoices
- ✅ **Period Close** - Monthly period close to prevent backdating
- ✅ **Soft Deletion** - Archive instead of delete, restore capability

### Business Intelligence
- ✅ **Job Costing & Profitability** - Material, labor, overhead tracking per job
- ✅ **Payment Allocation** - Allocate payments to invoices, track overpayments
- ✅ **Enhanced Audit Trail** - Old value → new value tracking

### Future-Ready
- ✅ **API Layer (Internal)** - Clean service APIs for future mobile app
- ✅ **Feature Flags** - Enable/disable features per tenant
- ✅ **Backup & Export** - Manual data export (CSV/PDF)

---

## Architecture Principles

### 1. Domain-Driven Design (DDD)
- Each module has its own bounded context
- Shared core entities (Company, User, Customer, Job, Payment)
- Modules communicate via domain events

### 2. Multi-Tenant
- One database, strict data isolation per company
- Tenant ID on all business tables
- Global scope enforces company_id filtering

### 3. Event-Driven
- Modules communicate via events, not direct calls
- Example: `JobCompleted` → triggers invoice generation, commission calculation, inventory deduction
- Enables future extraction to separate systems

### 4. Rule-Driven
- Business rules enforced at service layer
- **No job → no invoice → no payment → no commission**
- **No approval → no expense posting**

---

## Implementation Timeline

**Total Duration:** 11 weeks (3 months)

| Phase | Duration | Focus | Deliverables |
|-------|----------|-------|--------------|
| **Phase 1** | Weeks 1-2 | Core Foundation | Company, users, customers, basic jobs |
| **Phase 2** | Weeks 3-4 | Finance Integration | Invoices, payments, expenses |
| **Phase 3** | Weeks 5-6 | Inventory & Assets | Stock tracking, asset management |
| **Phase 4** | Weeks 7-8 | Payroll & Commission | Worker management, commission automation |
| **Phase 5** | Weeks 9-10 | Reporting & Dashboard | Financial reports, analytics dashboards |
| **Phase 6** | Week 11 | Geopamu Preset & Onboarding | Industry preset, onboarding wizard |

---

## Integration with Existing Modules

CMS integrates with three existing MyGrowNet modules:

### GrowFinance Integration (Highest Priority)
- **Job → Invoice**: Completed jobs automatically generate invoices
- **Expense Tracking**: Job materials automatically recorded as expenses
- **Customer Sync**: Shared customer entity (single source of truth)
- **Payment Reconciliation**: Invoice payments trigger commission calculations

### GrowBiz Integration (Medium Priority)
- **Job → Project**: Jobs become projects with Kanban/Gantt views
- **Team Management**: Shared worker/team member entity
- **Inventory Integration**: Unified asset tracking
- **Appointment Scheduling**: Site visits scheduled automatically

### BizBoost Integration (Lower Priority)
- **Marketing Automation**: Job completions trigger testimonial campaigns
- **Portfolio Building**: Job photos auto-posted to social media
- **Re-engagement Campaigns**: Automated customer retention

**See [MODULE_INTEGRATION.md](./MODULE_INTEGRATION.md) for complete integration details.**

---

## Future Extraction Strategy

The CMS is designed to be **extracted as a standalone product** later:

### Current (Integrated)
```
Single Laravel App
├── CMS Module
├── GrowFinance Module
├── GrowBiz Module
└── BizBoost Module
```

### Future (Extracted)
```
CMS SaaS (Separate App)  ←→  MyGrowNet Platform
- Own database               - GrowFinance
- REST API                   - GrowBiz
- Webhooks                   - BizBoost
```

**Key Design Decisions That Enable Extraction:**
- ✅ Event-driven communication (not direct function calls)
- ✅ Repository pattern (data access abstracted)
- ✅ No direct dependencies between modules
- ✅ API-first design (internal APIs can become external)

**See [MODULE_INTEGRATION.md](./MODULE_INTEGRATION.md) Section 11 for complete extraction strategy.**

---

## Pilot Tenant: Geopamu Investments Limited

**Industry:** Printing & Branding  
**Use Case:** First pilot tenant to validate CMS

**Geopamu Preset Includes:**
- Default job types (T-shirt printing, branding, signage)
- Default inventory items (ink, t-shirts, vinyl)
- Default commission model (per job rates)
- Predefined roles (Owner, Operations Manager, Printer)

---

## Exclusions (v1 Scope)

**CMS v1 will NOT include:**
- ❌ MLM features
- ❌ Marketplace selling
- ❌ Advertising tools
- ❌ Personal life management
- ❌ Family tree features
- ❌ AI features
- ❌ Advanced analytics
- ❌ Inventory forecasting
- ❌ Full payroll compliance engines
- ❌ Banking integrations

**These are v2+ features to keep the product focused and safe.**

---

## Getting Started

### For Business Stakeholders
1. Read [DEVELOPMENT_BRIEF.md](./DEVELOPMENT_BRIEF.md) - Understand business objectives
2. Read [COMPLETE_FEATURE_SPECIFICATION.md](./COMPLETE_FEATURE_SPECIFICATION.md) - Review all features
3. Review Geopamu preset configuration

### For Developers
1. Read [IMPLEMENTATION_PLAN.md](./IMPLEMENTATION_PLAN.md) - Understand technical architecture
2. Read [MODULE_INTEGRATION.md](./MODULE_INTEGRATION.md) - Understand integration points
3. Review database schemas in [COMPLETE_FEATURE_SPECIFICATION.md](./COMPLETE_FEATURE_SPECIFICATION.md)
4. Start with Phase 1 implementation

### For Project Managers
1. Review 11-week timeline in [IMPLEMENTATION_PLAN.md](./IMPLEMENTATION_PLAN.md)
2. Understand module dependencies
3. Plan Geopamu pilot deployment (Week 11)

---

## Success Criteria

1. **Traceability:** Every transaction links back to a job ✅
2. **Accountability:** All actions logged with user ID ✅
3. **Discipline:** Business rules enforced at service level ✅
4. **Isolation:** Complete data separation between companies ✅
5. **Performance:** Dashboard loads < 2 seconds ✅
6. **Usability:** SME owner can complete full workflow without training ✅

---

## Support & Questions

**Document Owner:** Development Team  
**Review Cycle:** Weekly during implementation  
**Next Review:** February 14, 2026

For questions or clarifications, refer to the specific document for your area of interest.

---

**Ready to start implementation!** 🚀

Begin with Phase 1 (Core Foundation) from [IMPLEMENTATION_PLAN.md](./IMPLEMENTATION_PLAN.md).
