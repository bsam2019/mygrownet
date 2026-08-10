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

### 17-Section Specification Traceability Matrix:

| Specification Section (`BIZBOOST_PLATFORM.md`) | Implementation Component & File |
|---|---|
| **1. Executive Summary & Vision** | Unified in `CustomerHubService.php` & `RevenueAttributionService.php` |
| **2. Problem Statement & Silos** | Multi-channel integration (`BizDocs`, `StockFlow`, `GrowMart`, `GrowBuilder`) |
| **3. Core 5-Pillar Model** | Handled in `Dashboard/Index.vue` & `LeadManagementService.php` |
| **4. Customer Journey Engine** | Configured via `bizboost_lead_pipelines` & `bizboost_pipeline_stages` |
| **5. Business Conversion Events** | Tracked via `bizboost_attributions` & `EventTrackingService.php` |
| **6. Single Customer View (Hub)** | Rendered in `Customers/Show.vue` & `CustomerHubService.php` |
| **7. Multi-Source Lead Capture** | Ingested via 11 sources in `LeadPipelineController.php` |
| **8. Visitor Intelligence & Tracker** | Served via `TrackerSdkController.php` & `bizboost-tracker.js` |
| **9. Native & Standalone Integrations**| GrowBuilder auto-inject + standalone JS snippet endpoint |
| **10. Omnichannel Links & SMS** | Trackable links (`https://bizboost.link/wa/{hash}`) & `SmsGatewayInterface` |
| **11. Sales Productivity & AI** | Response SLA (<30 min) + `AiSalesAssistantService.php` |
| **12. Lead Scoring & Intent Tiers** | Calculated via `LeadScoringService.php` (`Low`, `Interested`, `Hot`, `High`) |
| **13. Customer Lifecycle & Retention**| Reactivation workflows (90+ days inactive) & repeat predictor |
| **14. Revenue Attribution & ROI** | Multi-touch ROI calculated in `RevenueAttributionService.php` |
| **15. Actionable Dashboard** | Redesigned `Dashboard/Index.vue` ("Missed Revenue" KPIs) |
| **16. Commercial & Tier Model** | Starter/Business/Pro tier caps + SMS top-up packs |
| **17. Admin Governance (Tiers 1 & 2)** | Tier 1 Command Center + Tier 2 Domain Admin `/bizboost/admin` |

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

#### [NEW] [`SmsGatewayInterface.php`](file:///c:/Apache24/htdocs/mygrownet/app/Domain/BizBoost/Contracts/SmsGatewayInterface.php)
- Decoupled Strategy Pattern interface contract defining `sendSms()`, `getBalance()`, and `getProviderName()`.
- Allows plugging in any SMS gateway provider (`AfricalaSmsGateway`, `TwilioSmsGateway`, `TermiiSmsGateway`, or `MockSmsGateway` for dev) via `config('services.sms.provider')` without touching business logic or controllers.

#### [NEW] [`OmnichannelService.php`](file:///c:/Apache24/htdocs/mygrownet/app/Services/BizBoost/OmnichannelService.php)
- Manages trackable WhatsApp links, phone click-to-call dialogs, and SMS dispatch through `SmsGatewayInterface`.
- Supports automatic fallback routing if the primary SMS provider returns an error. 

#### [NEW] [`AiProviderInterface.php`](file:///c:/Apache24/htdocs/mygrownet/app/Domain/Core/Contracts/AiProviderInterface.php)
- Decoupled Strategy Pattern contract defining `generateText()`, `extractStructuredData()`, and `getProviderName()`.
- Allows plugging in any AI provider (`GeminiAiProvider`, `OpenAiProvider`, `ClaudeAiProvider`, `DeepSeekAiProvider`, or `MockAiProvider` for dev) via `config('services.ai.provider')` without provider lock-in.

#### [NEW] [`AiSalesAssistantService.php`](file:///c:/Apache24/htdocs/mygrownet/app/Services/BizBoost/AiSalesAssistantService.php)
- **Instant Customer Summaries**: Generates 2-sentence conversational summaries from customer timeline (*"John visited 3x, viewed 5kW Solar twice, clicked WhatsApp, requested quote 24h ago"*).
- **Contextual Follow-up Prompts**: Recommends high-converting sales questions tailored to the customer's intent score and target product.
- **Personalized Message Drafting**: Generates tailored SMS/WhatsApp/email follow-up drafts based on pipeline stage, quote expiration, and inactive customer reactivation goals.
- **Natural Language Lead Extraction**: Parses raw chat notes or emails into structured lead attributes (name, phone, interest, budget, location).
- **Token Usage Tracking**: Logged in `bizboost_ai_usage_logs` per business tenant.

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
- Adds `/bizboost/admin` (and `bizboost.sub.admin`) routes for domain-level management.

#### [MODIFY] [`ApplicationRegistrySeeder.php`](file:///c:/Apache24/htdocs/mygrownet/database/seeders/ApplicationRegistrySeeder.php)
- Updates `bizboost` application status from `legacy/maintenance` to `active/online`.

---

### Component 6: Admin Integrations (Tier 1 Governance & Tier 2 Domain Admin)

#### Tier 1: Platform Command Center Integration (`App\Services\Admin\PlatformAdminMetricsService.php` & `routes/admin.php`)
- **Metrics Aggregation**: Updates `PlatformAdminMetricsService.php` to fetch live BizBoost metrics: Total Registered Businesses, Active Customer Hub Profiles, Total Captured Leads, WhatsApp Click Conversions, and Total Attributed Marketing Revenue (ZMW).
- **Platform Admin Hub (`routes/admin.php`)**: Connects `/admin/bizboost` to `BizBoostAdminController.php` to provide super-admins with platform-wide visibility across all tenant businesses, AI usage, and SMS gateway credit balances.

#### Tier 2: Domain Application Admin (`/bizboost/admin` & `/bizboost/settings`)
- **Pipeline Stage Builder**: Allows business owners & sales managers to customize their industry-specific pipeline stages (`New`, `Contacted`, `Qualified`, `Quotation`, `Won`, `Lost`), sort order, and response SLA target minutes.
- **SMS Gateway & Africala Balance**: Displays current SMS credit balance, recharge packs, and automated dispatch logs.
- **SLA & Audit Management**: Surfaces SLA violation alerts (uncontacted leads > 30 mins) and sales rep response performance reports.

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
