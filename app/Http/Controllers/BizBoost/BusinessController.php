<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Domain\BizBoost\Services\BusinessService;
use App\Domain\BizBoost\Entities\BusinessProfile;
use App\Domain\BizBoost\Repositories\ProductRepositoryInterface;
use App\Domain\BizBoost\Repositories\LocationRepositoryInterface;
use App\Domain\BizBoost\Repositories\AnalyticsEventRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class BusinessController extends Controller
{
    public function __construct(
        private BusinessService $businessService,
        private ProductRepositoryInterface $productRepo,
        private LocationRepositoryInterface $locationRepo,
        private AnalyticsEventRepositoryInterface $analyticsEventRepo,
    ) {}

    public function profile(Request $request): Response
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);

        return Inertia::render('BizBoost/Business/Profile', [
            'business' => $business->toArray(),
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

        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $this->businessService->updateBusiness($business->id, $validated);

        $primaryLocation = $this->locationRepo->findPrimary($business->id);
        if ($primaryLocation) {
            $this->businessService->updateBusiness($business->id, [
                'address' => $validated['address'] ?? $primaryLocation->address,
                'city' => $validated['city'] ?? $primaryLocation->city,
                'phone' => $validated['phone'] ?? $primaryLocation->phone,
                'whatsapp' => $validated['whatsapp'] ?? $primaryLocation->whatsapp,
            ]);
        }

        return back()->with('success', 'Business profile updated successfully.');
    }

    public function updateLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $this->businessService->updateLogo($business->id, $request->file('logo'));

        return back()->with('success', 'Logo updated successfully.');
    }

    public function settings(Request $request): Response
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);

        return Inertia::render('BizBoost/Business/Settings', [
            'business' => $business->toArray(),
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

        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $this->businessService->updateBusiness($business->id, $validated);

        return back()->with('success', 'Settings updated successfully.');
    }

    public function miniWebsite(Request $request): Response
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $products = $this->productRepo->findActiveByBusiness($business->id, ['featured' => true]);

        return Inertia::render('BizBoost/Business/MiniWebsite', [
            'business' => $business->toArray(),
            'publicUrl' => route('bizboost.public.business', $business->slug),
            'products' => $products,
        ]);
    }

    public function updateMiniWebsite(Request $request)
    {
        $validated = $request->validate([
            'about' => 'nullable|string|max:2000',
            'tagline' => 'nullable|string|max:120',
            'contact_email' => 'nullable|email|max:255',
            'hero_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'about_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'business_story' => 'nullable|string|max:5000',
            'mission' => 'nullable|string|max:500',
            'vision' => 'nullable|string|max:500',
            'founding_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'business_hours' => 'nullable|array',
            'team_members' => 'nullable|array',
            'achievements' => 'nullable|array',
            'services' => 'nullable|array',
            'testimonials' => 'nullable|array',
            'show_products' => 'boolean',
            'show_services' => 'boolean',
            'show_gallery' => 'boolean',
            'show_testimonials' => 'boolean',
            'show_business_hours' => 'boolean',
            'show_contact_form' => 'boolean',
            'show_whatsapp_button' => 'boolean',
            'show_social_links' => 'boolean',
            'theme_settings' => 'nullable|array',
        ]);

        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $profile = $this->businessService->getProfile($business->id);

        $heroImagePath = $profile?->heroImagePath;
        if ($request->hasFile('hero_image')) {
            if ($heroImagePath) {
                Storage::disk('public')->delete($heroImagePath);
            }
            $heroImagePath = $request->file('hero_image')->store('bizboost/hero-images', 'public');
        }

        $aboutImagePath = $profile?->aboutImagePath;
        if ($request->hasFile('about_image')) {
            if ($aboutImagePath) {
                Storage::disk('public')->delete($aboutImagePath);
            }
            $aboutImagePath = $request->file('about_image')->store('bizboost/about-images', 'public');
        }

        $profileData = [
            'business_id' => $business->id,
            'about' => $validated['about'] ?? null,
            'tagline' => $validated['tagline'] ?? null,
            'contact_email' => $validated['contact_email'] ?? null,
            'hero_image_path' => $heroImagePath,
            'about_image_path' => $aboutImagePath,
            'business_story' => $validated['business_story'] ?? null,
            'mission' => $validated['mission'] ?? null,
            'vision' => $validated['vision'] ?? null,
            'founding_year' => $validated['founding_year'] ?? null,
            'business_hours' => $validated['business_hours'] ?? null,
            'team_members' => $validated['team_members'] ?? null,
            'achievements' => $validated['achievements'] ?? null,
            'services' => $validated['services'] ?? null,
            'testimonials' => $validated['testimonials'] ?? null,
            'show_products' => $validated['show_products'] ?? true,
            'show_services' => $validated['show_services'] ?? true,
            'show_gallery' => $validated['show_gallery'] ?? false,
            'show_testimonials' => $validated['show_testimonials'] ?? false,
            'show_business_hours' => $validated['show_business_hours'] ?? true,
            'show_contact_form' => $validated['show_contact_form'] ?? true,
            'show_whatsapp_button' => $validated['show_whatsapp_button'] ?? true,
            'show_social_links' => $validated['show_social_links'] ?? true,
            'theme_settings' => $validated['theme_settings'] ?? null,
        ];

        $this->businessService->saveProfile(BusinessProfile::reconstitute($profileData));

        return back()->with('success', 'Mini-website updated successfully.');
    }

    public function publishMiniWebsite(Request $request)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $profile = $this->businessService->getProfile($business->id);

        if (!$profile) {
            return back()->with('error', 'Please configure your mini-website first.');
        }

        $profileData = $profile->toArray();
        $profileData['is_published'] = true;
        $this->businessService->saveProfile(BusinessProfile::reconstitute($profileData));

        return back()->with('success', 'Your mini-website is now live!');
    }

    public function unpublishMiniWebsite(Request $request)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $profile = $this->businessService->getProfile($business->id);

        if ($profile) {
            $profileData = $profile->toArray();
            $profileData['is_published'] = false;
            $this->businessService->saveProfile(BusinessProfile::reconstitute($profileData));
        }

        return back()->with('success', 'Mini-website unpublished.');
    }

    public function publicPage(string $slug)
    {
        $business = $this->businessService->findBusinessBySlug($slug);
        if (!$business || !$business->isActive) {
            abort(404);
        }

        $profile = $this->businessService->getProfile($business->id);
        $isPublished = $profile?->isPublished ?? false;

        if (!$isPublished && !$business->marketplaceListed) {
            abort(404, 'This business page is not available');
        }

        $this->analyticsEventRepo->save(new \App\Domain\BizBoost\Entities\AnalyticsEvent(
            id: null,
            businessId: $business->id,
            eventType: 'page_view',
            source: 'mini_website',
            postId: null,
            payload: null,
            ipAddress: request()->ip(),
            userAgent: request()->userAgent(),
            referrer: request()->header('referer'),
            recordedAt: now()->toDateTimeString(),
            createdAt: null,
            updatedAt: null,
        ));

        return Inertia::render('BizBoost/Public/BusinessPage', [
            'business' => $business->toArray(),
        ]);
    }

    public function publicProducts(Request $request, string $slug)
    {
        $business = $this->businessService->findBusinessBySlug($slug);
        if (!$business || !$business->isActive) {
            abort(404);
        }

        $profile = $this->businessService->getProfile($business->id);
        $isPublished = $profile?->isPublished ?? false;

        if (!$isPublished && !$business->marketplaceListed) {
            abort(404, 'This business page is not available');
        }

        $filters = [
            'search' => $request->input('search', ''),
            'category' => $request->input('category', ''),
        ];

        $products = $this->productRepo->findActiveByBusiness($business->id, $filters);

        return Inertia::render('BizBoost/Public/Products', [
            'business' => $business->toArray(),
            'products' => $products,
            'filters' => $filters,
        ]);
    }

    public function publicProduct(string $slug, int $productId)
    {
        $business = $this->businessService->findBusinessBySlug($slug);
        if (!$business || !$business->isActive) {
            abort(404);
        }

        $profile = $this->businessService->getProfile($business->id);
        $isPublished = $profile?->isPublished ?? false;

        if (!$isPublished && !$business->marketplaceListed) {
            abort(404, 'This business page is not available');
        }

        $product = $this->productRepo->findById($productId);
        if (!$product) {
            abort(404);
        }

        return Inertia::render('BizBoost/Public/Product', [
            'business' => $business->toArray(),
            'product' => $product->toArray(),
        ]);
    }

    public function publicContact(Request $request, string $slug)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'message' => 'required|string|max:2000',
        ]);

        return back()->with('success', 'Thank you for your message! We will get back to you soon.');
    }
}