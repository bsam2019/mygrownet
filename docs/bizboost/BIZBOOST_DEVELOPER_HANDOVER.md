# BizBoost (MyGrowNet Marketing App) — Developer Handover

**Last Updated:** December 4, 2025  
**Status:** Phase 4 Complete - Enterprise & Learning  
**Module ID:** `bizboost`  
**Laravel Version:** 12.0 (PHP 8.3+)  
**Frontend:** Vue 3 + Vite + TypeScript

---

## Module Integration Overview

BizBoost is a **modular app** within the MyGrowNet ecosystem, following the same architecture as GrowFinance and GrowBiz. It uses the **Centralized Subscription Architecture** for tier management, usage limits, and feature gating.

### Key Integration Points

| Component | Location | Purpose |
|-----------|----------|---------|
| Module Config | `config/modules/bizboost.php` | Tier limits, features, pricing |
| Usage Provider | `app/Domain/BizBoost/Services/BizBoostUsageProvider.php` | Track usage metrics |
| Middleware | `app/Http/Middleware/CheckBizBoostSubscription.php` | Subscription enforcement |
| Routes | `routes/bizboost.php` | Module-specific routes |
| Layout | `resources/js/Layouts/BizBoostLayout.vue` | Module layout with nav |

### Related Documentation

- [Centralized Subscription Architecture](../CENTRALIZED_SUBSCRIPTION_ARCHITECTURE.md)
- [GrowFinance Module](../growfinance/) - Reference implementation
- [GrowBiz Module](../growbiz/) - Reference implementation

---

## Project Summary

### Objective

Build the **BizBoost Marketing App**: a complete marketing & growth assistant for SMEs (Zambia-first).

### Core Capabilities

- AI content generation
- Visual templates editor
- Scheduling/auto-posting (FB Pages & IG Business)
- WhatsApp campaign support
- Business mini-sites
- Marketing calendar
- Analytics
- Sales tracking
- Customer engagement
- Supplier directory
- QR codes
- MyGrowNet integration

### Primary Platforms

| Platform | Technology | Purpose |
|----------|------------|---------|
| Web SPA | Vue 3 + Vite | Main user-facing product |
| Admin Dashboard | Vue 3 (or Blade) | Platform operations |
| Mobile (Phase 2+) | Flutter (recommended) | API-first backend supports mobile |

---

## MVP Scope (Must Be Delivered First)

1. **User auth & business onboarding**
2. **Business mini-website** (profile + product list)
3. **AI Content Generator** (text-only MVP: captions, post ideas)
4. **Templates library** (static template assets + basic editor for text/logo)
5. **Manual share flow** to social apps + copy-to-clipboard caption
6. **Post scheduling** (store schedule, show calendar; publishing is manual or queued to FB Pages if integrated)
7. **Basic analytics** (views, clicks, post engagements pulled from page API where available; otherwise app-side events)
8. **Customer list** and simple WhatsApp message export (CSV) + basic broadcast composer (manual send)
9. **Admin panel**: user & business management, template management, billing overview
10. **Payments**: Stripe (or Paystack/Monnify) integration for subscriptions

---

## Tech Stack

### Backend (Aligned with MyGrowNet Platform)

| Component | Technology | Notes |
|-----------|------------|-------|
| Language | PHP 8.2+ | Same as MyGrowNet |
| Framework | Laravel 12 | Same as MyGrowNet |
| Authentication | Laravel built-in + Inertia | Uses existing auth system |
| Database | SQLite (dev) / MySQL (prod) | Same as MyGrowNet |
| Cache/Queue | Database queue | Uses existing `config/queue.php` |
| Background Jobs | Laravel Jobs / Events | Standard Laravel jobs |
| Dev Debugging | Laravel Telescope (dev only) | Optional |
| Payments | Existing payment integration | Uses MyGrowNet payment system |

### Frontend (Aligned with MyGrowNet Platform)

| Component | Technology | Notes |
|-----------|------------|-------|
| Framework | Vue 3 + TypeScript | Same as MyGrowNet |
| Build Tool | Vite with Laravel plugin | Same as MyGrowNet |
| Styling | Tailwind CSS | Same design system |
| UI Components | Radix Vue, Heroicons, Lucide | Same as MyGrowNet |
| Routing | Ziggy + Inertia | Same as MyGrowNet |
| File Upload | Local storage (dev) / S3 (prod) | Configurable |

### AI & Media (BizBoost-Specific)

| Component | Technology |
|-----------|------------|
| LLM | OpenAI API (pluggable) |
| Image Generation | Optional (future phase) |
| Processing | Background jobs for heavy AI tasks |

### Third-Party Integrations (BizBoost-Specific)

| Service | Purpose |
|---------|---------|
| Facebook Graph API | Publish to Pages & IG Business |
| Instagram Graph API | Content publishing |
| WhatsApp Business API | Future automation (MVP: export/manual) |
| OpenAI API | AI content generation |

### Deployment & Infrastructure (Uses Existing MyGrowNet Setup)

| Component | Status | Notes |
|-----------|--------|-------|
| Deployment Scripts | ✅ Existing | `deployment/` folder |
| Nginx Config | ✅ Existing | `deployment/mygrownet-nginx.conf` |
| Queue Workers | ✅ Existing | `deployment/manage-queue-worker.sh` |
| SSL | ✅ Existing | Already configured |
| Database Backups | ✅ Existing | Already automated |

**Important:** BizBoost does NOT require separate infrastructure. It integrates into the existing MyGrowNet platform.

---

## High-Level Architecture (Integrated with MyGrowNet)

```
┌─────────────────────────────────────────────────────────────────┐
│                   MyGrowNet Platform                            │
│              (Vue 3 + Inertia + TypeScript)                     │
├─────────────────────────────────────────────────────────────────┤
│  GrowFinance  │  GrowBiz  │  BizBoost  │  Main Platform        │
└─────────────────────────┬───────────────────────────────────────┘
                          │ Inertia
                          ▼
┌─────────────────────────────────────────────────────────────────┐
│                   Laravel 12 Backend                            │
│              (Shared Infrastructure)                            │
├─────────────────────────────────────────────────────────────────┤
│  Controllers → Domain Services → Repositories → Models         │
└─────────────────────────┬───────────────────────────────────────┘
                          │
          ┌───────────────┼───────────────┐
          ▼               ▼               ▼
    ┌──────────┐   ┌──────────┐   ┌──────────────┐
    │  SQLite  │   │ Database │   │    Local     │
    │   (dev)  │   │  Queue   │   │   Storage    │
    │  MySQL   │   │  (jobs)  │   │  (or S3)     │
    │  (prod)  │   │          │   │              │
    └──────────┘   └──────────┘   └──────────────┘
                          │
                          ▼
              ┌───────────────────────┐
              │    Queue Workers      │
              │  (php artisan queue:work)
              ├───────────────────────┤
              │ • AI Content Jobs     │
              │ • Media Processing    │
              │ • Social Publishing   │
              │ • Notifications       │
              └───────────────────────┘
```

**Note:** BizBoost shares the same database, queue, and storage infrastructure as the main MyGrowNet platform.

---

## Data Model (Core Tables)

### users
```sql
id, name, email, phone, password, role (user/admin), 
verified_at, meta(json), created_at, updated_at
```

### businesses
```sql
id, user_id, name, slug, description, logo_url, 
address, city, province, country, phone, whatsapp, 
timezone, locale, created_at, updated_at
```

### business_profiles (mini-website)
```sql
business_id, hero_image, banner, about, contact_email, 
socials(json), seo_meta(json)
```

### products
```sql
id, business_id, name, sku, price, currency, description, 
images(json), stock_qty, is_active, created_at, updated_at
```

### templates
```sql
id, name, category, template_json (design + token placeholders), 
thumbnail_url, price (nullable), created_by, 
visibility (global/business), created_at, updated_at
```

### posts
```sql
id, business_id, title, caption, media_urls(json), 
scheduled_at, status (draft/scheduled/published/failed), 
platform_targets(json), external_ids(json), analytics(json), 
created_at, updated_at
```

### campaigns
```sql
id, business_id, name, objective, start_date, end_date, 
campaign_json, status
```

### customers
```sql
id, business_id, name, phone, email, tags(json), notes
```

### sales
```sql
id, business_id, product_id, qty, price, total, sale_date, 
source (manual/linked_post), created_at
```

### analytics_events
```sql
id, business_id, event_type, payload(json), recorded_at
```

### integrations
```sql
id, business_id, provider (facebook, instagram, whatsapp), 
token (encrypted), meta(json), status, connected_at
```

### module_subscriptions (Centralized - Already Exists)
```sql
-- Uses centralized module_subscriptions table
-- See: docs/CENTRALIZED_SUBSCRIPTION_ARCHITECTURE.md
id, user_id, module_id ('bizboost'), subscription_tier, 
status, billing_cycle, amount, started_at, trial_ends_at, 
expires_at, cancelled_at, auto_renew, created_at, updated_at
```

### audit_logs
```sql
id, user_id, action, details(json), ip, user_agent, created_at
```

---

## Models & Relationships

```php
// User
User hasMany Businesses
User hasOne Subscription

// Business
Business belongsTo User
Business hasMany Products
Business hasMany Posts
Business hasMany Campaigns
Business hasMany Customers
Business hasMany Sales
Business hasOne BusinessProfile
Business hasMany Integrations
Business hasMany AnalyticsEvents

// Template
Template belongsTo User (optional)
Template can be global (visibility = 'global')

// Post
Post belongsTo Business

// Integration
Integration belongsTo Business

// Sale
Sale belongsTo Business
Sale belongsTo Product
```

---

## API Endpoints

### Authentication
```
POST   /api/v1/auth/register        {name, email, password, phone}
POST   /api/v1/auth/login           {email, password}
POST   /api/v1/auth/logout
POST   /api/v1/auth/forgot-password
POST   /api/v1/auth/reset-password
```

### Business & Onboarding
```
GET    /api/v1/businesses
POST   /api/v1/businesses           {name, slug, ...}
GET    /api/v1/businesses/{id}
PUT    /api/v1/businesses/{id}
```

### Templates & Editor
```
GET    /api/v1/templates            ?category=
GET    /api/v1/templates/{id}
POST   /api/v1/templates            (admin/business)
POST   /api/v1/templates/{id}/render {placeholders, brand_colors, logo}
```

### Posts & Scheduling
```
GET    /api/v1/businesses/{id}/posts
POST   /api/v1/businesses/{id}/posts {caption, media_urls, scheduled_at, platform_targets}
PUT    /api/v1/businesses/{id}/posts/{post_id}
POST   /api/v1/businesses/{id}/posts/{post_id}/publish (manual push)
DELETE /api/v1/businesses/{id}/posts/{post_id}
```

### Campaigns
```
GET    /api/v1/businesses/{id}/campaigns
POST   /api/v1/businesses/{id}/campaigns
PUT    /api/v1/businesses/{id}/campaigns/{campaign_id}
DELETE /api/v1/businesses/{id}/campaigns/{campaign_id}
```

### AI Content
```
POST   /api/v1/businesses/{id}/ai/generate-text
       {type: caption|ad|description, context, language}
```

### Integrations
```
POST   /api/v1/businesses/{id}/integrations/facebook/connect
POST   /api/v1/businesses/{id}/integrations/facebook/disconnect
GET    /api/v1/businesses/{id}/integrations
```

### Analytics
```
GET    /api/v1/businesses/{id}/analytics/overview
GET    /api/v1/businesses/{id}/analytics/posts/{post_id}
```

### Payments & Subscriptions
```
POST   /api/v1/payment/checkout     (create payment intent)
POST   /api/v1/webhooks/payment     (webhook handler)
```

### Admin
```
GET    /api/v1/admin/users
GET    /api/v1/admin/businesses
POST   /api/v1/admin/templates
```

### Webhook Listeners
```
POST   /api/v1/webhooks/facebook    (published posts, ad results)
POST   /api/v1/webhooks/whatsapp    (receipt/status)
```

---

## Background Jobs & Scheduler

### Main Queued Jobs

| Job | Purpose |
|-----|---------|
| `GenerateAiContentJob` | Heavy AI processing (async) |
| `ProcessMediaJob` | Resize, watermark, upload to CDN |
| `PublishToFacebookJob` | Handle FB Graph publish & retries |
| `PublishToInstagramJob` | Graph API for IG business |
| `CampaignSequenceJob` | Runs campaign steps per schedule |
| `AggregateAnalyticsJob` | Periodic analytics aggregation |
| `ExportCustomerListJob` | CSV/zip generation |

### Scheduler Tasks (Laravel Scheduler)

```php
// app/Console/Kernel.php

// Hourly: aggregate analytics, retry failed posts
$schedule->command('analytics:aggregate')->hourly();
$schedule->command('posts:retry-failed')->hourly();

// Cron: send scheduled posts (or jobs triggered via queue)
$schedule->command('posts:publish-scheduled')->everyMinute();

// Daily: subscription billing reconcile + reports
$schedule->command('billing:reconcile')->daily();
$schedule->command('reports:generate-daily')->dailyAt('06:00');
```

### Queue Configuration (Uses Existing MyGrowNet Setup)

BizBoost uses the existing MyGrowNet queue configuration. **Do NOT modify `config/queue.php`.**

```php
// Existing config/queue.php (already configured)
'default' => env('QUEUE_CONNECTION', 'database'),

// BizBoost jobs use the default queue
// No Horizon required - use standard queue:work
```

**Running Queue Workers:**
```bash
# Development
php artisan queue:work

# Production (use existing deployment script)
./deployment/manage-queue-worker.sh start
```

**BizBoost-Specific Queues (Optional):**
If needed for performance, BizBoost jobs can use named queues:
```php
// In job classes
public $queue = 'bizboost';  // Optional: separate queue for BizBoost

// Run with specific queue
php artisan queue:work --queue=bizboost,default
```

---

## Social APIs & Required Scopes

### Facebook Graph API (Pages & IG)

**Required Permissions:**
- `pages_manage_posts`
- `pages_read_engagement`
- `pages_read_user_content`
- `instagram_basic`
- `instagram_content_publish`

**Important Notes:**
- App Review required for publish-related permissions before production
- Use server-side long-lived page tokens stored encrypted
- Business Verification required for production access

### WhatsApp Business

- For automated messaging: need approved WhatsApp Business API & templates
- **MVP Alternative:** Generate message templates & provide export / manual broadcast instructions
- Full WhatsApp Business API integration in later phases

### Ads / Paid Posts

- Access to Marketing API requires advanced app review and business verification
- Plan for Phase 3+

---

## Frontend: Pages & Components (Vue 3 SPA)

### User Flows (Top-Level Pages)

| Page | Route | Description |
|------|-------|-------------|
| Landing | `/` | Marketing site |
| Auth | `/login`, `/register`, `/forgot-password` | Authentication |
| Dashboard | `/dashboard` | Overview |
| Onboarding | `/onboarding` | Business setup wizard |
| Business Profile | `/business/profile` | Mini-site editor + preview |
| Templates | `/templates` | Gallery |
| Template Editor | `/templates/:id/edit` | Text + logo + colors |
| Post Composer | `/posts/create` | Single/multi-media |
| Calendar | `/calendar` | Month/week view |
| Campaigns | `/campaigns` | Campaign builder wizard |
| Customers | `/customers` | CRM |
| Sales | `/sales` | Tracker + reports |
| Analytics | `/analytics` | Overview + post-level |
| Integrations | `/integrations` | FB/IG connect |
| Billing | `/billing` | Subscription page |
| Admin | `/admin/*` | Admin panel |
| Learning Hub | `/learn` | Help / Learning hub |

### Key Components

```
components/
├── layout/
│   ├── TopNav.vue
│   ├── SideNav.vue
│   └── PageHeader.vue
├── ui/
│   ├── Card.vue
│   ├── Modal.vue
│   ├── Button.vue
│   ├── DataTable.vue
│   ├── Toast.vue
│   └── Charts/
│       ├── LineChart.vue
│       ├── BarChart.vue
│       └── PieChart.vue
├── forms/
│   ├── FileUploader.vue (chunked)
│   ├── RichTextEditor.vue
│   └── ImageEditor.vue (crop/resize)
├── features/
│   ├── Calendar.vue
│   ├── PostComposer.vue
│   ├── TemplateGallery.vue
│   └── CampaignWizard.vue
└── shared/
    ├── LoadingSpinner.vue
    └── EmptyState.vue
```

### State & Data (Pinia Stores)

```typescript
// stores/
├── authStore.ts        // User authentication state
├── businessStore.ts    // Current business data
├── templatesStore.ts   // Templates cache
├── postsStore.ts       // Posts and drafts
├── campaignStore.ts    // Campaign data
├── analyticsStore.ts   // Analytics data
└── uiStore.ts          // UI state (modals, toasts, etc.)
```

### API Client

```typescript
// lib/api.ts
import axios from 'axios';

const api = axios.create({
    baseURL: '/api/v1',
    withCredentials: true, // Sanctum CSRF
});

// Interceptors for retry and 401 refresh
api.interceptors.response.use(
    response => response,
    async error => {
        if (error.response?.status === 401) {
            // Handle token refresh or redirect to login
        }
        return Promise.reject(error);
    }
);

export default api;
```

### Offline & PWA

- Service worker for offline drafts
- Cache templates locally
- Save post drafts offline
- Use IndexedDB (localforage) for local drafts
- Sync queue when online

### Localization & Languages

```typescript
// i18n configuration
import { createI18n } from 'vue-i18n';

const i18n = createI18n({
    locale: 'en',
    fallbackLocale: 'en',
    messages: {
        en: { /* English */ },
        bem: { /* Bemba */ },
        nya: { /* Nyanja */ },
        ton: { /* Tonga */ },
        loz: { /* Lozi */ },
    },
});
```

### Accessibility

- Components must follow a11y best practices
- Keyboard navigation support
- ARIA labels on all interactive elements
- Focus management in modals
- Screen reader friendly

---

## Admin Dashboard Features

| Feature | Description |
|---------|-------------|
| User Management | Suspend/ban users |
| Business Management | Approve business, view usage |
| Template Management | Create/feature/publish templates |
| Billing Dashboard | Revenue overview |
| Queue Monitoring | Horizon integration |
| Post Management | Manual retry & webhook inspector |
| Content Moderation | Flagged posts, templates |
| Reports Export | CSV exports |

---

## Security, Privacy & Compliance

### Security Measures

- Encrypt third-party tokens in DB (Laravel encryption)
- HTTPS everywhere
- Rate-limit API endpoints (throttle middleware)
- CSRF for SPA (Sanctum cookie approach)
- Secure file uploads (virus scan optional)
- Logging with PII redaction

### Privacy & Compliance

- Sensitive data retention policy
- Retain minimal personal data
- Offer export/erase endpoints (GDPR-like)
- Local data residency notes (if needed)
- Store backups and DB snapshots securely

---

## Testing & QA

### Test Types

| Type | Scope | Tool |
|------|-------|------|
| Unit Tests | Models, services, policies | Pest PHP |
| Feature Tests | API endpoints | Pest PHP |
| Integration Tests | Queue & scheduled tasks | Pest (sync driver) |
| E2E Tests | SPA flows | Cypress |
| API Contract Tests | Endpoint contracts | Postman / OpenAPI |
| Security Scans | Dependencies, static analysis | PHPStan, ESLint |

### Running Tests

```bash
# Run all PHP tests
./vendor/bin/pest

# Run specific test file
./vendor/bin/pest tests/Feature/BizBoost/PostsTest.php

# Run with coverage
./vendor/bin/pest --coverage

# Run E2E tests
npm run test:e2e
```

---

## Deployment (Uses Existing MyGrowNet Infrastructure)

BizBoost is deployed as part of the MyGrowNet platform. **Do NOT create separate deployment infrastructure.**

### Existing Deployment Scripts

The MyGrowNet platform already has deployment scripts in `deployment/`:

```
deployment/
├── deploy.sh                    # Main deployment script
├── deploy-with-migration.sh     # Deploy with migrations
├── deploy-with-build.sh         # Deploy with asset build
├── manage-queue-worker.sh       # Queue worker management
├── mygrownet-nginx.conf         # Nginx configuration
└── README.md                    # Deployment documentation
```

### BizBoost Deployment Checklist

When deploying BizBoost features:

- [ ] Run BizBoost migrations: `php artisan migrate`
- [ ] Seed BizBoost data (if needed): `php artisan db:seed --class=BizBoostDemoSeeder`
- [ ] Clear caches: `php artisan cache:clear && php artisan config:clear`
- [ ] Restart queue workers: `./deployment/manage-queue-worker.sh restart`
- [ ] Build frontend assets: `npm run build`

### Using Existing Deploy Script

```bash
# Standard deployment (includes migrations)
./deployment/deploy-with-migration.sh

# Or with asset build
./deployment/deploy-with-build.sh
```

### Local Development

```bash
# Start development server (uses existing composer dev command)
composer dev

# Or manually
php artisan serve
npm run dev
php artisan queue:work
```

### Testing Before Deployment

```bash
# Run all tests including BizBoost
./vendor/bin/pest

# Run only BizBoost tests
./vendor/bin/pest tests/Feature/BizBoost/
./vendor/bin/pest tests/Unit/BizBoost/

# Code quality
./vendor/bin/pint
npm run lint
```

---

## Monetisation & Billing

### Subscription Tiers (Centralized Config)

BizBoost uses the **Centralized Subscription Architecture**. All tier configuration is defined in `config/modules/bizboost.php`.

| Tier | Monthly | Posts/Mo | Templates | AI Credits | Storage |
|------|---------|----------|-----------|------------|---------|
| **Free** | K0 | 10 | 5 | 10 | 0 MB |
| **Basic** | K79 | 50 | 25 | 50 | 100 MB |
| **Professional** | K199 | Unlimited | Unlimited | 200 | 1 GB |
| **Business** | K399 | Unlimited | Unlimited | Unlimited | 5 GB |

### Module Config File

Create `config/modules/bizboost.php`:

```php
<?php

return [
    'id' => 'bizboost',
    'name' => 'BizBoost',
    'slug' => 'bizboost',
    'description' => 'Marketing & growth assistant for SMEs',
    'category' => 'marketing',
    'status' => 'active',
    'version' => '1.0.0',
    
    'tiers' => [
        'free' => [
            'name' => 'Free',
            'price_monthly' => 0,
            'price_annual' => 0,
            'limits' => [
                'posts_per_month' => 10,
                'templates' => 5,
                'ai_credits_per_month' => 10,
                'customers' => 20,
                'products' => 10,
                'campaigns' => 0,
                'storage_mb' => 0,
            ],
            'features' => [
                'dashboard',
                'basic_templates',
                'mini_website',
                'manual_sharing',
                'customer_list',
            ],
        ],
        'basic' => [
            'name' => 'Basic',
            'price_monthly' => 79,
            'price_annual' => 758,
            'limits' => [
                'posts_per_month' => 50,
                'templates' => 25,
                'ai_credits_per_month' => 50,
                'customers' => -1,
                'products' => -1,
                'campaigns' => 3,
                'storage_mb' => 100,
            ],
            'features' => [
                'dashboard',
                'templates',
                'mini_website',
                'scheduling',
                'customer_management',
                'basic_analytics',
                'industry_kits',
            ],
        ],
        'professional' => [
            'name' => 'Professional',
            'price_monthly' => 199,
            'price_annual' => 1910,
            'popular' => true,
            'limits' => [
                'posts_per_month' => -1,
                'templates' => -1,
                'ai_credits_per_month' => 200,
                'customers' => -1,
                'products' => -1,
                'campaigns' => -1,
                'storage_mb' => 1024,
            ],
            'features' => [
                'dashboard',
                'templates',
                'template_editor',
                'mini_website',
                'auto_posting',
                'auto_campaigns',
                'customer_management',
                'sales_tracking',
                'advanced_analytics',
                'whatsapp_tools',
                'qr_codes',
            ],
        ],
        'business' => [
            'name' => 'Business',
            'price_monthly' => 399,
            'price_annual' => 3830,
            'limits' => [
                'posts_per_month' => -1,
                'templates' => -1,
                'ai_credits_per_month' => -1,
                'customers' => -1,
                'products' => -1,
                'campaigns' => -1,
                'storage_mb' => 5120,
                'team_members' => 10,
                'locations' => 5,
            ],
            'features' => [
                'dashboard',
                'templates',
                'template_editor',
                'mini_website',
                'auto_posting',
                'auto_campaigns',
                'customer_management',
                'sales_tracking',
                'advanced_analytics',
                'whatsapp_tools',
                'qr_codes',
                'multi_location',
                'team_accounts',
                'api_access',
                'white_label',
                'supplier_directory',
                'certificates',
            ],
        ],
    ],
    
    'usage_metrics' => [
        'posts_per_month' => ['label' => 'Posts', 'period' => 'monthly'],
        'ai_credits_per_month' => ['label' => 'AI Credits', 'period' => 'monthly'],
        'templates' => ['label' => 'Templates', 'period' => 'lifetime'],
        'customers' => ['label' => 'Customers', 'period' => 'lifetime'],
        'products' => ['label' => 'Products', 'period' => 'lifetime'],
        'campaigns' => ['label' => 'Campaigns', 'period' => 'lifetime'],
        'storage_mb' => ['label' => 'Storage', 'period' => 'lifetime', 'unit' => 'MB'],
    ],
];
```

### Usage Provider Implementation

Create `app/Domain/BizBoost/Services/BizBoostUsageProvider.php`:

```php
<?php

namespace App\Domain\BizBoost\Services;

use App\Domain\Module\Contracts\ModuleUsageProviderInterface;
use App\Models\User;

class BizBoostUsageProvider implements ModuleUsageProviderInterface
{
    public function getModuleId(): string
    {
        return 'bizboost';
    }

    public function getMetric(User $user, string $metric): int
    {
        $business = $user->businesses()->first();
        if (!$business) return 0;

        return match ($metric) {
            'posts_per_month' => $business->posts()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'ai_credits_per_month' => $business->aiUsageLogs()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('credits_used'),
            'templates' => $business->customTemplates()->count(),
            'customers' => $business->customers()->count(),
            'products' => $business->products()->count(),
            'campaigns' => $business->campaigns()->count(),
            default => 0,
        };
    }

    public function getStorageUsed(User $user): int
    {
        $business = $user->businesses()->first();
        return $business ? (int) ($business->media()->sum('size') / 1024 / 1024) : 0;
    }
}
```

### Register in Service Provider

Add to `app/Providers/ModuleSubscriptionServiceProvider.php`:

```php
$this->app->tag([
    \App\Domain\GrowFinance\Services\GrowFinanceUsageProvider::class,
    \App\Domain\GrowBiz\Services\GrowBizUsageProvider::class,
    \App\Domain\BizBoost\Services\BizBoostUsageProvider::class, // Add this
], 'module.usage_providers');
```

### Payment Integration

Uses the centralized `ModuleSubscriptionService`:

```php
use App\Domain\Module\Services\ModuleSubscriptionService;

// Subscribe user to BizBoost
$subscriptionService->subscribe($user, 'bizboost', 'basic', 'monthly');

// Upgrade tier
$subscriptionService->upgrade($user, 'bizboost', 'professional');

// Check access
$subscriptionService->hasAccess($user, 'bizboost');
```

### Additional Revenue

- Coupon codes & referral credits (no cash returns)
- In-app purchase of premium templates
- Course purchases

---

## Analytics & Monitoring

### Event Tracking

- Server-side tracking + client events to Postgres/analytics service
- Aggregate queries for:
  - Monthly active users
  - Conversion rate
  - Retention

### Error Logging

```php
// config/logging.php
'channels' => [
    'sentry' => [
        'driver' => 'sentry',
    ],
],
```

### Metrics to Track

- Publish success/failure rate
- Publish latency
- Queue backlog
- API response times
- User engagement metrics

---

## Environment Variables (BizBoost-Specific Only)

BizBoost uses the existing MyGrowNet `.env` configuration. Only add BizBoost-specific variables.

**Add these to the existing `.env` file:**

```env
# ============================================
# BIZBOOST MODULE CONFIGURATION
# ============================================

# AI Content Generation (Required)
BIZBOOST_OPENAI_API_KEY=sk-your-openai-api-key
BIZBOOST_OPENAI_MODEL=gpt-4o-mini

# Facebook/Instagram Integration (Required for social publishing)
BIZBOOST_FACEBOOK_APP_ID=
BIZBOOST_FACEBOOK_APP_SECRET=
BIZBOOST_FACEBOOK_REDIRECT_URI=${APP_URL}/bizboost/integrations/facebook/callback
BIZBOOST_FACEBOOK_VERIFY_TOKEN=

# WhatsApp Business API (Optional - Phase 3+)
BIZBOOST_WHATSAPP_API_URL=
BIZBOOST_WHATSAPP_API_TOKEN=

# Media Storage (Optional - defaults to local)
BIZBOOST_MEDIA_DISK=local
# For S3/DigitalOcean Spaces, set to 's3' and configure AWS_* variables
```

**Existing variables used by BizBoost (already in .env):**
- `APP_URL` - Used for OAuth redirects
- `DB_*` - Database connection
- `QUEUE_CONNECTION` - Queue driver (database)
- `MAIL_*` - Email notifications
- `AWS_*` - S3 storage (if BIZBOOST_MEDIA_DISK=s3)

---

## Seeders & Sample Data

### What to Include

- 3 demo users (admin, power user, basic user)
- 5 demo businesses (salon, boutique, restaurant, grocery, mobile money booth)
- 10 templates per industry
- 10 sample posts (various statuses: draft, scheduled, published)
- Sample products for each business
- Sample customers and sales

### Seeder Commands

```bash
# Run all migrations and seeders
php artisan migrate --seed

# Run specific seeder
php artisan db:seed --class=DemoSeeder

# Fresh database with seeds
php artisan migrate:fresh --seed
```

### Demo Seeder Structure

```php
// database/seeders/DemoSeeder.php
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            BusinessSeeder::class,
            TemplateSeeder::class,
            ProductSeeder::class,
            PostSeeder::class,
            CustomerSeeder::class,
            SaleSeeder::class,
        ]);
    }
}
```

---

## Quick Start Commands (MyGrowNet Development)

BizBoost is developed within the existing MyGrowNet repository. **Do NOT create a separate repository.**

### Initial Setup (If Starting Fresh)

```bash
# Clone MyGrowNet repository (BizBoost is a module within it)
git clone <mygrownet-repo-url>
cd mygrownet

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Add BizBoost environment variables to .env
# (See Environment Variables section above)

# Run migrations (includes BizBoost tables)
php artisan migrate --seed
```

### Daily Development

```bash
# Start all services (server, queue, Vite)
composer dev

# Or run individually:
php artisan serve
npm run dev
php artisan queue:work
```

### Running BizBoost Migrations Only

```bash
# Run new BizBoost migrations
php artisan migrate --path=database/migrations/bizboost

# Seed BizBoost demo data
php artisan db:seed --class=BizBoostDemoSeeder
```

### Building for Production

```bash
# Build frontend assets
npm run build

# Deploy using existing scripts
./deployment/deploy-with-build.sh
```

---

## Wireframes & UX Notes

### Onboarding Flow

Keep it short:
1. Business name
2. Category/industry
3. Logo upload
4. Top product
5. What they want to achieve (goal selection)

### Navigation Structure

Left navigation with clear sections:
- Dashboard
- Posts
- Calendar
- Templates
- Campaigns
- Customers
- Sales
- Integrations
- Billing

### Post Composer

- Media upload area
- Caption editor
- CTA selection
- Target channels (FB, IG, WhatsApp)
- Schedule picker

### Templates Gallery

- Filter by industry
- Filter by popularity
- Filter by price (free/premium)
- Preview modal

### Calendar View

- Month/week toggle
- Drag-to-reschedule posts
- Color-coded by status

### Dashboard Widgets

- Scheduled posts count
- Top performing posts
- Upcoming campaigns
- Subscription status
- Quick actions

---

## Development Milestones

### Milestone A: Foundation
- Auth + business onboarding
- Mini-site
- Basic templates

### Milestone B: Content & Scheduling
- Post composer
- Scheduling
- Queue worker
- Manual sharing flow

### Milestone C: Social Integration
- FB Page publish
- Instagram business publish
- Analytics stubs

### Milestone D: Monetisation
- Payments
- Subscription gating
- Admin panel

### Milestone E: Advanced Features
- AI content improvements
- Auto-campaigns
- WhatsApp integration

---

## Operational Notes & Pitfalls

### Facebook/Instagram

- Business Verification and App Review required for publish permissions
- Process can take time; use test Pages for development
- Store tokens encrypted; rotate periodically

### WhatsApp Business

- Automated messages require template approval
- Templates must be pre-approved by WhatsApp

### Performance

- Ensure rate limiting on APIs
- Robust retry logic for publish jobs
- Be mindful of image sizes and storage costs
- Use CDN + image optimization

### Security

- Never log sensitive tokens
- Encrypt all third-party credentials
- Regular security audits

---

## Deliverables & Acceptance Criteria

### Deliverables

- [ ] BizBoost module integrated into MyGrowNet repository
- [ ] Module configuration: `config/modules/bizboost.php`
- [ ] Working API endpoints with documented routes
- [ ] Vue SPA pages: onboarding, post composer, templates gallery, scheduling calendar, business profile
- [ ] Admin dashboard integration for BizBoost management
- [ ] Queue jobs for AI content, social publishing, analytics
- [ ] AI text generation pipeline (OpenAI integration)
- [ ] Facebook/Instagram integration for social publishing
- [ ] Subscription flow using centralized module subscription system
- [ ] Seeders for demo data (businesses, templates, products)
- [ ] Tests: unit tests for services; feature tests for API routes
- [ ] Documentation: module README, API docs, environment variables

### Acceptance Criteria

- [ ] Users can register, create a business, upload logo and product, and publish a post as draft
- [ ] Users can schedule a post; scheduled jobs appear in queue and are processed
- [ ] Admin can view, edit, and delete templates
- [ ] AI content endpoint returns relevant caption text for prompts
- [ ] Payment flow creates subscription and webhook updates user subscription status
- [ ] Facebook integration publishes to a test Page (given valid page token)
- [ ] Local drafts saved offline are restored when online
- [ ] Core tests pass in CI and code coverage is reasonable for critical modules

---

## Handoff Requirements

### For Development Team

1. **Staging FB Test App** - Access to test pages and tokens for integration testing
2. **Design Tokens** - Colors, fonts, logos, and branding assets
3. **OpenAI Key** - For content generation tests (or use mock/stub)
4. **MyGrowNet SSO Details** - If SSO integration is required

### Expected from Development Team

1. **Postman Collection** - Complete API documentation
2. **OpenAPI JSON** - API specification
3. **Deployment Guide** - Step-by-step production deployment
4. **Architecture Decision Records** - Key technical decisions documented

---

## Contact & Escalation

| Role | Contact |
|------|---------|
| Product Owner | Samson Banda |
| Technical Lead | TBD (Kiro should propose) |
| Ops | Define who has access to cloud infra, FB Business Manager, payment provider console |

---

## Related Documents

- [BizBoost Master Concept](./BIZBOOST_MASTER_CONCEPT.md) - Product overview and features
- [Centralized Subscription Architecture](../CENTRALIZED_SUBSCRIPTION_ARCHITECTURE.md) - Module subscription system
- [GrowFinance Testing Guide](../growfinance/GROWFINANCE_TESTING_GUIDE.md) - Reference for test patterns

---

## Module Checklist

Before launching BizBoost, ensure these components are created:

### Configuration
- [ ] `config/modules/bizboost.php` - Tier configuration
- [ ] Register module in `config/modules/modules.php` (if exists)

### Domain Layer
- [ ] `app/Domain/BizBoost/Services/BizBoostUsageProvider.php`
- [ ] `app/Domain/BizBoost/Services/` - Business logic services

### Infrastructure
- [ ] Database migrations for BizBoost tables
- [ ] Eloquent models in `app/Infrastructure/Persistence/Eloquent/`

### Presentation
- [ ] `app/Http/Middleware/CheckBizBoostSubscription.php`
- [ ] `app/Http/Controllers/BizBoost/` - Controllers
- [ ] `routes/bizboost.php` - Route definitions

### Frontend
- [ ] `resources/js/Layouts/BizBoostLayout.vue`
- [ ] `resources/js/Pages/BizBoost/` - Vue pages
- [ ] `resources/js/Components/BizBoost/` - Shared components

### Testing
- [ ] `tests/Feature/BizBoost/` - Feature tests
- [ ] `tests/Unit/Domain/BizBoost/` - Unit tests

### Service Provider Registration
- [ ] Add `BizBoostUsageProvider` to `ModuleSubscriptionServiceProvider`
- [ ] Register middleware in `bootstrap/app.php` or kernel

---

## Changelog

### December 4, 2025
- Initial developer handover document created
- Complete technical specification for MVP
- API endpoints, data model, and architecture defined
- Added modular system integration details
- Added centralized subscription tier configuration
- Added usage provider implementation guide
- Added module checklist for launch readiness
