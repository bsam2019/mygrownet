# BizBoost — Master Implementation Plan

**Target Specification**: [`docs/platform-evolution/bizboost/BIZBOOST_PLATFORM.md`](file:///c:/Apache24/htdocs/mygrownet/docs/platform-evolution/bizboost/BIZBOOST_PLATFORM.md)  
**Effective Date**: August 2026  
**Module Slug**: `bizboost`  
**Domain Layer**: `App\Domain\BizBoost\`  
**Migrations Folder**: `database/migrations/bizboost/`  
**Route File**: `routes/bizboost.php`  
**Frontend Folder**: `resources/js/Pages/BizBoost/`  

---

## 1. Overview & Execution Strategy

This implementation plan outlines the step-by-step technical execution for refactoring and upgrading the **BizBoost** module from a legacy SME utility into a unified **Customer Engagement, Lead Pipeline & Marketing Revenue Attribution System** as specified in `BIZBOOST_PLATFORM.md`.

### Core Technical Pillars:
1. **Single Customer View (Customer Hub)**: Unify identity, sessions, enquiries, quotations (BizDocs), and sales (StockFlow/GrowMart) under `bizboost_customers`.
2. **Multi-Source Lead Pipeline Engine**: Configurable pipeline stages, Kanban board, response-time SLA monitoring (<30 min target), and AI lead scoring.
3. **Omnichannel Communication & Tracking Engine**: Trackable WhatsApp link generator, phone enquiry dialog, and Africala SMS dispatch integration.
4. **JavaScript Tracker SDK (`bizboost-tracker.js`)**: Anonymous session tracking, identity resolution upon form/WhatsApp click, and event stream ingestion.
5. **Offline & Online Revenue Attribution**: Connect in-store POS transactions, invoice completions, and web orders back to marketing touchpoints.

---

## 2. Proposed System Architecture & Changes

### Component 1: Incremental Database Migrations (`database/migrations/bizboost/`)

> [!NOTE]
> **Schema Preservation**: BizBoost already has 36 active migrations defining `bizboost_businesses`, `bizboost_customers`, `bizboost_campaigns`, `bizboost_sales`, `bizboost_qr_codes`, and `bizboost_omnichannel_logs`. The new implementation extends these tables incrementally without dropping existing data.

#### [NEW] [`2026_08_10_000001_add_lead_pipeline_and_revenue_attribution_tables.php`](file:///c:/Apache24/htdocs/mygrownet/database/migrations/bizboost/2026_08_10_000001_add_lead_pipeline_and_revenue_attribution_tables.php)
- **Extends `bizboost_customers`**: Adds `intent_score` (default 0), `clv_zmw` (decimal 12,2), `intent_tier` (enum: `low`, `interested`, `hot`, `high_intent`), and `canonical_organization_id`.
- **Extends `bizboost_campaigns`**: Adds `spend_zmw` (decimal 12,2), `attributed_revenue_zmw` (decimal 12,2), and `marketing_roi_ratio` (decimal 8,2).
- **Creates `bizboost_lead_pipelines`**: Pipeline definitions per business (e.g. Retail Sales, School Admissions, Real Estate).
- **Creates `bizboost_pipeline_stages`**: Configurable stages (`New`, `Contacted`, `Qualified`, `Quotation`, `Won`, `Lost`) with sort ordering and SLA targets.
- **Creates `bizboost_leads`**: Individual lead cards linked to `bizboost_customers`, pipeline stage, assigned user, `first_response_at`, `sla_target_minutes` (default 30), and estimated deal value.
- **Creates `bizboost_trackable_links`**: Campaign short links (`https://bizboost.link/wa/{hash}`) recording visitor IP, UTM parameters, device, and target product prior to redirecting to WhatsApp/Call.
- **Creates `bizboost_attributions`**: Revenue attribution logs connecting StockFlow POS transactions, BizDocs paid invoices, and GrowMart orders back to `bizboost_leads` and `bizboost_campaigns`.

---

### Component 2: Domain & Service Layer (`App\Domain\BizBoost\` & `App\Services\BizBoost\`)

#### [NEW] [`CustomerHubService.php`](file:///c:/Apache24/htdocs/mygrownet/app/Services/BizBoost/CustomerHubService.php)
- Aggregates identity, web activity, quotation history (from `BizDocs`), and POS sales (from `StockFlow`) into a single customer profile.
- Calculates Customer Lifetime Value (CLV) and recency score.

#### [NEW] [`LeadManagementService.php`](file:///c:/Apache24/htdocs/mygrownet/app/Services/BizBoost/LeadManagementService.php)
- Manages pipeline stage transitions.
- Enforces response-time SLAs (alerts when uncontacted lead > 30 mins).
- Triggers automated follow-up reminders.

#### [NEW] [`EventTrackingService.php`](file:///c:/Apache24/htdocs/mygrownet/app/Services/BizBoost/EventTrackingService.php)
- Handles anonymous session ingestion from `bizboost-tracker.js`.
- Resolves anonymous session IDs to registered customer profiles upon form submission or WhatsApp link click.

#### [NEW] [`LeadScoringService.php`](file:///c:/Apache24/htdocs/mygrownet/app/Services/BizBoost/LeadScoringService.php)
- Evaluates dynamic customer intent scores based on interaction weights (`page_view` +5, `pricing_view` +10, `whatsapp_click` +20, `form_submit` +30).
- Assigns intent tiers (`Low`, `Interested`, `Hot`, `High Intent`).

#### [NEW] [`RevenueAttributionService.php`](file:///c:/Apache24/htdocs/mygrownet/app/Services/BizBoost/RevenueAttributionService.php)
- Attributes completed POS sales (StockFlow), invoice payments (BizDocs), or online orders (GrowMart) back to original marketing campaigns.
- Computes Marketing ROI ratio (`Attributed Revenue / Campaign Spend`).

---

### Component 3: HTTP Controllers (`App\Http\Controllers\BizBoost\`)

#### [MODIFY] [`DashboardController.php`](file:///c:/Apache24/htdocs/mygrownet/app/Http/Controllers/BizBoost/DashboardController.php)
- Refactors dashboard to present "Missed Revenue" KPIs: Uncontacted Leads, Pending Quotation Value, High-Intent Anonymous Visitors, Inactive Customers (90+ days), and SLA alerts.

#### [MODIFY] [`CustomerController.php`](file:///c:/Apache24/htdocs/mygrownet/app/Http/Controllers/BizBoost/CustomerController.php)
- Connects to `CustomerHubService` for 360-degree customer view, interaction timeline, and tagging.

#### [NEW] [`LeadPipelineController.php`](file:///c:/Apache24/htdocs/mygrownet/app/Http/Controllers/BizBoost/LeadPipelineController.php)
- Serves Kanban drag-and-drop board, stage updates, SLA monitoring, and sales rep assignments.

#### [NEW] [`CampaignAttributionController.php`](file:///c:/Apache24/htdocs/mygrownet/app/Http/Controllers/BizBoost/CampaignAttributionController.php)
- Manages trackable links generator, SMS dispatch, and revenue ROI reporting.

#### [NEW] [`TrackerSdkController.php`](file:///c:/Apache24/htdocs/mygrownet/app/Http/Controllers/BizBoost/TrackerSdkController.php)
- Serves public `bizboost-tracker.js` SDK asset and processes event ingest webhooks (`POST /bizboost/api/track`).

---

### Component 4: Vue Frontend Layer (`resources/js/Pages/BizBoost/`)

#### [MODIFY] [`Dashboard/Index.vue`](file:///c:/Apache24/htdocs/mygrownet/resources/js/Pages/BizBoost/Dashboard/Index.vue)
- Redesigns command center around actionable financial metrics, uncontacted lead banners, and customer reactivation prompts.

#### [NEW] [`Leads/Pipeline.vue`](file:///c:/Apache24/htdocs/mygrownet/resources/js/Pages/BizBoost/Leads/Pipeline.vue)
- Drag-and-drop Kanban pipeline board with stage totals, SLA badge indicators, and quick-call modal.

#### [NEW] [`Customers/Show.vue`](file:///c:/Apache24/htdocs/mygrownet/resources/js/Pages/BizBoost/Customers/Show.vue)
- Single Customer View displaying interaction timeline, intent score badge, BizDocs quotes, StockFlow purchases, and SMS dispatch modal.

#### [NEW] [`Campaigns/Index.vue`](file:///c:/Apache24/htdocs/mygrownet/resources/js/Pages/BizBoost/Campaigns/Index.vue)
- Omnichannel campaign manager with trackable WhatsApp link generator, SMS blast manager, and attributed revenue cards.

---

### Component 5: Route Configuration & Application Registry

#### [MODIFY] [`routes/bizboost.php`](file:///c:/Apache24/htdocs/mygrownet/routes/bizboost.php)
- Registers new pipeline, customer hub, attribution, and tracker SDK routes for both subdomain (`bizboost.mygrownet.com`) and main path (`/bizboost`).

#### [MODIFY] [`ApplicationRegistrySeeder.php`](file:///c:/Apache24/htdocs/mygrownet/database/seeders/ApplicationRegistrySeeder.php)
- Updates `bizboost` application status from `legacy/maintenance` to `active/online`.

---

## 3. Verification & Testing Plan

### Automated Tests:
1. `tests/Feature/BizBoost/CustomerHubTest.php`: Asserts single customer view aggregation across sessions, quotes, and POS sales.
2. `tests/Feature/BizBoost/LeadPipelineTest.php`: Tests Kanban stage transitions, response SLA timestamp calculations, and intent score updates.
3. `tests/Feature/BizBoost/RevenueAttributionTest.php`: Tests revenue attribution mapping from StockFlow POS sales back to trackable campaign links.
4. `tests/Feature/BizBoost/TrackerSdkTest.php`: Verifies `bizboost-tracker.js` event ingestion and identity resolution.

### Manual Verification:
1. Load `bizboost.mygrownet.com` or `/bizboost` in browser and verify "Missed Revenue" dashboard cards render cleanly.
2. Generate a trackable WhatsApp link, simulate a click, and verify identity resolution in Customer Hub.
3. Test Kanban lead pipeline drag-and-drop stage updates.
