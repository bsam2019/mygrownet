# GrowStream — Implementation Plan

**Version:** 1.4  
**Date:** August 2026  
**Based on:** PRODUCT_STRATEGIC_PLAN.md v1.0 + current codebase audit  
**Status:** Phase 1 ✅, Phase 2 ✅, Phase 3 ✅, Phase 4 ✅ (backend, Aug 2026); Phase 5 not started

---

## Phase 4 Completion Log (Aug 2026)

Creator economy monetization implemented and tested:

| Task | Status |
|---|---|
| **4.1 Pay-Per-View (Rentals)** | ✅ `growstream_video_rentals` table + `VideoRental` model + `EloquentVideoRentalRepository`. `RentalService` (`rent`, `hasActiveRental`, durations 24h/48h/7d/30d). `WatchService::canAccessVideo()` grants access via active rental (bypasses subscription gate for the rented video). |
| **4.2 Creator subscriptions** | ✅ `growstream_creator_subscriptions` table + `CreatorSubscription` model + repo. `CreatorSubscriptionService` (`subscribe` idempotent, `cancel`, `isSubscribed`, `subscriberCount`). |
| **4.3 Tips & direct support** | ✅ `growstream_creator_tips` table + `CreatorTip` model + repo. `TipService` (`send` with message/anonymous/status, `totalForCreator`, `countForCreator`). |
| **Service wiring** | ✅ Explicit `WatchService` binding in `GrowStreamServiceProvider` — the container silently substituted `= null` defaults for unbound optional class constructor deps (AccessControlService, RentalService), disabling gating. Added `use` imports so `RentalService`/`CreatorSubscriptionService`/`TipService`/`RevenuePoolService`/`PayoutService`/`WatchService` singletons bind to the correct FQCNs (they were resolving to the provider's namespace). |
| **4.4 Sponsorship fund** | ✅ `growstream_sponsorship_grants` table + `SponsorshipGrant` model + repo. `SponsorshipService` (apply/approve/reject/disburse/complete; disburse credits the creator `pending_payout` ledger). `SponsorshipController` (admin) + `CreatorSponsorshipController` (creator) + `Admin/Sponsorship.vue` + `Creator/Sponsorship.vue`. |
| **Bug fix** | ✅ `Video::reconstitute()` key mismatches with Eloquent columns (`creator_profile_id`→`creator_id`, `duration_seconds`→`duration`, `provider`→`video_provider`) — would crash `WatchService::authorizePlayback` on real data. Added fallbacks. |
| **Tests** | ✅ 23 new (7 rental incl. expiry + gating integration, 7 creator economy, 9 sponsorship). **Total: 426 GrowStream tests passing.** |

**Schema additions (4 migrations):**
- `2026_08_03_000008` — `growstream_video_rentals`
- `2026_08_03_000009` — `growstream_creator_subscriptions`
- `2026_08_03_000010` — `growstream_creator_tips`
- `2026_08_03_000011` — `growstream_sponsorship_grants`

**Not yet done (Phase 4.5):** brand sponsorship marketplace (brand onboarding, creator-brand matching, campaign management) — deferred, requires product decisions.

---

## Current State Summary

The GrowStream backend is ~99% complete. 17 database tables, 15 Eloquent models, 13 repository interfaces (all implemented), 12 domain services, 43 API endpoints, 6 web controllers, 8 console commands, 15 Vue pages.

**Critical gaps remaining:** no DRM, no sponsorship fund/marketplace (Phase 4.4/4.5), no regional expansion (Phase 5).

---

## Phase 3 Completion Log (Aug 2026)

Backend for the Zambia launch implemented and tested:

| Task | Status |
|---|---|
| **3.1 Subscription & access gating** | ✅ Leveraged the existing platform module-subscription system (PawaPay checkout via `SubscriptionCheckoutController`, `config/modules/growstream.php` tiers: free/starter/business). Added `AccessControlService` mapping module tier → video access level (free/starter/business), wired into `WatchService` (`authorizePlayback`, `canWatch`) and `WatchController::canWatch()`. Added `EnsureGrowStreamSubscription` middleware (alias `growstream.subscription`). |
| **3.2 Watch-time revenue tracking** | ✅ `growstream_creator_earnings` table + `CreatorEarning` model + repository. `RevenuePoolService::calculateForPeriod()` distributes 70% of subscription revenue proportionally to premium watch seconds (upserts per creator/period). `growstream:calculate-revenue` artisan command. |
| **3.3 Payout system** | ✅ `growstream_creator_payouts` table + `CreatorPayout` model + repository. `PayoutService::processEligible()` (min threshold K100, aggregates periods, marks earnings paid), `markCompleted()`, `markFailed()`. `growstream:process-payouts` artisan command. |
| **3.4 Revenue analytics** | ✅ `AnalyticsController::revenue()` now aggregates real subscription revenue (from `module_subscriptions`), creator earnings, paid/pending payouts, revenue by tier, and daily earnings (was hardcoded zeros). |
| **Bug fix** | ✅ Added missing `growstream_watch_history.watch_duration` column (referenced by analytics/repos but never migrated) + `watch_duration` synced in `updateProgress`. |
| **Tests** | ✅ 20 new tests (5 AccessControlService unit, 4 RevenuePool, 6 Payout, 5 SubscriptionAccess integration incl. middleware/API gating). **Total: 403 GrowStream tests passing.** |

**Schema additions (3 migrations):**
- `2026_08_03_000005` — `growstream_creator_earnings`
- `2026_08_03_000006` — `growstream_creator_payouts`
- `2026_08_03_000007` — `growstream_watch_history.watch_duration`

**Not yet done (Phase 3.4 remaining):** data-saving mode, offline viewing, recommendations engine, full-text search, trending algorithm polish — deferred to frontend work (requires manual build).

---

## Current State Summary

The GrowStream backend is ~98% complete. 14 database tables, 12 Eloquent models, 10 repository interfaces (all implemented), 9 domain services, 43 API endpoints, 6 web controllers, 8 console commands, 15 Vue pages.

**Critical gaps remaining:** no DRM, no creator economy expansion (Phase 4), no regional expansion (Phase 5).

---

## Phase 2 Completion Log (Aug 2026)

All Phase 2 tasks implemented and tested:

| Task | Status |
|---|---|
| **2.1 Creator registration & onboarding** | ✅ Creator application flow (register → pending → admin approve/reject). `growstream_creator_profiles.status` enum (pending/approved/rejected/suspended) + `channel_name` + `rejected_reason`/`suspension_reason`/`suspended_at`. `growstream_creator_agreements` table + `CreatorAgreementRepository` for version-tracked digital acceptance. `CreatorProfileService::applyForCreator/approveCreator/rejectCreator/acceptAgreement/pendingCreators`. `CreatorOnboardingController` + `GrowStream/Creator/Register.vue` + `PendingApproval.vue` + `Dashboard.vue`. |
| **2.2 Creator self-service upload** | ✅ `CreatorVideoController` (index/create/store/edit/update/destroy/analytics) + `GrowStream/Creator/Videos.vue` + `Upload.vue` + `Analytics.vue`. Upload via `VideoProviderFactory` (Cloudflare) with file OR video URL; monthly upload limit via `canUploadMore()`; category/tag handling. |
| **2.3 Content moderation workflow** | ✅ `growstream_videos.moderation_status` enum (pending_review/approved/rejected) + `moderation_reason` + `reviewed_at` + `reviewed_by`. `VideoManagementService::submitForReview/approveVideoReview/rejectVideoReview`. `ModerationController` (queue/approve/reject/publish) + `GrowStream/Admin/Moderation.vue`. Rights declaration required at upload; verified creators' uploads auto-approved; `CreatorAdminController` gained reject + improved approve. |
| **Bug fix** | ✅ `growstream_videos` was missing the `video_url` column (model/admin referenced it but no migration created it) — added via migration. |
| **Platform fix** | ✅ Registered `role` + `permission` middleware aliases in `bootstrap/app.php` (spatie auto-alias wasn't resolving, so `role:admin` failed in tests). |
| **Tests** | ✅ 23 new feature tests (creator service onboarding, HTTP onboarding, video upload + moderation). **Total: 382 GrowStream tests passing.** |

**Schema additions (4 migrations in `database/migrations/growstream/`):**
- `2026_08_03_000001` — creator profile onboarding fields (status, channel_name, rejection/suspension)
- `2026_08_03_000002` — `growstream_creator_agreements`
- `2026_08_03_000003` — video moderation fields
- `2026_08_03_000004` — `growstream_videos.video_url` (schema gap fix)

---

## Current State Summary

The GrowStream backend is ~95% complete. 11 database tables, 9 Eloquent models, 8 repository interfaces (all implemented), 7 domain services, 43 API endpoints, 6 web controllers, 4 jobs, 3 events, 4 listeners, 15 Vue pages, 4 console commands.

**Critical gaps remaining:** no subscription/payment integration (Phase 3), revenue analytics placeholder, no DRM.

---

## Phase 1 Completion Log (Aug 2026)

All Phase 1 tasks implemented and tested:

| Task | Status |
|---|---|
| **1.1 Cloudflare Stream provider** | ✅ `CloudflareStreamProvider` implements `VideoProviderInterface` — direct upload (TUS URL), server-side streaming PUT, status polling (ready/processing/failed), signed playback URLs (HMAC token), customer-subdomain normalization, delete. Wired in `VideoProviderFactory` + registered as singleton. |
| **1.2 Repository implementations** | ✅ `EloquentWatchlistRepository`, `EloquentVideoViewRepository`, `EloquentVideoTagRepository` created + bound in `GrowStreamServiceProvider`. All 8 repository interfaces now have implementations. |
| **1.3 Domain exceptions** | ✅ 9 exception classes in `app/Domain/GrowStream/Exceptions/` (base `GrowStreamException extends \RuntimeException` + 8 specific). Services updated to throw typed exceptions with messages preserved. |
| **1.4 Thumbnail generation** | ✅ `GenerateThumbnailsJob` now: (1) reuses provider-generated thumbnails (Cloudflare), (2) FFmpeg frame extraction when the binary + local source are available, (3) content-type placeholder fallback. |
| **1.5 Automated tests** | ✅ 15 Cloudflare provider tests + 32 repository feature tests. Existing 312 unit tests still pass. **Total: 359 GrowStream tests passing.** |
| **Bug fix** | ✅ `ProcessVideoJob` called non-existent `getStatus()` → now uses `getVideo()`. `ProviderVideoResponse` gained `?string $error` field. `getViewsAnalytics` now handles string dates from SQLite. |

**Config:** `GROWSTREAM_VIDEO_PROVIDER=cloudflare` + `CLOUDFLARE_*` env keys already present in `.env`/production. Switch default provider to `cloudflare` in production `.env` when ready to cut over from DigitalOcean Spaces.

---

## Current State Summary

The GrowStream backend is ~90% complete. 10 database tables, 8 Eloquent models, 4 domain entities, 13 value objects, 8 repository interfaces (all implemented), 7 domain services, 43 API endpoints, 4 jobs, 3 events, 4 listeners, 11 Vue pages, 4 console commands. The frontend has browse, video detail, series detail, admin video management, admin analytics, and admin creator management pages.

**Critical gaps remaining:** no subscription/payment integration, no creator self-service uploads, revenue analytics placeholder, no DRM.

---

## Phase 1 — Foundation Completion (Finish MVP)

**Goal:** Close remaining gaps so the platform is shippable for admin-managed content.

### 1.1 Cloudflare Stream Provider

**Priority:** HIGH  
**Estimate:** 2–3 days  
**Dependencies:** Cloudflare API credentials already configured

| Task | Detail |
|---|---|
| Implement `CloudflareStreamProvider` | Implements `VideoProviderInterface`. Uses Cloudflare Stream API at `https://api.cloudflare.com/client/v4/accounts/{account_id}/stream`. Methods: `upload()` (direct upload URL via POST), `getVideo()` (GET video status), `getPlaybackUrl()` (Cloudflare Stream signed URL), `delete()` (DELETE video), `getUploadStatus()` (poll ready/processing/failed) |
| Wire `VideoProviderFactory` | Remove `throw new \Exception('not yet implemented')` for `'cloudflare'` case. Create and return `CloudflareStreamProvider` with env-configured credentials |
| Add `CLOUDFLARE_CUSTOMER_SUBDOMAIN` env | For branded video URLs (`customer-{code}.cloudflarestream.com`) |
| Switch default provider | Set `GROWSTREAM_VIDEO_PROVIDER=cloudflare` in production .env |
| Update `config/growstream.php` | Document Cloudflare as default provider, add Cloudflare-specific config keys |
| **Files:** `app/Domain/GrowStream/Infrastructure/Providers/CloudflareStreamProvider.php` (new), `VideoProviderFactory.php` (edit), `config/growstream.php` (edit), `.env.example` (edit) |

### 1.2 Repository Implementation Gap

**Priority:** HIGH  
**Estimate:** 1 day  

| Task | Detail |
|---|---|
| `EloquentWatchlistRepository` | Implements `WatchlistRepositoryInterface`. currently used directly via Eloquent model in controllers — extract to repository |
| `EloquentVideoViewRepository` | Implements `VideoViewRepositoryInterface` |
| `EloquentVideoTagRepository` | Implements `VideoTagRepositoryInterface` |
| Bind all 3 in `GrowStreamServiceProvider` | `$this->app->bind(Interface::class, Implementation::class)` |
| **Files:** 3 new repo files in `app/Infrastructure/Persistence/Repositories/GrowStream/`, edit `GrowStreamServiceProvider.php` |

### 1.3 Domain Exceptions

**Priority:** MEDIUM  
**Estimate:** 0.5 days  

| Task | Detail |
|---|---|
| Create exception classes | `VideoNotFoundException`, `SeriesNotFoundException`, `CreatorNotFoundException`, `InsufficientAccessException`, `UploadFailedException`, `ProcessingFailedException` — all extend `GrowStreamException` which extends `\DomainException` |
| Replace generic exceptions | Replace `\RuntimeException`/`\InvalidArgumentException`/`\Exception` in services and controllers with domain-specific exceptions |
| **Directory:** `app/Domain/GrowStream/Exceptions/` |

### 1.4 Thumbnail Generation

**Priority:** MEDIUM  
**Estimate:** 1 day  

| Task | Detail |
|---|---|
| Implement `GenerateThumbnailsJob` | Use FFmpeg (if available on server) or Cloudflare Stream's thumbnail API to extract thumbnails at 10%, 30%, 50% positions. Fall back to first frame capture |
| Update video `thumbnail_url` after generation | Set on the Video entity once thumbnail is generated |
| **Files:** `Jobs/GenerateThumbnailsJob.php` (edit), optionally use Cloudflare thumbnail URL instead |

### 1.5 Automated Tests

**Priority:** HIGH  
**Estimate:** 3–4 days  

| Task | Detail |
|---|---|
| Value Object tests | Test each of the 13 VOs for construction, equality, label/color, fromString |
| Entity tests | Test Video, VideoSeries, CreatorProfile, WatchHistory — creation, reconstitution, behavior methods, state transitions |
| Repository tests | Feature tests for all 8 Eloquent repository implementations (CRUD, queries, edge cases) |
| Service tests | Unit/feature tests for VideoManagementService, VideoCatalogService, WatchService, CreatorProfileService |
| Controller tests | Feature tests for API endpoints (public + admin) |
| **Directory:** `tests/Unit/Domain/GrowStream/`, `tests/Feature/GrowStream/` |

---

## Phase 2 — Content Acquisition & Creator Onboarding

**Goal:** Enable creators to self-register, upload, and manage their content, with admin moderation. Target: 40 strategic partners.

### 2.1 Creator Registration & Onboarding Flow

**Priority:** HIGH  
**Estimate:** 2–3 days  
**Dependencies:** Phase 1 complete  

| Task | Detail |
|---|---|
| Creator registration page | Vue page `pages/GrowStream/Creator/Register.vue` — collects bio, content focus area, social links, portfolio links, agreement acceptance |
| `CreatorOnboardingController` | `showRegister`, `storeRegistration` (creates creator profile with status=pending), `showPendingApproval` |
| Creator agreement service | Stores digital acceptance of terms (rights, revenue share, content standards). Model `CreatorAgreement` with version tracking |
| Admin creator approval dashboard | Approve/reject creator applications, view pending list |
| **Routes:** `POST /creator/register`, `GET /creator/pending-approval` |
| **Files:** ~6 new files (controller, Vue pages, model, migration) |

### 2.2 Creator Self-Service Upload

**Priority:** HIGH  
**Estimate:** 2–3 days  

| Task | Detail |
|---|---|
| Creator upload page | Vue page `pages/GrowStream/Creator/Upload.vue` — file upload with progress, title/description/poster/category/tags/access-level form |
| Creator video dashboard | `pages/GrowStream/Creator/Videos.vue` — list creator's videos with status badges, edit/delete actions |
| `CreatorVideoController` | `index`, `create`, `store`, `edit`, `update`, `destroy` — scoped to authenticated creator's own videos |
| Creator analytics page | `pages/GrowStream/Creator/Analytics.vue` — views, watch time, completion rate, revenue |
| Rate limiting | Enforce `upload_limit_per_month` from config (default 50) |
| **Routes:** `GET/POST /creator/videos`, `GET /creator/analytics`, `GET /creator/videos/{id}/edit` |
| **Files:** ~5 new files |

### 2.3 Content Moderation Workflow

**Priority:** HIGH  
**Estimate:** 1–2 days  

| Task | Detail |
|---|---|
| Rights declaration at upload | Required checkbox + copyright declaration text on upload form |
| Content review queue | Admin page listing videos with status=pending_review |
| Approval workflow | Admin can approve (status=ready) or reject (status=rejected) with reason |
| Expedited approval for verified partners | Verified creators' uploads skip review (auto-approved) |
| Content takedown | Admin can take down published content with reason/copyright claim |
| Email notifications | Notify creator on approval, rejection, takedown |
| **Vue:** `pages/GrowStream/Admin/Moderation.vue` (new) |
| **Files:** ~3 new files |

---

## Phase 3 — Zambia Launch

**Goal:** Release to viewers with subscription monetization, premium content, and local payments.

### 3.1 Subscription & Payment Integration

**Priority:** HIGH  
**Estimate:** 4–5 days  
**Dependencies:** Platform Payments domain exists (Phase F2, Jul 2026)  

| Task | Detail |
|---|---|
| GrowStream subscription plans | Create subscription plans in `subscription_plans` table: Free (limited preview), Basic (ZMW 50/mo), Premium (ZMW 100/mo), Family (ZMW 150/mo) |
| Payment checkout flow | Integrate with existing `SubscriptionCheckoutController` + `PaymentService` (PlatformPayments domain). Support mobile money (MTN, Airtel) + card |
| Premium access gating | Middleware `growstream.subscription` that checks user has active subscription before serving premium/full videos. Preview (5 min) for non-subscribers |
| Free tier restrictions | 5-minute preview only, ads-supported placeholder, watermarked? |
| **Files:** ~5 files (middleware, plan seeder, checkout Vue pages) |
| **Config:** Leverage `PlatformPayments` + `PaymentGateway` infrastructure |

### 3.2 Watch-Time Revenue Tracking

**Priority:** HIGH  
**Estimate:** 2–3 days  

| Task | Detail |
|---|---|
| Revenue pool calculation | Daily/weekly cron job: total premium subscription revenue × 70% → distribute to creators proportionally by premium watch minutes |
| `CreatorEarning` model | Tracks per-creator earnings per period: total minutes watched by premium users, pool share, calculated payout |
| Revenue dashboard (admin) | Replace hardcoded zeros in `AnalyticsController@revenue` with real pool/revenue data |
| Creator earnings page | Vue page showing creator's earnings breakdown, payout schedule, history |
| Anti-gaming safeguards | Minimum watch duration before counting (e.g. 30 seconds), unique viewer per video per day, bot detection |
| **Files:** ~5 new files |

### 3.3 Payout System

**Priority:** MEDIUM  
**Estimate:** 2 days  

| Task | Detail |
|---|---|
| Payout processing | Monthly payout run: calculate each creator's earnings, generate payout records, process via mobile money API (MTN/Airtel via AntiCorruption adapters) |
| `CreatorPayout` model + migration | Payout records with status (pending, processing, completed, failed), amount, reference |
| Minimum payout threshold | K100 minimum (from config), roll over to next period if below threshold |
| Payout history | Creator-facing payout history page |
| **Files:** ~4 new files |

### 3.4 Viewer Experience Enhancements

**Priority:** MEDIUM  
**Estimate:** 2–3 days  

| Task | Detail |
|---|---|
| Data-saving mode | Lower quality transcodes (via Cloudflare Stream dynamic quality) |
| Offline viewing | Download for offline (requires DRM consideration) |
| Continue watching | Already partially implemented — polish UI |
| Content recommendations | Simple collaborative/hybrid: "Viewers who watched X also watched Y" + "Because you watched [genre]" |
| Search improvements | Full-text search on title + description + tags (MySQL `FULLTEXT` index or Meilisearch) |
| Trending algorithm | Views in last 7 days + watch time weighting |
| **Files:** ~8 Vue component edits/new |

---

## Phase 4 — Creator Economy Expansion

**Goal:** Additional monetization streams for creators beyond the watch-time pool.

### 4.1 Pay-Per-View (Rentals)

**Priority:** MEDIUM  
**Estimate:** 3–4 days  

| Task | Detail |
|---|---|
| Rental pricing model | Creators set price for individual videos (e.g. K20–K50 for a movie, K5–K10 for a comedy special) |
| Rental checkout flow | User pays → granted access for limited time (24h, 48h, 7 days) |
| `VideoRental` model + migration | Tracks rental purchases, expiry, access grants |
| Revenue split for rentals | 70/30 same as watch-time pool, or creator-determined split |
| Rental access gating | Middleware checks rental status before serving video |
| **Files:** ~6 new files |

### 4.2 Creator Subscriptions

**Priority:** MEDIUM  
**Estimate:** 2–3 days  

| Task | Detail |
|---|---|
| "Subscribe to Creator" button | Fans pay monthly fee to support a specific creator, get exclusive content/badges |
| `CreatorSubscription` model | Per-user, per-creator subscription with status + renewal |
| Exclusive content gating | Creators mark videos as subscriber-exclusive |
| Creator subscription dashboard | Manage subscribers, exclusive content |
| **Files:** ~5 new files |

### 4.3 Tips & Direct Support

**Priority:** LOW  
**Estimate:** 1–2 days  

| Task | Detail |
|---|---|
| Tip button on video page | One-click tip via mobile money (predefined amounts: K5, K10, K20, K50, custom) |
| `CreatorTip` model | Tip records with amount, message, anonymous option |
| Tip leaderboard | Top tippers per creator (optional, opt-in) |
| Revenue split for tips | Creator keeps 90%, platform 10% |
| **Files:** ~3 new files |

### 4.4 Creator Sponsorship Fund (GrowStream-side)

**Priority:** LOW  
**Estimate:** 2–3 days  

| Task | Detail |
|---|---|
| Fund allocation dashboard | Admin manages sponsorship fund pool, allocates to selected creators |
| Creator application for sponsorship | Creators submit proposals for funding |
| `SponsorshipGrant` model | Tracks grants, milestones, disbursements |
| Revenue share on sponsored content | GrowStream recoups via higher platform share on sponsored productions |
| **Files:** ~4 new files |

### 4.5 Sponsorship Marketplace (Brands)

**Priority:** LOW (Future)  
**Estimate:** TBD  

| Task | Detail |
|---|---|
| Brand onboarding | Brands register, browse creators, create sponsorship offers |
| Creator-brand matching | Algorithmic or manual matching |
| Campaign management | Brand manages sponsorship campaign, views metrics |
| Revenue split | Platform 15%, creator 85% (brokered by platform) |

---

## Phase 5 — Regional Expansion

**Goal:** Expand beyond Zambia to Southern Africa, then wider Africa.

### 5.1 Multi-Country Support

| Task | Detail |
|---|---|
| Country-specific content | Regional content categories, country filters |
| Multi-currency payments | ZMW, ZAR, USD, KES, NGN — fall back to platform `FinancialServicesCore` |
| Localization | UI translations for Swahili, French, Portuguese |
| Regional payment methods | M-Pesa (Kenya/Tanzania), MTN MoMo (various), Airtel Money |
| CDN optimization | Cloudflare regional edge caching |

---

## Prioritized Implementation Order

```
Now (Sprint 1–2):  
  1.1 Cloudflare Stream Provider       ← unlocks real video infrastructure
  1.2 Repository implementations       ← complete the DDD architecture
  1.3 Domain exceptions                ← proper error handling
  1.5 Automated tests                  ← quality baseline

Sprint 3–4:  
  2.1 Creator registration/onboarding  ← get creators into the platform
  2.2 Creator self-service upload      ← creators manage own content
  2.3 Content moderation workflow      ← quality control

Sprint 5–6:  
  3.1 Subscription & payment           ← monestiation live
  1.4 Thumbnail generation             ← polish
  3.4 Viewer experience enhancements   ← competitive UX

Sprint 7–8:  
  3.2 Watch-time revenue tracking      ← core monetization
  3.3 Payout system                    ← creators get paid

Sprint 9+:  
  Phase 4 (creator economy expansion)  ← additional revenue streams
  Phase 5 (regional expansion)         ← growth
```

---

## Architecture Decisions

1. **Cloudflare Stream first.** DO Spaces is a stopgap. Cloudflare provides encoding, adaptive streaming, signed URLs, and analytics out of the box. The provider abstraction already supports this swap.

2. **Platform Payments reuse.** GrowStream does NOT build its own payments. It uses the existing `PlatformPayments` domain (`PaymentService`, `PaymentGateway` adapters for MTN/Airtel, `PaymentTransaction`), `SubscriptionCheckoutController`, and `FinancialServicesCore` for multi-currency.

3. **Creator profiles stay in GrowStream.** `growstream_creator_profiles` is the canonical creator record. Links to platform `organizations` table for creators who also have org accounts, but creators don't need organizations.

4. **Revenue tracking is GrowStream-specific.** While the platform has `PlatformBilling`, the 70% watch-time pool distribution is GrowStream's business logic. Payouts flow through platform payments.

5. **Frontend stays in the GrowStream module build.** All Vue pages live under `pages/GrowStream/` and are built via `npm run build:growstream`. No shared workspace dependencies for the streaming experience — the viewer UI is self-contained.

---

## Success Metrics (from Strategic Plan)

| Category | Metric | Target |
|---|---|---|
| Viewer | Monthly active users | Launch: 500, Year 1: 5,000 |
| Viewer | Average watch time | >15 min/session |
| Viewer | Subscription conversion | >5% of free users |
| Creator | Active creators | Launch: 40, Year 1: 100 |
| Creator | Average monthly earnings | >K500 |
| Business | Monthly platform revenue | Break-even by Month 12 |
| Technical | Playback start time | <3 seconds |
| Technical | Video processing success | >95% |
