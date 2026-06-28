<?php

namespace App\Http\Controllers\BizBoost;

use App\Domain\Module\Services\SubscriptionService;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WhiteLabelController extends Controller
{
    private const MODULE_ID = 'bizboost';

    public function __construct(
        private SubscriptionService $subscriptionService
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

        $business = $this->getBusiness($request);
        $config = json_decode($business->white_label_config ?? '{}', true);

        return Inertia::render('BizBoost/WhiteLabel/Index', [
            'settings' => [
                'custom_domain' => $config['custom_domain'] ?? null,
                'logo_url' => !empty($config['custom_logo']) ? asset('storage/' . $config['custom_logo']) : null,
                'favicon_url' => $config['favicon_url'] ?? null,
                'primary_color' => $config['primary_color'] ?? '#7c3aed',
                'secondary_color' => $config['secondary_color'] ?? '#4f46e5',
                'hide_branding' => $config['hide_powered_by'] ?? false,
                'custom_css' => $config['custom_css'] ?? null,
            ],
            'hasWhiteLabelAccess' => true,
            'previewUrl' => route('bizboost.public.business', $business->slug),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        if (!$this->subscriptionService->hasFeature($user, 'white_label', self::MODULE_ID)) {
            return back()->withErrors(['access' => 'White-label requires Business tier.']);
        }

        $validated = $request->validate([
            'primary_color' => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
            'accent_color' => 'nullable|string|max:7',
            'font_family' => 'nullable|string|max:100',
            'logo_position' => 'nullable|in:left,center,right',
            'hide_powered_by' => 'boolean',
            'custom_css' => 'nullable|string|max:5000',
            'custom_domain' => 'nullable|string|max:255',
            'favicon_url' => 'nullable|url|max:500',
            'og_image_url' => 'nullable|url|max:500',
        ]);

        $business = $this->getBusiness($request);
        $business->white_label_config = json_encode($validated);
        $business->save();

        return back()->with('success', 'White-label settings updated.');
    }

    public function uploadLogo(Request $request)
    {
        $user = $request->user();
        if (!$this->subscriptionService->hasFeature($user, 'white_label', self::MODULE_ID)) {
            return back()->withErrors(['access' => 'White-label requires Business tier.']);
        }

        $request->validate([
            'logo' => 'required|image|max:2048',
        ]);

        $business = $this->getBusiness($request);
        $path = $request->file('logo')->store('bizboost/logos', 'public');

        $config = json_decode($business->white_label_config ?? '{}', true);
        $config['custom_logo'] = $path;
        $business->white_label_config = json_encode($config);
        $business->save();

        return back()->with('success', 'Logo uploaded.');
    }

    private function getBusiness(Request $request): BizBoostBusinessModel
    {
        return BizBoostBusinessModel::where('user_id', $request->user()->id)->firstOrFail();
    }
}
