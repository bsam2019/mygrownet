<?php

namespace App\Http\Controllers\BizBoost;

use App\Domain\Module\Services\SubscriptionService;
use App\Http\Controllers\Controller;
use App\Domain\BizBoost\Services\BusinessService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WhiteLabelController extends Controller
{
    private const MODULE_ID = 'bizboost';

    public function __construct(
        private SubscriptionService $subscriptionService,
        private BusinessService $businessService,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $hasAccess = $this->subscriptionService->hasFeature($user, 'white_label', self::MODULE_ID);

        if (!$hasAccess) {
            return Inertia::render('BizBoost/FeatureUpgradeRequired', [
                'feature' => 'White Label',
                'description' => 'Customize your business page with your own branding, colors, and domain.',
                'requiredTier' => 'business',
            ]);
        }

        $business = $this->businessService->getBusinessOrFail($user->id);

        return Inertia::render('BizBoost/WhiteLabel/Index', [
            'business' => $business->toArray(),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $hasAccess = $this->subscriptionService->hasFeature($user, 'white_label', self::MODULE_ID);

        if (!$hasAccess) {
            return back()->with('error', 'White label feature requires a Business+ plan.');
        }

        $validated = $request->validate([
            'primary_color' => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
            'accent_color' => 'nullable|string|max:7',
            'font_family' => 'nullable|string|max:100',
            'custom_css' => 'nullable|string|max:10000',
            'custom_js' => 'nullable|string|max:10000',
            'show_powered_by' => 'boolean',
            'custom_domain' => 'nullable|string|max:255',
            'custom_logo' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'favicon' => 'nullable|image|mimes:ico,png,svg|max:1024',
        ]);

        $business = $this->businessService->getBusinessOrFail($user->id);
        $this->businessService->updateBusiness($business->id, $validated);

        return back()->with('success', 'White label settings updated.');
    }
}