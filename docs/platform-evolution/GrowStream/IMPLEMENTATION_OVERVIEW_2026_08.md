# GrowStream Implementation Overview & Public Launch Readiness

**Status: Current state review — August 2026. Reflects the actual codebase and production data at time of writing.**

> This document supersedes the March 2026 `IMPLEMENTATION_STATUS.md` (which described the pre-launch MVP scaffold). It reviews what is genuinely implemented now, separates "built and live" from "built but not yet populated / not yet hardened," and gives a launch-readiness verdict.

---

## 1. Executive summary

GrowStream's **software is substantially built** — the consumer product (web) has a complete surface: public landing + auth, discovery, search, video playback with entitlements, watch history, downloads, watchlist, creator onboarding/studio/analytics/payouts, subscriptions, PPV rentals, notifications, and an admin suite. Video infrastructure runs on **Cloudflare Stream** (production credentials configured). Payments run on **PawaPay** (production mode, mock disabled) plus gateway abstractions for MTN MoMo, Airtel Money, Zamtel, DPO, Flutterwave, and MoneyUnify.

**What it is NOT ready for:** *public launch to real audiences.* The blocking gap is not engineering — it is **content and operational readiness**. Production holds essentially **zero content** (1 video, 0 series, 1 unapproved creator, 44 empty categories) and **zero live subscriptions** (4 test rows). Launching an empty catalogue is a failed launch regardless of how complete the code is.

**Verdict: engineering-ready for a controlled/beta launch; not content-ready for a public launch.** See Section 11 for the exact gating list.

---

## 2. What is implemented and live (verified in code + production)

### 2.1 Consumer web (Vue + Inertia, dark "Stitch" theme)

| Area | Pages | Notes |
|---|---|---|
| Public marketing | `Landing.vue` + `Pages/{About,Help,Terms,Privacy,Contact}.vue` | Guest-facing landing w/ animations, promo banner, FAQ, footer. Public info pages wired. |
| Auth | Identity gateway (`auth.mygrownet.com`) via `RedirectToMyGrowIdentity` | Sessions shared via `SESSION_DOMAIN=.mygrownet.com` |
| Home (logged in) | `Home.vue` | Personalized hero (resume/trending), sticky category chips, Continue Watching, For You, Trending, Top Creators, Series, My List, Latest |
| Discovery | `Browse.vue` | Filters: search, category (44 seeded), content type, sort; paginated grid |
| Search | `Search.vue` | Keyword search across title/description + creators + series |
| Video player | `VideoDetail.vue` + `VideoPlayer.vue` | Cloudflare Stream iframe embed, entitlements check, watch progress, watchlist toggle, share, PPV rent CTA |
| Series | `SeriesDetail.vue` | Episodes grouped by season |
| Watch history | `MyVideos.vue` | History + progress |
| Downloads | `Downloads.vue` | Lists `is_downloadable` videos |
| Notifications | `Notifications.vue` | Unread count polled in header; list + mark-read/archive |
| Creator profile | `CreatorProfile.vue` | Public channel page |

### 2.2 Creator economy

| Feature | Pages | Backing |
|---|---|---|
| Creator application | `Creator/Register.vue`, `Creator/PendingApproval.vue` | `CreatorOnboardingController` — new apps get `status=pending` |
| Admin approval | `Admin/Creators.vue` | Approve/reject wired (reason modal for reject), verify, suspend, limits, pending filter |
| Creator studio | `Creator/Dashboard.vue`, `Creator/Videos.vue`, `Creator/Upload.vue` | `CreatorVideoController` — TUS resumable upload to Cloudflare Stream |
| Creator analytics | `Creator/Analytics.vue` | `AttributionService` + `CreatorEarning`/`CreatorPayout` models |
| Creator payouts | `Creator/Payouts.vue` | `PayoutService`, `ProcessPayoutsCommand` |
| Sponsorship fund | `Creator/Sponsorship.vue` | `CreatorSponsorshipController` |

### 2.3 Monetisation

| Feature | Detail |
|---|---|
| Tier plans | `config/modules/growstream.php` — Free / Starter (K35) / Premium (K75) / Unlimited (K145), monthly + annual, watch-minute allowances |
| Plans page | Centralized `Payments/ModulePlans.vue` via `SubscriptionCheckoutController@pricing`, now public (reachable pre-login) |
| Checkout | `growstream.checkout` (auth) — unified subscription checkout |
| Payment gateway | **PawaPay (production)** — `PAYMENT_DEFAULT_GATEWAY=pawapay`, `AUTOMATED_PAYMENTS_ENABLED=true`, `PAWAPAY_MOCK_MODE=false`. Mobile-money payment flow with webhook verification |
| Gateway abstraction | `App\Domain\PlatformPayments` — `GatewayProvider` (7 cases), `AbstractPaymentGateway`, `PaymentGatewayFactory`, plus MTN/Airtel/Zamtel/DPO/Flutterwave/MoneyUnify/PawaPay implementations |
| PPV rentals | `VideoRentalController` + `ActivateVideoRentalOnPaymentCompleted` listener (K15, 48h) |
| Access control | `AccessControlService` — server-side entitlement + subscription checks, watch-minute throttling |
| Subscriptions table | `module_subscriptions` (4 test rows on prod) |

### 2.4 Admin suite (GrowStream admin, behind `admin.or.role`)

`Admin/Videos.vue`, `Admin/Moderation.vue`, `Admin/Creators.vue`, `Admin/Analytics.vue`, `Admin/Categories.vue` (new), `Admin/Sponsorship.vue`. Backed by `VideoManagementController`, `ModerationController` (pending_review queue), `CreatorAdminController`, `AnalyticsController`, `CategoryAdminController`, `SponsorshipController`.

### 2.5 Content infrastructure

- **Video provider:** Cloudflare Stream (account + API token set). TUS resumable upload (`tus-init` → PATCH → `tus-complete`), thumbnail generation, signed playback URLs (signing key vars present in `.env`).
- **Asset storage:** **Wasabi** (S3-compatible) for file & thumbnail assets (`ThumbnailService`, `thumbnail_storage_disk='wasabi'`). `FILESYSTEM_DISK=wasabi`, `STORAGE_MIGRATION_MODE=wasabi_only`. DigitalOcean Spaces was discontinued and has been fully removed from the codebase (`DigitalOceanSpacesProvider` deleted; `GROWSTREAM_VIDEO_PROVIDER` defaults to `cloudflare`).
- **Schema:** `growstream_videos`, `_video_series`, `_video_categories` + pivot, `_video_tags`, `_watch_history`, `_creator_profiles`, `_watchlists`, `_video_views`, `_video_analytics_daily` — all migrated in production.
- **Categories:** 44 seeded (Comedy, Movies, Series, Music, Documentary, Education, Lifestyle, Sports & Fitness, News & Talk, Kids + subcats), manageable in admin.
- **Domain layer:** DDD structure under `app/Domain/GrowStream/` with repositories, services, Eloquent persistence.

### 2.6 Recent notable additions (from git history)

- Static dark theme (no CSS-variable indirection) — reliably dark across pages
- Landing page + shared header/footer as default UI chrome (drawer removed)
- Category management (CRUD) in admin
- Creator application approval UI
- Post-login home redesign (Stitch-style)
- Public info pages (About/Help/Terms/Privacy/Contact)

---

## 3. Production data state (measured, not guessed)

| Metric | Value |
|---|---|
| Videos (total / published) | 1 / 1 |
| Series | 0 |
| Creators (total / approved) | 1 / 0 |
| Categories | 44 |
| Users (platform-wide) | 169 |
| Module subscriptions | 4 (test) |
| Video rentals | 0 |
| GrowStream orders | 0 |

**Interpretation:** The platform is configured and the plumbing works, but the catalogue and the audience are empty. 169 users are the wider MyGrowNet platform's users, not GrowStream consumers.

---

## 4. What the code claims vs. what is actually true

| Claim (older docs) | Reality |
|---|---|
| "10 tables, MVP complete, production ready" (Mar 2026 status doc) | Schema is larger now; "production ready" was premature — content/ops gaps remain |
| "8 main categories with 32 subcategories" (old seeder) | Replaced with 10 streaming-appropriate top-level cats + subcats = 44 rows; old business/education set removed |
| DigitalOcean Spaces provider (video) | Removed — discontinued. Video runs on Cloudflare Stream; file/thumbnail assets run on **Wasabi** (S3-compatible) |
| Creator management via `/api/v1/growstream/admin` (axios) | Admin now runs on web-session Inertia routes (`growstream.admin.*`); some axios calls in `useGrowStreamAdmin` target endpoints that may not all exist — legacy path |
| Watchlist | Implemented + wired in UI |
| Manual payment numbers (MTN/Airtel) | Present as fallback, but `automated_payments_enabled=true` → PawaPay is the live path |

---

## 5. Gaps and risks (anything not built, or half-built)

### 5.1 Content & operations (the actual launch blocker)
- **Empty catalogue** — 1 video, 0 series. No reason for a viewer to stay.
- **No approved creators** — the 1 applicant is still `pending`; the approval UI works but nothing has been approved.
- **No seed/marketing content strategy executed** — categories exist but are empty.

### 5.2 Product completeness
- **Certificates** — not built (documented as future in the Hubs doc; fine).
- **Live streaming** — not built (planned/optional).
- **Native mobile apps** — not built; web PWA path exists. `sw.js` registration is **disabled** in `app-growstream.ts` (unregistered on purpose due to video playback issues) — so PWA/offline is currently off.
- **Downloads** — page exists and lists `is_downloadable` videos, but there is **no offline playback/save pipeline** verified; likely just a listing.

### 5.3 Engineering debt / hygiene
- Duplicate/legacy admin controllers (`CreatorManagementController`, `GrowStreamAdminController`, `VideoAdminController` coexist with the active ones) — dead code risk.
- `useGrowStreamAdmin` axios composable references `/api/v1/growstream/admin/*` — verify these routes still resolve or retire the composable.
- Duplicate `'resend'` key in `config/services.php`.
- Tests: ~857 GrowStream-related tests were passing historically, but no evidence of a current full-suite green run against the latest UI changes.

---

## 6. Security posture

- Server-side entitlement enforcement via `AccessControlService` (not frontend-gated) ✓
- Signed playback URLs with Cloudflare signing keys configured ✓
- Admin routes behind `admin.or.role` middleware ✓
- Identity via centralized gateway, shared-session domain ✓
- **Open items:** tenant isolation is irrelevant until the Hubs/B2B phase (documented separately); no penetration-test evidence; webhook verification relies on PawaPay secret being correct in production.

---

## 7. Recommended launch gating checklist

Before a public launch (ordered by criticality):

1. **Populate content** — approve ≥1 creator, publish a meaningful starter catalogue (target: 20–50 videos across 5+ categories, ≥5 series with 3+ episodes).
2. **Verify the paid funnel end-to-end in production** — subscribe (free + paid tier) via PawaPay, confirm entitlement unlocks premium content, confirm rental purchase.
3. **Test the full creator loop in production** — apply → approve → upload → moderate → publish → appear in catalogue → watch → analytics → payout readiness.
4. **Decide PWA/offline** — either re-enable and test the service worker, or drop the Downloads/offline claim from marketing until it works.
5. **Retire or fix legacy admin/composable paths** — remove dead controllers and the stale axios composable so there's one supported admin path.
6. **Run the test suite** against the current build and fix regressions.
7. **Ops readiness** — monitoring/alerting on Cloudflare Stream quotas, PawaPay webhook health, failed jobs (DLQ), and the analytics aggregation schedule.
8. **Seed marketing content** (thumbnails/descriptions/SEO) so the catalogue doesn't look like placeholder data.

---

## 8. What can launch first (soft-launch path)

If a controlled launch is desired before the full content strategy is ready:

- **Invite-only creator beta** — approve a handful of vetted creators, let them upload, validate the whole loop with real content, then open to viewers.
- **Free-tier-first launch** — publish the free catalogue, defer paid monetisation until content volume justifies it, then flip subscriptions on.
- **Feature flags** — the config already supports this shape (tiers, limits, payment gateway toggle).

---

## 9. Relationship to future phases

- **GrowStream Platform / Hubs** (B2B branded portals, courses, custom domains, BYOP payments) — documented in `GROWSTREAM_PLATFORM.md`; explicitly **not** a current build target. Current schema work should keep `access_level` (who) vs. `publishing_destination` (where) as separate columns so the door stays open.
- **Native apps** — web PWA is the current mobile story; apps are a documented later phase.

---

## 10. Source of truth

- Live schema: `database/migrations/growstream/`
- Domain code: `app/Domain/GrowStream/`
- Frontend: `resources/js/pages/GrowStream/`, `resources/js/layouts/GrowStreamLayout.vue`, `resources/js/components/GrowStream/`
- Routes: `routes/growstream.php` (71 named routes across public + auth + admin)
- Tier/plan config: `config/modules/growstream.php`
- Payments: `config/payment.php`, `config/services.php`, `app/Domain/PlatformPayments/`
- Product strategy: `PRODUCT_STRATEGIC_PLAN.md`, `GROWSTREAM_CONCEPT.md`, `GROWSTREAM_PLATFORM.md`
- Prior status: `IMPLEMENTATION_STATUS.md` (March 2026 — superseded by this doc)
