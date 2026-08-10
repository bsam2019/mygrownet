# GrowBuilder — Master Implementation Plan

**Target Specification**: [`docs/platform-evolution/growbuilder/GROWBUILDER_PLATFORM.md`](file:///c:/Apache24/htdocs/mygrownet/docs/platform-evolution/growbuilder/GROWBUILDER_PLATFORM.md)  
**Effective Date**: August 2026  
**Module Slug**: `growbuilder`  
**Domain Layer**: `App\Domain\GrowBuilder\`  
**Migrations Folder**: `database/migrations/growbuilder/`  
**Route File**: `routes/growbuilder.php`  
**Frontend Folder**: `resources/js/Pages/GrowBuilder/`  

---

## 1. Overview & Execution Strategy

This implementation plan details the step-by-step technical execution for evolving **GrowBuilder** from a template-based site builder into an **AI-Powered Structured Business Digital Presence Platform** as specified in `GROWBUILDER_PLATFORM.md`.

### Core Architectural Pillars:
1. **The Business Profile Engine**: Centralized structured business data (`identity`, `trust/PACRA/TPIN`, `services`, `hours`, `location`) that powers site generation and multi-channel publication.
2. **AI Engine & Provider Strategy Pattern**: Decoupled `AiGeneratorEngineInterface` (0% vendor lock-in, supporting Gemini, OpenAI, Claude, DeepSeek, or Mock).
3. **Static Site Generation (SSG) & CDN-First Serving**: Compiles Inertia site models into standalone, compressed HTML/CSS assets for sub-100ms page loads in low-bandwidth environments.
4. **Page Revision History & One-Click Rollback**: Automated page snapshots (`growbuilder_page_revisions`) allowing non-destructive undo and template upgrades.
5. **Physical-to-Digital Bridge (QR & WhatsApp)**: Embedded QR code link generation and WhatsApp ordering workflows for local Zambian/SADC retail & service businesses.

---

## 2. 36-Section Specification Traceability Matrix

| Specification Section (`GROWBUILDER_PLATFORM.md`) | Implementation Component & File |
|---|---|
| **1. Executive Summary & Vision** | Business Profile & AI-assisted presence platform repositioning |
| **2. Product Thesis & Distinction** | Operating a digital presence vs generic one-shot site generator |
| **3. Competitive Positioning** | Local fit vs Wix/Squarespace (Mobile Money, PACRA/TPIN, WhatsApp) |
| **4. Operating Constraints** | 5 working principles (automation, reusable components, pharmacy first) |
| **5. Market Entry Strategy** | Zambia first -> Pharmacy vertical -> Taradasi Medics pilot tenant |
| **6. AI-Assisted Design System** | 5-step onboarding flow in `Sites/Create.vue` & `WebsiteGeneratorService.php` |
| **7. Business Profile Data Model** | `growbuilder_business_profiles` table & `BusinessProfileService.php` |
| **8. Phased Roadmap** | Phase 1 (Core Profile & AI), Phase 2 (E-Commerce), Phase 3 (Multi-Channel) |
| **9. Onboarding Model** | Self-serve wizard + guided concierge onboarding |
| **10. Distribution Strategy** | Platform workspace catalog + warm sales to StockFlow tenants |
| **11. Export & Ownership Policy** | Export site as standalone ZIP (HTML/CSS/JS) |
| **12. Deferred "Not Now" List** | Mobile native apps, plugin marketplace (deferred) |
| **13. Open Questions & Risks** | AI rate limits, bandwidth optimization, domain DNS routing |
| **14. Technical Architecture** | 40 database migrations, 33 controllers, 22 services, 42 Vue pages |
| **15. DDD Layer Architecture** | `App\Domain\GrowBuilder\` entities, value objects, repositories |
| **16. AI Engine Architecture** | `AiGeneratorEngineInterface.php` & `AIContentService.php` |
| **17. E-Commerce & Payments** | MTN MoMo, Airtel Money, QuickInvoice integration |
| **18. Agency & White-Label** | Multi-tenant agency sub-accounts & custom domains |
| **19. Subscription Tier Matrix** | Starter, Professional, Business, Agency tiers |
| **20. Cross-Module Integration** | Sync from `StockFlow` (inventory) & `BizDocs` (quotations) |
| **21. Gap Analysis** | Transitioning template picker to Business Profile pre-population |
| **22. Legal & Financial Policies** | Zambia PACRA / TPIN display & privacy policy compliance |
| **23. Deployment Topology** | CDN-first static serving + Laravel SSR fallback |
| **24. SEO & Discoverability** | Auto-generated XML sitemaps, JSON-LD schema, meta tags |
| **25. PWA & Offline Strategy** | Service worker caching, offline PWA manifest generation |
| **26. Bandwidth Optimization** | WebP image compression, CSS purging, inline critical styles |
| **27. Multi-Language (i18n)** | English (Zambia primary), Nyanja, Bemba localization support |
| **28. Analytics & Retention Metrics** | Pageviews, WhatsApp clicks, form conversions, lead generation |
| **29. Security Architecture** | Content Security Policy (CSP), XSS sanitization, CORS rules |
| **30. Physical-to-Digital Bridge** | QR Code generator (`/qr/{code}`) linking offline signage to site |
| **31. Template Versioning** | Non-destructive template upgrades & component migration |
| **32. SSG & CDN Serving** | `SsgPublisherService.php` & `growbuilder_ssg_deployments` |
| **33. Infrastructure Scaling** | S3 asset storage & Cloudflare CDN integration |
| **34. Page Revision History** | `growbuilder_page_revisions` & `PageRevisionService.php` |
| **35. Stability Grading** | Tier-1 Core Stability rating for production resilience |
| **36. AI-Proof Guarantee** | Structured data ownership outlasting individual AI models |

---

## 3. Proposed System Architecture & Changes

### Component 1: Incremental Database Migrations (`database/migrations/growbuilder/`)

> [!NOTE]
> **Schema Preservation**: GrowBuilder already has 40 active migrations. The new implementation extends these tables incrementally without dropping existing data.

#### [NEW] [`2026_08_10_000001_add_growbuilder_business_profile_and_ssg_tables.php`](file:///c:/Apache24/htdocs/mygrownet/database/migrations/growbuilder/2026_08_10_000001_add_growbuilder_business_profile_and_ssg_tables.php)
- **Extends `growbuilder_sites`**: Adds `canonical_organization_id`, `pwa_enabled` (boolean), `ssg_enabled` (boolean), `theme_preset` (string), and `last_ssg_deployed_at` (timestamp).
- **Creates `growbuilder_business_profiles`**: Centralized structured business identity (`legal_name`, `trade_name`, `tpin`, `pacra_number`, `phone`, `whatsapp`, `email`, `physical_address`, `city`, `province`, `opening_hours`, `services_json`, `trust_badges_json`).
- **Creates `growbuilder_page_revisions`**: Page snapshot versioning (`site_id`, `page_id`, `revision_number`, `layout_json`, `created_by_user_id`, `commit_message`).
- **Creates `growbuilder_ssg_deployments`**: Deployment audit log (`site_id`, `status`, `asset_zip_path`, `cdn_url`, `deployed_at`).

---

### Component 2: Domain Layer & Strategy Pattern Contracts (`App\Domain\GrowBuilder\` & `App\Services\GrowBuilder\`)

#### [NEW] [`AiGeneratorEngineInterface.php`](file:///c:/Apache24/htdocs/mygrownet/app/Domain/GrowBuilder/Contracts/AiGeneratorEngineInterface.php)
- Decoupled Strategy Pattern contract defining `generateSiteLayout()`, `reWriteSectionContent()`, and `getProviderName()`.
- Supports Gemini, OpenAI, Claude, DeepSeek, or Mock drivers via `config('services.ai.provider')`.

#### [NEW] [`SsgDeploymentEngineInterface.php`](file:///c:/Apache24/htdocs/mygrownet/app/Domain/GrowBuilder/Contracts/SsgDeploymentEngineInterface.php)
- Contract for compiling site pages into static HTML assets and deploying to storage/CDN.

#### [NEW] [`BusinessProfileService.php`](file:///c:/Apache24/htdocs/mygrownet/app/Services/GrowBuilder/BusinessProfileService.php)
- Syncs platform `organizations` details into `growbuilder_business_profiles` and feeds structured data to site generators.

#### [NEW] [`PageRevisionService.php`](file:///c:/Apache24/htdocs/mygrownet/app/Services/GrowBuilder/PageRevisionService.php)
- Saves page snapshots on edit and handles one-click undo/rollback to previous revisions.

#### [NEW] [`SsgPublisherService.php`](file:///c:/Apache24/htdocs/mygrownet/app/Services/GrowBuilder/SsgPublisherService.php)
- Generates static HTML/CSS asset packages for ultra-fast CDN serving.

---

### Component 3: HTTP Controllers (`App\Http\Controllers\GrowBuilder\` & `App\Http\Controllers\Admin\`)

#### [MODIFY] [`SiteController.php`](file:///c:/Apache24/htdocs/mygrownet/app/Http/Controllers/GrowBuilder/SiteController.php)
- Refactors site creation to auto-hydrate layout from `BusinessProfileService`.

#### [MODIFY] [`EditorController.php`](file:///c:/Apache24/htdocs/mygrownet/app/Http/Controllers/GrowBuilder/EditorController.php)
- Integrates `PageRevisionService` snapshot history and rollback endpoints.

#### [NEW] [`BusinessProfileController.php`](file:///c:/Apache24/htdocs/mygrownet/app/Http/Controllers/GrowBuilder/BusinessProfileController.php)
- CRUD controller for managing structured business identity data.

#### [NEW] [`SsgDeployController.php`](file:///c:/Apache24/htdocs/mygrownet/app/Http/Controllers/GrowBuilder/SsgDeployController.php)
- Triggers static site compilation and CDN cache purging.

---

### Component 4: Vue Frontend Layer (`resources/js/Pages/GrowBuilder/`)

#### [MODIFY] [`Sites/Create.vue`](file:///c:/Apache24/htdocs/mygrownet/resources/js/Pages/GrowBuilder/Sites/Create.vue)
- Upgrades onboarding wizard to pre-fill from Business Profile.

#### [MODIFY] [`Editor/Index.vue`](file:///c:/Apache24/htdocs/mygrownet/resources/js/Pages/GrowBuilder/Editor/Index.vue)
- Adds Page Revision History drawer for visual undo/rollback.

#### [NEW] [`BusinessProfile/Edit.vue`](file:///c:/Apache24/htdocs/mygrownet/resources/js/Pages/GrowBuilder/BusinessProfile/Edit.vue)
- Form interface for managing structured business information.

---

### Component 5: Route Configuration & Application Registry

#### [MODIFY] [`routes/growbuilder.php`](file:///c:/Apache24/htdocs/mygrownet/routes/growbuilder.php)
- Registers Business Profile, SSG deployment, page revisions, and `/growbuilder/admin` routes.

#### [MODIFY] [`ApplicationRegistrySeeder.php`](file:///c:/Apache24/htdocs/mygrownet/database/seeders/ApplicationRegistrySeeder.php)
- Updates `growbuilder` application status to `active` and `online`.

---

### Component 6: Admin Integrations (Tier 1 Governance & Tier 2 Domain Admin)

#### Tier 1: Platform Command Center (`PlatformAdminMetricsService.php` & `routes/admin.php`)
- Aggregate total sites, active custom domains, storage usage, and SSG deployments.

#### Tier 2: Domain Application Admin (`/growbuilder/admin`)
- Manage templates, theme presets, AI generation tokens, and agency white-label accounts.

---

## 4. Verification & Testing Plan

### Automated Tests:
1. `tests/Feature/GrowBuilder/BusinessProfileTest.php`: Asserts business profile creation and auto-hydration during site generation.
2. `tests/Feature/GrowBuilder/PageRevisionTest.php`: Tests automated page snapshotting and rollback.
3. `tests/Feature/GrowBuilder/SsgPublisherTest.php`: Verifies static site compilation and asset packaging.

### Manual Verification:
1. Launch GrowBuilder creation wizard and verify Business Profile pre-population.
2. Test page editor snapshot rollback.
3. Verify SSG deployment generation.
