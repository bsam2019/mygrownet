# GrowStream Platform — Refined Product Document

**Status: Architecture reference for future-phase work. Not a current build target.**

> **Naming note (read first).** "GrowStream Hub" (route `/hub`) is the official **product label and route** for this B2B capability. Internally — in code, ADRs, tickets, and data models — "GrowStream Hub" represents the central platform engine that orchestrates independent creator platforms and portals, avoiding collision with MyGrowNet's shared `App\Domain\Platform*` infrastructure. Keep that distinction everywhere.

GrowStream Consumer (public streaming) is pre-launch and is the priority. This document exists so today's data models, permissions, and content-visibility architecture don't have to be torn up later — not to start parallel development now. Section 0 below makes this explicit; keep it attached whenever this document is shared with opencode or anyone else scoping work.

---

## 0. Relationship to the current roadmap

GrowStream Platform should influence **schema and architecture decisions being made right now** — specifically:

- Content visibility/ownership model — and, critically, keep two **orthogonal axes** separate in the schema rather than collapsing them into one field:
  - **`access_level`** (the *who* axis): who is allowed to watch — `free` / `basic` / `premium` / `institutional` (already exists on GrowStream videos). Per-tenant variants may add `subscription` / `purchase` / `private` later.
  - **`publishing_destination`** (the *where* axis): where content is surfaced — `public` (GrowStream catalogue) / `portal` (customer-owned only) / `both`. This is new and must be a first-class column from the start, not retrofitted.
  A single enum like `visibility = public|private` would conflate the two and force a migration later. Keep them as two columns.
- Organisation/permissions structure (so a future tenant boundary isn't fighting the existing single-catalogue assumption)
- Video asset ownership (so a video can belong to an org, not just a creator, without a schema rewrite)

It should **not** currently receive:

- Engineering time on courses, certificates, custom domains, SSO, LMS integration, or the API platform
- Product/design time beyond what's needed to keep the door open architecturally
- Any commitment to the phase timeline in Section 10 — that timeline is illustrative, not scheduled

### Revisit gate (concrete, not a vibe)

Reopen this document when **all** of the following are true:

1. GrowStream Consumer has shipped subscriptions, PPV, the creator earnings dashboard, and the native apps; **and**
2. Consumer has real usage data spanning at least one full billing cycle from which per-tier delivery cost per subscriber can be computed (Cloudflare Stream storage + delivery spend ÷ active subscribers); **and**
3. The resulting per-subscriber cost model has been written down alongside the K35/K75/K145 derivation.

**Owner:** the GrowStream product lead (whoever owns Consumer pricing today). **Cadence:** re-review quarterly after Consumer launch until the gate is met. Until then, treat Sections 5–7 and 13 as design inputs only — nothing in them is committed.

---

## 1. Product overview

GrowStream Platform extends GrowStream's existing infrastructure so creators, educators, training organisations, businesses, and institutions can operate their own branded video service on the same underlying platform.

A customer receives a hosted subdomain (`academy.growstream.app`) or connects their own domain. They publish their own videos, courses, and subscription content to their own audience. Customer content is **not** automatically included in the public GrowStream catalogue — the customer decides what, if anything, is also published publicly.

Two distinct publishing environments:

```text
                         GROWSTREAM
                              |
             ┌────────────────┴────────────────┐
             |                                 |
       PUBLIC STREAMING                 GROWSTREAM PLATFORM
             |                                 |
   Existing GrowStream model          Customer-owned portal
   Public discovery                   Private/branded content
   Creator monetisation               Subscriptions, courses,
   Existing 70% model                 paid content, own audience, APIs
```

---

## 2. Target customers

- **Individual creators** — teachers, tutors, coaches, fitness instructors, business trainers, religious educators, professional trainers
- **Educational organisations** — private schools, colleges, universities, training institutes, tutoring centres, exam prep and certification providers
- **Businesses** — employee training, customer education, product training, onboarding, compliance training
- **Media and entertainment organisations** — film/drama producers, independent studios, sports organisations, documentary producers

Support all four without building separate products for each.

---

## 3. Customer workspace

New workspace context inside the existing MyGrowNet organisation architecture — not a separate tenant system:

```text
MyGrowNet Workspace
       |
       └── GrowStream Platform
                |
                └── ABC Academy
```

Customer-managed: brand, domain, content, courses, users, subscriptions, payments, analytics, team, API credentials, integrations.

---

## 4. Branded portal

Hosted portal per customer (`abcacademy.growstream.app`): logo, org name, brand colours, cover image, favicon, custom navigation, homepage sections, about/contact, social links, courses, videos, series, subscription plans. Customer should not need to build a separate website.

---

## 5. Custom domains — treat as a standalone infrastructure project

This is not a checklist item alongside branding — it's comparable in scope to what Vercel/Netlify/Webflow built as core infrastructure. It requires:

- DNS verification flow with clear customer-facing instructions
- Automated SSL provisioning and renewal per domain
- Correct tenant resolution on every request (domain → organisation → portal → content), with no possibility of cross-resolution
- Monitoring/alerting for domain misconfiguration or SSL failure, since a broken custom domain is a broken business for that customer

Scope this as its own engineering project with its own estimate when the time comes — don't let it hide inside a "Phase 3" bullet list.

---

## 6. Cost model and unit economics — currently missing, required before build

GrowStream Consumer already has a disciplined cost model: Cloudflare Stream storage ($5/1,000 min stored) and delivery ($1/1,000 min delivered), watch-minute allowances per tier, hard caps to protect margin at low subscriber counts. **GrowStream Platform needs the same rigor, and doesn't have it yet.**

Before any platform tier is priced or built, model:

- **Storage exposure per tenant** — an educational institution or media producer could upload far more content than an individual consumer creator. Is storage cost passed through, capped, or absorbed into the platform fee?
- **Delivery exposure per tenant** — a university with 5,000 students watching lecture video, or a mining company's 2,500 employees completing compliance training, generates delivery costs that dwarf a single consumer subscriber. The document's own example ("XYZ Mining Ltd, 2,500 employees") has zero attached cost analysis.
- **What the configurable platform fee needs to cover** — currently framed only as revenue share (e.g. 85/15), with no stated relationship to actual Cloudflare cost per tenant. A platform fee that doesn't scale with usage will lose money on exactly the highest-usage customers.
- **Whether "Starter / Professional / Enterprise" tiers differ in storage/delivery allowance**, not just features — the original tiers list feature gates only, no usage limits.

### Money flows — the tenant is both customer and payee

Two distinct money flows exist, and both must be modelled:

1. **Money in (tenant → GrowStream):** the platform fee / subscription the tenant pays GrowStream (Section 7).
2. **Money out (GrowStream → tenant):** the tenant's portal revenue (customer payments minus the platform fee) that GrowStream must **hold, track, and remit** to the tenant — i.e. a treasury/escrow position. GrowStream collects K100 from the tenant's customer, keeps K15, and owes the tenant K85.

> **BYOP changes flow 2 — see Section 7 (Bring Your Own Payment).** When a tenant connects their own payment provider, customer money flows directly to the tenant's provider account and **never passes through GrowStream**, so there is no revenue-share capture point and no GrowStream-held balance for that tenant. In BYOP mode the platform fee must be collected differently (flat subscription, metered usage, or top-up model) — this is a fee-model decision that must be settled before Phase 2, not during implementation. The Section 6 cost model must cover **both** money-flow modes.

Flow 2 is not the same as the existing consumer creator-payout mechanism: it is per-tenant, on a configurable fee split, and can span many end-customers per tenant. It needs explicit decisions on:

- When and how tenants are paid out (schedule, minimum balance, payment rail — mobile money vs. bank transfer for institutional tenants)
- Whether GrowStream holds funds in escrow, and the reconciliation/audit trail for per-tenant ledgers
- What happens to held balances on tenant churn, refunds, or disputes

This should integrate with the existing MyGrowNet financial architecture (financial events, payment providers, payout rails) — but it is a **distinct ledger posture** and must be scoped as such in Phase 2, not assumed to inherit the consumer payout flow.

This needs a spreadsheet-level model, the same way K35/K75/K145 consumer pricing was derived, before any tier pricing is communicated to a prospective customer.

---

## 7. Payments — B2B is a different business than consumer mobile money

GrowStream Consumer's payment rails (MTN, Airtel, Zamtel mobile money, self-serve checkout) do not fit how institutional and enterprise customers actually buy:

- A university or mining company will expect **invoicing, purchase orders, and possibly NET-30 payment terms** — not a mobile money prompt.
- Larger customers may require **annual contracts negotiated by a human**, not a self-serve signup flow.
- This implies a genuinely different sales motion (a sales process, contract management, possibly a CRM) alongside the technical payment integration — a business-process gap, not just an engineering one.

Individual creators and small organisations can likely still use the existing self-serve mobile money flow — but Business/Enterprise tiers need this addressed explicitly, not assumed to inherit consumer payment rails.

### Bring Your Own Payment (BYOP) — merchants connect their own payment system

Some merchants will want to collect payments through **their own payment provider account** rather than through GrowStream's rails. This must be a first-class capability, not an afterthought. There are two payment modes per tenant:

1. **GrowStream-managed (default):** payments run through the existing MyGrowNet payment infrastructure (MTN, Airtel, Zamtel, cards), with the tenant receiving the fee-split payout (Section 6).
2. **BYOP (tenant-connected):** the tenant connects their own provider account (e.g. their own Paystack, Flutterwave, DPO, Stripe, or local gateway). Payments go tenant → provider → tenant's account; GrowStream does not hold the funds.

The tenant should be able to choose, per portal (or per plan/product), which mode applies.

**What BYOP requires:**

- **Per-tenant gateway configuration** — a tenant supplies their own provider credentials (API keys, merchant/terminal IDs) for one or more supported gateways. Credentials are stored **encrypted, per-tenant, never exposed** to other tenants or to the frontend.
- **Gateway abstraction** — reuse the existing MyGrowNet payment-provider abstraction (the platform already has a `PaymentProvider` contract and multiple gateway implementations). BYOP adds *tenant-scoped* provider instances resolved at checkout time by the tenant's configuration, rather than the platform's global provider config.
- **Tenant-routed webhooks** — provider payment notifications must be verified (provider signature) and routed to the **correct tenant's** order/entitlement. A webhook for Tenant A must never create an entitlement for Tenant B (see Section 11 isolation).
- **Entitlement creation is mode-agnostic** — whether the payment landed via GrowStream rails or the tenant's provider, a successful confirmation must trigger the same order → entitlement → unlock flow (Section 9). The access rules stay enforced server-side regardless of which rail collected the money.
- **Fees change in BYOP mode** — when the tenant's own gateway collects the money, GrowStream no longer has a revenue-share capture point (the money never passes through GrowStream). The platform fee must then be collected differently: e.g. a flat platform subscription, a metered usage fee, or a top-up/wallet model. This is a **Section 6 fee-model decision**, not a detail to resolve during implementation.
- **Refunds and disputes** — refunds in BYOP mode are executed against the tenant's provider account, not GrowStream's. The tenant owns that relationship; GrowStream surfaces the tools and audit trail.
- **Payout ledger** — in BYOP mode there is no GrowStream-side payout balance for that tenant's portal revenue (money stays in their provider account). The financial dashboard (Section 19 of the source) must show both modes clearly: "held by GrowStream" vs. "collected via your provider."
- **API exposure** — gateway selection and configuration should be manageable via the platform API too (Section 12), so an institution can toggle rails programmatically.

**Phasing note:** the gateway abstraction and per-tenant credential store belong in Phase 2 (Monetisation) alongside entitlements; the fee-model change it forces belongs to Section 6 and must be settled **before** Phase 2 pricing.

---

## 8. Three publishing options (unchanged from original, confirmed sound)

Every piece of content has a publishing destination: **GrowStream Public** (existing 70% creator model, public discovery), **Customer Portal** (private, branded-only), or **Both** (free/marketing content publicly, premium content gated to the portal). This lets a creator use public GrowStream as a funnel into their own paid platform — a good mechanic, keep as designed.

**Model as the `publishing_destination` axis** (`public` / `portal` / `both`) — this is the *where* axis and is **separate** from `access_level` (the *who* axis: who may watch). A video can be `publishing_destination=portal` with `access_level=premium`; or `destination=public` with `access_level=free`; or `destination=both` where the free first episode is public and the rest are portal-gated. These are independent columns; do not merge them into one visibility enum (see Section 0).

---

## 9. Content types, courses, access models, subscriptions, course purchases

*(Unchanged from original — see the source document for full detail: videos, series, episodes, courses/modules/lessons, playlists, live content, downloadable resources; access models of Free/Registered/Subscription/Course purchase/Individual purchase/Private; subscription plan structure; one-time course purchase and entitlement flow.)*

These are well-specified and don't need rework — they're appropriately deferred to Phase 2 per Section 10 below.

---

## 10. Trust & safety for tenant-uploaded content — currently unaddressed

GrowStream Consumer has (or is building) a content moderation/vetting queue before public content goes live. **GrowStream Platform has no equivalent story.** Before any tenant can upload to a private portal:

- Decide whether private/tenant content is moderated at all, or trusted by default because it's "private."
- Establish legal exposure: if an organisation uploads illegal or harmful content to their private portal on GrowStream's infrastructure, under their own domain, what is GrowStream's liability and takedown process?
- Define a reporting/abuse mechanism even for content that never appears on the public catalogue.

### Copyright / piracy is the highest-probability risk — address it explicitly

For a video platform, the realistic #1 tenant risk is not "harmful content" but **piracy**: a tenant uploading copyrighted films, music, or broadcast footage to a portal hosted under `growstream.app`. This is distinct from illegal-content moderation and needs its own policy:

- **Copyright policy + takedown process** (DMCA-style notice-and-takedown, adapted to Zambia) covering how a rights holder reports infringement and how quickly content is removed.
- **Strike / repeat-offender handling** per tenant, and whether repeat infringement triggers tenant suspension.
- **Hosted subdomain vs. custom domain exposure differs:** on a hosted subdomain (`tenant.growstream.app`) the infringement is more directly attributable to GrowStream, increasing exposure; on a custom domain the tenant carries more of the direct association but GrowStream still provides the underlying hosting and streaming. Define how the takedown and liability posture differs per case.

This matters more here than for the vetted individual-creator model on public GrowStream, because tenants are arbitrary third-party organisations, not individually vetted creators — and because pirated premium video is the exact content pirates want to host.

---

## 11. Multi-tenancy — real retrofit work, not "reuse existing architecture"

The original document states multi-tenancy should "use the existing MyGrowNet organisation architecture." In practice: **GrowStream Consumer's current data model is shaped around a single public catalogue, not per-organisation isolation.** Retrofitting genuine tenant isolation — so Organisation B cannot access Organisation A's users, content, or signed video playback URLs under any circumstance — is a non-trivial architecture project, not a checkbox. Scope it honestly when the time comes:

```text
Organisation A                    Organisation B
 ├── Users                         ├── Users
 ├── Videos                        ├── Videos
 ├── Courses                       ├── Courses
 ├── Orders                        ├── Orders
 └── Subscriptions                 └── Subscriptions
```

No organisation should be able to access another's data — verified server-side, on every request, not assumed from routing.

### Identity across tenants — make the model explicit

The isolation diagram above shows "Organisation A Users / Organisation B Users," but the platform runs on **one shared users table**. Decide this explicitly so the tenant-boundary work doesn't fork the user model:

- **One global identity** (the existing `users` table) — a person has a single GrowStream/MyGrowNet account and can be a member of, or subscriber to, many tenants. A student enrolled at two academies, or a staff member who is both an ABC College admin and a Mr Banda subscriber, is one user with multiple memberships/entitlements.
- **Tenant membership is a join, not a copy:** per-org membership, role, entitlement, and progress live on organisation-scoped records, not duplicated user rows.

Keep this: **users stay global; tenants add organisation-scoped memberships, entitlements, and progress.** This matches the existing MyGrowNet `organization_members` architecture and avoids the SSO/account-migration mess of per-tenant user tables.

---

## 12. API platform, webhooks, LMS integration, SSO

*(Unchanged from original — see the source document.)* Appropriately scoped as Phase 4–6, later-stage work. Moodle prioritised for LMS integration given its presence in Zambia — reasonable, keep as-is.

Two additions from other sections that the API surface must include:

- **Gateway configuration API** (Section 7, BYOP): manage a tenant's connected payment providers programmatically — list supported gateways, attach/detach credentials, toggle GrowStream-managed vs. BYOP per plan/product. Credentials stay encrypted server-side; the API returns references, never secrets.
- **Tenant-routed webhooks** (Section 7, BYOP): provider payment notifications are signature-verified and routed to the correct tenant's order/entitlement — cross-tenant webhook routing must never create an entitlement for the wrong organisation (Section 11).

---

## 13. Product tiers — pricing deliberately undecided

Feature gating across Creator / Professional / Business / Enterprise tiers is a reasonable starting shape. **Explicitly flagging, not silently deferring:** none of these tiers have pricing, and per Section 6 above, they can't be priced responsibly until the cost model exists. Do not communicate tier pricing externally until that modelling is done.

### Consider a free / hobby tier as an acquisition mechanic

The original tiers are all paid. Like consumer GrowStream's free tier, a **free/hobby Hubs tier** (hosted subdomain only, hard storage/delivery caps, GrowStream branding) is a low-friction way to get individual educators and creators in before they upgrade. It also gives real per-tenant usage data to feed the Section 6 cost model. Worth including in the pricing exercise even if it ships later.

### Per-tenant operations story is thin

With N tenants you inherit per-tenant operational concerns that Consumer (single catalogue) never had. At minimum decide who owns: tenant-level quotas/limits enforcement, per-tenant analytics + alerting, tenant support SLAs and escalation, tenant onboarding/offboarding, and abuse detection across tenants (see Section 10). None of this is specified yet — flag it as an ops workstream alongside Phase 1.

### Data residency

If Zambia or the region ever imposes data-sovereignty or local-hosting requirements for institutional/educational data, note it here as an open question — Cloudflare Stream (global edge) and the current MyGrowNet stack should be re-assessed for any tenant that requires data to stay in-country. Not a current requirement; document the assumption.

---

## 14. Implementation principle (unchanged, load-bearing)

> **Do not rebuild GrowStream. Extend the existing GrowStream architecture and MyGrowNet platform core.**

Reuse: users, organisations, authentication, video upload, Cloudflare Stream, video playback, creator infrastructure, watch tracking, payments, notifications, permissions, financial events, workspace architecture.

---

## 15. Phasing — with explicit "prep only" framing added

### Phase 0 (current): Architecture prep only
No user-facing build. Ensure content-visibility, organisation, and video-ownership models in the *current* GrowStream Consumer codebase don't foreclose this later.

### Phase 1: Multi-tenant Creator Hubs
Organisation activation, hosted subdomains, portal branding, content visibility, customer dashboard, private content, user registration. **Blocked on:** GrowStream Consumer launch and stabilisation.

### Phase 2: Monetisation
Subscription plans, course purchases, entitlements, orders, payment integration, revenue allocation. **Includes Bring Your Own Payment** (Section 7): per-tenant gateway config, encrypted credential store, tenant-routed provider webhooks, and the fee-model change BYOP forces. **Blocked on:** Section 7 (B2B payment/billing model + BYOP fee decision) and Section 6 (cost model covering both money-flow modes).

### Phase 3: Custom domains
Per Section 5 — scoped as its own project.

### Phase 4: API
API auth, video/course/user/subscription/entitlement/progress APIs.

### Phase 5: Integrations
Webhooks, embedded player, Moodle integration, OAuth/SSO.

### Phase 6: Enterprise
SSO, advanced analytics, enterprise controls, advanced security, custom integrations, custom contracts.

---

## 16. Architectural Enhancements & Refinements (2026 Audit)

To ensure GrowStream Hubs seamlessly integrates with MyGrowNet's existing DDD and platform core infrastructure, the following 5 technical refinements are incorporated into the specification:

### 1. Integration with Platform Core Domain Resolution (`ResolveDomainContext`)
- Reuse MyGrowNet's canonical `domains` table (`2400xx_add_domains_table`).
- Hosted subdomains (`*.growstream.app`) and connected custom domains (`www.mymathstuition.com`) resolve via `ResolveDomainContext` middleware to `DomainResolution` (`type = 'organization'`, `target_id = organization_id`).
- Guarantees zero cross-tenant contamination during request lifecycle.

### 2. Immediate Zero-Downtime Schema Prep
- Add `publishing_destination` (`public`, `portal`, `both`, default `public`) and nullable `organization_id` columns to `growstream_videos` and `growstream_video_series` now.
- Keeps current consumer codebase working with zero breaking changes while opening the door for Hubs content scoping.

### 3. Usage Metering & Quota Engine (`TenantUsageMeter`)
- Implement a real-time usage meter (`TenantUsageMeter.php`) tracking stored video minutes ($5/1000 min Cloudflare cost) and streamed bandwidth per organization.
- Triggers soft warnings at 80% quota and hard streaming playback throttles at 100% quota to protect platform margins.

### 4. Tenant-Scoped BYOP Resolution (`PaymentGatewayFactory`)
- Extend platform core `App\Domain\PlatformPayments\PaymentGatewayFactory` to accept `(?Organization $tenant = null)`.
- Resolves tenant-specific encrypted credentials from `organization_payment_gateways` table for BYOP mode checkout without altering the core payment pipeline.

### 5. Automated Cloudflare Video Fingerprinting & Piracy Mitigation
- Hook Cloudflare Stream's automated hash matching API into `tusComplete` video processing.
- Instantly flags duplicate copyrighted uploads across private tenant portals before video status is marked as `ready`.

---

## 17. Creator Hub Tenant Dashboard Architecture & Dual-State UX Specification

The **Creator Hub Tenant Dashboard** (`/creator/dashboard`) provides the administrative interface for tenant owners (e.g. Mr. Banda managing `mrbanda.mygrownet.com`). It is intentionally distinct from the public GrowStream Studio dashboard, focusing on **operating an academy business** rather than managing a single public channel.

### 1. UX Priorities & Design Principles
1. **Health-First over Vanity Metrics**:
   - Leads with business health signals: **Active Students**, **Monthly Revenue**, **Course Completion Rate %**, **Pending Payout**, and immediate **Alert Banners** (e.g., failed subscription payments).
2. **Grouped Navigation Hierarchy**:
   - **Content**: Videos & Series, Courses & Modules.
   - **Business**: Students & Audience, Subscriptions & Orders, Payments & Payouts, Attribution Analytics.
   - **Setup**: Branding, Custom Domain, Team Members, API & Integrations (collapsible by default to eliminate interface noise for solo educators).

### 2. Dual-State Lifecycle Architecture

```text
               CREATOR HUB TENANT LOG-IN
                           │
             ┌─────────────┴─────────────┐
             ▼                           ▼
     NEW TENANT STATE           ESTABLISHED STATE
 (0 Uploaded Courses/Videos)  (≥1 Published Courses/Videos)
             │                           │
 ┌──────────────────────────┐ ┌──────────────────────────┐
 │ - Setup Progress (2/5)   │ │ - Failed Payment Alerts  │
 │ - Unblocking Checklist   │ │ - 4 Health Stat Cards    │
 │ - Next Action Highlight  │ │ - 7-Day Watch Time Chart │
 └──────────────────────────┘ │ - Most Watched Lessons   │
                              └──────────────────────────┘
```

- **New Tenant State**: Focuses on guided onboarding via a 5-step checklist (*Academy Provisioned ➔ Branding Configured ➔ Upload First Course/Video [Active CTA] ➔ Connect Payment Gateway ➔ Publish Domain*).
- **Established Academy State**: Provides a full operational control center with a 7-day watch-time bar chart and top student engagement tables.
- **Interactive Preview Switcher**: The frontend Vue component ([`Dashboard.vue`](file:///c:/Apache24/htdocs/mygrownet/resources/js/Pages/GrowStream/Creator/Dashboard.vue)) includes an interactive state toggle allowing creator admins to preview both views.

### 3. Workspace Entry & SSO Integration
- **Context Resolution**: Integrated with MyGrowNet's `ContextResolverService` and `WorkspaceContext`.
- **Flexible Entry Points**:
  1. **Direct Tenant Subdomain Entry**: Logging in at `mrbanda.mygrownet.com` resolves `DomainResolution(type = 'organization')` and directs straight to the academy dashboard.
  2. **Shared Platform Entry with Workspace Switcher**: Logging in at `auth.mygrownet.com` passes `organization_id` in `WorkspaceContext`, allowing creator owners to switch seamlessly between Personal Workspace and Creator Hub Org Workspaces.

---

## 18. Multi-Category Tenant Support & Dynamic Terminology Mapping Architecture

To support the four primary target customer categories without code duplication or separate dashboard components, Creator Hub incorporates a **Category-Aware Terminology & Behavior Engine** ([`TenantTerminologyService.php`](file:///c:/Apache24/htdocs/mygrownet/app/Domain/GrowStream/Services/TenantTerminologyService.php)).

### 1. Tenant Category Matrix

| Category | Audience Label | Enrollment Action | Content Unit | Completion Metric | Revenue Metrics | Self-Serve Checkout |
|---|---|---|---|---|---|---|
| `education` | Students | Enroll | Course / Module / Lesson | Course Completion | Shown (K59,000) | Enabled |
| `business` | Employees | Assign | Training / Module | Compliance Completion | Hidden (Trained Count) | Disabled (Org-managed) |
| `media` | Viewers | Subscribe | Series / Episode | Watch Time | Shown (K59,000) | Enabled |
| `creator` | Subscribers | Follow | Series / Episode | Watch Time | Shown (K59,000) | Enabled |

### 2. Behavioral Differences per Category
1. **Access Model Defaults**:
   - `business` tenants: Self-serve checkout and public subscriptions are disabled. Default access is **Org-Managed Assignment** (administrators assign employees manually or via batch CSV import).
   - `education`, `media`, `creator` tenants: Self-serve mobile money and card subscriptions are enabled.
2. **Publishing Destination Toggle Visibility**:
   - Hidden for `business` tenants (internal corporate training is strictly private `portal` content).
   - Displayed for `education`, `media`, and `creator` tenants (`public` / `portal` / `both`).
3. **Content Structure Defaults**:
   - `education` and `business` default to structured Courses ➔ Modules ➔ Lessons.
   - `media` and `creator` default to standard Series ➔ Episodes.

---

## 19. Revised Creator Journey & Centralized Auth Architecture Audit

To maintain complete architectural integrity with MyGrowNet's platform core, GrowStream and Creator Hub enforce a **Single Identity & Context-Switching Lifecycle**:

### 1. Centralized Identity Principles
- **No Isolated Registration Forms**: All "Sign Up" and "Log In" CTAs across public GrowStream (`growstream.mygrownet.com`), Creator Hub (`/hub`), and tenant portals (`mrbanda.mygrownet.com`) route directly through MyGrowNet's centralized Identity Gateway (`auth.mygrownet.com` via `RedirectToMyGrowIdentity` middleware).
- **Role Additions over Account Duplication**:
  - **"Become a Creator"** (`/creator/register`): Authenticated users apply for channel creator status; no duplicate email/password prompt.
  - **"Start a Creator Hub"** (`/hub`): Authenticated users activate an organization workspace context (`organization_id`); no duplicate account creation.
- **Workspace Switcher Integration**: Users navigate between personal streaming profiles and Creator Hub organization workspaces via MyGrowNet's global workspace launcher (`/workspace` and `ContextSwitcher.vue`).

### 2. Audience-Facing Portal UX Architecture (`mrbanda.mygrownet.com`)
- **Branded Tenant View**: Served by `PortalLayout.vue` and `PortalHome.vue` with custom branding, logo, accent colors, and tenant-scoped hero content.
- **Tenant-Scoped Entitlements**: Audience authentication is handled via centralized SSO, with course enrollments, subscriptions, and progress tracked via organization-scoped join tables (`user_application_subscriptions`, `growstream_purchases`).

---

## Strategic distinction (unchanged, central to the whole document)

**GrowStream Consumer:** a destination where people discover and watch content. **GrowStream Platform:** infrastructure that lets someone else create their own destination using GrowStream. This distinction should remain central to architecture, product positioning, database design, permissions, APIs, and financial architecture — whenever this work actually begins.
