# GrowBuilder Agency Architecture

**Last Updated:** March 16, 2026  
**Status:** Design & Implementation Specification  
**Version:** 1.0  
**Framework:** Laravel 12.0 with Inertia.js + Vue 3

## Overview

Complete agency architecture for MyGrowNet's GrowBuilder platform, enabling agencies to operate professionally, manage multiple clients, and scale without forcing every client to open a full MyGrowNet account.

GrowBuilder transforms from a simple website builder into a **reseller-ready digital service platform** where agencies can manage staff, clients, websites, billing, and provide secure client portal access with white-label branding.

## Core Principle

**GrowBuilder treats an agency as a business unit inside MyGrowNet, not just as one subscribed user with many sites.**

## Architectural Layers

```
MyGrowNet Platform
    ↓
Agency (Business Unit)
    ↓
Agency Team Members (Staff with roles)
    ↓
Clients (Agency's customers)
    ↓
Sites (Websites/stores/landing pages)
    ↓
Services (Hosting, maintenance, SEO, etc.)
```

### Eight Core Layers

1. **Agency Account Layer** - Foundation with subscriptions, quotas, branding
2. **Agency Team & Permissions Layer** - Multi-user with role-based access
3. **Client Management Layer** - CRM for managing customers
4. **Site Management Layer** - Website creation linked to agency and client
5. **Billing & Commercial Layer** - Service tracking, invoicing, payments
6. **Client Access Layer** - Secure portal without full MyGrowNet accounts
7. **Operations & Support Layer** - Workflows, reporting, activity logs
8. **Platform Governance Layer** - Quota enforcement, subscription management



---

## Database Schema & Migrations

### Schema Overview

```
agencies (foundation)
├── agency_users (staff)
├── agency_roles (permissions)
├── agency_clients (customers)
│   ├── agency_client_contacts
│   └── agency_client_tags
├── growbuilder_sites (websites)
│   ├── growbuilder_site_settings
│   └── growbuilder_site_revisions
├── agency_client_services (offerings)
├── agency_client_invoices (billing)
│   └── agency_client_invoice_items
├── agency_client_payments
├── agency_client_portal_users (client access)
├── agency_client_requests (support tickets)
├── agency_branding_settings
├── agency_activity_logs
└── subscription_plans
```

### 1. Agencies Table

```php
// Migration: create_agencies_table.php
Schema::create('agencies', function (Blueprint $table) {
    $table->id();
    $table->string('agency_name');
    $table->string('slug')->unique();
    $table->foreignId('owner_user_id')->constrained('users')->onDelete('restrict');
    $table->foreignId('subscription_plan_id')->nullable()->constrained('subscription_plans');
    
    // Contact Information
    $table->string('business_email');
    $table->string('phone')->nullable();
    $table->string('country')->default('ZM');
    $table->string('currency')->default('ZMW');
    $table->string('timezone')->default('Africa/Lusaka');
    $table->string('locale')->default('en');
    
    // Status & Lifecycle
    $table->enum('status', ['trial', 'active', 'suspended', 'cancelled'])->default('trial');
    $table->timestamp('trial_ends_at')->nullable();
    $table->timestamp('suspended_at')->nullable();
    $table->boolean('onboarding_completed')->default(false);
    
    // Quotas & Limits
    $table->unsignedBigInteger('storage_limit_mb')->default(20480); // 20GB
    $table->unsignedBigInteger('storage_used_mb')->default(0);
    $table->unsignedInteger('site_limit')->default(10);
    $table->unsignedInteger('sites_used')->default(0);
    $table->unsignedInteger('team_member_limit')->default(3);
    
    // Features
    $table->boolean('allow_white_label')->default(false);
    
    // Growth & Referral
    $table->string('referral_code')->unique()->nullable();
    
    $table->timestamps();
    $table->softDeletes();
    
    // Indexes
    $table->index('owner_user_id');
    $table->index(['status', 'trial_ends_at']);
    $table->index('referral_code');
});
```

**Key Fields:**
- `trial_ends_at` - Tracks trial expiry
- `suspended_at` - Audit trail for suspensions
- `timezone` - Agencies bill clients in local time
- `locale` - Affects date/number formatting
- `onboarding_completed` - Post-signup wizard flag
- `referral_code` - Agency referral program



### 2. Agency Users & Roles

```php
// Migration: create_agency_users_table.php
Schema::create('agency_users', function (Blueprint $table) {
    $table->id();
    $table->foreignId('agency_id')->constrained('agencies')->onDelete('cascade');
    $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
    $table->foreignId('role_id')->constrained('agency_roles')->onDelete('restrict');
    $table->enum('status', ['active', 'inactive', 'invited'])->default('invited');
    $table->foreignId('invited_by')->nullable()->constrained('users');
    $table->timestamp('joined_at')->nullable();
    $table->timestamps();
    $table->softDeletes();
    
    // Indexes
    $table->unique(['agency_id', 'user_id']);
    $table->index(['agency_id', 'status']);
    $table->index('user_id');
});

// Migration: create_agency_roles_table.php
Schema::create('agency_roles', function (Blueprint $table) {
    $table->id();
    $table->foreignId('agency_id')->nullable()->constrained('agencies')->onDelete('cascade');
    $table->string('role_name');
    $table->boolean('is_system_role')->default(false);
    $table->json('permissions_json'); // Array of permission strings
    $table->text('description')->nullable();
    $table->timestamps();
    $table->softDeletes();
    
    // Indexes
    $table->index(['agency_id', 'role_name']);
    $table->index('is_system_role');
});
```

**System Roles (Seeded):**
- Owner - Full control
- Admin - Most permissions except billing
- Project Manager - Client and site management
- Designer - Site editing, templates
- Developer - Site editing, domains
- Content Editor - Content only
- Billing Officer - Client billing, invoices
- Support Staff - View-only

**Permissions:**
```json
[
    "create_site", "edit_site", "delete_site", "publish_site", "suspend_site",
    "connect_domain", "manage_storage",
    "manage_clients", "view_clients", "edit_clients", "delete_clients",
    "manage_billing", "view_billing", "create_invoices", "record_payments",
    "invite_team_members", "manage_team_members",
    "view_reports", "export_data",
    "manage_templates", "manage_branding"
]
```



### 3. Agency Clients

```php
// Migration: create_agency_clients_table.php
Schema::create('agency_clients', function (Blueprint $table) {
    $table->id();
    $table->foreignId('agency_id')->constrained('agencies')->onDelete('cascade');
    $table->string('client_code')->unique(); // e.g., AG001-CL001
    $table->enum('client_type', [
        'individual', 'business', 'institution', 'church', 'school', 'ngo', 'government'
    ])->default('business');
    
    // Basic Information
    $table->string('client_name');
    $table->string('company_name')->nullable();
    $table->string('email');
    $table->string('phone');
    $table->string('alternative_phone')->nullable();
    
    // Address
    $table->text('address')->nullable();
    $table->string('city')->nullable();
    $table->string('country')->default('ZM');
    
    // Status & Lifecycle
    $table->enum('status', ['lead', 'active', 'suspended', 'cancelled', 'archived'])->default('lead');
    $table->enum('onboarding_status', ['new', 'in_progress', 'completed'])->default('new');
    
    // Notes
    $table->text('notes')->nullable();
    
    $table->timestamps();
    $table->softDeletes();
    
    // Indexes
    $table->index(['agency_id', 'status']);
    $table->index(['agency_id', 'email']);
    $table->index('client_code');
});

// Migration: create_agency_client_contacts_table.php
Schema::create('agency_client_contacts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('client_id')->constrained('agency_clients')->onDelete('cascade');
    $table->string('full_name');
    $table->string('email');
    $table->string('phone')->nullable();
    $table->string('role_title')->nullable();
    $table->boolean('is_primary')->default(false);
    $table->timestamps();
    $table->softDeletes();
    
    // Indexes
    $table->index('client_id');
    $table->index(['client_id', 'is_primary']);
});

// Migration: create_agency_client_tags_table.php
Schema::create('agency_client_tags', function (Blueprint $table) {
    $table->id();
    $table->foreignId('agency_id')->constrained('agencies')->onDelete('cascade');
    $table->string('name');
    $table->string('color')->default('#3b82f6');
    $table->timestamps();
    
    $table->unique(['agency_id', 'name']);
});

Schema::create('agency_client_tag_map', function (Blueprint $table) {
    $table->foreignId('client_id')->constrained('agency_clients')->onDelete('cascade');
    $table->foreignId('tag_id')->constrained('agency_client_tags')->onDelete('cascade');
    $table->timestamps();
    
    $table->primary(['client_id', 'tag_id']);
});
```



### 4. GrowBuilder Sites (Updated)

```php
// Migration: update_growbuilder_sites_for_agency_architecture.php
Schema::table('growbuilder_sites', function (Blueprint $table) {
    // Add agency and client relationships
    $table->foreignId('agency_id')->after('id')->constrained('agencies')->onDelete('cascade');
    $table->foreignId('client_id')->after('agency_id')->constrained('agency_clients')->onDelete('restrict');
    
    // Enhanced site information
    $table->enum('site_type', [
        'business_website', 'ecommerce', 'landing_page', 'portfolio', 'blog', 'event', 'booking'
    ])->after('site_name')->default('business_website');
    
    $table->string('internal_subdomain')->after('site_type')->unique();
    $table->string('custom_domain')->after('internal_subdomain')->nullable();
    
    $table->enum('site_status', [
        'draft', 'in_review', 'published', 'suspended', 'archived', 'deleted'
    ])->after('custom_domain')->default('draft');
    
    // Resource Usage
    $table->unsignedBigInteger('storage_used_mb')->after('site_status')->default(0);
    $table->unsignedBigInteger('bandwidth_used_mb')->after('storage_used_mb')->default(0);
    $table->unsignedInteger('page_count')->after('bandwidth_used_mb')->default(0);
    $table->unsignedInteger('product_count')->after('page_count')->default(0);
    
    // Metadata
    $table->timestamp('last_published_at')->nullable();
    $table->foreignId('created_by')->nullable()->constrained('users');
    
    $table->softDeletes();
    
    // Indexes
    $table->index(['agency_id', 'client_id']);
    $table->index(['agency_id', 'site_status']);
    $table->index('internal_subdomain');
    $table->index('custom_domain');
});

// Migration: create_growbuilder_site_settings_table.php
Schema::create('growbuilder_site_settings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('site_id')->constrained('growbuilder_sites')->onDelete('cascade');
    
    // Feature Toggles
    $table->boolean('seo_enabled')->default(true);
    $table->boolean('forms_enabled')->default(true);
    $table->boolean('ecommerce_enabled')->default(false);
    $table->boolean('blog_enabled')->default(false);
    $table->boolean('analytics_enabled')->default(true);
    $table->boolean('maintenance_mode')->default(false);
    
    // Client Permissions
    $table->boolean('client_can_edit')->default(false);
    $table->boolean('client_can_view_analytics')->default(false);
    $table->boolean('client_can_manage_content')->default(false);
    
    // SEO Settings
    $table->text('meta_description')->nullable();
    $table->json('meta_keywords')->nullable();
    $table->string('og_image')->nullable();
    
    // Custom Code
    $table->text('custom_css')->nullable();
    $table->text('custom_js')->nullable();
    $table->text('header_code')->nullable();
    $table->text('footer_code')->nullable();
    
    $table->timestamps();
    
    $table->unique('site_id');
});

// Migration: create_growbuilder_site_revisions_table.php
Schema::create('growbuilder_site_revisions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('site_id')->constrained('growbuilder_sites')->onDelete('cascade');
    $table->json('snapshot_json'); // Complete site content
    $table->foreignId('created_by')->constrained('users');
    $table->string('label')->nullable();
    $table->boolean('is_auto_save')->default(false);
    $table->text('change_summary')->nullable();
    $table->timestamps();
    
    // Indexes
    $table->index(['site_id', 'created_at']);
    $table->index(['site_id', 'is_auto_save']);
});
```

**Site Revisions:** Auto-save every 30 minutes, manual save before major changes. Restore UI can be built later, but table must exist from start.



### 5. Billing & Commercial Layer

**Important:** Two separate billing relationships:
- **MyGrowNet → Agency**: Platform subscription (managed by MyGrowNet)
- **Agency → Client**: Reseller relationship (tracked by agency, NOT collected by MyGrowNet)

```php
// Migration: create_agency_client_services_table.php
Schema::create('agency_client_services', function (Blueprint $table) {
    $table->id();
    $table->foreignId('agency_id')->constrained('agencies')->onDelete('cascade');
    $table->foreignId('client_id')->constrained('agency_clients')->onDelete('cascade');
    $table->enum('service_type', [
        'website', 'hosting', 'domain_management', 'seo', 
        'maintenance', 'ads', 'redesign', 'content_updates'
    ]);
    $table->string('service_name');
    $table->foreignId('linked_site_id')->nullable()->constrained('growbuilder_sites');
    $table->enum('billing_model', ['monthly', 'quarterly', 'annual', 'one_time']);
    $table->decimal('unit_price', 10, 2);
    $table->unsignedInteger('quantity')->default(1);
    $table->date('start_date');
    $table->date('renewal_date')->nullable();
    $table->enum('status', ['active', 'paused', 'cancelled'])->default('active');
    $table->timestamps();
    $table->softDeletes();
    
    // Indexes
    $table->index(['agency_id', 'client_id']);
    $table->index(['agency_id', 'status']);
    $table->index('renewal_date');
});

// Migration: create_agency_client_invoices_table.php
Schema::create('agency_client_invoices', function (Blueprint $table) {
    $table->id();
    $table->foreignId('agency_id')->constrained('agencies')->onDelete('cascade');
    $table->foreignId('client_id')->constrained('agency_clients')->onDelete('cascade');
    $table->string('invoice_number')->unique();
    $table->date('invoice_date');
    $table->date('due_date');
    $table->decimal('subtotal', 10, 2);
    $table->decimal('total', 10, 2);
    $table->string('currency', 3)->default('ZMW');
    $table->enum('payment_status', [
        'draft', 'sent', 'partial', 'paid', 'overdue', 'cancelled'
    ])->default('draft');
    $table->text('notes')->nullable();
    $table->timestamps();
    $table->softDeletes();
    
    // Indexes
    $table->index(['agency_id', 'payment_status']);
    $table->index(['client_id', 'due_date']);
    $table->index('invoice_number');
});

// Migration: create_agency_client_invoice_items_table.php
Schema::create('agency_client_invoice_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('invoice_id')->constrained('agency_client_invoices')->onDelete('cascade');
    $table->foreignId('service_id')->nullable()->constrained('agency_client_services');
    $table->text('description');
    $table->decimal('amount', 10, 2);
    $table->unsignedInteger('quantity')->default(1);
    $table->decimal('total', 10, 2);
    $table->timestamps();
    
    // Indexes
    $table->index('invoice_id');
});

// Migration: create_agency_client_payments_table.php
Schema::create('agency_client_payments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('invoice_id')->constrained('agency_client_invoices')->onDelete('cascade');
    $table->decimal('amount', 10, 2);
    $table->date('payment_date');
    $table->string('payment_method')->nullable();
    $table->string('reference')->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();
    $table->softDeletes();
    
    // Indexes
    $table->index('invoice_id');
    $table->index('payment_date');
});
```



### 6. Client Portal Access

```php
// Migration: create_agency_client_portal_users_table.php
Schema::create('agency_client_portal_users', function (Blueprint $table) {
    $table->id();
    $table->foreignId('client_id')->constrained('agency_clients')->onDelete('cascade');
    $table->string('full_name');
    $table->string('email');
    $table->string('phone')->nullable();
    $table->string('password_hash')->nullable();
    $table->enum('auth_mode', ['magic_link', 'otp', 'password'])->default('magic_link');
    $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
    $table->timestamp('last_login_at')->nullable();
    $table->timestamps();
    $table->softDeletes();
    
    // Indexes
    $table->index('client_id');
    $table->index('email');
});

// Migration: create_agency_client_portal_permissions_table.php
Schema::create('agency_client_portal_permissions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('portal_user_id')->constrained('agency_client_portal_users')->onDelete('cascade');
    $table->foreignId('site_id')->constrained('growbuilder_sites')->onDelete('cascade');
    $table->boolean('can_view_site')->default(true);
    $table->boolean('can_edit_content')->default(false);
    $table->boolean('can_view_analytics')->default(false);
    $table->boolean('can_manage_forms')->default(false);
    $table->boolean('can_download_assets')->default(false);
    $table->boolean('can_request_changes')->default(true);
    $table->timestamps();
    
    // Indexes
    $table->unique(['portal_user_id', 'site_id']);
    $table->index('portal_user_id');
});

// Migration: create_agency_client_requests_table.php
Schema::create('agency_client_requests', function (Blueprint $table) {
    $table->id();
    $table->foreignId('client_id')->constrained('agency_clients')->onDelete('cascade');
    $table->foreignId('site_id')->nullable()->constrained('growbuilder_sites');
    $table->enum('request_type', [
        'content_change', 'bug_report', 'feature_request', 'support', 'billing'
    ]);
    $table->text('message');
    $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');
    $table->foreignId('assigned_to')->nullable()->constrained('users');
    $table->timestamp('resolved_at')->nullable();
    $table->timestamps();
    $table->softDeletes();
    
    // Indexes
    $table->index(['client_id', 'status']);
    $table->index('assigned_to');
});
```

**Authentication:** Use magic link or OTP (not permanent tokens) for security. Links expire, access can be revoked, login activity tracked.



### 7. White-Label & Branding

```php
// Migration: create_agency_branding_settings_table.php
Schema::create('agency_branding_settings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('agency_id')->constrained('agencies')->onDelete('cascade');
    $table->string('logo_url')->nullable();
    $table->string('favicon_url')->nullable();
    $table->string('primary_color')->default('#2563eb');
    $table->string('secondary_color')->default('#059669');
    $table->string('support_email')->nullable();
    $table->string('support_phone')->nullable();
    $table->string('portal_domain')->nullable(); // For full white-label
    $table->boolean('remove_mygrownet_branding')->default(false);
    $table->text('custom_login_message')->nullable();
    $table->text('custom_email_footer')->nullable();
    $table->timestamps();
    
    // Indexes
    $table->unique('agency_id');
    $table->unique('portal_domain');
});
```

### 8. Templates

```php
// Migration: create_growbuilder_templates_table.php
Schema::create('growbuilder_templates', function (Blueprint $table) {
    $table->id();
    $table->enum('owner_type', ['platform', 'agency']);
    $table->foreignId('owner_id')->nullable(); // agency_id for agency templates
    $table->string('name');
    $table->string('category')->nullable();
    $table->string('industry')->nullable(); // schools, churches, hardware, salons, etc.
    $table->boolean('is_public')->default(false);
    $table->string('preview_image')->nullable();
    $table->json('configuration_json');
    $table->timestamps();
    $table->softDeletes();
    
    // Indexes
    $table->index(['owner_type', 'owner_id']);
    $table->index(['owner_type', 'is_public']);
    $table->index('industry');
});
```

### 9. Activity Logs

```php
// Migration: create_agency_activity_logs_table.php
Schema::create('agency_activity_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('agency_id')->constrained('agencies')->onDelete('cascade');
    $table->foreignId('user_id')->nullable()->constrained('users');
    $table->string('action_type'); // created, updated, deleted, published, suspended
    $table->string('entity_type'); // site, client, invoice, etc.
    $table->unsignedBigInteger('entity_id')->nullable();
    $table->text('description');
    $table->json('metadata')->nullable();
    $table->timestamps();
    
    // Indexes
    $table->index(['agency_id', 'created_at']);
    $table->index(['entity_type', 'entity_id']);
    $table->index('user_id');
});
```

### 10. Subscription Plans

```php
// Migration: create_subscription_plans_table.php
Schema::create('subscription_plans', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->decimal('monthly_price', 10, 2);
    $table->decimal('annual_price', 10, 2);
    $table->unsignedInteger('site_limit');
    $table->unsignedBigInteger('storage_limit_mb');
    $table->unsignedInteger('team_member_limit');
    $table->unsignedInteger('client_limit')->nullable(); // null = unlimited
    $table->json('features_json'); // Feature flags
    $table->boolean('is_active')->default(true);
    $table->unsignedInteger('sort_order')->default(0);
    $table->timestamps();
    
    // Indexes
    $table->index('slug');
    $table->index(['is_active', 'sort_order']);
});
```

**Feature Flags Example:**
```json
{
    "white_label": true,
    "client_portal": true,
    "advanced_analytics": false,
    "priority_support": true,
    "api_access": false,
    "custom_domains": 10,
    "email_campaigns": false
}
```



---

## Laravel Implementation Details

### Tenant Isolation Strategy

**Critical:** Every major object must carry `agency_id` to prevent cross-agency data leaks.

#### Global Scopes

Apply to all major models to automatically filter by agency:

```php
// app/Infrastructure/GrowBuilder/Models/Scopes/AgencyScope.php
namespace App\Infrastructure\GrowBuilder\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class AgencyScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        if (auth()->check() && auth()->user()->currentAgency) {
            $builder->where($model->getTable() . '.agency_id', auth()->user()->currentAgency->id);
        }
    }
}
```

**Apply to models:**
```php
// In each model
protected static function booted()
{
    static::addGlobalScope(new AgencyScope());
    
    // Auto-set agency_id on creation
    static::creating(function ($model) {
        if (auth()->check() && auth()->user()->currentAgency) {
            $model->agency_id = auth()->user()->currentAgency->id;
        }
    });
}
```

**Models requiring AgencyScope:**
- `Agency` (no scope, but owner check)
- `AgencyClient`
- `GrowBuilderSite`
- `AgencyClientService`
- `AgencyClientInvoice`
- `AgencyClientPortalUser`
- `GrowBuilderTemplate` (when owner_type = 'agency')
- `AgencyActivityLog`



### Model Observers

Use observers to maintain data consistency:

```php
// app/Observers/GrowBuilderSiteObserver.php
namespace App\Observers;

use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;

class GrowBuilderSiteObserver
{
    public function created(GrowBuilderSite $site)
    {
        // Increment agency sites_used
        $site->agency->increment('sites_used');
    }
    
    public function deleted(GrowBuilderSite $site)
    {
        // Decrement agency sites_used
        $site->agency->decrement('sites_used');
    }
    
    public function updated(GrowBuilderSite $site)
    {
        // If storage changed, update agency storage_used_mb
        if ($site->isDirty('storage_used_mb')) {
            $oldStorage = $site->getOriginal('storage_used_mb');
            $newStorage = $site->storage_used_mb;
            $difference = $newStorage - $oldStorage;
            
            $site->agency->increment('storage_used_mb', $difference);
        }
    }
}

// Register in AppServiceProvider
public function boot()
{
    GrowBuilderSite::observe(GrowBuilderSiteObserver::class);
    GrowBuilderMedia::observe(GrowBuilderMediaObserver::class);
}
```



### Permissions & Authorization

#### Permission Enforcement Pattern

```php
// app/Policies/GrowBuilderSitePolicy.php
namespace App\Policies;

use App\Models\User;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;

class GrowBuilderSitePolicy
{
    public function create(User $user)
    {
        return $user->agencyRole?->hasPermission('create_site');
    }
    
    public function update(User $user, GrowBuilderSite $site)
    {
        return $site->agency_id === $user->currentAgency?->id
            && $user->agencyRole?->hasPermission('edit_site');
    }
    
    public function delete(User $user, GrowBuilderSite $site)
    {
        return $site->agency_id === $user->currentAgency?->id
            && $user->agencyRole?->hasPermission('delete_site');
    }
    
    public function publish(User $user, GrowBuilderSite $site)
    {
        return $site->agency_id === $user->currentAgency?->id
            && $user->agencyRole?->hasPermission('publish_site');
    }
}
```

#### AgencyRole Model Helper

```php
// app/Models/AgencyRole.php
namespace App\Models;

class AgencyRole extends Model
{
    protected $casts = [
        'permissions_json' => 'array',
    ];
    
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions_json ?? []);
    }
    
    public function hasAnyPermission(array $permissions): bool
    {
        return !empty(array_intersect($permissions, $this->permissions_json ?? []));
    }
    
    public function hasAllPermissions(array $permissions): bool
    {
        return empty(array_diff($permissions, $this->permissions_json ?? []));
    }
}
```

#### Gate Registration

```php
// app/Providers/AuthServiceProvider.php
public function boot()
{
    Gate::define('create-site', function (User $user) {
        return $user->agencyRole?->hasPermission('create_site');
    });
    
    Gate::define('manage-clients', function (User $user) {
        return $user->agencyRole?->hasPermission('manage_clients');
    });
    
    Gate::define('manage-billing', function (User $user) {
        return $user->agencyRole?->hasPermission('manage_billing');
    });
    
    // ... more gates
}
```

#### Middleware Usage

```php
// In routes
Route::middleware(['auth', 'agency'])->group(function () {
    Route::get('/sites', [SiteController::class, 'index'])
        ->can('view_sites');
    
    Route::post('/sites', [SiteController::class, 'store'])
        ->can('create_site');
});
```



### Inertia Shared Data Strategy

```php
// app/Http/Middleware/HandleInertiaRequests.php
namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            // Always available to every Vue page
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                ] : null,
            ],
            
            // Agency context (when in agency dashboard)
            'agency' => $request->user()?->currentAgency ? [
                'id' => $request->user()->currentAgency->id,
                'name' => $request->user()->currentAgency->agency_name,
                'logo' => $request->user()->currentAgency->branding?->logo_url,
                'plan' => $request->user()->currentAgency->subscriptionPlan?->name,
                'status' => $request->user()->currentAgency->status,
            ] : null,
            
            // Current user's role and permissions
            'auth_user' => $request->user()?->agencyUser ? [
                'role' => $request->user()->agencyUser->role->role_name,
                'permissions' => $request->user()->agencyUser->role->permissions_json,
            ] : null,
            
            // Quota information
            'quota' => $request->user()?->currentAgency ? [
                'storage_used_mb' => $request->user()->currentAgency->storage_used_mb,
                'storage_limit_mb' => $request->user()->currentAgency->storage_limit_mb,
                'storage_percentage' => round(
                    ($request->user()->currentAgency->storage_used_mb / 
                     $request->user()->currentAgency->storage_limit_mb) * 100, 
                    1
                ),
                'sites_used' => $request->user()->currentAgency->sites_used,
                'site_limit' => $request->user()->currentAgency->site_limit,
                'sites_percentage' => round(
                    ($request->user()->currentAgency->sites_used / 
                     $request->user()->currentAgency->site_limit) * 100, 
                    1
                ),
            ] : null,
            
            // Notifications
            'notifications' => [
                'unread_count' => $request->user()?->unreadNotifications()->count() ?? 0,
            ],
            
            // Flash messages
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'warning' => $request->session()->get('warning'),
            ],
        ]);
    }
}
```

**Usage in Vue:**
```vue
<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const agency = page.props.agency;
const permissions = page.props.auth_user?.permissions || [];
const quota = page.props.quota;

const canCreateSite = permissions.includes('create_site');
const storageWarning = quota?.storage_percentage > 80;
</script>
```



### Client Portal Routing Strategy

**Option A (Phase 1):** Same Laravel app, separate route group with own middleware and layout

```php
// routes/portal.php
Route::prefix('portal')->name('portal.')->middleware(['portal.auth'])->group(function () {
    Route::get('/dashboard', [PortalDashboardController::class, 'index'])->name('dashboard');
    Route::get('/sites', [PortalSiteController::class, 'index'])->name('sites.index');
    Route::get('/sites/{site}', [PortalSiteController::class, 'show'])->name('sites.show');
    Route::get('/invoices', [PortalInvoiceController::class, 'index'])->name('invoices.index');
    Route::post('/requests', [PortalRequestController::class, 'store'])->name('requests.store');
});

// Magic link authentication
Route::get('/portal/auth/{token}', [PortalAuthController::class, 'authenticate'])
    ->name('portal.auth');
```

**Middleware:**
```php
// app/Http/Middleware/PortalAuthenticate.php
namespace App\Http\Middleware;

class PortalAuthenticate
{
    public function handle($request, Closure $next)
    {
        if (!session()->has('portal_user_id')) {
            return redirect()->route('portal.login');
        }
        
        $portalUser = AgencyClientPortalUser::find(session('portal_user_id'));
        
        if (!$portalUser || $portalUser->status !== 'active') {
            session()->forget('portal_user_id');
            return redirect()->route('portal.login');
        }
        
        // Attach portal user to request
        $request->merge(['portal_user' => $portalUser]);
        
        return $next($request);
    }
}
```

**Option B (Phase 6):** Subdomain routing for white-label

```php
// In RouteServiceProvider
Route::domain('{agency}.growbuilder.mygrownet.com')
    ->middleware(['portal.auth'])
    ->group(base_path('routes/portal.php'));
```



### Queued Jobs & Async Operations

Several workflows require background processing:

```php
// app/Jobs/GrowBuilder/CheckExpiringTrials.php
namespace App\Jobs\GrowBuilder;

class CheckExpiringTrials implements ShouldQueue
{
    public function handle()
    {
        $expiringAgencies = Agency::where('status', 'trial')
            ->where('trial_ends_at', '<=', now()->addDays(3))
            ->where('trial_ends_at', '>', now())
            ->get();
        
        foreach ($expiringAgencies as $agency) {
            // Send reminder email
            Mail::to($agency->business_email)
                ->send(new TrialExpiringMail($agency));
        }
    }
}

// app/Jobs/GrowBuilder/CheckServiceRenewals.php
class CheckServiceRenewals implements ShouldQueue
{
    public function handle()
    {
        $upcomingRenewals = AgencyClientService::where('status', 'active')
            ->where('renewal_date', '<=', now()->addDays(30))
            ->where('renewal_date', '>', now())
            ->with(['agency', 'client'])
            ->get();
        
        foreach ($upcomingRenewals as $service) {
            // Notify agency
            Notification::send(
                $service->agency->users,
                new ServiceRenewalDueNotification($service)
            );
        }
    }
}

// app/Jobs/GrowBuilder/CheckOverdueInvoices.php
class CheckOverdueInvoices implements ShouldQueue
{
    public function handle()
    {
        AgencyClientInvoice::where('payment_status', 'sent')
            ->where('due_date', '<', now())
            ->update(['payment_status' => 'overdue']);
    }
}

// app/Jobs/GrowBuilder/RecalculateAgencyStorage.php
class RecalculateAgencyStorage implements ShouldQueue
{
    public function __construct(public int $agencyId) {}
    
    public function handle()
    {
        $agency = Agency::find($this->agencyId);
        
        $totalStorage = GrowBuilderSite::where('agency_id', $agency->id)
            ->sum('storage_used_mb');
        
        $agency->update(['storage_used_mb' => $totalStorage]);
    }
}

// app/Jobs/GrowBuilder/CreateSiteRevision.php
class CreateSiteRevision implements ShouldQueue
{
    public function __construct(
        public int $siteId,
        public int $userId,
        public bool $isAutoSave = true
    ) {}
    
    public function handle()
    {
        $site = GrowBuilderSite::find($this->siteId);
        
        GrowBuilderSiteRevision::create([
            'site_id' => $site->id,
            'snapshot_json' => $site->content_json,
            'created_by' => $this->userId,
            'is_auto_save' => $this->isAutoSave,
        ]);
        
        // Keep only last 50 auto-saves
        if ($this->isAutoSave) {
            GrowBuilderSiteRevision::where('site_id', $site->id)
                ->where('is_auto_save', true)
                ->orderBy('created_at', 'desc')
                ->skip(50)
                ->take(PHP_INT_MAX)
                ->delete();
        }
    }
}
```

**Schedule in Console Kernel:**
```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->job(new CheckExpiringTrials)->daily();
    $schedule->job(new CheckServiceRenewals)->daily();
    $schedule->job(new CheckOverdueInvoices)->daily();
}
```



### Database Indexing Strategy

**Critical for multi-tenant performance:**

```php
// Composite indexes for common queries

// agencies table
$table->index('owner_user_id');
$table->index(['status', 'trial_ends_at']);
$table->index('referral_code');

// agency_users table
$table->unique(['agency_id', 'user_id']);
$table->index(['agency_id', 'status']);
$table->index('user_id');

// agency_clients table
$table->index(['agency_id', 'status']);
$table->index(['agency_id', 'email']);
$table->index('client_code');

// growbuilder_sites table
$table->index(['agency_id', 'client_id']);
$table->index(['agency_id', 'site_status']);
$table->index('internal_subdomain');
$table->index('custom_domain');

// agency_client_invoices table
$table->index(['agency_id', 'payment_status']);
$table->index(['client_id', 'due_date']);
$table->index('invoice_number');

// agency_client_services table
$table->index(['agency_id', 'client_id']);
$table->index(['agency_id', 'status']);
$table->index('renewal_date');

// agency_activity_logs table
$table->index(['agency_id', 'created_at']);
$table->index(['entity_type', 'entity_id']);
$table->index('user_id');
```

**Performance Tips:**
- Always filter by `agency_id` first in queries
- Use composite indexes for common WHERE clauses
- Index foreign keys
- Index status and date columns used in filtering
- Monitor slow queries and add indexes as needed



### Soft Deletes Strategy

**All major tables must use soft deletes** to protect data from accidental hard deletion:

```php
use Illuminate\Database\Eloquent\SoftDeletes;

class Agency extends Model
{
    use SoftDeletes;
}
```

**Tables requiring soft deletes:**
- `agencies`
- `agency_users`
- `agency_roles`
- `agency_clients`
- `agency_client_contacts`
- `growbuilder_sites`
- `growbuilder_site_revisions`
- `agency_client_services`
- `agency_client_invoices`
- `agency_client_payments`
- `agency_client_portal_users`
- `agency_client_requests`
- `growbuilder_templates`

**Status fields vs soft deletes:**
- Status fields (`active`, `suspended`, `cancelled`, `archived`) are for business logic
- Soft deletes are for data protection
- Use both: status for workflow, soft deletes for safety

**Restoration:**
```php
// Restore soft-deleted record
$agency = Agency::withTrashed()->find($id);
$agency->restore();

// Permanently delete
$agency->forceDelete();

// Query including trashed
Agency::withTrashed()->get();

// Query only trashed
Agency::onlyTrashed()->get();
```



---

## Business Logic & Workflows

### Subscription Plans

**Recommended Plans:**

| Plan | Sites | Storage | Team | Clients | White-Label | Price/mo |
|------|-------|---------|------|---------|-------------|----------|
| Starter | 10 | 20GB | 3 | Unlimited | No | K500 |
| Pro | 50 | 100GB | 10 | Unlimited | Basic | K2,000 |
| Premium | 200 | 500GB | 50 | Unlimited | Full | K8,000 |
| Enterprise | Unlimited | 2TB | Unlimited | Unlimited | Full | Custom |

**Feature Flags by Plan:**
```json
// Starter
{
    "white_label": false,
    "client_portal": true,
    "advanced_analytics": false,
    "priority_support": false,
    "api_access": false,
    "custom_domains": 3,
    "email_campaigns": false
}

// Pro
{
    "white_label": true,
    "client_portal": true,
    "advanced_analytics": true,
    "priority_support": false,
    "api_access": false,
    "custom_domains": 20,
    "email_campaigns": true
}

// Premium
{
    "white_label": true,
    "client_portal": true,
    "advanced_analytics": true,
    "priority_support": true,
    "api_access": true,
    "custom_domains": 100,
    "email_campaigns": true
}
```

**Checking Features:**
```php
// In Agency model
public function hasFeature(string $feature): bool
{
    $features = $this->subscriptionPlan?->features_json ?? [];
    return $features[$feature] ?? false;
}

// Usage
if ($agency->hasFeature('white_label')) {
    // Show white-label settings
}
```



### Operational Workflows

#### 1. Client Onboarding Workflow

```
1. Agency creates client profile
   - Fill client information
   - Add contacts
   - Add tags
   - Set status to 'lead'

2. Agency creates service package
   - Define services (website, hosting, etc.)
   - Set pricing and billing model
   - Set start and renewal dates

3. Agency creates site project
   - Select client
   - Choose site type
   - Select template
   - Set internal subdomain

4. Agency builds site
   - Add pages and content
   - Configure settings
   - Add custom domain (optional)

5. Agency reviews and publishes
   - Change status to 'in_review'
   - Final checks
   - Publish site
   - Update client status to 'active'

6. Client gets portal access (optional)
   - Create portal user
   - Set permissions
   - Send magic link

7. Billing cycle begins
   - Generate first invoice
   - Track payments
   - Set up renewal reminders
```

#### 2. Site Suspension Workflow

```
1. Invoice becomes overdue
   - Automatic status change to 'overdue'
   - Notification sent to agency

2. Agency reviews situation
   - Contact client
   - Attempt to collect payment

3. Agency suspends site (if needed)
   - Change site_status to 'suspended'
   - Site shows suspension page to public
   - Agency retains edit access

4. Client makes payment
   - Record payment
   - Update invoice status to 'paid'

5. Agency reactivates site
   - Change site_status to 'published'
   - Site goes live again
```

#### 3. Renewal Workflow

```
1. System checks upcoming renewals (30 days before)
   - Job runs daily
   - Identifies services due for renewal

2. Agency receives notification
   - Dashboard shows upcoming renewals
   - Email notification sent

3. Agency contacts client
   - Reminder email/call
   - Confirm renewal

4. Generate renewal invoice
   - Create new invoice
   - Link to service
   - Send to client

5. Track payment
   - Record payment when received
   - Update service renewal_date
   - Continue service
```



### Quota Enforcement

```php
// app/Services/GrowBuilder/QuotaService.php
namespace App\Services\GrowBuilder;

class QuotaService
{
    public function canCreateSite(Agency $agency): bool
    {
        return $agency->sites_used < $agency->site_limit;
    }
    
    public function canUploadFile(Agency $agency, int $fileSizeMb): bool
    {
        $newTotal = $agency->storage_used_mb + $fileSizeMb;
        return $newTotal <= $agency->storage_limit_mb;
    }
    
    public function canInviteTeamMember(Agency $agency): bool
    {
        $currentMembers = $agency->users()->count();
        return $currentMembers < $agency->team_member_limit;
    }
    
    public function getStorageWarningLevel(Agency $agency): string
    {
        $percentage = ($agency->storage_used_mb / $agency->storage_limit_mb) * 100;
        
        if ($percentage >= 100) return 'critical';
        if ($percentage >= 90) return 'danger';
        if ($percentage >= 80) return 'warning';
        return 'normal';
    }
    
    public function getSitesWarningLevel(Agency $agency): string
    {
        $percentage = ($agency->sites_used / $agency->site_limit) * 100;
        
        if ($percentage >= 100) return 'critical';
        if ($percentage >= 90) return 'danger';
        if ($percentage >= 80) return 'warning';
        return 'normal';
    }
}
```

**Usage in Controllers:**
```php
public function store(Request $request, QuotaService $quotaService)
{
    $agency = auth()->user()->currentAgency;
    
    if (!$quotaService->canCreateSite($agency)) {
        return back()->with('error', 'Site limit reached. Please upgrade your plan.');
    }
    
    // Create site...
}
```



### Activity Logging

```php
// app/Services/GrowBuilder/ActivityLogger.php
namespace App\Services\GrowBuilder;

use App\Models\AgencyActivityLog;

class ActivityLogger
{
    public function log(
        int $agencyId,
        string $actionType,
        string $entityType,
        ?int $entityId,
        string $description,
        ?array $metadata = null
    ): void {
        AgencyActivityLog::create([
            'agency_id' => $agencyId,
            'user_id' => auth()->id(),
            'action_type' => $actionType,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }
    
    public function logSiteCreated(GrowBuilderSite $site): void
    {
        $this->log(
            $site->agency_id,
            'created',
            'site',
            $site->id,
            "Created site '{$site->site_name}' for client '{$site->client->client_name}'",
            ['site_type' => $site->site_type]
        );
    }
    
    public function logSitePublished(GrowBuilderSite $site): void
    {
        $this->log(
            $site->agency_id,
            'published',
            'site',
            $site->id,
            "Published site '{$site->site_name}'",
            ['domain' => $site->custom_domain ?? $site->internal_subdomain]
        );
    }
    
    public function logClientCreated(AgencyClient $client): void
    {
        $this->log(
            $client->agency_id,
            'created',
            'client',
            $client->id,
            "Added new client '{$client->client_name}'",
            ['client_type' => $client->client_type]
        );
    }
    
    public function logInvoiceCreated(AgencyClientInvoice $invoice): void
    {
        $this->log(
            $invoice->agency_id,
            'created',
            'invoice',
            $invoice->id,
            "Created invoice {$invoice->invoice_number} for {$invoice->client->client_name}",
            ['amount' => $invoice->total, 'due_date' => $invoice->due_date]
        );
    }
}
```

**Usage:**
```php
// In controllers or services
public function store(Request $request, ActivityLogger $logger)
{
    $site = GrowBuilderSite::create($validated);
    
    $logger->logSiteCreated($site);
    
    return redirect()->route('sites.show', $site);
}
```



---

## Implementation Phases

### Phase 1: Agency Foundation (Weeks 1-3)

**Goal:** Establish agency as first-class entity

**Tasks:**
1. Create migrations for `agencies`, `agency_users`, `agency_roles`, `subscription_plans`
2. Seed system roles and starter plans
3. Update `growbuilder_sites` to include `agency_id` and `client_id`
4. Implement `AgencyScope` global scope
5. Create agency dashboard with quota visibility
6. Build team member invitation system
7. Implement role-based permissions

**Success Criteria:**
- Multiple staff can log in to same agency
- Storage and site limits enforced at agency level
- Basic role-based permissions working
- Agency dashboard shows quotas

**Files to Create:**
- `database/migrations/*_create_agencies_table.php`
- `database/migrations/*_create_agency_users_table.php`
- `database/migrations/*_create_agency_roles_table.php`
- `database/migrations/*_create_subscription_plans_table.php`
- `database/seeders/AgencyRolesSeeder.php`
- `database/seeders/SubscriptionPlansSeeder.php`
- `app/Models/Agency.php`
- `app/Models/AgencyUser.php`
- `app/Models/AgencyRole.php`
- `app/Models/SubscriptionPlan.php`
- `app/Infrastructure/GrowBuilder/Models/Scopes/AgencyScope.php`
- `app/Http/Controllers/GrowBuilder/AgencyDashboardController.php`
- `resources/js/Pages/GrowBuilder/Agency/Dashboard.vue`



### Phase 2: Client Management (Weeks 4-6)

**Goal:** Separate clients from sites

**Tasks:**
1. Create migrations for `agency_clients`, `agency_client_contacts`, `agency_client_tags`
2. Build client list and search interface
3. Create client profile pages
4. Implement client creation and editing
5. Add client assignment to sites
6. Build client notes and tags system

**Success Criteria:**
- Agencies can create clients without creating sites
- One client can have multiple sites
- Client information properly organized
- Client search and filtering works

**Files to Create:**
- `database/migrations/*_create_agency_clients_table.php`
- `database/migrations/*_create_agency_client_contacts_table.php`
- `database/migrations/*_create_agency_client_tags_table.php`
- `app/Models/AgencyClient.php`
- `app/Models/AgencyClientContact.php`
- `app/Models/AgencyClientTag.php`
- `app/Http/Controllers/GrowBuilder/ClientController.php`
- `resources/js/Pages/GrowBuilder/Clients/Index.vue`
- `resources/js/Pages/GrowBuilder/Clients/Show.vue`
- `resources/js/Pages/GrowBuilder/Clients/Create.vue`

### Phase 3: Commercial Tools (Weeks 7-9)

**Goal:** Enable business management

**Tasks:**
1. Create migrations for services, invoices, payments
2. Build service tracking interface
3. Implement invoice generation
4. Create payment recording system
5. Add due date reminders
6. Build renewal tracking

**Success Criteria:**
- Agencies can track what clients owe
- Renewal dates visible
- Overdue clients flagged
- Invoice generation works

**Files to Create:**
- `database/migrations/*_create_agency_client_services_table.php`
- `database/migrations/*_create_agency_client_invoices_table.php`
- `database/migrations/*_create_agency_client_invoice_items_table.php`
- `database/migrations/*_create_agency_client_payments_table.php`
- `app/Models/AgencyClientService.php`
- `app/Models/AgencyClientInvoice.php`
- `app/Models/AgencyClientPayment.php`
- `app/Http/Controllers/GrowBuilder/ServiceController.php`
- `app/Http/Controllers/GrowBuilder/InvoiceController.php`
- `app/Jobs/GrowBuilder/CheckServiceRenewals.php`
- `app/Jobs/GrowBuilder/CheckOverdueInvoices.php`
- `resources/js/Pages/GrowBuilder/Billing/Services.vue`
- `resources/js/Pages/GrowBuilder/Billing/Invoices.vue`



### Phase 4: Team Workflow & Reporting (Weeks 10-12)

**Goal:** Professional operations

**Tasks:**
1. Build staff invitation flow
2. Implement permission-based dashboards
3. Create activity logs
4. Build agency analytics
5. Add client/site reporting
6. Implement export capabilities

**Success Criteria:**
- Different staff see appropriate views
- All major actions logged
- Agencies can generate reports
- Export to CSV/PDF works

**Files to Create:**
- `database/migrations/*_create_agency_activity_logs_table.php`
- `app/Models/AgencyActivityLog.php`
- `app/Services/GrowBuilder/ActivityLogger.php`
- `app/Http/Controllers/GrowBuilder/ReportController.php`
- `resources/js/Pages/GrowBuilder/Reports/Dashboard.vue`
- `resources/js/Pages/GrowBuilder/Reports/ActivityLog.vue`

### Phase 5: Client Portal (Weeks 13-16)

**Goal:** Client self-service

**Tasks:**
1. Create portal user authentication (magic link/OTP)
2. Build portal dashboard
3. Implement limited content editing
4. Create client request system
5. Add invoice visibility
6. Implement analytics viewing

**Success Criteria:**
- Clients can log in securely
- Clients can edit permitted content
- Clients can view their invoices
- Agencies control what clients see

**Files to Create:**
- `database/migrations/*_create_agency_client_portal_users_table.php`
- `database/migrations/*_create_agency_client_portal_permissions_table.php`
- `database/migrations/*_create_agency_client_requests_table.php`
- `app/Models/AgencyClientPortalUser.php`
- `app/Models/AgencyClientRequest.php`
- `app/Http/Middleware/PortalAuthenticate.php`
- `app/Http/Controllers/Portal/PortalAuthController.php`
- `app/Http/Controllers/Portal/PortalDashboardController.php`
- `routes/portal.php`
- `resources/js/Pages/Portal/Dashboard.vue`
- `resources/js/Layouts/PortalLayout.vue`

### Phase 6: White-Label & Advanced (Weeks 17-20)

**Goal:** Professional branding

**Tasks:**
1. Create agency branding settings
2. Implement portal custom domain
3. Build custom email templates
4. Create branded maintenance pages
5. Build agency-private template library
6. Add advanced analytics

**Success Criteria:**
- Agencies can use custom domain
- All client-facing areas branded
- Agencies can save and reuse templates
- Advanced analytics available

**Files to Create:**
- `database/migrations/*_create_agency_branding_settings_table.php`
- `database/migrations/*_create_growbuilder_templates_table.php`
- `app/Models/AgencyBrandingSetting.php`
- `app/Models/GrowBuilderTemplate.php`
- `app/Http/Controllers/GrowBuilder/BrandingController.php`
- `app/Http/Controllers/GrowBuilder/TemplateController.php`
- `resources/js/Pages/GrowBuilder/Settings/Branding.vue`
- `resources/js/Pages/GrowBuilder/Templates/Index.vue`



---

## Security Considerations

### Tenant Isolation Rules

1. **Every agency is isolated from other agencies**
   - Use `AgencyScope` on all models
   - Always filter by `agency_id` in queries
   - Never expose cross-agency data

2. **Every client record belongs to one agency**
   - Enforce in database with foreign keys
   - Validate in policies
   - Check in controllers

3. **Every site query must be filtered by agency ownership**
   - Global scope handles this automatically
   - Double-check in raw queries
   - Validate in API endpoints

4. **Portal access must never expose other clients**
   - Separate authentication system
   - Strict permission checks
   - Session isolation

5. **Domain ownership validation**
   - Verify DNS records before activation
   - Check for conflicts
   - Prevent domain hijacking

### Recommended Protections

```php
// 1. Role-based permissions for internal team
Gate::define('edit-site', function (User $user, GrowBuilderSite $site) {
    return $site->agency_id === $user->currentAgency?->id
        && $user->agencyRole?->hasPermission('edit_site');
});

// 2. Expiring magic links for portal access
$token = Str::random(64);
$expiresAt = now()->addHours(24);

Cache::put("portal_token:{$token}", [
    'portal_user_id' => $portalUser->id,
    'expires_at' => $expiresAt,
], $expiresAt);

// 3. Audit logs for important actions
$logger->log($agency->id, 'deleted', 'site', $site->id, "Deleted site '{$site->site_name}'");

// 4. File upload restrictions
$request->validate([
    'file' => 'required|file|max:10240|mimes:jpg,png,pdf,doc,docx',
]);

// 5. Rate limiting on portal access
Route::middleware(['throttle:10,1'])->group(function () {
    Route::post('/portal/login', [PortalAuthController::class, 'login']);
});
```



---

## Key Differences from Simple Multi-Site Model

### What NOT to Do

❌ Store all client info inside `growbuilder_sites` table  
❌ Use only metadata for clients  
❌ Rely mainly on token access for clients  
❌ Make agency equal to one user account  
❌ Use single-user permissions model  
❌ Hard-code permissions in controllers  
❌ Skip soft deletes  
❌ Ignore tenant isolation  

### What TO Do

✅ Create `agencies` as first-class entities  
✅ Add agency team members and roles  
✅ Create dedicated `clients` table  
✅ Implement service and billing tracker  
✅ Use secure client portal authentication  
✅ Maintain activity logs  
✅ Support reusable agency templates  
✅ Implement workflow statuses  
✅ Use global scopes for tenant isolation  
✅ Apply soft deletes to all major tables  
✅ Use Laravel Policies for permissions  
✅ Use Model Observers for consistency  

---

## Testing Strategy

### Unit Tests

```php
// tests/Unit/Models/AgencyTest.php
test('agency can check if it has a feature', function () {
    $plan = SubscriptionPlan::factory()->create([
        'features_json' => ['white_label' => true, 'api_access' => false],
    ]);
    
    $agency = Agency::factory()->create(['subscription_plan_id' => $plan->id]);
    
    expect($agency->hasFeature('white_label'))->toBeTrue();
    expect($agency->hasFeature('api_access'))->toBeFalse();
});

test('agency role can check permissions', function () {
    $role = AgencyRole::factory()->create([
        'permissions_json' => ['create_site', 'edit_site'],
    ]);
    
    expect($role->hasPermission('create_site'))->toBeTrue();
    expect($role->hasPermission('delete_site'))->toBeFalse();
});
```

### Feature Tests

```php
// tests/Feature/GrowBuilder/SiteCreationTest.php
test('agency can create site within quota', function () {
    $agency = Agency::factory()->create(['site_limit' => 10, 'sites_used' => 5]);
    $user = User::factory()->create();
    $agency->users()->attach($user, ['role_id' => $ownerRole->id]);
    
    actingAs($user)
        ->post(route('growbuilder.sites.store'), [
            'site_name' => 'Test Site',
            'client_id' => $client->id,
        ])
        ->assertRedirect();
    
    expect($agency->fresh()->sites_used)->toBe(6);
});

test('agency cannot create site when quota exceeded', function () {
    $agency = Agency::factory()->create(['site_limit' => 10, 'sites_used' => 10]);
    $user = User::factory()->create();
    $agency->users()->attach($user, ['role_id' => $ownerRole->id]);
    
    actingAs($user)
        ->post(route('growbuilder.sites.store'), [
            'site_name' => 'Test Site',
            'client_id' => $client->id,
        ])
        ->assertSessionHas('error');
});
```

### Policy Tests

```php
// tests/Feature/Policies/SitePolicyTest.php
test('user can only edit sites in their agency', function () {
    $agency1 = Agency::factory()->create();
    $agency2 = Agency::factory()->create();
    
    $user = User::factory()->create();
    $agency1->users()->attach($user, ['role_id' => $adminRole->id]);
    
    $site1 = GrowBuilderSite::factory()->create(['agency_id' => $agency1->id]);
    $site2 = GrowBuilderSite::factory()->create(['agency_id' => $agency2->id]);
    
    expect($user->can('update', $site1))->toBeTrue();
    expect($user->can('update', $site2))->toBeFalse();
});
```



---

## Performance Optimization

### Caching Strategy

```php
// Cache agency quota information
Cache::remember("agency:{$agencyId}:quota", 3600, function () use ($agencyId) {
    $agency = Agency::find($agencyId);
    return [
        'storage_used_mb' => $agency->storage_used_mb,
        'storage_limit_mb' => $agency->storage_limit_mb,
        'sites_used' => $agency->sites_used,
        'site_limit' => $agency->site_limit,
    ];
});

// Cache subscription plan features
Cache::remember("plan:{$planId}:features", 86400, function () use ($planId) {
    return SubscriptionPlan::find($planId)->features_json;
});

// Cache user permissions
Cache::remember("user:{$userId}:permissions", 3600, function () use ($userId) {
    return User::find($userId)->agencyRole?->permissions_json ?? [];
});
```

### Eager Loading

```php
// Load relationships to avoid N+1 queries
$sites = GrowBuilderSite::with([
    'agency',
    'client',
    'settings',
    'createdBy',
])->where('agency_id', $agencyId)->get();

// Load counts
$clients = AgencyClient::withCount([
    'sites',
    'services',
    'invoices',
])->where('agency_id', $agencyId)->get();
```

### Query Optimization

```php
// Use select to limit columns
$sites = GrowBuilderSite::select([
    'id', 'site_name', 'site_status', 'storage_used_mb', 'last_published_at'
])->where('agency_id', $agencyId)->get();

// Use chunk for large datasets
GrowBuilderSite::where('agency_id', $agencyId)
    ->chunk(100, function ($sites) {
        foreach ($sites as $site) {
            // Process site
        }
    });
```

---

## Troubleshooting

### Common Issues

**Issue:** Cross-agency data leak  
**Solution:** Ensure `AgencyScope` is applied to all models. Check raw queries for `agency_id` filtering.

**Issue:** Quota not updating  
**Solution:** Verify Model Observers are registered. Check for direct database updates bypassing Eloquent.

**Issue:** Permission checks failing  
**Solution:** Ensure user has `agencyRole` relationship loaded. Check `permissions_json` format.

**Issue:** Portal authentication not working  
**Solution:** Verify magic link token in cache. Check token expiration. Ensure portal user status is 'active'.

**Issue:** Storage calculation incorrect  
**Solution:** Run `RecalculateAgencyStorage` job. Check for orphaned media files.

**Issue:** Soft deletes not working  
**Solution:** Ensure `SoftDeletes` trait is used. Check for `forceDelete()` calls.



---

## Future Enhancements

### Phase 7+: Advanced Features

**API Access**
- RESTful API for agency integrations
- OAuth2 authentication
- Rate limiting per plan
- Webhook support for events

**Marketplace**
- Let agencies sell templates to other agencies
- Revenue sharing model
- Template reviews and ratings
- Featured templates

**Reseller Program**
- Multi-tier agency relationships
- Parent-child agency structure
- Commission tracking
- White-label reselling

**Advanced Analytics**
- Heatmaps for site visitors
- Session recordings
- A/B testing framework
- Conversion funnel tracking

**E-commerce Integration**
- Full shop management
- Inventory tracking
- Order processing
- Payment gateway integration

**Email Marketing**
- Built-in email campaigns
- Subscriber management
- Email templates
- Campaign analytics

**Booking Systems**
- Appointments and reservations
- Calendar integration
- Automated reminders
- Payment collection

**Membership Sites**
- Gated content
- Subscription management
- Member-only areas
- Drip content

---

## Related Documents

- `AGENCY_CLIENT_MANAGEMENT.md` - Existing client management notes
- `GROWBUILDER_ROUTING_FIX.md` - Current routing structure
- `docs/structure.md` - Overall project structure
- `docs/tech.md` - Technology stack details

---

## Changelog

### March 16, 2026 - Phase 1 Implementation Complete
- Initial comprehensive documentation created
- Combined concept and implementation details
- Added all database schemas
- Included Laravel-specific implementation
- Added security, testing, and performance sections
- Defined 6-phase implementation plan

**Phase 1 Complete:**
- ✅ Created migrations: subscription_plans, agencies, agency_roles, agency_users
- ✅ Created models: SubscriptionPlan, Agency, AgencyRole, AgencyUser
- ✅ Created seeders: SubscriptionPlansSeeder (4 plans), AgencyRolesSeeder (8 roles)
- ✅ Ran migrations and seeders successfully
- ✅ Created AgencyScope for tenant isolation
- ✅ Updated User model with agency relationships
- ✅ Created QuotaService for quota management
- ✅ Created ActivityLogger service
- ✅ Created EnsureUserHasAgency middleware
- ✅ Created AgencyDashboardController
- ✅ Created Agency Dashboard Vue component
- ✅ Added agency routes to growbuilder.php
- ✅ Phase 1 foundation complete - ready for Phase 2

**Next:** Phase 2 - Client Management (agency_clients, contacts, tags)

