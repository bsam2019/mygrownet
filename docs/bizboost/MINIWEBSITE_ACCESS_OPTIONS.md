# BizBoost Mini-Website Access Options

**Last Updated:** December 20, 2025  
**Status:** Decision Required

## Current Implementation

Mini-websites are currently accessible via path-based URLs:
- **Format:** `mygrownet.com/biz/{slug}`
- **Example:** `mygrownet.com/biz/lusaka-fashion-boutique`
- **Routes:** Defined in `routes/bizboost.php`
- **Controller:** `BusinessController::publicPage()`

### Pros of Current Approach
✅ No DNS configuration needed  
✅ Works immediately  
✅ Simple to implement  
✅ No subdomain conflicts  
✅ Easy to maintain

### Cons of Current Approach
❌ Less professional appearance  
❌ Longer URLs  
❌ Not as memorable  
❌ Doesn't feel like "their own website"

---

## Option 1: Keep Current Path-Based URLs

**Recommendation:** Best for MVP and initial launch

### Implementation
Already implemented! No changes needed.

### User Experience
- Users share: `mygrownet.com/biz/their-business-name`
- Clear that it's part of MyGrowNet platform
- Works on all devices immediately

---

## Option 2: Wildcard Subdomain URLs

**Recommendation:** Better for premium feel and GrowBuilder future

### How It Works

#### One-Time DNS Setup (You do this ONCE)
1. Go to Cloudflare DNS settings
2. Add wildcard A record:
   - **Type:** A
   - **Name:** `*` (asterisk)
   - **Content:** Your server IP (e.g., `123.45.67.89`)
   - **Proxy status:** Proxied (orange cloud)
3. Save

That's it! Now ALL subdomains automatically route to your server:
- `lusaka-fashion.mygrownet.com` ✅
- `rockshield.mygrownet.com` ✅
- `any-business.mygrownet.com` ✅

#### Laravel Routing (Automatic)
Laravel checks the subdomain and shows the correct business:

```php
// routes/bizboost.php
Route::domain('{subdomain}.mygrownet.com')->group(function () {
    Route::get('/', [BusinessController::class, 'subdomainPage']);
    Route::get('/products', [BusinessController::class, 'subdomainProducts']);
    Route::get('/product/{id}', [BusinessController::class, 'subdomainProduct']);
});
```

#### Reserved Subdomains
You need to prevent users from claiming important subdomains:

**Reserved List:**
- `www` - Main website
- `app` - Application
- `api` - API endpoints
- `admin` - Admin panel
- `mail` - Email services
- `marketplace` - GrowNet Market
- `bizboost` - BizBoost app
- `lifeplus` - LifePlus module
- `wallet` - Wallet services
- `support` - Support portal
- `help` - Help center
- `docs` - Documentation
- `blog` - Company blog
- `cdn` - CDN resources
- `static` - Static assets
- `assets` - Asset files
- `images` - Image hosting
- `files` - File storage
- `download` - Downloads
- `upload` - Uploads
- `test` - Testing
- `dev` - Development
- `staging` - Staging environment
- `demo` - Demo accounts

### Implementation Steps

#### 1. Add Reserved Subdomains to Config
```php
// config/bizboost.php
'reserved_subdomains' => [
    'www', 'app', 'api', 'admin', 'mail', 'marketplace', 
    'bizboost', 'lifeplus', 'wallet', 'support', 'help',
    'docs', 'blog', 'cdn', 'static', 'assets', 'images',
    'files', 'download', 'upload', 'test', 'dev', 'staging', 'demo'
],
```

#### 2. Update Business Slug Validation
```php
// In business creation/update validation
'slug' => [
    'required',
    'string',
    'max:100',
    'regex:/^[a-z0-9-]+$/',
    Rule::notIn(config('bizboost.reserved_subdomains')),
    Rule::unique('bizboost_businesses', 'slug')->ignore($businessId),
],
```

#### 3. Add Subdomain Routes
```php
// routes/bizboost.php

// Subdomain-based mini-websites
Route::domain('{subdomain}.mygrownet.com')
    ->where('subdomain', '^(?!www$).*') // Exclude www
    ->group(function () {
        Route::get('/', [BusinessController::class, 'subdomainPage'])
            ->name('bizboost.subdomain.home');
        Route::get('/products', [BusinessController::class, 'subdomainProducts'])
            ->name('bizboost.subdomain.products');
        Route::get('/product/{id}', [BusinessController::class, 'subdomainProduct'])
            ->name('bizboost.subdomain.product');
        Route::post('/contact', [BusinessController::class, 'subdomainContact'])
            ->name('bizboost.subdomain.contact');
    });
```

#### 4. Add Controller Methods
```php
// app/Http/Controllers/BizBoost/BusinessController.php

public function subdomainPage(string $subdomain)
{
    // Check if subdomain is reserved
    if (in_array($subdomain, config('bizboost.reserved_subdomains'))) {
        abort(404);
    }

    $business = BizBoostBusinessModel::where('slug', $subdomain)
        ->where('is_active', true)
        ->with(['profile', 'products' => function ($q) {
            $q->where('is_active', true)
                ->with('primaryImage')
                ->orderBy('is_featured', 'desc')
                ->orderBy('sort_order')
                ->take(20);
        }])
        ->firstOrFail();

    // Check if published or marketplace listed
    $isPublished = $business->profile?->is_published ?? false;
    $isMarketplaceListed = $business->marketplace_listed ?? false;
    
    if (!$isPublished && !$isMarketplaceListed) {
        abort(404, 'This business page is not available');
    }

    // Track page view
    \DB::table('bizboost_analytics_events')->insert([
        'business_id' => $business->id,
        'event_type' => 'page_view',
        'source' => 'subdomain',
        'ip_address' => request()->ip(),
        'user_agent' => request()->userAgent(),
        'referrer' => request()->header('referer'),
        'recorded_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return Inertia::render('BizBoost/Public/BusinessPage', [
        'business' => $business,
        'isSubdomain' => true,
    ]);
}

// Similar methods for subdomainProducts, subdomainProduct, subdomainContact
```

#### 5. Update .env
```env
APP_URL=https://mygrownet.com
SESSION_DOMAIN=.mygrownet.com
SANCTUM_STATEFUL_DOMAINS=*.mygrownet.com,mygrownet.com
```

### User Experience
- Users share: `their-business.mygrownet.com`
- Feels like their own website
- More professional and memorable
- Better for branding

### Pros
✅ Professional appearance  
✅ Memorable URLs  
✅ Feels like "their own site"  
✅ One-time DNS setup  
✅ Automatic for all businesses  
✅ Better for GrowBuilder future

### Cons
❌ Slightly more complex routing  
❌ Need to manage reserved subdomains  
❌ Session/cookie domain configuration  
❌ SSL certificate must support wildcards (Cloudflare handles this)

---

## Option 3: Hybrid Approach (Recommended)

**Best of both worlds!**

### Implementation
Support BOTH access methods:
1. **Path-based:** `mygrownet.com/biz/{slug}` (always works)
2. **Subdomain:** `{slug}.mygrownet.com` (premium feel)

### Benefits
✅ Backwards compatible  
✅ Users can choose which URL to share  
✅ Gradual migration path  
✅ Fallback if subdomain issues  
✅ Both URLs show same content

### How It Works
- Both routes point to same controller methods
- Canonical URL in meta tags prevents SEO issues
- Users can use whichever URL they prefer

---

## Recommendation

### For Current Phase (MVP)
**Keep path-based URLs** (`mygrownet.com/biz/{slug}`)
- Already working
- No additional setup
- Focus on core features

### For GrowBuilder Phase
**Implement hybrid approach**
- Add subdomain support
- Keep path-based as fallback
- Premium feel for website builder
- One-time Cloudflare DNS setup

---

## DNS Setup Instructions (When Ready)

### Cloudflare Configuration

1. **Login to Cloudflare**
   - Go to your domain: `mygrownet.com`
   - Click "DNS" tab

2. **Add Wildcard Record**
   - Click "Add record"
   - **Type:** A
   - **Name:** `*` (just an asterisk)
   - **IPv4 address:** Your server IP
   - **Proxy status:** Proxied (orange cloud icon)
   - **TTL:** Auto
   - Click "Save"

3. **Verify Setup**
   - Wait 5-10 minutes for propagation
   - Test: `ping test.mygrownet.com` (should resolve to your IP)
   - Test: `ping anything.mygrownet.com` (should resolve to your IP)

4. **SSL Certificate**
   - Cloudflare automatically handles wildcard SSL
   - No additional configuration needed
   - All subdomains get HTTPS automatically

### That's It!
No need to add DNS records for each business. The wildcard handles everything automatically.

---

## Custom Domains (Future Premium Feature)

For GrowBuilder premium users, allow custom domains:
- User owns: `mybusiness.com`
- Points to: `their-slug.mygrownet.com`
- Requires: User adds CNAME record in their DNS
- Premium feature: K200-500/year

---

## Decision Checklist

- [ ] Choose approach (path-based, subdomain, or hybrid)
- [ ] If subdomain: Set up wildcard DNS in Cloudflare
- [ ] If subdomain: Add reserved subdomains list
- [ ] If subdomain: Update slug validation
- [ ] If subdomain: Add subdomain routes
- [ ] If subdomain: Add controller methods
- [ ] If subdomain: Update .env configuration
- [ ] Test with multiple businesses
- [ ] Update documentation for users
- [ ] Add URL display in dashboard

---

## Files to Modify (If Implementing Subdomains)

1. `config/bizboost.php` - Add reserved subdomains
2. `routes/bizboost.php` - Add subdomain routes
3. `app/Http/Controllers/BizBoost/BusinessController.php` - Add subdomain methods
4. `.env` - Update domain configuration
5. Business creation/update validation - Check reserved subdomains
6. Dashboard UI - Show both URL options

---

## Questions?

**Q: Do I need to configure DNS for each business?**  
A: No! One wildcard DNS record handles ALL subdomains automatically.

**Q: What if a user wants `www.mygrownet.com`?**  
A: That's reserved. The reserved subdomains list prevents this.

**Q: Can users have custom domains?**  
A: Yes, as a premium feature in GrowBuilder. They add a CNAME record pointing to their subdomain.

**Q: Does this work with SSL?**  
A: Yes! Cloudflare's wildcard SSL covers all subdomains automatically.

**Q: What about session/authentication?**  
A: Set `SESSION_DOMAIN=.mygrownet.com` (note the leading dot) to share sessions across subdomains.


One-Time Cloudflare Configuration
You only need to set up DNS once in Cloudflare:

Add a single wildcard A record: *.mygrownet.com → Your server IP
That's it! Every subdomain automatically routes to your server
How It Works
Once the wildcard DNS is set up:

lusaka-fashion.mygrownet.com → Automatically works
rockshield.mygrownet.com → Automatically works
any-slug.mygrownet.com → Automatically works
Laravel handles the routing logic to determine which business to show based on the subdomain.