<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Domain\BizBoost\Services\BusinessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class MarketplaceController extends Controller
{
    public function __construct(
        private BusinessService $businessService,
    ) {}

    public function publicIndex(Request $request)
    {
        $query = DB::table('bizboost_businesses as b')
            ->where('b.marketplace_listed', true)
            ->leftJoin('bizboost_business_profiles as p', 'b.id', '=', 'p.business_id')
            ->select(
                'b.id', 'b.name', 'b.industry as category', 'b.description', 'b.logo_path', 'b.slug', 'b.city', 'p.tagline'
            )->selectRaw('(SELECT COUNT(*) FROM bizboost_products WHERE business_id = b.id AND is_active = 1) as products_count');

        if ($request->filled('category')) {
            $query->where('b.industry', $request->category);
        }
        if ($request->filled('city')) {
            $query->where('b.city', $request->city);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('b.name', 'like', "%{$search}%")
                  ->orWhere('b.description', 'like', "%{$search}%")
                  ->orWhere('p.tagline', 'like', "%{$search}%");
            });
        }

        $businesses = $query->orderByDesc('b.marketplace_listed_at')
            ->paginate(12)
            ->through(function ($b) {
                $b->logo_url = $b->logo_path ? asset('storage/' . $b->logo_path) : null;
                return $b;
            });

        $featuredBusinesses = DB::table('bizboost_businesses as b')
            ->where('b.marketplace_listed', true)
            ->leftJoin('bizboost_business_profiles as p', 'b.id', '=', 'p.business_id')
            ->select('b.id', 'b.name', 'b.industry as category', 'b.description', 'b.logo_path', 'b.slug', 'b.city', 'p.tagline')
            ->selectRaw('(SELECT COUNT(*) FROM bizboost_products WHERE business_id = b.id AND is_active = 1) as products_count')
            ->orderByRaw('products_count DESC')
            ->limit(6)
            ->get()
            ->map(function ($b) {
                $b->logo_url = $b->logo_path ? asset('storage/' . $b->logo_path) : null;
                return $b;
            });

        $cities = DB::table('bizboost_businesses')
            ->whereNotNull('city')->where('marketplace_listed', true)
            ->distinct()->pluck('city')->filter()->values();

        $categoryCounts = DB::table('bizboost_businesses')
            ->where('marketplace_listed', true)
            ->select('industry as category')->selectRaw('COUNT(*) as count')
            ->groupBy('industry')->pluck('count', 'category');

        return Inertia::render('BizBoost/Marketplace/Public', [
            'businesses' => $businesses,
            'featuredBusinesses' => $featuredBusinesses,
            'categories' => $this->getCategories(),
            'categoryCounts' => $categoryCounts,
            'cities' => $cities,
            'filters' => [
                'search' => $request->search ?? '',
                'category' => $request->category ?? '',
                'city' => $request->city ?? '',
            ],
            'totalBusinesses' => DB::table('bizboost_businesses')->where('marketplace_listed', true)->count(),
        ]);
    }

    public function publicCategory(Request $request, string $category)
    {
        $request->merge(['category' => $category]);
        return $this->publicIndex($request);
    }

    public function publicSearch(Request $request)
    {
        return $this->publicIndex($request);
    }

    public function index(Request $request)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $marketplaceSettings = json_decode($business->settings ?? '{}', true)['marketplace'] ?? [];

        $products = DB::table('bizboost_products')
            ->where('business_id', $business->id)->where('is_active', true)
            ->select('id', 'name')->limit(20)->get();

        return Inertia::render('BizBoost/Marketplace/Index', [
            'settings' => [
                'is_listed' => $business->marketplaceListed ?? false,
                'listing_title' => $marketplaceSettings['listing_title'] ?? $business->name,
                'listing_description' => $marketplaceSettings['marketplace_description'] ?? $business->description,
                'listing_category' => $business->industry,
                'featured_products' => $marketplaceSettings['featured_products'] ?? [],
                'show_contact' => $marketplaceSettings['show_contact'] ?? true,
                'show_location' => $marketplaceSettings['show_location'] ?? true,
            ],
            'business' => [
                'name' => $business->name, 'slug' => $business->slug,
                'logo_url' => $business->logoPath ? asset('storage/' . $business->logoPath) : null,
            ],
            'categories' => $this->getCategories(),
            'products' => $products,
            'hasMarketplaceAccess' => true,
        ]);
    }

    public function toggleListing(Request $request)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);

        if ($business->marketplaceListed) {
            $this->businessService->updateBusiness($business->id, ['marketplace_listed' => false, 'marketplace_listed_at' => null]);
            $message = 'Business removed from marketplace.';
        } else {
            if (!$business->name || !$business->industry) {
                return back()->withErrors(['listing' => 'Please complete your business profile first.']);
            }
            $this->businessService->updateBusiness($business->id, ['marketplace_listed' => true, 'marketplace_listed_at' => now()->toDateTimeString()]);
            $message = 'Business listed on marketplace!';
        }

        return back()->with('success', $message);
    }

    public function updateListing(Request $request)
    {
        $validated = $request->validate([
            'marketplace_description' => 'nullable|string|max:1000',
            'marketplace_tags' => 'nullable|array|max:10',
            'marketplace_tags.*' => 'string|max:50',
            'show_products' => 'boolean',
            'show_contact' => 'boolean',
        ]);

        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $settings = json_decode($business->settings ?? '{}', true);
        $settings['marketplace'] = $validated;
        $this->businessService->updateBusiness($business->id, ['settings' => json_encode($settings)]);

        return back()->with('success', 'Marketplace listing updated.');
    }

    public function browse(Request $request)
    {
        $businesses = DB::table('bizboost_businesses as b')
            ->where('b.marketplace_listed', true)
            ->leftJoin('bizboost_business_profiles as p', 'b.id', '=', 'p.business_id')
            ->select('b.id', 'b.name', 'b.industry as category', 'b.description', 'b.logo_path as logo_url', 'b.slug', 'b.city')
            ->selectRaw('(SELECT COUNT(*) FROM bizboost_products WHERE business_id = b.id AND is_active = 1) as products_count')
            ->orderByDesc('b.marketplace_listed_at')
            ->limit(50)
            ->get()
            ->map(function ($b) {
                $b->logo_url = $b->logo_url ? asset('storage/' . $b->logo_url) : null;
                return $b;
            });

        $cities = DB::table('bizboost_businesses')
            ->whereNotNull('city')->where('marketplace_listed', true)
            ->distinct()->pluck('city')->filter()->values();

        return Inertia::render('BizBoost/Marketplace/Browse', [
            'businesses' => $businesses,
            'categories' => $this->getCategories(),
            'cities' => $cities,
            'filters' => ['search' => $request->search ?? '', 'category' => $request->category ?? '', 'city' => $request->city ?? ''],
        ]);
    }

    private function getCategories(): array
    {
        return ['Retail', 'Food & Beverage', 'Services', 'Health & Beauty', 'Fashion', 'Technology', 'Agriculture', 'Manufacturing', 'Education', 'Other'];
    }
}