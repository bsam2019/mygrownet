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
24. [SEO & Discoverability Architecture](#24-seo--discoverability-architecture)
25. [Progressive Web App (PWA) & Offline Strategy](#25-progressive-web-app-pwa--offline-strategy)
26. [Performance & Bandwidth Optimization](#26-performance--bandwidth-optimization)
27. [Multi-Language & i18n Strategy](#27-multi-language--i18n-strategy)
28. [Analytics KPIs & Retention Metrics](#28-analytics-kpis--retention-metrics)
29. [Security Architecture](#29-security-architecture)
30. [Physical-to-Digital Bridge — QR Code & Offline Attribution](#30-physical-to-digital-bridge--qr-code--offline-attribution)
31. [Template Versioning, Non-Destructive Upgrade & Rollback](#31-template-versioning-non-destructive-upgrade--rollback-architecture)
32. [Static Site Generation (SSG) & CDN-First Serving](#32-static-site-generation-ssg--cdn-first-serving-architecture)
33. [Infrastructure Scaling Roadmap](#33-infrastructure-scaling-roadmap)
34. [Page Revision History & Editor Undo](#34-page-revision-history--editor-undo-architecture)
35. [Architecture Evolution Summary & Stability Grading](#35-architecture-evolution-summary--stability-grading)
36. [AI-Proof Resilience Guarantee & Strategic Pillars](#36-ai-proof-resilience-guarantee--strategic-pillars)

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

### Critical Gaps (Must Build — Ordered by Impact)

| # | Gap | Strategic Importance | Effort | Section |
|---|---|---|---|---|
| 1 | **Static Site Generation (SSG) for published sites** | #1 long-term stability risk — solves SEO, 3G performance, server cost, and scaling in one change. Current client-side rendering (`Site.vue` 163.5 KB) is incompatible with the mobile-first, SEO-dependent Zambian market strategy. | Large | §32 |
| 2 | **Structured Business Profile entity** | Foundation of entire strategy — without it, AI generates from prompts instead of structured data | Medium | §7 |
| 3 | **Industry Blueprint layer** | Pharmacy-first vertical strategy requires blueprint abstraction | Medium | §8 |
| 4 | **Page revision history** | No undo capability in editor — destructive edits are permanent. Table stakes for any serious content editor. | Medium | §34 |
| 5 | **Monthly retention digest email** | Core retention loop — prevents one-shot churn | Small | §28 |
| 6 | **AI improvement suggestions** | Proactive "your site could improve" notifications | Medium | §28 |

### Important Gaps (Should Build in Phase 1–2)

| Gap | Strategic Importance | Effort |
|---|---|---|
| JSON-LD LocalBusiness schema markup | Critical for local SEO — "pharmacy near me Lusaka" | Small |
| Sitemap.xml auto-generation | SEO baseline requirement for Google indexing | Small |
| QR code auto-generation + print download | Physical-to-digital bridge — the real customer entry ramp for offline businesses | Small |
| 3-variant AI style recommendations | Better onboarding UX — choice builds confidence | Small |
| StockFlow ↔ product sync pipeline | Phase 2 differentiator — live inventory on website | Medium |
| BizDocs company → Business Profile | Platform integration story — PACRA/TPIN trust badges | Small |
| Business Profile JSON/CSV export | Trust and ownership policy compliance | Small |
| Paystack/Flutterwave card payments | Broader payment coverage beyond mobile money | Medium |
| Content Security Policy (CSP) headers | Published site security hardening | Small |

### Infrastructure Gaps (Phase 2–3, Plan Now)

| Gap | Trigger Point | Section |
|---|---|---|
| CDN-first serving for published sites | After SSG engine is built | §32 |
| Database read replica for analytics | 50+ tenants | §33 |
| Managed database migration | 200+ tenants or first SLA commitment | §33 |
| Horizontal scaling (app server) | After CDN offloads published site traffic | §33 |

### Deferred Gaps (Phase 3+)

| Gap | Notes |
|---|---|
| AI Change Engine (cascading multi-entity updates) | Highest-value Phase 3 feature — depends on Business Profile |
| Proactive AI recommendation engine | Needs analytics baseline from Phase 1 retention loop |
| Multi-channel publishing (WhatsApp catalog, Google Business) | Depends on structured Business Profile |
| Template versioning & non-destructive upgrades | Depends on SSG + Business Profile being stable (§31) |

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

### Component Matrix — Current (Phase 0–1)

| Component | Location | Technology |
|---|---|---|
| **Application Server** | DigitalOcean Droplet (138.197.187.134) | Laravel 13 / PHP 8.3 |
| **Frontend (Dashboard/Editor)** | Compiled Vue 3 SPA via Inertia.js | Vite build → `public/build/` |
| **Frontend (Published Sites)** | Client-side rendered via Inertia (⚠️ see §32) | `Site.vue` (163.5 KB) renders JSON |
| **Database** | Same droplet (production) | MySQL / SQLite (dev) |
| **AI Providers** | External APIs | OpenAI, Groq, NVIDIA |
| **CDN & DNS** | Cloudflare | Edge caching, SSL, custom domain routing |
| **File Storage** | Local / S3-compatible | Media library, exports |
| **Payment Gateways** | External APIs | MTN MoMo API, Airtel Money API |
| **WhatsApp** | Meta Cloud API | Order notifications, click-to-chat |

### Component Matrix — Target (Phase 2+, after SSG)

| Component | Location | Technology |
|---|---|---|
| **Application Server** | DigitalOcean Droplet(s) | Laravel 13 / PHP 8.3 |
| **Frontend (Dashboard/Editor)** | Compiled Vue 3 SPA via Inertia.js | Vite build → `public/build/` |
| **Frontend (Published Sites)** | **Cloudflare CDN edge (static HTML)** | Pre-rendered by SSG engine (§32) |
| **Database** | Managed MySQL + read replica | Analytics offloaded to replica |
| **AI Providers** | External APIs via abstraction layer | Swappable provider config |
| **CDN & DNS** | Cloudflare Pages / R2 | Static site hosting + media |
| **File Storage** | Cloudflare R2 or S3 | Media library, exports, static builds |

### Subdomain Routing — Current

```
growbuilder.mygrownet.com     → GrowBuilder dashboard & editor (dynamic, Inertia)
{subdomain}.mygrownet.com     → Published GrowBuilder site (dynamic, Inertia — ⚠️ inefficient)
{custom-domain.com}           → CNAME → Published GrowBuilder site (dynamic)
```

### Subdomain Routing — Target (after SSG)

```
growbuilder.mygrownet.com     → GrowBuilder dashboard & editor (dynamic, Inertia)
{subdomain}.mygrownet.com     → Cloudflare CDN → Static HTML (zero server load)
{custom-domain.com}           → CNAME → Cloudflare CDN → Static HTML (zero server load)
```

### Deployment Rules

1. **NEVER run `npm run build` on production** — build locally, deploy separately
2. Clear caches after pull: `php artisan route:clear && php artisan config:clear && php artisan cache:clear && php artisan route:cache && php artisan config:cache`
3. Run pending migrations: `php artisan migrate --path=database/migrations/growbuilder`
4. **After SSG implementation**: Published site deploys are triggered by `php artisan growbuilder:build-site {siteId}` or automatically on publish/update

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

---

## 31. Template Versioning, Non-Destructive Upgrade & Rollback Architecture

### The Problem

A business publishes a site using Template v1 in August 2026. In October 2026, GrowBuilder releases Template v2 with a better mobile layout, a new "Customer Reviews" section, improved performance, and updated design tokens. **How does the existing business owner upgrade without losing their custom content — text, images, products, Business Profile data, and CSS overrides?**

Without a solution, published sites become frozen artifacts that drift further from the platform's evolving capabilities. Over time, this creates a two-tier user base: recent sites look modern and feature-complete, while early adopters are stuck on obsolete templates. That dynamic punishes loyalty and undermines the retention loop.

### Architecture: Separation of Concerns

GrowBuilder's JSON-based section architecture makes non-destructive upgrades possible because the template and content operate at different layers:

```
TEMPLATE LAYER (platform-owned, upgradeable)     CONTENT LAYER (user-owned, preserved)
──────────────────────────────────────────────    ──────────────────────────────────────
• Section component definitions                  • Section text, headings, descriptions
• Section ordering / page structure              • Images, logos, uploaded media
• Design tokens (colors, fonts, spacing)         • Products, prices, stock levels
• Responsive breakpoints                         • Business Profile structured data
• New feature sections (reviews, chatbot)         • Custom CSS overrides
• Performance optimizations (lazy load, WebP)     • Form field configurations
• SEO structure (JSON-LD, sitemap patterns)       • WhatsApp / contact settings
```

**Rule**: A template upgrade replaces the template layer while preserving the content layer. Content is mapped into the new structure by matching section types and field keys.

### Data Model

#### Template Version Tracking

```
growbuilder_sites
├── template_id          (FK → site_templates.id)
├── template_version     (integer — which version was applied)
├── template_locked      (boolean — if true, skip upgrade notifications)
└── last_template_sync   (timestamp — when template was last applied/upgraded)

site_templates
├── id
├── current_version      (integer — latest published version)
├── version_history      (JSON — array of version records with changelog)
└── upgrade_strategy     (enum: 'merge', 'replace', 'manual')
```

#### Site Version Snapshots (Rollback Safety Net)

```
growbuilder_site_snapshots
├── id
├── site_id              (FK → growbuilder_sites.id)
├── snapshot_type         (enum: 'pre_upgrade', 'manual', 'auto_backup')
├── pages_json           (LONGTEXT — full serialized page sections JSON)
├── design_tokens_json   (JSON — colors, fonts, spacing at time of snapshot)
├── metadata             (JSON — template_version, trigger, user notes)
├── expires_at           (timestamp — auto-cleanup after 90 days)
├── created_at
```

### The Upgrade Flow

#### Step 1: Detect & Notify

When a template's `current_version` is incremented, every site using that template where `site.template_version < template.current_version` receives:

- An in-app notification (via the existing `NotificationModel` infrastructure): *"A template update is available for your site. New features: Customer Reviews section, improved mobile layout."*
- A dashboard badge: **🔄 Update Available** on the site card in `Dashboard.vue`.
- Optionally, a mention in the monthly retention digest email (Section 28).

#### Step 2: Preview (Side-by-Side Comparison)

The user clicks **"Preview Update"** which:

1. Creates a `pre_upgrade` snapshot of the current site state.
2. Generates a temporary preview applying the new template version to the existing content.
3. Renders a **split-screen comparison**:
   - Left: Current site (live)
   - Right: Updated site (preview)
4. Highlights what's new (new sections, visual changes) and what content migrated automatically.

#### Step 3: Selective Adoption

Not every template change needs to be all-or-nothing. The upgrade UI presents toggleable options:

```
┌─────────────────────────────────────────────────────────┐
│  Template Update v2.0 — What's New                      │
│                                                         │
│  ☑ Updated design tokens (new color palette, fonts)     │
│  ☑ Improved mobile responsive layout                    │
│  ☑ New section: Customer Reviews                        │
│  ☐ New section: Instagram Feed (requires API key)       │
│  ☑ Performance: lazy-loaded images, WebP auto-convert   │
│  ☑ SEO: JSON-LD LocalBusiness schema added              │
│                                                         │
│  ⚠ Your custom CSS overrides will be preserved.         │
│  ⚠ Your product catalog is unchanged.                   │
│                                                         │
│  [Apply Selected Updates]     [Keep Current Version]    │
└─────────────────────────────────────────────────────────┘
```

#### Step 4: Content Migration Engine

The migration engine maps existing user content into the new template structure:

```php
// Pseudocode for content migration
foreach ($existingPage->sections as $section) {
    $newSection = $newTemplate->findMatchingSection($section->type);
    
    if ($newSection) {
        // Same section type exists in new template — merge content into new structure
        $newSection->mergeContent($section->userContent);
    } else {
        // Section type removed from new template — preserve as custom section
        $migratedPage->appendCustomSection($section);
    }
}

// Add new sections from template that didn't exist before
foreach ($newTemplate->newSections as $section) {
    if ($userSelectedThisSection) {
        $migratedPage->insertAtRecommendedPosition($section);
    }
}
```

**Key Rules:**
- **Text content** is never overwritten — it maps by section-type and field-key.
- **Images** are never replaced — user-uploaded media stays in place.
- **Products and Business Profile data** are untouched — they live in their own tables.
- **Custom CSS** is appended after the new template CSS, preserving overrides.
- **Sections that exist in the old template but not the new** are preserved as "custom sections" at the bottom of the page.
- **New sections** are inserted at positions recommended by the template, with default content that the user can customize.

#### Step 5: Rollback (30-Day Safety Window)

After applying an upgrade, the user sees:

```
✅ Template updated to v2.0 — applied 4 of 5 available changes.
   Undo this update within 30 days → [Rollback to Previous Version]
```

Clicking rollback restores the `pre_upgrade` snapshot. After 30 days (configurable), snapshots are auto-cleaned by a scheduled command.

### Automatic vs. Manual Upgrades

| Upgrade Type | When to Use | Example |
|---|---|---|
| **Silent / automatic** | Non-breaking performance and SEO improvements that don't change visible layout | WebP image optimization, JSON-LD injection, `<meta>` tag updates |
| **Notified / opt-in** | Design changes, new sections, layout restructuring | New hero layout, customer reviews section, color palette refresh |
| **Manual only** | Breaking changes that require content re-mapping | Complete template redesign, section type deprecation |

The `upgrade_strategy` field on `site_templates` controls which mode applies:
- `merge`: Automatic content migration with user notification
- `replace`: Full template replacement with preview required
- `manual`: User must manually rebuild (rare, only for complete redesigns)

### Notification Integration

Template update notifications use the same notification infrastructure as policy updates:

```php
// In a TemplateVersionService or Artisan command
NotificationModel::create([
    'id'              => (string) Str::uuid(),
    'notifiable_type' => User::class,
    'notifiable_id'   => $siteOwner->id,
    'module'          => 'growbuilder',
    'type'            => 'template_update',
    'category'        => 'product',
    'title'           => "Template update available for {$site->name}",
    'message'         => "Version {$newVersion}: {$changelog}",
    'action_url'      => route('growbuilder.sites.settings', $site->id) . '#template-update',
    'action_text'     => 'Preview Update',
    'priority'        => 'normal',
    'created_at'      => now(),
]);
```

### Current Implementation Status

| Component | Status |
|---|---|
| Template versioning on `site_templates` | ❌ Gap — no `current_version` or `version_history` columns |
| Site-level template version tracking | ❌ Gap — no `template_version` column on `growbuilder_sites` |
| Site snapshot/rollback system | ❌ Gap — no `growbuilder_site_snapshots` table |
| Content migration engine | ❌ Gap — `ApplySiteTemplateUseCase` applies templates to new sites but doesn't merge existing content |
| Split-screen preview UI | ❌ Gap |
| Template update notification | ❌ Gap — notification infrastructure exists, just needs wiring |
| Silent SEO/performance upgrades | ❌ Gap — could be implemented server-side without UI changes |

### Strategic Alignment

This feature passes the AI advancement test from Section 2:

> **Does it get stronger as AI improves?** Yes — AI can power smarter content migration (understanding which content maps where), generate better changelogs, and eventually auto-suggest template improvements based on analytics data. A generic AI tool cannot do this because it doesn't have the template version history or the structured content mapping.

---

## 32. Static Site Generation (SSG) & CDN-First Serving Architecture

### Why This Is the #1 Architectural Priority

Published GrowBuilder sites are currently rendered **client-side** — the browser downloads `Site.vue` (163.5 KB) plus the Vue 3 runtime, then executes JavaScript to parse the page's JSON sections and render them into HTML. This architecture has four compounding problems that worsen as the platform grows:

| Problem | Impact | Severity |
|---|---|---|
| **SEO** | Google can index JS-rendered content but treats it as second-class. For "pharmacy near me Lusaka" — the primary acquisition channel — static HTML ranks faster and more reliably. | Critical |
| **Performance on 3G** | A Zambian customer on MTN 3G waits for ~200KB+ of JavaScript to download and execute before seeing anything. The Section 26 target (FCP < 2s on 3G) is nearly impossible with client-side rendering. | Critical |
| **Server load** | Every published page view hits the Laravel backend to serve the Inertia response. 100 sites × 50 visitors/day = 5,000 dynamic requests daily that could be zero. | High |
| **Hosting cost** | Server costs scale linearly with published-site traffic. Static sites on CDN cost nearly nothing. | High |

### The Architecture

```
┌──────────────────────────────────────────────────────────────────────────┐
│                     CURRENT: CLIENT-SIDE RENDERING                      │
│                                                                          │
│  Visitor → DNS → DigitalOcean Droplet → Laravel → Inertia → Vue SSR     │
│           → Browser downloads 200KB+ JS → Parses JSON → Renders HTML    │
│                                                                          │
│  Problems: Slow FCP, poor SEO, server load per visit, linear cost       │
└──────────────────────────────────────────────────────────────────────────┘
                                    ↓
┌──────────────────────────────────────────────────────────────────────────┐
│                     TARGET: STATIC SITE GENERATION                       │
│                                                                          │
│  Owner publishes/updates → SSG Engine compiles static HTML/CSS/JS        │
│           → Deploys to Cloudflare CDN / Pages / R2                       │
│                                                                          │
│  Visitor → DNS → Cloudflare Edge (< 50ms) → Pre-rendered static HTML     │
│           → Zero server load, perfect SEO, instant FCP                   │
│                                                                          │
│  Benefits: < 1s FCP on 3G, full SEO, zero server load, near-zero cost   │
└──────────────────────────────────────────────────────────────────────────┘
```

### What Gets Statically Generated vs. What Stays Dynamic

| Component | Rendering | Why |
|---|---|---|
| Published site pages (Home, About, Services, etc.) | **Static HTML on CDN** | Read-only content that changes infrequently |
| Product catalog pages | **Static HTML on CDN** | Regenerated when products change |
| Contact forms | **Static form + API endpoint** | Form HTML is static; submission POSTs to a serverless function or Laravel API |
| AI chatbot widget | **Client-side JS embed** | Small JS widget loaded async, doesn't block page render |
| E-commerce checkout | **Dynamic (Laravel/Inertia)** | Payment flows require server-side session and security |
| Site dashboard & editor | **Dynamic (Laravel/Inertia)** | Interactive, authenticated — no change |
| Analytics tracking | **Lightweight JS pixel** | Async, non-blocking |

### Build Engine Architecture

`StaticExportService` (92.0 KB) already compiles GrowBuilder sites into downloadable HTML/CSS/JS ZIP packages. The SSG engine is an evolution of this same service:

```php
// Pseudocode for the SSG build pipeline
class StaticSiteBuilder
{
    public function build(Site $site): BuildResult
    {
        // 1. Resolve Business Profile
        $profile = $this->profileRepo->findBySite($site);
        
        // 2. Compile each published page to static HTML
        foreach ($site->publishedPages() as $page) {
            $html = $this->renderer->renderToHtml($page, $profile, $site->designTokens);
            $html = $this->injectSeo($html, $profile);      // JSON-LD, meta, canonical
            $html = $this->injectAnalytics($html, $site);    // Tracking pixel
            $html = $this->injectChatbot($html, $site);      // Async chatbot widget
            $this->outputStore->write($site->subdomain, $page->slug, $html);
        }
        
        // 3. Generate auxiliary files
        $this->generateSitemap($site);
        $this->generateRobotsTxt($site);
        $this->generateManifest($site);                      // PWA manifest
        
        // 4. Optimize assets
        $this->optimizeImages($site);                        // WebP, resize
        $this->minifyCss($site);                             // Critical CSS inline
        
        // 5. Deploy to CDN
        $this->deployer->deploy($site->subdomain, $this->outputStore);
        
        return new BuildResult(success: true, url: $site->publicUrl());
    }
}
```

### Build Triggers

| Trigger | When | Rebuild Scope |
|---|---|---|
| `PublishSiteUseCase` | Owner clicks "Publish" | Full site rebuild |
| `SavePageContentUseCase` | Owner saves a page edit (on published site) | Single page rebuild + sitemap |
| Business Profile update | Owner changes phone, hours, address | Full site rebuild (structured data changes) |
| Product CRUD | Owner adds/edits/deletes product | Product pages + catalog page |
| Template upgrade (§31) | Owner applies template update | Full site rebuild |
| `php artisan growbuilder:build-site {id}` | Manual / CI trigger | Full site rebuild |
| `php artisan growbuilder:rebuild-all` | Platform-wide rebuild (e.g., after global SEO improvement) | All published sites |

### CDN Deployment Options & Cost Analysis

> **Key point: SSG costs nothing extra.** Phase 1 runs on your existing droplet and Cloudflare free tier. Phase 2 uses Cloudflare Pages/R2 free tiers. Paid Cloudflare tiers only become relevant at 100+ tenants, where subscription revenue far exceeds the cost.

#### Option 1: Self-Hosted Nginx (Phase 1 — Recommended Start)

**Extra cost: $0.** Your droplet already runs Nginx. SSG compiles HTML to disk; Nginx serves static files directly — no PHP, no Laravel, no new infrastructure.

```nginx
# Nginx config for serving GrowBuilder static sites
# Added to existing server block — no new services needed

# Published GrowBuilder sites: serve static HTML from disk
location ~* ^/sites/(?<subdomain>[a-z0-9-]+)/(?<page>.*)$ {
    root /var/www/growbuilder-sites/$subdomain;
    try_files /$page /index.html =404;
    expires 1h;                          # Cloudflare free tier caches this
    add_header Cache-Control "public";
    add_header X-Served-By "static";
}
```

Since you already use Cloudflare for DNS, Cloudflare's **free tier** automatically caches these static responses at edge locations worldwide. Visitors in Lusaka hit the Cloudflare PoP in Johannesburg (~20ms) instead of your droplet.

#### Option 2: Cloudflare Pages (Phase 2 — Optional Upgrade)

**Extra cost: $0** on free tier (500 builds/month, unlimited bandwidth, unlimited requests). For 20–50 sites that update a few times per week, this is well within free limits.

#### Option 3: Cloudflare R2 + Workers (Phase 3 — Scale)

**Extra cost: $0** on free tier (10 GB storage, 10M Class B requests/month). Paid tier starts at ~$0.015/GB/month — for small business sites (a few pages each, ~5MB per site), 200 sites = 1 GB = $0.015/month.

#### Cost Breakdown

| Phase | SSG Engine Cost | CDN/Serving Cost | Cloudflare Tier | Total Extra Cost |
|---|---|---|---|---|
| **Phase 1** (1–50 tenants) | $0 (same droplet) | $0 (Nginx + CF free cache) | Free (already using it) | **$0** |
| **Phase 2** (50–100 tenants) | $0 (same droplet) | $0 (CF Pages free: 500 builds/mo) | Free | **$0** |
| **Phase 3** (100–200 tenants) | $0 (same droplet) | ~$20/mo (CF Pro for cache rules) | Pro ($20/mo) | **~$20/mo** |
| **Phase 4** (200+ tenants) | $0 | ~$20/mo (CF Pro) + ~$1/mo (R2) | Pro | **~$21/mo** |

At K149/month (Starter tier), 100 tenants = K14,900/month revenue. $20/month Cloudflare Pro is less than 0.5% of revenue.

#### SSG Actually Saves Money

SSG doesn't add cost — it **reduces** cost by eliminating PHP processing for published site traffic:

| Scenario (100 tenants × 50 visits/day) | Current (CSR) | After SSG |
|---|---|---|
| Laravel requests per day | **5,000** | **~50** (dashboard/editor only) |
| PHP workers needed | 4–8 | 1–2 |
| Droplet RAM required | 4 GB ($48/mo) | **1–2 GB ($12–24/mo)** |
| When second server needed | ~50 tenants | **~500+ tenants** |
| Monthly droplet savings at 100 tenants | — | **~$24–36/mo saved** |

**Recommended migration path**:
1. **Phase 1 (now)**: SSG engine compiles HTML to disk on the same droplet. Nginx serves static files directly (bypasses Laravel entirely for published sites). Massive improvement over client-side rendering at **zero infrastructure cost**.
2. **Phase 2 (optional)**: Move static builds to Cloudflare Pages or R2. Published sites served from global edge. Server handles only dashboard/editor/API. Still **$0 extra** on free tier.

### Existing Foundation

| Component | Reuse Potential |
|---|---|
| `StaticExportService` (92.0 KB) | **High** — already compiles full HTML/CSS/JS. Needs adaptation from ZIP output to disk/CDN deploy. |
| `SectionTemplateService` (34.5 KB) | **High** — section definitions used by the renderer. |
| `ImageOptimizationService` | **High** — WebP conversion and resizing. |
| `Preview/Site.vue` (163.5 KB) | **Reference only** — the client-side renderer logic informs the server-side HTML compilation, but SSG replaces it for published sites. |

### Impact Assessment

| Metric | Before (CSR) | After (SSG) |
|---|---|---|
| First Contentful Paint (3G) | ~4–8s | **< 1.5s** |
| Google PageSpeed score | ~40–60 | **85–100** |
| SEO indexing reliability | Uncertain (JS-dependent) | **Guaranteed** (static HTML) |
| Server load per published page view | 1 Laravel request | **0** (CDN serves) |
| Hosting cost per 100 tenants | Linear server scaling | **Near-zero marginal cost** |
| Time to first byte (TTFB) | ~200–500ms (droplet) | **< 50ms** (Cloudflare edge) |

---

## 33. Infrastructure Scaling Roadmap

GrowBuilder currently runs on a single DigitalOcean droplet shared with the entire MyGrowNet platform. This section defines the scaling triggers and migration path.

### Scaling Phases

```
 TENANTS    INFRASTRUCTURE                           TRIGGER
 ───────    ──────────────                           ───────
   1–20     Single droplet, shared DB                ← Current state
            Acceptable. Focus on product-market fit.

  20–50     + SSG engine (§32, Phase 1 interim)      ← First paying customers
            Published sites served as static files
            from disk via Nginx. Server load drops
            dramatically.

  50–100    + Database read replica                  ← Analytics queries slow down
            Offload growbuilder_page_views, form
            submissions, and reporting queries to
            read replica. Write queries stay on
            primary.

 100–200    + CDN-first serving (Cloudflare Pages)   ← Droplet CPU consistently > 60%
            Published sites move to Cloudflare edge.
            Server handles only dashboard, editor,
            AI API, and payment webhooks.

   200+     + Managed database (DO Managed MySQL)    ← First SLA commitment
            + Horizontal app scaling (2+ droplets    ← Redundancy required
              behind load balancer)
            + Separate media storage (Cloudflare R2)
```

### Database Scaling Strategy

| Phase | Database Config | Analytics Query Path |
|---|---|---|
| **Now** | Single MySQL on droplet | Direct queries on primary |
| **50+ tenants** | Primary + read replica | `growbuilder_page_views`, `growbuilder_form_submissions`, `growbuilder_orders` read from replica |
| **200+ tenants** | Managed MySQL (DO/AWS RDS) | Consider time-series DB for page views (ClickHouse, TimescaleDB) |

### Table Growth Projections

| Table | Rows per tenant/month | At 100 tenants (1 year) | Mitigation |
|---|---|---|---|
| `growbuilder_page_views` | ~1,500 | **1.8M rows** | Partition by month; archive after 12 months |
| `growbuilder_form_submissions` | ~50 | 60K rows | Acceptable |
| `growbuilder_orders` | ~20 | 24K rows | Acceptable |
| `growbuilder_ai_usage` | ~100 | 120K rows | Reset monthly; archive |

### Cost Model

| Phase | Monthly Infrastructure Cost | Cost per Tenant |
|---|---|---|
| Now (1–20 tenants) | ~$24 (single droplet) | $1.20–$24.00 |
| SSG interim (20–50) | ~$24 (same droplet, less load) | $0.48–$1.20 |
| CDN-first (50–100) | ~$44 ($24 droplet + $20 Cloudflare Pro) | $0.44–$0.88 |
| Scaled (200+) | ~$100 (managed DB + 2 droplets + CDN) | $0.50 |

At K149/month (Starter tier), even the most expensive scaling phase yields healthy margins.

---

## 34. Page Revision History & Editor Undo Architecture

### The Problem

Currently, when a user saves a page edit in the visual editor, the previous version is permanently overwritten. There is no undo history, no "restore previous version", and no audit trail of content changes. If a user accidentally deletes a section and saves, that content is gone.

This is below the baseline expectation set by WordPress (revisions), Google Docs (version history), Notion (page history), and every modern content editor.

### Data Model

```
growbuilder_page_revisions
├── id                 (bigint, auto-increment)
├── page_id            (FK → growbuilder_pages.id)
├── revision_number    (integer — monotonically increasing per page)
├── sections_json      (LONGTEXT — full page sections JSON at this point in time)
├── design_tokens_json (JSON — page-level design overrides at this point)
├── meta_json          (JSON — page meta title, description, OG tags)
├── change_summary     (string, nullable — auto-generated: "Added section: Customer Reviews")
├── created_by         (FK → users.id)
├── created_at         (timestamp)
```

### Revision Capture Rules

| Trigger | Creates Revision? | Notes |
|---|---|---|
| User clicks "Save" in editor | ✅ Yes | Always capture before overwriting |
| AI chat applies canvas action | ✅ Yes | AI edits are reversible |
| Template upgrade applied (§31) | ✅ Yes | Pre-upgrade snapshot |
| Auto-save (every 60s during editing) | ⚠️ Conditional | Only if content changed since last revision |
| Publish/unpublish | ✅ Yes | Marks the "published" state for reference |

### Retention Policy

| Tier | Revisions Kept | Rationale |
|---|---|---|
| Free | Last 10 revisions per page | Sufficient for basic undo |
| Starter | Last 30 revisions per page | 1 month of daily edits |
| Business | Last 100 revisions per page | Full audit trail |
| Agency | Unlimited (90-day window) | Client accountability |

Older revisions are pruned by a scheduled command: `php artisan growbuilder:prune-revisions`.

### Editor UI

The visual editor (`Editor/Index.vue`) gains a **"Version History"** panel in the sidebar:

```
┌─────────────────────────────────────────┐
│  📋 Version History                     │
│                                         │
│  v12 — Today 2:45 PM (current)          │
│     Added "Customer Reviews" section    │
│                                         │
│  v11 — Today 1:30 PM                    │
│     AI: Updated hero headline           │
│                                         │
│  v10 — Yesterday 4:15 PM                │
│     Changed product prices              │
│                                         │
│  v9 — Aug 7, 11:00 AM                   │
│     Template upgrade v2.0 applied       │
│                                         │
│  [Preview v10]  [Restore v10]           │
└─────────────────────────────────────────┘
```

### Strategic Value

Revision history is not just an undo feature — it creates a **content changelog** that feeds into:
- **Retention metrics** (Section 28): "Your site was updated 12 times this month" vs. "Your site hasn't been edited in 45 days."
- **AI recommendations**: "You reverted the hero section 3 times — would you like AI to suggest alternatives?"
- **Agency accountability**: Clients can see exactly what their agency changed and when.

---

## 35. Architecture Evolution Summary & Stability Grading

This section provides an honest assessment of every architectural dimension, its current grade, and the path to long-term stability.

### Current Grading

| Dimension | Grade | Assessment | Path to A |
|---|---|---|---|
| **Domain architecture (DDD)** | A | Strong. Entities, value objects, repository contracts, use cases — all properly layered. | Maintain discipline. |
| **AI strategy** | A | Swappable engine thesis is correct. OpenAI/Groq/NVIDIA already abstracted. | Formalize provider abstraction into a config-driven factory. |
| **Product thesis (Business Profile)** | A | Right strategic answer. | Build the entity (§7, §21 Gap #2). |
| **Local-market fit** | A | MTN MoMo, Airtel Money, WhatsApp, PACRA/TPIN — genuine moat. | Add QR code bridge (§30). |
| **Published site rendering** | C | Client-side rendering is incompatible with SEO + 3G performance targets. | Implement SSG (§32). |
| **Hosting & scaling** | B- | Single droplet is fine for now. No plan documented for growth. | Follow scaling roadmap (§33). |
| **Data model** | B+ | Strong entity design. Business Profile scattered across JSON. | Build Business Profile entity. |
| **Editor UX** | B | Powerful drag-and-drop. No undo/revision history. | Implement page revisions (§34). |
| **Security** | B | Solid basics (TLS, RBAC, tenant isolation, encrypted credentials). Missing CSP headers. | Add CSP, sanitize custom HTML injection. |
| **SEO** | B- | AI-generated meta tags. No structured data, no sitemap, no robots.txt. | Implement local SEO stack (§24). |
| **Performance** | B- | Image optimization and lazy loading built. FCP target unachievable with current CSR. | SSG fixes this automatically (§32). |
| **Retention loop** | C+ | Analytics dashboard exists. No proactive outreach, no digest, no freshness scoring. | Build monthly digest + AI suggestions (§28). |

### Architecture Evolution Trajectory

```
                    NOW                    PHASE 1               PHASE 2              PHASE 3
                    Aug 2026               Oct 2026              Q1 2027              Q3 2027
                    ────────               ────────              ────────             ────────

Published Sites:    Client-side render  →  SSG to disk (Nginx) → CDN-first (CF Pages) → Edge compute
                    Grade: C               Grade: B+              Grade: A              Grade: A+

Business Data:      Scattered JSON      →  Business Profile     → Platform integration → Multi-channel
                    Grade: B+              Grade: A               Grade: A              Grade: A+

SEO:                Meta tags only      →  JSON-LD + Sitemap    → Google Business sync → Rich results
                    Grade: B-              Grade: A-              Grade: A              Grade: A+

Editor:             No undo             →  Page revisions       → Collaborative edit   → AI co-pilot
                    Grade: B               Grade: A-              Grade: A              Grade: A+

Retention:          Analytics only      →  Monthly digest email  → AI recommendations  → Auto-optimization
                    Grade: C+              Grade: B+              Grade: A              Grade: A+

Infrastructure:     Single droplet      →  Same (SSG reduces    → Read replica + CDN   → Managed DB +
                    Grade: B-              load) Grade: B          Grade: A-              multi-node A

Security:           Basic TLS + RBAC    →  CSP + sanitization   → WAF rules            → SOC 2 Type I
                    Grade: B               Grade: A-              Grade: A              Grade: A+
```

### The Three-Move Sequence

If GrowBuilder executes only three changes from this entire document, they should be:

1. **Build the Business Profile entity** — unlocks everything else (AI from structured data, JSON-LD, multi-channel, template upgrades from profile).
2. **Implement Static Site Generation** — solves SEO, performance, cost, and scaling simultaneously.
Everything else improves the product. These three determine whether it survives.

---

## 36. AI-Proof Resilience Guarantee & Strategic Pillars

### Core Principle

> **GrowBuilder shall evolve in lockstep with AI advancements and must NEVER be susceptible to replacement by standalone AI generation tools.**

Generic AI site generators (Durable, 10Web, Wix ADI, or raw LLM prompts) create static outputs in a vacuum. As AI models improve, standalone generators become commoditized, and users abandon them for newer tools. GrowBuilder is engineered so that **every breakthrough in AI automatically increases the platform's value**, rather than threatening it.

### The 4 Strategic Pillars of AI Resilience

```
┌────────────────────────────────────────────────────────────────────────┐
│                        GENERIC AI BUILDERS                             │
│                                                                        │
│  User Prompt ──> AI Generator ──> One-time Static HTML File           │
│                                                                        │
│  Result: Commoditized and replaced when a new AI model drops.          │
└────────────────────────────────────────────────────────────────────────┘

                                  VS

┌────────────────────────────────────────────────────────────────────────┐
│                        GROWBUILDER PLATFORM                            │
│                                                                        │
│  Business Profile ──> Platform Context ──> Swappable AI Engine         │
│         │                  │                      │                    │
│         ├── StockFlow      ├── Payments (MoMo)    ├── Auto-Updates     │
│         ├── BizDocs        ├── Local SEO & QR     └── SSG to Edge      │
│         └── GrowFinance    └── WhatsApp Workflow                       │
│                                                                        │
│  Result: Continuously evolves WITH AI. Compounds in value over time.  │
└────────────────────────────────────────────────────────────────────────┘
```

#### Pillar 1: The Reality Anchor (Data & Operational Context)

Standalone AI models have no connection to real-world business operations. They do not know a pharmacy's live stock levels, official PACRA registration, tax status, invoice records, or mobile money merchant details.

GrowBuilder serves as the **Reality Anchor**. It anchors AI generation directly to authentic, live business data (via StockFlow, BizDocs, GrowFinance, and the Business Profile). As AI models grow more intelligent, they consume this structured context to produce increasingly accurate, automated, and high-converting digital presences.

#### Pillar 2: Swappable AI Engine Architecture

In GrowBuilder's codebase (`AIContentService`, domain services, provider contracts), AI is completely decoupled from business logic:
- Today it invokes OpenAI, Groq, or NVIDIA endpoints.
- When next-generation AI models launch, changing a provider configuration instantly upgrades the entire platform.
- Overnight, every GrowBuilder tenant gains superior content synthesis, smarter visual layouts, more precise local SEO advice, and proactive business recommendations without rewriting underlying domain code.

#### Pillar 3: Structured Business Profile (Persistent Data > Ephemeral Output)

With generic AI tools, generated content is trapped inside static page structures. In GrowBuilder:
- Business identity, offerings, hours, trust credentials, and locations reside in the canonical **Business Profile entity** (§7).
- A website is merely **one presentation view** of this persistent data layer.
- Future AI advancements allow the same Business Profile to automatically publish to WhatsApp catalogs, Google Business Profiles, AI voice assistants, and emerging digital channels without manual re-entry.

#### Pillar 4: Local Execution Moat

Global AI labs optimize for broad, high-volume markets. They will not build or maintain:
- Zambian MTN Mobile Money & Airtel Money payment settlement integration
- PACRA legal compliance & TPIN verification trust badges
- Print-ready offline QR code window sticker generation with UTM attribution
- WhatsApp Cloud API ordering workflows optimized for 3G conditions

This operational execution moat ensures that no global AI generator can match GrowBuilder's local effectiveness.

