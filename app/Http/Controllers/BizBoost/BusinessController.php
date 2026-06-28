<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessProfileModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class BusinessController extends Controller
{
    public function profile(Request $request): Response
    {
        $business = $this->getBusiness($request);

        return Inertia::render('BizBoost/Business/Profile', [
            'business' => $business->load('profile'),
            'industries' => config('modules.bizboost.industry_kits', []),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'industry' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'social_links' => 'nullable|array',
            'business_hours' => 'nullable|array',
        ]);

        $business = $this->getBusiness($request);
        $business->update($validated);

        // Sync address/phone changes to primary location if it exists
        $this->syncProfileToPrimaryLocation($business, $validated);

        return back()->with('success', 'Business profile updated successfully.');
    }

    /**
     * Sync business profile changes to the primary location.
     */
    private function syncProfileToPrimaryLocation($business, array $data): void
    {
        $primaryLocation = \DB::table('bizboost_locations')
            ->where('business_id', $business->id)
            ->where('is_primary', true)
            ->first();

        if ($primaryLocation) {
            \DB::table('bizboost_locations')
                ->where('id', $primaryLocation->id)
                ->update([
                    'address' => $data['address'] ?? $primaryLocation->address,
                    'city' => $data['city'] ?? $primaryLocation->city,
                    'phone' => $data['phone'] ?? $primaryLocation->phone,
                    'whatsapp' => $data['whatsapp'] ?? $primaryLocation->whatsapp,
                    'updated_at' => now(),
                ]);
        }
    }

    public function updateLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $business = $this->getBusiness($request);

        // Delete old logo if exists
        if ($business->logo_path) {
            Storage::disk('public')->delete($business->logo_path);
        }

        $path = $request->file('logo')->store('bizboost/logos', 'public');
        $business->update(['logo_path' => $path]);

        return back()->with('success', 'Logo updated successfully.');
    }

    public function settings(Request $request): Response
    {
        $business = $this->getBusiness($request);

        return Inertia::render('BizBoost/Business/Settings', [
            'business' => $business->load('profile'),
        ]);
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'timezone' => 'nullable|string|max:50',
            'locale' => 'nullable|string|max:10',
            'currency' => 'nullable|string|max:10',
            'settings' => 'nullable|array',
        ]);

        $business = $this->getBusiness($request);
        $business->update($validated);

        return back()->with('success', 'Settings updated successfully.');
    }

    public function miniWebsite(Request $request): Response
    {
        $business = $this->getBusiness($request);
        
        // Load products for preview
        $products = $business->products()
            ->where('is_active', true)
            ->with('primaryImage')
            ->orderBy('is_featured', 'desc')
            ->orderBy('sort_order')
            ->take(8)
            ->get();

        return Inertia::render('BizBoost/Business/MiniWebsite', [
            'business' => $business->load('profile'),
            'publicUrl' => route('bizboost.public.business', $business->slug),
            'products' => $products,
        ]);
    }

    public function updateMiniWebsite(Request $request)
    {
        $validated = $request->validate([
            // Basic content
            'about' => 'nullable|string|max:2000',
            'tagline' => 'nullable|string|max:120',
            'contact_email' => 'nullable|email|max:255',
            'hero_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'about_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            
            // Enhanced About Us fields
            'business_story' => 'nullable|string|max:5000',
            'mission' => 'nullable|string|max:500',
            'vision' => 'nullable|string|max:500',
            'founding_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'business_hours' => 'nullable|array',
            'team_members' => 'nullable|array',
            'team_members.*.name' => 'nullable|string|max:100',
            'team_members.*.role' => 'nullable|string|max:100',
            'team_members.*.image' => 'nullable|string|max:255',
            'achievements' => 'nullable|array',
            'achievements.*.title' => 'nullable|string|max:200',
            'achievements.*.year' => 'nullable|integer',
            
            // Services
            'services' => 'nullable|array',
            'services.*.name' => 'nullable|string|max:100',
            'services.*.description' => 'nullable|string|max:500',
            'services.*.price' => 'nullable|string|max:50',
            'services.*.icon' => 'nullable|string|max:50',
            
            // Testimonials
            'testimonials' => 'nullable|array',
            'testimonials.*.name' => 'nullable|string|max:100',
            'testimonials.*.content' => 'nullable|string|max:500',
            'testimonials.*.rating' => 'nullable|integer|min:1|max:5',
            
            // Section toggles
            'show_products' => 'boolean',
            'show_services' => 'boolean',
            'show_gallery' => 'boolean',
            'show_testimonials' => 'boolean',
            'show_business_hours' => 'boolean',
            'show_contact_form' => 'boolean',
            'show_whatsapp_button' => 'boolean',
            'show_social_links' => 'boolean',
            
            // Theme settings
            'theme_settings' => 'nullable|array',
            'theme_settings.primary_color' => 'nullable|string|max:20',
            'theme_settings.secondary_color' => 'nullable|string|max:20',
            'theme_settings.accent_color' => 'nullable|string|max:20',
            'theme_settings.nav_color' => 'nullable|string|max:20',
            'theme_settings.template' => 'nullable|string|in:professional,elegant,bold,minimal,creative,corporate,luxury,modern',
            'theme_settings.layout' => 'nullable|string|in:modern,classic,bold,minimal,elegant,corporate,creative,luxury',
            'theme_settings.font_style' => 'nullable|string|in:default,elegant,modern,playful',
            'theme_settings.button_style' => 'nullable|string|in:rounded,pill,square,outline,ghost',
            'theme_settings.hero_style' => 'nullable|string|in:gradient,image,solid,split,wave,diagonal,particles',
            'theme_settings.about_layout' => 'nullable|string|in:story,stats,team,minimal',
            'theme_settings.show_hero_overlay' => 'nullable|boolean',
        ]);

        $business = $this->getBusiness($request);
        
        // Handle hero image upload
        $heroImagePath = $business->profile?->hero_image_path;
        if ($request->hasFile('hero_image')) {
            // Delete old hero image if exists
            if ($heroImagePath) {
                Storage::disk('public')->delete($heroImagePath);
            }
            $heroImagePath = $request->file('hero_image')->store('bizboost/hero-images', 'public');
        }
        
        // Handle about image upload
        $aboutImagePath = $business->profile?->about_image_path;
        if ($request->hasFile('about_image')) {
            // Delete old about image if exists
            if ($aboutImagePath) {
                Storage::disk('public')->delete($aboutImagePath);
            }
            $aboutImagePath = $request->file('about_image')->store('bizboost/about-images', 'public');
        }
        
        // Prepare data for update
        $profileData = [
            // Basic content
            'about' => $validated['about'] ?? null,
            'tagline' => $validated['tagline'] ?? null,
            'contact_email' => $validated['contact_email'] ?? null,
            'hero_image_path' => $heroImagePath,
            'about_image_path' => $aboutImagePath,
            
            // Enhanced About Us fields
            'business_story' => $validated['business_story'] ?? null,
            'mission' => $validated['mission'] ?? null,
            'vision' => $validated['vision'] ?? null,
            'founding_year' => $validated['founding_year'] ?? null,
            'business_hours' => $validated['business_hours'] ?? null,
            'team_members' => $validated['team_members'] ?? null,
            'achievements' => $validated['achievements'] ?? null,
            'services' => $validated['services'] ?? null,
            'testimonials' => $validated['testimonials'] ?? null,
            
            // Section toggles
            'show_products' => $validated['show_products'] ?? true,
            'show_services' => $validated['show_services'] ?? true,
            'show_gallery' => $validated['show_gallery'] ?? false,
            'show_testimonials' => $validated['show_testimonials'] ?? false,
            'show_business_hours' => $validated['show_business_hours'] ?? true,
            'show_contact_form' => $validated['show_contact_form'] ?? true,
            'show_whatsapp_button' => $validated['show_whatsapp_button'] ?? true,
            'show_social_links' => $validated['show_social_links'] ?? true,
        ];
        
        // Handle theme_settings
        $themeSettings = $validated['theme_settings'] ?? null;
        
        if (empty($themeSettings) && $request->has('theme_settings')) {
            $themeSettings = $request->input('theme_settings');
        }
        
        if (!empty($themeSettings) && is_array($themeSettings)) {
            if (isset($themeSettings['show_hero_overlay'])) {
                $themeSettings['show_hero_overlay'] = filter_var(
                    $themeSettings['show_hero_overlay'], 
                    FILTER_VALIDATE_BOOLEAN
                );
            }
            $profileData['theme_settings'] = $themeSettings;
        }
        
        $business->profile()->updateOrCreate(
            ['business_id' => $business->id],
            $profileData
        );

        return back()->with('success', 'Mini-website updated successfully.');
    }

    public function publishMiniWebsite(Request $request)
    {
        $business = $this->getBusiness($request);
        $profile = $business->profile;

        if (!$profile) {
            return back()->with('error', 'Please configure your mini-website first.');
        }

        $profile->update(['is_published' => true]);

        return back()->with('success', 'Your mini-website is now live!');
    }

    public function unpublishMiniWebsite(Request $request)
    {
        $business = $this->getBusiness($request);
        $business->profile?->update(['is_published' => false]);

        return back()->with('success', 'Mini-website unpublished.');
    }

    /**
     * Public mini-website page
     */
    public function publicPage(string $slug)
    {
        $business = BizBoostBusinessModel::where('slug', $slug)
            ->where('is_active', true)
            ->with(['profile', 'products' => function ($q) {
                $q->where('is_active', true)
                    ->with('primaryImage')
                    ->orderBy('is_featured', 'desc')
                    ->orderBy('sort_order')
                    ->take(20);
            }])
            ->firstOrFail();

        // Check if mini-website is published OR business is listed on marketplace
        // Marketplace-listed businesses should be accessible even without publishing mini-website
        $isPublished = $business->profile?->is_published ?? false;
        $isMarketplaceListed = $business->marketplace_listed ?? false;
        
        if (!$isPublished && !$isMarketplaceListed) {
            abort(404, 'This business page is not available');
        }

        // Track page view
        \DB::table('bizboost_analytics_events')->insert([
            'business_id' => $business->id,
            'event_type' => 'page_view',
            'source' => 'mini_website',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'referrer' => request()->header('referer'),
            'recorded_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return Inertia::render('BizBoost/Public/BusinessPage', [
            'business' => $business,
        ]);
    }

    /**
     * Public products listing
     */
    public function publicProducts(Request $request, string $slug)
    {
        $business = BizBoostBusinessModel::where('slug', $slug)
            ->where('is_active', true)
            ->with('profile')
            ->firstOrFail();

        // Check if mini-website is published OR business is listed on marketplace
        $isPublished = $business->profile?->is_published ?? false;
        $isMarketplaceListed = $business->marketplace_listed ?? false;
        
        if (!$isPublished && !$isMarketplaceListed) {
            abort(404, 'This business page is not available');
        }

        $filters = [
            'search' => $request->input('search', ''),
            'category' => $request->input('category', ''),
        ];

        $query = $business->products()
            ->where('is_active', true)
            ->with('images');

        // Apply search filter
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        // Apply category filter
        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        $products = $query
            ->orderBy('is_featured', 'desc')
            ->orderBy('sort_order')
            ->paginate(20);

        // Get unique categories for filter dropdown
        $categories = $business->products()
            ->where('is_active', true)
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->toArray();

        return Inertia::render('BizBoost/Public/Products', [
            'business' => $business->load('profile'),
            'products' => $products,
            'categories' => $categories,
            'filters' => $filters,
        ]);
    }

    /**
     * Public single product
     */
    public function publicProduct(string $slug, int $productId)
    {
        $business = BizBoostBusinessModel::where('slug', $slug)
            ->where('is_active', true)
            ->with('profile')
            ->firstOrFail();

        // Check if mini-website is published OR business is listed on marketplace
        $isPublished = $business->profile?->is_published ?? false;
        $isMarketplaceListed = $business->marketplace_listed ?? false;
        
        if (!$isPublished && !$isMarketplaceListed) {
            abort(404, 'This business page is not available');
        }

        $product = $business->products()
            ->where('is_active', true)
            ->with('images')
            ->findOrFail($productId);

        // Get related products (same category or random if no category)
        $relatedProducts = $business->products()
            ->where('is_active', true)
            ->where('id', '!=', $productId)
            ->when($product->category, function ($query) use ($product) {
                $query->where('category', $product->category);
            })
            ->with('images')
            ->inRandomOrder()
            ->take(4)
            ->get();

        return Inertia::render('BizBoost/Public/Product', [
            'business' => $business,
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }

    /**
     * Handle contact form submission
     */
    public function publicContact(Request $request, string $slug)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'message' => 'required|string|max:2000',
        ]);

        $business = BizBoostBusinessModel::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // Store as a lead/customer inquiry
        // This could trigger a notification to the business owner
        
        return back()->with('success', 'Thank you for your message! We will get back to you soon.');
    }

    private function getBusiness(Request $request): BizBoostBusinessModel
    {
        return BizBoostBusinessModel::where('user_id', $request->user()->id)->firstOrFail();
    }
}
