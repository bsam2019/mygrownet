# GROWBUILDER — Master Architecture & Product Strategy Specification

**AI-Powered Business Digital Presence Platform**
A MyGrowNet Business Application

**Document Version**: 2.0
**Effective Date**: August 2026
**Jurisdiction**: Republic of Zambia (Primary), SADC Secondary, International Expansion
**Module Slug**: `growbuilder`
**Subdomain**: `growbuilder.mygrownet.com`
**Service Provider**: `GrowBuilderServiceProvider`
**Migration Folder**: `database/migrations/growbuilder/` (40 migrations)

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [Product Thesis & Vision](#2-product-thesis--vision)
3. [Competitive Positioning](#3-competitive-positioning)
4. [Operating Constraints](#4-operating-constraints)
5. [Market Entry Strategy](#5-market-entry-strategy)
6. [From Templates to an AI-Assisted Design System](#6-from-templates-to-an-ai-assisted-design-system)
7. [Business Profile — Data Model](#7-business-profile--data-model)
8. [Phased Roadmap](#8-phased-roadmap)
9. [Onboarding Model](#9-onboarding-model)
10. [Distribution Strategy](#10-distribution-strategy)
11. [Export & Ownership Policy](#11-export--ownership-policy)
12. [Deferred — The "Not Now" List](#12-deferred--the-not-now-list)
13. [Open Questions & Risks](#13-open-questions--risks)
14. [Technical Architecture — Current Production State](#14-technical-architecture--current-production-state)
15. [DDD Layer Architecture](#15-ddd-layer-architecture)
16. [AI Engine Architecture](#16-ai-engine-architecture)
17. [E-Commerce & Payment Architecture](#17-e-commerce--payment-architecture)
18. [Agency & White-Label Architecture](#18-agency--white-label-architecture)
19. [Subscription Tier Matrix](#19-subscription-tier-matrix)
20. [Cross-Module Integration Architecture](#20-cross-module-integration-architecture)
21. [Gap Analysis — Strategy vs. Current Implementation](#21-gap-analysis--strategy-vs-current-implementation)
22. [Legal, Financial & Compliance Policies](#22-legal-financial--compliance-policies)
23. [Deployment & Infrastructure Topology](#23-deployment--infrastructure-topology)

---

## 1. Executive Summary

GrowBuilder currently exists as a **template-based website builder** with a drag-and-drop visual editor, section-level AI editing, a full e-commerce engine, agency white-label management, and 32 AI-powered API endpoints. It is one of the most feature-dense modules in the MyGrowNet platform — with 33 controllers, 22 domain services, 42+ Vue pages, 40 database migrations, and ~100+ routes already deployed to production.

AI-generated website creation has become commoditized — tools like ChatGPT, Google's AI web tools, and dedicated AI site builders (Durable, 10Web, Wix ADI) can now produce a working website from a short description in minutes. Competing purely on generation quality against companies investing billions of dollars is not a sustainable strategy for a small team.

**This document sets out a repositioning**: GrowBuilder should not aim to be the best website generator. It should become the system that owns a business's **structured digital identity** — the **Business Profile** — and uses AI, wherever it currently stands, as the swappable engine that turns that data into a website, and eventually into other customer-facing channels. As AI generation improves industry-wide, GrowBuilder's output improves with it, rather than being threatened by it.

The plan is deliberately narrow. It commits to one country (Zambia first), one vertical (pharmacies), one flagship customer relationship to start (Taradasi Medics), and a phased build that defers ambitious ideas — AI agent marketplaces, mobile apps, plugin ecosystems — until the core loop is proven with real customers. The differentiators are local-market fit (mobile money, PACRA/TPIN, WhatsApp, mobile performance) and, over time, live integration with the rest of the MyGrowNet platform (BizDocs, StockFlow, GrowFinance, BizBoost).

**How to read this document**: Sections 1–13 set out the product strategy. Sections 14–23 map the strategy against the existing production implementation, identify gaps, and define technical architecture that is already built or needs to be built.

---

## 2. Product Thesis & Vision

GrowBuilder does not compete on AI generation quality. Frontier AI labs will always out-invest a small team on raw generation capability, and that gap will keep widening over time. The durable position is the layer beneath generation — the **structured business data and platform context** that a generic AI assistant does not have and cannot easily obtain.

### The Core Distinction

> **General AI generates a website. GrowBuilder operates a business's digital presence.**

That distinction should drive every product decision from here. A website built by a generic AI assistant is a one-time output — a finished file the owner must remember to update. A business run through GrowBuilder has its digital presence continuously reflect what is actually true about the business, because the website is generated from structured data rather than static content.

### What GrowBuilder Is Built Around

- **The Business Profile** — a structured record of the business (identity, contact details, services, products, pricing, hours, trust information) that every output is generated from, rather than content created and stored independently per page.
- **Local-market fit** — mobile money (MTN MoMo, Airtel Money), PACRA/TPIN, WhatsApp Cloud API, and mobile-first performance, prioritized in a way that global AI website builders have little commercial incentive to match.
- **Platform integration** — over time, a live connection to BizDocs, StockFlow, GrowFinance, and BizBoost, so the website reflects real business data (inventory, pricing, documents) rather than drifting out of date.

### Why This Survives AI Advancement

The test for any GrowBuilder feature going forward:

> **Does it get stronger as AI improves, or does it get commoditized as AI improves?**

| ✅ Passes the test | ❌ Fails the test |
|---|---|
| Live data sync from StockFlow/BizDocs | Generic "AI writes your blog" |
| Industry blueprints with local compliance | Template gallery generation |
| PACRA/TPIN trust verification | Static content editing |
| WhatsApp ordering workflows | Basic page builder |
| Business Profile → multi-channel publish | One-shot site generation |

### Product Thesis Statement

> GrowBuilder helps small businesses establish and maintain a professional digital presence. It starts by creating industry-specific websites using AI, but its long-term value comes from maintaining structured business information that powers customer interactions across digital channels.

---

## 3. Competitive Positioning

GrowBuilder should not be positioned against Wix, Squarespace, or ChatGPT-style AI website generation. Comparing GrowBuilder to those tools invites a comparison it cannot win on generation quality alone, and it misidentifies who the target customer is actually choosing between today.

### The Real Comparison

For the target customer — a Zambian small or micro business — the realistic "before" state is not a competitor website builder. It is a combination of a Facebook page, a WhatsApp number, and scattered paper or PDF documents.

| Before GrowBuilder | After GrowBuilder |
|---|---|
| Facebook page for visibility | Professional, owned website with a clear business identity |
| WhatsApp number for orders and enquiries | WhatsApp integrated into a structured enquiry and ordering flow |
| Scattered paper or PDF documents | Business information centrally maintained and consistently applied |
| Manual, ad-hoc updates | Business Profile changes reflected automatically wherever they matter |

### Positioning Statement

> GrowBuilder is an AI-powered digital presence platform that maintains a single source of truth for a business and automatically publishes, updates, and improves that business's presence across every customer touchpoint.

---

## 4. Operating Constraints

GrowBuilder is being built by a small team alongside other active work, including the StockFlow inventory platform and the broader MyGrowNet platform foundation. This plan is written to explicitly account for that.

### Working Principles

1. **Automation is preferred** over manual services wherever both are viable.
2. **Reusable components are preferred** over one-off custom work.
3. **One vertical is prioritized** over attempting many at once.
4. **One acquisition channel is tested and proven** before investing in others.
5. **One clear customer outcome is prioritized** over a long feature list.

Any proposed addition to scope — at any phase — should be checked against these five principles before it is added to the roadmap.

---

## 5. Market Entry Strategy

### Geography: Zambia First

Launch and prove the model in Zambia before expanding. Once proven, expansion to other SADC markets with similar dynamics — mobile money adoption (M-Pesa, EcoCash), similar bandwidth and trust considerations — should follow the same playbook rather than requiring a new one.

### Vertical: Pharmacies

Pharmacies are the strongest starting vertical. They need a product catalogue, WhatsApp ordering, location and trust information, and payments — all core to the GrowBuilder value proposition — without the deeper compliance and patient-data complexity that a clinic or hospital vertical would introduce at this early stage.

### First Customer: Taradasi Medics

Taradasi Medics is already a pilot tenant on the StockFlow / BizDocs relationship, which means the first GrowBuilder conversation is a warm extension of an existing trust relationship rather than a cold sale:

> *"You already manage your business digitally — would you like your online presence to work the same way?"*

### The Engine Stays Generic

Pharmacy is the first industry blueprint, not GrowBuilder's permanent identity. The underlying engine, design system, and Business Profile model are built to be industry-agnostic from the start.

```
GrowBuilder Engine → Industry Blueprint Layer → Pharmacy Blueprint (first)
                                              → School (later)
                                              → Restaurant (later)
                                              → Professional Services (later)
```

---

## 6. From Templates to an AI-Assisted Design System

The template picker should not be removed — its purpose changes. For a customer unfamiliar with AI tools, being asked to "describe your business and trust the AI" with no visual reassurance is a leap many will not make. A visible choice between prepared options builds the confidence needed to proceed.

### The New Creation Flow

| Step | What Happens |
|---|---|
| 1. **Describe** | The business owner briefly describes their business |
| 2. **Choose a Style** | AI recommends three industry-appropriate designs; the owner picks one |
| 3. **Generate** | AI generates content and structure into the chosen design, drawing on the Business Profile |
| 4. **Refine** | The owner edits specific sections as needed |
| 5. **Publish** | The site goes live |

### Current Implementation Status

| Step | Status | Implementation |
|---|---|---|
| 1. Describe | ✅ Built | `AIController::generateWebsite()` accepts business description prompts |
| 2. Choose a Style | ✅ Built | `SiteTemplateController` serves template gallery with industry filters; `Create.vue` includes template picker |
| 3. Generate | ✅ Built | `WebsiteGeneratorService` + `AIContentService` (126.8 KB) generate multi-page layouts from prompts |
| 4. Refine | ✅ Built | `EditorController` + `Editor/Index.vue` (83.0 KB) — full drag-and-drop visual canvas with AI chat assistant |
| 5. Publish | ✅ Built | `PublishSiteUseCase` + `SiteController::publish()` + custom domain via `CustomDomainService` |

### What Needs Improvement

- **Step 2 should present AI-curated recommendations**, not just a static gallery. The AI should analyze the business description and pre-select 3 templates with industry-appropriate sections.
- **Step 3 should pre-populate from the Business Profile** (Section 7), not just from the prompt text. If the business already has TPIN, logo, and WhatsApp in its profile, those should appear in the generated site without re-entry.

---

## 7. Business Profile — Data Model

The Business Profile is the foundation the entire strategy is built on. Version 1 should be seeded, not fully built out — it should contain only what is needed to generate and maintain a website.

### Version 1 Schema

| Group | Fields (v1) |
|---|---|
| **Identity** | Business name · Logo · Industry · Description · Location |
| **Contact** | Phone · WhatsApp · Email · Social links |
| **Business Information** | Services · Products · Pricing · Opening hours |
| **Trust** | PACRA registration number · TPIN · Certifications |
| **Media** | Images |

### Current Implementation Mapping

| Business Profile Field | Current Storage Location | Status |
|---|---|---|
| Business name | `growbuilder_sites.name` | ✅ Exists — site-level |
| Logo | `growbuilder_media` | ✅ Exists — per-site media library |
| Industry | `site_templates.industry` | ⚠️ Partial — template-level, not profile-level |
| Description | `growbuilder_sites.description` | ✅ Exists |
| Location | Page section JSON content | ⚠️ Unstructured — embedded in section content, not a first-class field |
| Phone / WhatsApp | `growbuilder_sites` WhatsApp columns | ✅ Exists for WhatsApp; phone in section JSON |
| Email | Page section JSON | ⚠️ Unstructured |
| Social links | Page section JSON | ⚠️ Unstructured |
| Services / Products | `growbuilder_products` table | ✅ Full product entity with variants, stock, pricing |
| Opening hours | Page section JSON | ⚠️ Unstructured |
| PACRA / TPIN | Not stored | ❌ Gap — needs dedicated columns or integration with `organizations.registration_number` / `organizations.tax_number` |
| Certifications | Not stored | ❌ Gap |

### Gap: Structured Business Profile Entity

**The most critical architectural gap.** The Business Profile does not yet exist as a first-class domain entity. Business information is scattered across site columns, section JSON, and the product table. A dedicated `growbuilder_business_profiles` table (or integration with the platform `organizations` table via `CompanyDetailsProvider`) is needed to:

1. Pre-populate AI generation from structured data instead of free-text prompts.
2. Keep the website automatically synchronized when business details change.
3. Power future multi-channel publishing (WhatsApp catalog, Google Business Profile, BizBoost).

### Recommended Resolution

Use the platform's canonical `organizations` table (already extended with `logo_path`, `address`, `phone`, `email`, `website`, `tax_number`, `registration_number` — see AGENTS.md Canonical Company Details) via the `CompanyDetailsProvider` contract, extended with a GrowBuilder-specific `growbuilder_business_profiles` table for fields unique to digital presence (industry category, opening hours JSON, social links JSON, certifications JSON, WhatsApp business config).

---

## 8. Phased Roadmap

### Phase 0 — Foundation

| Deliverable | Status |
|---|---|
| Business Profile v1 schema | ❌ **Gap** — needs dedicated entity (Section 7) |
| Design component system | ✅ Built — `SectionTemplateService` (34.5 KB) with pre-built heroes, features, pricing, footers |
| AI generation workflow | ✅ Built — `AIContentService` (126.8 KB) + `WebsiteGeneratorService` (24.4 KB) |
| Website publishing engine | ✅ Built — `PublishSiteUseCase` + subdomain routing + custom domain DNS |
| Basic analytics event tracking | ✅ Built — `SiteAnalyticsService` + `growbuilder_page_views` table |

### Phase 1 — Pharmacy Digital Presence Product

**Customer-facing pitch**: *"Create your pharmacy website in minutes."*

| Deliverable | Status |
|---|---|
| Pharmacy blueprint | ❌ **Gap** — industry blueprint layer not yet implemented |
| Three AI design choices | ⚠️ Partial — AI generates one site; should generate 3 style variants |
| Product and service catalogue | ✅ Built — full `growbuilder_products` CRUD with variants, images, stock |
| WhatsApp contact | ✅ Built — `WhatsAppCloudService` + click-to-chat + order notifications |
| Location and opening hours | ⚠️ Partial — section JSON, not structured profile fields |
| Mobile money payment links | ✅ Built — `MoMoPaymentGateway` (MTN) + `AirtelMoneyPaymentGateway` (Airtel) |
| Enquiry and outcome tracking | ✅ Built — `growbuilder_form_submissions` + `ChatbotLead` + WhatsApp clicks |

### The Retention Loop

Built into Phase 1, not deferred: a simple monthly summary of enquiries generated plus suggested next actions (add reviews, update prices, upload new photos). This directly addresses the core weakness of one-shot website builders.

| Component | Status |
|---|---|
| Analytics dashboard | ✅ Built — `Analytics.vue` (32.8 KB) with visitor graphs, traffic sources, geolocation |
| Monthly summary email | ❌ **Gap** — no automated digest email |
| AI-powered improvement suggestions | ❌ **Gap** — AI can generate content on demand but does not proactively suggest improvements |

**Success Measure**: Five pharmacies launched, generating real customer enquiries, and returning to improve their sites.

### Phase 2 — MyGrowNet Integration

Connect GrowBuilder to: **BizDocs, StockFlow, GrowFinance, BizBoost.**

| Integration | Status |
|---|---|
| GrowMarket marketplace sync | ✅ Built — `MarketplaceSyncService` + `MarketplaceIntegrationController` |
| StockFlow inventory → product sync | ❌ **Gap** — no direct StockFlow ↔ GrowBuilder product pipeline |
| BizDocs company profile → Business Profile | ⚠️ Partial — `CompanyDetailsProvider` contract exists at platform level |
| GrowFinance invoice/payment → website checkout | ❌ **Gap** |
| BizBoost marketing → website promotions | ❌ **Gap** |

### Phase 3 — AI Business Operations

**The AI Change Engine** — The single highest-value feature identified in this strategy.

> The business owner states a change in plain language — *"we now sell solar panels"* — and the system proposes a cascading set of updates: a new homepage section, a new product category, an announcement, an updated company profile, a generated brochure. One approval applies all of them.

| Component | Status |
|---|---|
| AI smart chat with canvas actions | ✅ Built — `EditorActionService` executes insert/alter/reorder commands from AI chat |
| Cascading multi-entity change proposals | ❌ **Gap** — AI operates on single sections, not cross-entity cascades |
| Business Profile assistant (customer Q&A) | ✅ Built — `AISiteChatbotService` answers visitor questions from site data |
| Ongoing AI recommendations | ❌ **Gap** — proactive recommendation engine not yet built |

---

## 9. Onboarding Model

Onboarding capacity must be sized to actual team capacity, not to an assumed large support organization.

| Customer Range | Approach | Goal |
|---|---|---|
| 1–5 | **High-touch** — hands-on support, starting with Taradasi Medics | Learning: what information is hard for the business to maintain |
| 6–20 | **Assisted** — the system builds most of the site; a person fills gaps | Validate willingness to pay; founder / early pricing |
| 20+ | **Self-service with AI assistance** | The product carries the workload |

---

## 10. Distribution Strategy

Distribution is treated as a product design input, not a marketing activity to figure out after launch.

| Priority | Channel | Notes |
|---|---|---|
| 1 | **Existing relationships** | MyGrowNet / BizDocs / StockFlow customers, especially Taradasi Medics |
| 2 | **Pharmacy network** | Referrals within the vertical, once the first cohort demonstrates real value |
| 3 | **Referral channels** (test, don't assume) | Accountants, PACRA registration agents, business consultants |
| 4 | **Paid acquisition** | Only once conversion economics are actually understood |

---

## 11. Export & Ownership Policy

| Tier | Can Export | Stays Platform-Bound |
|---|---|---|
| **Free** | Business Profile data (JSON/CSV), basic website package | Live AI optimization, analytics, integrations |
| **Paid** | Full website export (HTML/CSS/JS), full content export | Payment workflows, inventory sync, ongoing recommendations |

### Current Implementation Status

| Feature | Status |
|---|---|
| Static HTML/CSS/JS ZIP export | ✅ Built — `StaticExportService` (92.0 KB) + `ExportController` |
| Business Profile JSON/CSV export | ❌ **Gap** — no structured profile export |

---

## 12. Deferred — The "Not Now" List

These ideas are explicitly out of scope until the core product and first vertical are proven:

- AI agent marketplace / multiple specialized agents
- Social media automation
- Mobile applications (responsive mobile-first website covers this)
- Plugin / marketplace ecosystem
- Enterprise features — multi-location management, advanced permissions, SSO
- Industry verticals beyond pharmacy (schools, restaurants, professional services)
- Broad partnership programmes
- A standalone generic chatbot widget (the Business-Profile-aware assistant in Phase 3 is a different, later feature)
- Formal multi-tier pricing — needs real unit-economics work first

---

## 13. Open Questions & Risks

### Unit Economics
Actual hosting and AI cost per tenant is not yet known. Current AI integration uses OpenAI GPT-4/3.5 and Groq endpoints — cost per generation call, monthly AI prompt quotas (tracked in `growbuilder_ai_usage`), and Cloudflare/server hosting costs need to be benchmarked against subscription revenue.

### Onboarding Capacity
Who executes hands-on onboarding for customers 1–20, given other active work — StockFlow, consulting engagements, and MyGrowNet platform foundation work?

### Distribution Channel Validation
Of the four channels in Section 10, only real usage with the first cohort will show which one is worth investing further in.

### Business Profile Source of Truth
Should GrowBuilder's Business Profile be a standalone entity or read entirely from the platform `organizations` table via `CompanyDetailsProvider`? The recommended approach (Section 7) is a hybrid — canonical company details from `organizations`, with GrowBuilder-specific extensions in a dedicated table.

---

## 14. Technical Architecture — Current Production State

### System Overview

```
┌─────────────────────────────────────────────────────────────────────┐
│                        GROWBUILDER MODULE                           │
│                                                                     │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐              │
│  │  33 Controllers│  │ 22 Services  │  │ 42+ Vue Pages│              │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘              │
│         │                  │                  │                      │
│  ┌──────▼──────────────────▼──────────────────▼───────┐             │
│  │              Application Use Cases (13)             │             │
│  │   CreateSite · UpdateSite · PublishSite · SavePage  │             │
│  │   CreateProduct · UpdateProduct · DeleteProduct     │             │
│  └──────────────────────┬──────────────────────────────┘             │
│                         │                                            │
│  ┌──────────────────────▼──────────────────────────────┐             │
│  │                  Domain Layer (DDD)                   │             │
│  │   Entities: Site · Page · Product · Order · Template  │             │
│  │   ValueObjects: SiteId · Subdomain · SitePlan · Money │             │
│  │   Repositories: 5 interface contracts                  │             │
│  └──────────────────────┬──────────────────────────────┘             │
│                         │                                            │
│  ┌──────────────────────▼──────────────────────────────┐             │
│  │              Infrastructure Layer                     │             │
│  │   Eloquent Models (18) · Repositories (5)             │             │
│  │   Payment Gateways: MTN MoMo · Airtel Money           │             │
│  │   AI Providers: OpenAI · Groq · NVIDIA                 │             │
│  └───────────────────────────────────────────────────────┘             │
│                                                                     │
│  Database: 40 migrations · ~30 tables                               │
│  Routes: ~100+ endpoints in routes/growbuilder.php (35.3 KB)        │
└─────────────────────────────────────────────────────────────────────┘
```

### Key File Sizes (Complexity Indicators)

| File | Size | Significance |
|---|---|---|
| `AIContentService.php` | 126.8 KB | Core AI engine — prompts, generation, SEO, chatbot |
| `Settings.vue` | 111.2 KB | Deep site configuration (SEO, domains, theme, analytics) |
| `StaticExportService.php` | 92.0 KB | Full static HTML/CSS/JS ZIP compiler |
| `Dashboard.vue` | 91.7 KB | Main dashboard with site list, analytics, storage |
| `Editor/Index.vue` | 83.0 KB | Visual drag-and-drop canvas editor |
| `Preview/Site.vue` | 163.5 KB | Client-side template renderer for published sites |
| `AIController.php` | 56.4 KB | 32 AI API endpoints |
| `SiteController.php` | 41.4 KB | Site CRUD, dashboard, settings, publishing |
| `routes/growbuilder.php` | 35.3 KB | All route definitions |
| `SectionTemplateService.php` | 34.5 KB | Pre-built section template definitions |

---

## 15. DDD Layer Architecture

```
app/Domain/GrowBuilder/
├── Entities/              Site, Page, Product, Order, Template
├── ValueObjects/          SiteId, PageId, Subdomain, SitePlan, SiteStatus,
│                          OrderStatus, PageContent, TemplateCategory, Theme, Money
├── Repositories/          5 interface contracts
├── Services/              SiteDashboardService, SiteAnalyticsService,
│                          GrowBuilderBillingIntegration, GrowBuilderPaymentService
└── Payment/               PaymentGatewayInterface, PaymentResult

app/Application/GrowBuilder/
├── DTOs/                  CreateSiteDTO, SavePageContentDTO, UpdateSiteDTO
└── UseCases/              CreateSite, UpdateSite, PublishSite, UnpublishSite,
                           SavePageContent, ApplySiteTemplate
                           Product/{Create,Update,Delete,List}ProductUseCase

app/Infrastructure/GrowBuilder/
├── Models/                18 Eloquent models (sites, pages, products, orders, media,
│                          forms, blog, auth, AI, agency)
├── Models/Scopes/         AgencyScope (multi-tenant isolation)
└── Services/              MoMoPaymentGateway, AirtelMoneyPaymentGateway

app/Services/GrowBuilder/  22 domain services (AI, export, editor, analytics,
                           WhatsApp, marketplace, image, storage, quotas)
```

### Repository Bindings (registered in `GrowBuilderServiceProvider`)

| Interface | Implementation |
|---|---|
| `SiteRepositoryInterface` | `EloquentSiteRepository` |
| `PageRepositoryInterface` | `EloquentPageRepository` |
| `ProductRepositoryInterface` | `EloquentProductRepository` |
| `OrderRepositoryInterface` | `EloquentOrderRepository` |
| `TemplateRepositoryInterface` | `EloquentTemplateRepository` |

---

## 16. AI Engine Architecture

GrowBuilder includes a complete AI suite with **32 API endpoints** powering website generation, visual editor assistance, site analysis, and visitor interaction:

### AI Services

| Service | Purpose |
|---|---|
| `AIContentService` (126.8 KB) | Core engine — website generation, section generation, text improvement, color palettes, SEO metadata, testimonial/FAQ generation, multi-language translation |
| `WebsiteGeneratorService` (24.4 KB) | Automated full-site generator using industry prompts and template assembly |
| `AIImageService` | AI image and logo generation for custom site branding |
| `AIReferenceImportService` | Scrapes external URLs and converts them into native GrowBuilder layout JSON |
| `AISiteChatbotService` | Visitor-facing chatbot widget on published sites — real-time Q&A and lead capture |
| `AIUsageService` | Monthly AI prompt quota tracking and enforcement per site/user |
| `EditorSessionContext` | Tracks stateful AI chat context during visual editor sessions |
| `EditorActionService` | Executes canvas actions from AI instructions (insert section, alter text, change background, reorder elements) |

### AI Provider Configuration

| Provider | Models | Usage |
|---|---|---|
| OpenAI | GPT-4, GPT-3.5-turbo | Primary content generation, SEO, and chatbot |
| Groq | LLaMA variants | Fast inference for editor chat and real-time suggestions |
| NVIDIA | Specialized models | Image analysis and reference site conversion |

### AI Endpoint Categories (32 total via `AIController.php`)

- **Site Generation**: `/ai/generate-website`, `/ai/refine-website`, `/ai/publish-generated-website`, `/ai/generate-multi-page`
- **Editor Chat**: `/sites/{siteId}/ai/context`, `/sites/{siteId}/ai/chat` (smart chat with direct canvas action execution)
- **Content**: Text improvement, color suggestions, SEO metadata, FAQ/testimonial generation
- **Visual**: `/ai/generate-image`, `/ai/generate-logo`
- **Reference Import**: `/ai/analyze-reference`, `/ai/convert-reference`
- **Chatbot**: `/gb-chatbot/{siteId}/ask`, `/gb-chatbot/{siteId}/capture-lead`, `/sites/{siteId}/ai/chatbot/leads`

---

## 17. E-Commerce & Payment Architecture

### Product Catalog

Full e-commerce product entity with:
- Name, description, price (ZMW), stock quantity, SKU
- Image gallery via `growbuilder_media`
- Variant support (size, color, etc.)
- Category organization via `growbuilder_product_categories`
- Marketplace sync to GrowMarket via `MarketplaceSyncService`

### Payment Gateways

| Gateway | Implementation | Status |
|---|---|---|
| MTN Mobile Money | `MoMoPaymentGateway` | ✅ Built |
| Airtel Money | `AirtelMoneyPaymentGateway` | ✅ Built |
| Manual / Bank Transfer | Via `growbuilder_payment_settings` | ✅ Built |
| Paystack (Card) | Not yet implemented | ❌ Gap |
| Flutterwave | Not yet implemented | ❌ Gap |

### Order Lifecycle

```
Customer browses → Add to cart → Checkout (Checkout.vue) →
  → Mobile Money payment (MoMo/Airtel) OR Manual payment →
    → Order created (pending) → Payment confirmed (paid) →
      → Fulfilled (shipped) → Completed
```

Tables: `growbuilder_orders`, `growbuilder_payments`, `growbuilder_site_payment_configs`, `growbuilder_site_payment_transactions`.

---

## 18. Agency & White-Label Architecture

GrowBuilder includes a complete **agency management system** for web design agencies managing multiple client sites:

### Agency Tables (12 migrations)

`agencies`, `agency_roles`, `agency_users`, `agency_clients`, `agency_client_contacts`, `agency_client_tags`, `agency_client_tag_map`, `agency_activity_logs`, `agency_client_services`, `agency_client_invoices`, `agency_client_invoice_items`, `agency_client_payments`.

### Agency Features

- **Multi-client dashboard**: `AgencyDashboardController` + `Agency/Dashboard.vue`
- **Client CRUD**: `ClientController` (18.4 KB) + `Clients/{Index,Create,Show,Edit,Analytics}.vue`
- **Service retainers**: `ServiceController` + `Billing/Services.vue`
- **Invoice generation**: `InvoiceController` (17.6 KB) + `Billing/Invoices.vue`
- **Tenant isolation**: `AgencyScope` global Eloquent scope
- **White-label branding removal**: Available on `agency` tier

---

## 19. Subscription Tier Matrix

Defined in `config/modules/growbuilder.php`:

| Feature | Free (K0) | Starter (K149/mo) | Business (K399/mo) | Agency (K999/mo) |
|---|---|---|---|---|
| Sites | 1 | 1 | 3 | 20 |
| Storage | 500 MB | 1 GB | 2 GB | 10 GB |
| Products | 0 | 20 | Unlimited | Unlimited |
| AI prompts/month | 50 | 100 | Unlimited | Unlimited (priority) |
| Custom domain | ❌ | ✅ | ✅ | ✅ |
| E-commerce | ❌ | ✅ | ✅ | ✅ |
| Payment gateways | ❌ | Manual only | Full | Full |
| AI section generator | ❌ | ✅ | ✅ | ✅ |
| Static export | ❌ | ❌ | ✅ | ✅ |
| Analytics | Basic | Basic | Full | Full |
| Remove branding | ❌ | ❌ | ✅ | ✅ |
| Team members | 1 | 1 | 5 | 20 |
| White label | ❌ | ❌ | ❌ | ✅ |

---

## 20. Cross-Module Integration Architecture

### Current Integrations

| Integration | Direction | Service | Status |
|---|---|---|---|
| GrowMarket (marketplace) | GrowBuilder → GrowMarket | `MarketplaceSyncService` | ✅ Built |
| Platform Organizations | Organizations → GrowBuilder | `CompanyDetailsProvider` contract | ⚠️ Partial |
| Platform Auth (SSO) | Identity Gateway → GrowBuilder | `identity.redirect` middleware | ✅ Built |

### Planned Integrations (Phase 2)

| Integration | Direction | Value Proposition |
|---|---|---|
| **StockFlow → GrowBuilder** | Inventory → Product catalog | Real-time stock levels on website; auto-disable out-of-stock items |
| **BizDocs → Business Profile** | Company docs → Trust section | PACRA certificate, TPIN verification badge on website |
| **GrowFinance → Checkout** | Invoicing → Payment | Generate invoices from website orders; reconcile payments |
| **BizBoost → Website** | Campaigns → Promotions | Publish BizBoost promotional banners and discount codes on website |

---

## 21. Gap Analysis — Strategy vs. Current Implementation

### Critical Gaps (Must Build)

| Gap | Strategic Importance | Effort |
|---|---|---|
| **Structured Business Profile entity** | Foundation of entire strategy — without it, AI generates from prompts instead of data | Medium |
| **Industry Blueprint layer** | Pharmacy-first vertical strategy requires blueprint abstraction | Medium |
| **Monthly retention digest email** | Core retention loop — prevents one-shot churn | Small |
| **AI improvement suggestions** | Proactive "your site could improve" notifications | Medium |

### Important Gaps (Should Build in Phase 1–2)

| Gap | Strategic Importance | Effort |
|---|---|---|
| 3-variant AI style recommendations | Better onboarding UX — choice builds confidence | Small |
| StockFlow ↔ product sync pipeline | Phase 2 differentiator — live inventory on website | Medium |
| BizDocs company → Business Profile | Platform integration story — PACRA/TPIN trust badges | Small |
| Business Profile JSON/CSV export | Trust and ownership policy compliance | Small |
| Paystack/Flutterwave card payments | Broader payment coverage beyond mobile money | Medium |

### Deferred Gaps (Phase 3+)

| Gap | Notes |
|---|---|
| AI Change Engine (cascading multi-entity updates) | Highest-value Phase 3 feature — depends on Business Profile |
| Proactive AI recommendation engine | Needs analytics baseline from Phase 1 retention loop |
| Multi-channel publishing (WhatsApp catalog, Google Business) | Depends on structured Business Profile |

---

## 22. Legal, Financial & Compliance Policies

### Website Ownership & Intellectual Property

- **Content Ownership**: All content created by a business owner (text, images, products) remains the property of the business owner. GrowBuilder is licensed to host and serve that content.
- **Template License**: Pre-built templates and design components are licensed for use within GrowBuilder. They are not transferable outside the platform except via the Static Export feature on paid tiers.
- **AI-Generated Content**: Content generated by AI services is owned by the business owner who commissioned its generation.

### Data Protection (Zambia DPA 2021 & GDPR)

- **Site Visitor Data**: Page view analytics, form submissions, and chatbot interactions are stored per-site with tenant isolation via `AgencyScope`.
- **Business Owner Data**: Business Profile information, payment credentials, and account data are protected under the Zambian Data Protection Act No. 4 of 2021.
- **Cross-Border**: Where sites use Cloudflare CDN for global delivery, data processing agreements guarantee equivalent protection levels.

### Payment Compliance

- **PCI-DSS**: GrowBuilder does not store raw card numbers. Mobile money integrations (MTN MoMo, Airtel Money) use provider-hosted payment flows.
- **ZRA Tax Compliance**: Subscription fees are subject to 16% Zambian VAT where applicable. Agency invoicing features include tax line item support.

### Acceptable Use Policy

Sites hosted on GrowBuilder shall not publish:
- Content that violates Zambian law or international sanctions
- Counterfeit pharmaceutical product listings (critical for pharmacy vertical)
- Malicious code, phishing pages, or unauthorized data collection
- Content advocating hate speech, violence, or illegal activities

---

## 23. Deployment & Infrastructure Topology

### Component Matrix

| Component | Location | Technology |
|---|---|---|
| **Application Server** | DigitalOcean Droplet (138.197.187.134) | Laravel 13 / PHP 8.3 |
| **Frontend** | Compiled Vue 3 SPA via Inertia.js | Vite build → `public/build/` |
| **Database** | Same droplet (production) | MySQL / SQLite (dev) |
| **AI Providers** | External APIs | OpenAI, Groq, NVIDIA |
| **CDN & DNS** | Cloudflare | Edge caching, SSL, custom domain routing |
| **File Storage** | Local / S3-compatible | Media library, exports |
| **Payment Gateways** | External APIs | MTN MoMo API, Airtel Money API |
| **WhatsApp** | Meta Cloud API | Order notifications, click-to-chat |

### Subdomain Routing

```
growbuilder.mygrownet.com     → GrowBuilder dashboard & editor
{subdomain}.mygrownet.com     → Published GrowBuilder site (tenant)
{custom-domain.com}           → CNAME → Published GrowBuilder site
```

### Deployment Rules

1. **NEVER run `npm run build` on production** — build locally, deploy separately
2. Clear caches after pull: `php artisan route:clear && php artisan config:clear && php artisan cache:clear && php artisan route:cache && php artisan config:cache`
3. Run pending migrations: `php artisan migrate --path=database/migrations/growbuilder`

---

## Strategic Distinction (Central to This Entire Document)

> **GrowBuilder Consumer Site**: a destination where customers discover a business and make enquiries or purchases.
>
> **GrowBuilder Platform**: infrastructure that lets a business owner create and operate that destination using AI, structured data, and MyGrowNet integrations.

This distinction should remain central to architecture, product positioning, database design, permissions, APIs, and financial architecture at every phase.

---

## 24. SEO & Discoverability Architecture

For Zambian SMEs, the most valuable traffic source is **Google Search** — a customer searching "pharmacy near me Lusaka" or "dental clinic Kitwe." GrowBuilder must make every published site search-engine-ready without requiring the business owner to understand SEO.

### Auto-Generated SEO (Already Built)

`AIContentService` already generates meta titles, descriptions, and Open Graph tags. The gap is **local SEO structured data**.

### Local SEO Strategy (Gap)

| Feature | Status | Priority |
|---|---|---|
| Auto-generated `<title>` and `<meta description>` | ✅ Built — `AIContentService` | — |
| Open Graph / Twitter Card meta tags | ✅ Built — `Settings.vue` SEO section | — |
| **JSON-LD LocalBusiness schema markup** | ❌ Gap | High |
| **Google Business Profile sync** | ❌ Gap — Phase 2+ | Medium |
| **Sitemap.xml auto-generation** | ❌ Gap | High |
| **robots.txt per-site** | ❌ Gap | Small |
| Canonical URL management | ⚠️ Partial — custom domain vs subdomain | Small |

### JSON-LD LocalBusiness Schema

Every published GrowBuilder site should automatically emit structured data from the Business Profile:

```json
{
  "@context": "https://schema.org",
  "@type": "Pharmacy",
  "name": "Taradasi Medics",
  "address": { "@type": "PostalAddress", "addressLocality": "Lusaka", "addressCountry": "ZM" },
  "telephone": "+260977123456",
  "openingHours": "Mo-Fr 08:00-18:00, Sa 08:00-13:00",
  "paymentAccepted": "MTN Mobile Money, Airtel Money, Cash",
  "priceRange": "$$"
}
```

This is a **direct function of the Business Profile** — another reason the structured profile entity is the critical gap.

---

## 25. Progressive Web App (PWA) & Offline Strategy

### Current Implementation

`ManifestController` already generates dynamic PWA manifests per site. Published GrowBuilder sites can be "installed" on mobile home screens.

### What Needs Improvement

| Feature | Status |
|---|---|
| Dynamic `manifest.json` generation | ✅ Built — `ManifestController` |
| Service worker for offline caching | ⚠️ Partial — `Offline.vue` exists but service worker registration needs verification |
| Push notification support | ❌ Gap — deferred to Phase 2+ |
| App-like navigation (no browser chrome) | ✅ Built — `display: standalone` in manifest |

### Strategic Value

For Zambian users on intermittent 3G/4G connections, offline caching of the business's core information (name, products, contact, location) is a genuine differentiator against global site builders that assume always-on broadband.

---

## 26. Performance & Bandwidth Optimization

### Mobile-First Performance Budget

Target: **First Contentful Paint < 2s on 3G** for published GrowBuilder sites.

| Optimization | Status |
|---|---|
| Image auto-compression (WebP) | ✅ Built — `ImageOptimizationService` |
| Lazy-loading images | ✅ Built — renderer uses `loading="lazy"` |
| Critical CSS inlining | ❌ Gap — static export includes full CSS bundles |
| Edge caching via Cloudflare | ✅ Built — Cloudflare CDN configured |
| Minified static export | ⚠️ Partial — `StaticExportService` bundles assets but doesn't minify |
| Data-saver mode for low-bandwidth | ❌ Gap — could reduce image quality on metered connections |

---

## 27. Multi-Language & i18n Strategy

### Current Capability

`AIContentService` already includes multi-language translation capabilities. This is valuable for Zambia (English, Bemba, Nyanja, Tonga) and critical for SADC expansion (Portuguese for Mozambique, French for DRC).

### Strategy

| Phase | Scope |
|---|---|
| Phase 1 | English-only generated content (Zambia primary market) |
| Phase 2 | AI-powered translation to Bemba/Nyanja for key sections (contact, products, hours) |
| SADC expansion | French, Portuguese, Swahili translation from Business Profile |

Multi-language is another feature that **gets stronger as AI improves** — it passes the strategic test in Section 2.

---

## 28. Analytics KPIs & Retention Metrics

The retention loop (Section 8, Phase 1) needs defined KPIs. Without measurable outcomes, the "monthly summary" becomes noise.

### Tier 1 — Business Outcome Metrics (What the owner cares about)

| Metric | Source | Status |
|---|---|---|
| **Enquiries received** (forms + WhatsApp clicks + calls) | `growbuilder_form_submissions` + `ChatbotLead` + WhatsApp click events | ✅ Tracked |
| **Orders placed** | `growbuilder_orders` | ✅ Tracked |
| **Revenue generated** | `growbuilder_payments` | ✅ Tracked |
| **Enquiry-to-order conversion rate** | Computed | ❌ Gap — not surfaced |

### Tier 2 — Site Health Metrics (What the platform watches)

| Metric | Source | Status |
|---|---|---|
| Unique visitors / month | `growbuilder_page_views` | ✅ Tracked |
| Bounce rate | Page view session analysis | ⚠️ Partial |
| Top referral sources | `SiteAnalyticsService` | ✅ Tracked |
| Mobile vs desktop split | Device detection | ✅ Tracked |
| **Site freshness score** (days since last edit) | `growbuilder_sites.updated_at` | ❌ Gap — key retention trigger |
| **AI usage rate** (prompts used / quota) | `growbuilder_ai_usage` | ✅ Tracked |

### Monthly Digest Email Content

```
📊 Your monthly business report for Taradasi Medics (July 2026)

✅ 47 website visitors (+12% from June)
✅ 8 WhatsApp enquiries received
✅ 3 orders placed (K4,200 revenue)
✅ Top referral: Google Search (62%)

💡 Suggested improvements:
  → Add customer reviews (sites with reviews get 35% more enquiries)
  → Update your product prices (last updated 45 days ago)
  → Upload a new photo of your storefront
```

---

## 29. Security Architecture

### Published Site Security

| Control | Status |
|---|---|
| TLS 1.3 via Cloudflare SSL | ✅ Active |
| Content Security Policy (CSP) headers | ❌ Gap — should be set per published site |
| Rate limiting on form submissions | ⚠️ Partial — Laravel throttle middleware |
| Spam filtering on contact forms | ✅ Built — `FormSubmissionController` |
| DDoS protection via Cloudflare | ✅ Active |
| XSS prevention in user-generated content | ⚠️ Partial — section JSON is sanitized but custom HTML injection (header code) is raw |

### Platform Security

| Control | Status |
|---|---|
| Centralized SSO via Identity Gateway | ✅ Active |
| RBAC for site team members | ✅ Built — `SiteRoleService` + `site_roles` / `site_permissions` |
| Agency tenant isolation | ✅ Built — `AgencyScope` global Eloquent scope |
| Payment credential encryption | ✅ — stored via `growbuilder_payment_settings` with Laravel encryption |
| Audit logging | ✅ Built — `ActivityLogger` + `agency_activity_logs` |

---

## 30. Physical-to-Digital Bridge — QR Code & Offline Attribution

For offline businesses (the primary target market), the website is only valuable if customers can find it. The physical-to-digital bridge is how a pharmacy window sticker, a business card, or a flyer connects to the digital presence.

### QR Code Engine (Gap)

| Feature | Status | Priority |
|---|---|---|
| Auto-generated QR code linking to published site | ❌ Gap | High (Phase 1) |
| Downloadable QR code for print (PNG/SVG/PDF) | ❌ Gap | High |
| UTM-tagged QR URLs for attribution | ❌ Gap | Medium |
| QR code on GrowBuilder dashboard for quick sharing | ❌ Gap | High |
| WhatsApp share link with site preview | ⚠️ Partial — WhatsApp click-to-chat exists but not a shareable site link |

### Why This Matters

The customer journey for a Zambian pharmacy is not `Google → Website`. It is:

```
Window sticker with QR code → Customer scans → Website → WhatsApp order → Mobile money payment
```

or:

```
Business card with QR code → Customer scans → Product catalog → Enquiry form → Follow-up call
```

The QR code is the **entry ramp** for businesses whose customers are physically present. Without it, the website exists in a digital vacuum that the target customer never discovers. This is a differentiator that global AI site builders have no incentive to optimize for.

### BizBoost Integration Opportunity

QR codes with UTM attribution feed directly into BizBoost's physical attribution engine (Dynamic QR Code engine from `BIZBOOST_PLATFORM.md` Section 22). GrowBuilder generates the QR; BizBoost tracks the conversion.

