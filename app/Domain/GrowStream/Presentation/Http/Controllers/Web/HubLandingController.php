<?php

namespace App\Domain\GrowStream\Presentation\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class HubLandingController extends Controller
{
    public function show(Request $request): Response
    {
        $setting = DB::table('platform_settings')->where('key', 'growstream.hub_pricing_tiers')->first();

        $pricingTiers = ($setting && !empty($setting->value))
            ? json_decode($setting->value, true)
            : $this->getDefaultPricingTiers();

        return Inertia::render('GrowStream/LandingHub', [
            'appName' => 'GrowStream Hub',
            'pricingTiers' => $pricingTiers,
        ]);
    }

    public function adminPricingShow(Request $request): Response
    {
        $setting = DB::table('platform_settings')->where('key', 'growstream.hub_pricing_tiers')->first();

        $pricingTiers = ($setting && !empty($setting->value))
            ? json_decode($setting->value, true)
            : $this->getDefaultPricingTiers();

        return Inertia::render('GrowStream/Admin/HubPricing', [
            'pricingTiers' => $pricingTiers,
        ]);
    }

    public function adminPricingUpdate(Request $request)
    {
        $request->validate([
            'pricingTiers' => 'required|array',
            'pricingTiers.*.name' => 'required|string|max:100',
            'pricingTiers.*.price' => 'required|string|max:50',
            'pricingTiers.*.period' => 'required|string|max:50',
            'pricingTiers.*.description' => 'required|string|max:255',
            'pricingTiers.*.storage' => 'required|string|max:100',
            'pricingTiers.*.bandwidth' => 'required|string|max:100',
        ]);

        DB::table('platform_settings')->updateOrInsert(
            ['key' => 'growstream.hub_pricing_tiers'],
            [
                'value' => json_encode($request->pricingTiers),
                'type' => 'json',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return redirect()->back()->with('success', 'Creator Hub pricing updated successfully.');
    }

    public function adminHubsIndex(Request $request): Response
    {
        $hubs = \App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorPlatform::all()
            ->map(function ($hub) {
                $quota = \App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\PlatformQuota::where('organization_id', $hub->organization_id)->first();
                return [
                    'id' => $hub->id,
                    'organization_id' => $hub->organization_id,
                    'brand_name' => $hub->brand_name,
                    'subdomain' => $hub->subdomain,
                    'custom_domain' => $hub->custom_domain,
                    'category' => $hub->category,
                    'subscription_plan' => $hub->subscription_plan ?? 'starter',
                    'subscription_status' => $hub->subscription_status ?? 'active',
                    'is_active' => (bool) $hub->is_active,
                    'quota' => $quota ? [
                        'current_storage_minutes' => $quota->current_storage_minutes,
                        'storage_minutes_limit' => $quota->storage_minutes_limit,
                        'current_delivery_gb' => $quota->current_delivery_gb,
                        'delivery_gb_limit' => $quota->delivery_gb_limit,
                    ] : null,
                ];
            });

        return Inertia::render('GrowStream/Admin/Hubs', [
            'hubs' => $hubs,
        ]);
    }

    public function adminHubToggleStatus(Request $request, int $id)
    {
        $hub = \App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorPlatform::findOrFail($id);
        $hub->is_active = !$hub->is_active;
        if ($hub->is_active) {
            $hub->subscription_status = 'active';
        }
        $hub->save();

        return redirect()->back()->with('success', 'Platform status updated successfully.');
    }

    public function subscribeShow(Request $request): Response|\Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $orgId = $user->organization_id ?? $user->id;
        $existingPlatform = \App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorPlatform::where('organization_id', $orgId)->first();

        // If user already has an active platform, redirect to platform settings
        if ($existingPlatform && $existingPlatform->is_active && $existingPlatform->subscription_status === 'active') {
            return redirect()->route('growstream.creator.platform.show')
                ->with('info', 'You already have an active Creator Hub platform.');
        }

        $selectedPlanSlug = strtolower((string) $request->query('plan', 'starter'));
        $setting = DB::table('platform_settings')->where('key', 'growstream.hub_pricing_tiers')->first();
        $pricingTiers = ($setting && !empty($setting->value))
            ? json_decode($setting->value, true)
            : $this->getDefaultPricingTiers();

        $selectedPlan = collect($pricingTiers)->first(function ($t) use ($selectedPlanSlug) {
            return str_contains(strtolower($t['name']), $selectedPlanSlug);
        }) ?? $pricingTiers[0];

        $isAdmin = false;
        if (method_exists($user, 'hasRole')) {
            $isAdmin = $user->hasRole('super_admin') || $user->hasRole('admin');
        }
        $isAdmin = $isAdmin || ($user->is_admin ?? false);

        return Inertia::render('GrowStream/HubSubscribe', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => $isAdmin,
            ],
            'selectedPlan' => $selectedPlan,
            'pricingTiers' => $pricingTiers,
        ]);
    }

    public function subscribeProcess(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $request->validate([
            'brand_name' => 'required|string|max:255',
            'subdomain' => 'required|string|max:100|alpha_dash',
            'plan' => 'required|string',
            'payment_method' => 'required|string|in:momo,card,admin_bypass',
        ]);

        $orgId = $user->organization_id ?? $user->id;

        // Check subdomain uniqueness across other organizations
        $subdomainConflict = \App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorPlatform::where('subdomain', $request->subdomain)
            ->where('organization_id', '!=', $orgId)
            ->exists();

        if ($subdomainConflict) {
            return back()->withErrors(['subdomain' => 'This subdomain is already taken. Please choose another one.']);
        }

        // Provision platform and set quota limits according to selected plan
        $platform = \App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorPlatform::updateOrCreate(
            ['organization_id' => $orgId],
            [
                'brand_name' => $request->brand_name,
                'subdomain' => strtolower($request->subdomain),
                'brand_color' => '#e2571f',
                'subscription_plan' => strtolower($request->plan),
                'subscription_status' => 'active',
                'subscribed_at' => now(),
                'is_active' => true,
            ]
        );

        // Map plan quota limits
        $storageLimits = ['starter' => 500, 'professional' => 1500, 'business' => 5000];
        $deliveryLimits = ['starter' => 100, 'professional' => 300, 'business' => 1000];

        $planKey = strtolower($request->plan);
        $storageMins = $storageLimits[$planKey] ?? 500;
        $deliveryGb = $deliveryLimits[$planKey] ?? 100;

        \App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\PlatformQuota::updateOrCreate(
            ['organization_id' => $orgId],
            [
                'storage_minutes_limit' => $storageMins,
                'delivery_gb_limit' => $deliveryGb,
            ]
        );

        return redirect()->route('growstream.creator.platform.show')
            ->with('success', 'Your Creator Hub platform [' . $platform->brand_name . '] has been successfully launched!');
    }

    public function getDefaultPricingTiers(): array
    {
        return [
            [
                'name' => 'Starter Hub',
                'price' => 'K195',
                'period' => '/ month',
                'description' => 'Essential features for individual tutors & private educators starting out.',
                'storage' => '500 Mins Video Storage',
                'bandwidth' => '3,000 Streaming Watch Mins / mo',
                'domain' => 'Hosted Subdomain (acme.growstream.app)',
                'byop' => false,
                'cta' => 'Subscribe to Starter Hub',
            ],
            [
                'name' => 'Professional Hub',
                'price' => 'K695',
                'period' => '/ month',
                'description' => 'Ideal for established online tuition academies & schools.',
                'storage' => '1,500 Mins Video Storage',
                'bandwidth' => '15,000 Streaming Watch Mins / mo',
                'domain' => 'Custom Domain (www.mymathstuition.com)',
                'byop' => true,
                'is_popular' => true,
                'cta' => 'Subscribe to Professional Hub',
            ],
            [
                'name' => 'Business Hub',
                'price' => 'K2,450',
                'period' => '/ month',
                'description' => 'For universities, colleges, and large training institutes.',
                'storage' => '5,000 Mins Video Storage',
                'bandwidth' => '50,000 Streaming Watch Mins / mo',
                'domain' => 'Multi-domain & Dedicated SSL',
                'byop' => true,
                'cta' => 'Subscribe to Business Hub',
            ],
        ];
    }
}
