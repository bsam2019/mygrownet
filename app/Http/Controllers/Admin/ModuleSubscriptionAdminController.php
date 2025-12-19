<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domain\Module\Services\TierConfigurationService;
use App\Models\ModuleTier;
use App\Models\ModuleTierFeature;
use App\Models\ModuleDiscount;
use App\Models\ModuleSpecialOffer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ModuleSubscriptionAdminController extends Controller
{
    public function __construct(
        private TierConfigurationService $tierConfigService
    ) {}
    /**
     * List all modules with subscription stats
     */
    public function index()
    {
        $modules = $this->getModulesWithStats();

        return Inertia::render('Admin/ModuleSubscriptions/Index', [
            'modules' => $modules,
            'stats' => $this->getOverallStats(),
        ]);
    }

    /**
     * Show module details with tiers
     */
    public function show(string $moduleId)
    {
        $moduleConfig = config("modules.{$moduleId}");
        
        if (!$moduleConfig) {
            abort(404, 'Module not found');
        }

        $tiers = ModuleTier::forModule($moduleId)
            ->with('activeFeatures')
            ->ordered()
            ->get();

        // If no DB tiers, load from config
        if ($tiers->isEmpty()) {
            $tiers = $this->getTiersFromConfig($moduleId);
        }

        $discounts = ModuleDiscount::forModule($moduleId)
            ->orderBy('created_at', 'desc')
            ->get();

        $subscribers = $this->getModuleSubscribers($moduleId);

        return Inertia::render('Admin/ModuleSubscriptions/Show', [
            'moduleId' => $moduleId,
            'module' => $moduleConfig,
            'tiers' => $tiers,
            'discounts' => $discounts,
            'subscribers' => $subscribers,
            'stats' => $this->getModuleStats($moduleId),
        ]);
    }

    /**
     * Store a new tier
     */
    public function storeTier(Request $request, string $moduleId)
    {
        $validated = $request->validate([
            'tier_key' => 'required|string|max:50',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'price_monthly' => 'required|numeric|min:0',
            'price_annual' => 'required|numeric|min:0',
            'user_limit' => 'nullable|integer|min:1',
            'storage_limit_mb' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $validated['module_id'] = $moduleId;

        // If setting as default, unset other defaults
        if ($validated['is_default'] ?? false) {
            ModuleTier::forModule($moduleId)->update(['is_default' => false]);
        }

        $tier = ModuleTier::create($validated);

        // Clear tier cache
        $this->tierConfigService->clearCache($moduleId);

        return back()->with('success', "Tier '{$tier->name}' created successfully.");
    }

    /**
     * Update a tier
     */
    public function updateTier(Request $request, string $moduleId, ModuleTier $tier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'price_monthly' => 'required|numeric|min:0',
            'price_annual' => 'required|numeric|min:0',
            'user_limit' => 'nullable|integer|min:1',
            'storage_limit_mb' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'sort_order' => 'integer',
        ]);

        // If setting as default, unset other defaults
        if ($validated['is_default'] ?? false) {
            ModuleTier::forModule($moduleId)
                ->where('id', '!=', $tier->id)
                ->update(['is_default' => false]);
        }

        $tier->update($validated);

        return back()->with('success', "Tier '{$tier->name}' updated successfully.");
    }

    /**
     * Delete a tier
     */
    public function destroyTier(string $moduleId, ModuleTier $tier)
    {
        // Check for active subscribers
        $activeSubscribers = DB::table('module_subscriptions')
            ->where('module_id', $moduleId)
            ->where('subscription_tier', $tier->tier_key)
            ->where('status', 'active')
            ->count();

        if ($activeSubscribers > 0) {
            return back()->with('error', "Cannot delete tier with {$activeSubscribers} active subscribers.");
        }

        $tier->delete();

        return back()->with('success', 'Tier deleted successfully.');
    }

    /**
     * Update tier features
     */
    public function updateFeatures(Request $request, string $moduleId, ModuleTier $tier)
    {
        $validated = $request->validate([
            'features' => 'required|array',
            'features.*.feature_key' => 'required|string|max:100',
            'features.*.feature_name' => 'required|string|max:100',
            'features.*.feature_type' => 'required|in:boolean,limit,text',
            'features.*.value_boolean' => 'boolean',
            'features.*.value_limit' => 'nullable|integer',
            'features.*.value_text' => 'nullable|string|max:255',
            'features.*.is_active' => 'boolean',
        ]);

        DB::transaction(function () use ($tier, $validated) {
            // Delete existing features
            $tier->features()->delete();

            // Create new features
            foreach ($validated['features'] as $feature) {
                $tier->features()->create($feature);
            }
        });

        return back()->with('success', 'Features updated successfully.');
    }

    /**
     * Store a new discount
     */
    public function storeDiscount(Request $request)
    {
        $validated = $request->validate([
            'module_id' => 'nullable|string|max:50',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'applies_to' => 'required|in:all_tiers,specific_tiers,annual_only,monthly_only',
            'tier_keys' => 'nullable|array',
            'code' => 'nullable|string|max:50|unique:module_discounts,code',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after:starts_at',
            'max_uses' => 'nullable|integer|min:1',
            'min_purchase_amount' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['created_by'] = auth()->id();

        $discount = ModuleDiscount::create($validated);

        return back()->with('success', "Discount '{$discount->name}' created successfully.");
    }

    /**
     * Update a discount
     */
    public function updateDiscount(Request $request, ModuleDiscount $discount)
    {
        $validated = $request->validate([
            'module_id' => 'nullable|string|max:50',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'applies_to' => 'required|in:all_tiers,specific_tiers,annual_only,monthly_only',
            'tier_keys' => 'nullable|array',
            'code' => 'nullable|string|max:50|unique:module_discounts,code,' . $discount->id,
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after:starts_at',
            'max_uses' => 'nullable|integer|min:1',
            'min_purchase_amount' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $discount->update($validated);

        return back()->with('success', "Discount '{$discount->name}' updated successfully.");
    }

    /**
     * Delete a discount
     */
    public function destroyDiscount(ModuleDiscount $discount)
    {
        $discount->delete();
        return back()->with('success', 'Discount deleted successfully.');
    }

    /**
     * Store a special offer
     */
    public function storeOffer(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'offer_type' => 'required|in:bundle,upgrade,trial_extension,bonus_feature',
            'module_ids' => 'required|array|min:1',
            'tier_key' => 'nullable|string|max:50',
            'original_price' => 'required|numeric|min:0',
            'offer_price' => 'required|numeric|min:0|lt:original_price',
            'savings_display' => 'nullable|string|max:50',
            'billing_cycle' => 'required|in:monthly,annual,one_time',
            'bonus_features' => 'nullable|array',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'max_purchases' => 'nullable|integer|min:1',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['created_by'] = auth()->id();

        $offer = ModuleSpecialOffer::create($validated);

        return back()->with('success', "Offer '{$offer->name}' created successfully.");
    }

    /**
     * Update a special offer
     */
    public function updateOffer(Request $request, ModuleSpecialOffer $offer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'offer_type' => 'required|in:bundle,upgrade,trial_extension,bonus_feature',
            'module_ids' => 'required|array|min:1',
            'tier_key' => 'nullable|string|max:50',
            'original_price' => 'required|numeric|min:0',
            'offer_price' => 'required|numeric|min:0|lt:original_price',
            'savings_display' => 'nullable|string|max:50',
            'billing_cycle' => 'required|in:monthly,annual,one_time',
            'bonus_features' => 'nullable|array',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'max_purchases' => 'nullable|integer|min:1',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $offer->update($validated);

        return back()->with('success', "Offer '{$offer->name}' updated successfully.");
    }

    /**
     * Delete a special offer
     */
    public function destroyOffer(ModuleSpecialOffer $offer)
    {
        $offer->delete();
        return back()->with('success', 'Offer deleted successfully.');
    }

    /**
     * List all discounts
     */
    public function discounts()
    {
        $discounts = ModuleDiscount::with('creator')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $modules = $this->getModulesList();

        return Inertia::render('Admin/ModuleSubscriptions/Discounts', [
            'discounts' => $discounts,
            'modules' => $modules,
        ]);
    }

    /**
     * List all special offers
     */
    public function offers()
    {
        $offers = ModuleSpecialOffer::with('creator')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $modules = $this->getModulesList();

        return Inertia::render('Admin/ModuleSubscriptions/Offers', [
            'offers' => $offers,
            'modules' => $modules,
        ]);
    }

    /**
     * Seed tiers from config to database
     */
    public function seedFromConfig(string $moduleId)
    {
        $configTiers = config("modules.{$moduleId}.subscription_tiers", []);

        if (empty($configTiers)) {
            return back()->with('error', 'No tiers found in config for this module.');
        }

        DB::transaction(function () use ($moduleId, $configTiers) {
            $sortOrder = 0;
            foreach ($configTiers as $tierKey => $tierConfig) {
                $tier = ModuleTier::updateOrCreate(
                    ['module_id' => $moduleId, 'tier_key' => $tierKey],
                    [
                        'name' => $tierConfig['name'] ?? ucfirst($tierKey),
                        'description' => $tierConfig['description'] ?? null,
                        'price_monthly' => $tierConfig['price'] ?? $tierConfig['price_monthly'] ?? 0,
                        'price_annual' => $tierConfig['price_annual'] ?? (($tierConfig['price'] ?? 0) * 10),
                        'user_limit' => $tierConfig['user_limit'] ?? null,
                        'storage_limit_mb' => $tierConfig['storage_limit_mb'] ?? null,
                        'is_active' => true,
                        'is_default' => $tierKey === 'free',
                        'sort_order' => $sortOrder++,
                    ]
                );

                // Seed features
                if (isset($tierConfig['features'])) {
                    foreach ($tierConfig['features'] as $featureKey => $featureValue) {
                        $featureType = is_bool($featureValue) ? 'boolean' : (is_int($featureValue) ? 'limit' : 'text');
                        
                        ModuleTierFeature::updateOrCreate(
                            ['module_tier_id' => $tier->id, 'feature_key' => $featureKey],
                            [
                                'feature_name' => ucwords(str_replace('_', ' ', $featureKey)),
                                'feature_type' => $featureType,
                                'value_boolean' => $featureType === 'boolean' ? $featureValue : false,
                                'value_limit' => $featureType === 'limit' ? $featureValue : null,
                                'value_text' => $featureType === 'text' ? $featureValue : null,
                                'is_active' => true,
                            ]
                        );
                    }
                }
            }
        });

        return back()->with('success', 'Tiers seeded from config successfully.');
    }

    // Helper methods

    private function getModulesWithStats(): array
    {
        $modules = [];
        $configModules = config('modules', []);

        foreach ($configModules as $moduleId => $moduleConfig) {
            if ($moduleId === 'settings' || $moduleId === 'categories') {
                continue;
            }

            $subscribers = DB::table('module_subscriptions')
                ->where('module_id', $moduleId)
                ->where('status', 'active')
                ->count();

            $revenue = DB::table('transactions')
                ->where('description', 'like', "%{$moduleId}%")
                ->where('transaction_type', 'subscription_payment')
                ->where('status', 'completed')
                ->sum('amount');

            $dbTiers = ModuleTier::forModule($moduleId)->active()->count();

            $modules[] = [
                'id' => $moduleId,
                'name' => $moduleConfig['name'] ?? $moduleId,
                'category' => $moduleConfig['category'] ?? 'other',
                'icon' => $moduleConfig['icon'] ?? 'cube',
                'color' => $moduleConfig['color'] ?? 'gray',
                'subscribers' => $subscribers,
                'revenue' => abs($revenue),
                'tiers_in_db' => $dbTiers,
                'requires_subscription' => $moduleConfig['requires_subscription'] ?? true,
            ];
        }

        return $modules;
    }

    private function getModulesList(): array
    {
        $modules = [];
        $configModules = config('modules', []);

        foreach ($configModules as $moduleId => $moduleConfig) {
            if ($moduleId === 'settings' || $moduleId === 'categories') {
                continue;
            }
            $modules[] = [
                'id' => $moduleId,
                'name' => $moduleConfig['name'] ?? $moduleId,
            ];
        }

        return $modules;
    }

    private function getOverallStats(): array
    {
        $totalSubscribers = DB::table('module_subscriptions')
            ->where('status', 'active')
            ->count();

        $totalRevenue = DB::table('transactions')
            ->where('transaction_type', 'subscription_payment')
            ->where('status', 'completed')
            ->sum('amount');

        $activeDiscounts = ModuleDiscount::valid()->count();
        $activeOffers = ModuleSpecialOffer::valid()->count();

        return [
            'total_subscribers' => $totalSubscribers,
            'total_revenue' => abs($totalRevenue),
            'active_discounts' => $activeDiscounts,
            'active_offers' => $activeOffers,
        ];
    }

    private function getModuleStats(string $moduleId): array
    {
        $subscribers = DB::table('module_subscriptions')
            ->where('module_id', $moduleId)
            ->selectRaw('subscription_tier, count(*) as count')
            ->where('status', 'active')
            ->groupBy('subscription_tier')
            ->pluck('count', 'subscription_tier')
            ->toArray();

        $monthlyRevenue = DB::table('transactions')
            ->where('description', 'like', "%{$moduleId}%")
            ->where('transaction_type', 'subscription_payment')
            ->where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->sum('amount');

        return [
            'subscribers_by_tier' => $subscribers,
            'total_subscribers' => array_sum($subscribers),
            'monthly_revenue' => abs($monthlyRevenue),
        ];
    }

    private function getModuleSubscribers(string $moduleId): array
    {
        return DB::table('module_subscriptions')
            ->join('users', 'module_subscriptions.user_id', '=', 'users.id')
            ->where('module_subscriptions.module_id', $moduleId)
            ->select([
                'users.id',
                'users.name',
                'users.email',
                'module_subscriptions.subscription_tier',
                'module_subscriptions.status',
                'module_subscriptions.expires_at',
                'module_subscriptions.created_at',
            ])
            ->orderBy('module_subscriptions.created_at', 'desc')
            ->limit(50)
            ->get()
            ->toArray();
    }

    private function getTiersFromConfig(string $moduleId): array
    {
        $configTiers = config("modules.{$moduleId}.subscription_tiers", []);
        $tiers = [];

        foreach ($configTiers as $tierKey => $tierConfig) {
            $tiers[] = [
                'id' => null,
                'module_id' => $moduleId,
                'tier_key' => $tierKey,
                'name' => $tierConfig['name'] ?? ucfirst($tierKey),
                'description' => $tierConfig['description'] ?? null,
                'price_monthly' => $tierConfig['price'] ?? 0,
                'price_annual' => $tierConfig['price_annual'] ?? (($tierConfig['price'] ?? 0) * 10),
                'user_limit' => $tierConfig['user_limit'] ?? null,
                'storage_limit_mb' => $tierConfig['storage_limit_mb'] ?? null,
                'is_active' => true,
                'is_default' => $tierKey === 'free',
                'sort_order' => 0,
                'features' => $tierConfig['features'] ?? [],
                'from_config' => true,
            ];
        }

        return $tiers;
    }
}
