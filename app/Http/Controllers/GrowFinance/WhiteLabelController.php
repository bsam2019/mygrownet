<?php

namespace App\Http\Controllers\GrowFinance;

use App\Domain\GrowFinance\Services\WhiteLabelService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WhiteLabelController extends Controller
{
    public function __construct(
        private WhiteLabelService $whiteLabelService
    ) {}

    /**
     * Display white-label settings
     */
    public function index(Request $request): Response
    {
        $businessId = $request->user()->id;

        $canUse = $this->whiteLabelService->canUseWhiteLabel($request->user());

        if (!$canUse['allowed']) {
            return Inertia::render('GrowFinance/FeatureUpgradeRequired', [
                'feature' => 'White-Label Branding',
                'requiredTier' => 'business',
                'message' => $canUse['reason'],
            ]);
        }

        $settings = $this->whiteLabelService->getSettings($businessId);

        return Inertia::render('GrowFinance/WhiteLabel/Index', [
            'settings' => $settings,
        ]);
    }

    /**
     * Update white-label settings
     */
    public function update(Request $request): RedirectResponse
    {
        $businessId = $request->user()->id;

        $canUse = $this->whiteLabelService->canUseWhiteLabel($request->user());
        if (!$canUse['allowed']) {
            return back()->with('error', $canUse['reason']);
        }

        $validated = $request->validate([
            'company_name' => 'nullable|string|max:100',
            'primary_color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'secondary_color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'accent_color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'custom_domain' => 'nullable|string|max:255',
            'hide_powered_by' => 'nullable|boolean',
            'custom_css' => 'nullable|string|max:5000',
        ]);

        $this->whiteLabelService->saveSettings($businessId, $validated);

        return back()->with('success', 'Branding settings updated!');
    }

    /**
     * Upload logo
     */
    public function uploadLogo(Request $request): RedirectResponse
    {
        $businessId = $request->user()->id;

        $canUse = $this->whiteLabelService->canUseWhiteLabel($request->user());
        if (!$canUse['allowed']) {
            return back()->with('error', $canUse['reason']);
        }

        $request->validate([
            'logo' => 'required|image|mimes:png,jpg,jpeg,svg|max:2048',
        ]);

        $result = $this->whiteLabelService->uploadLogo($businessId, $request->file('logo'));

        return back()->with('success', 'Logo uploaded!');
    }

    /**
     * Upload favicon
     */
    public function uploadFavicon(Request $request): RedirectResponse
    {
        $businessId = $request->user()->id;

        $canUse = $this->whiteLabelService->canUseWhiteLabel($request->user());
        if (!$canUse['allowed']) {
            return back()->with('error', $canUse['reason']);
        }

        $request->validate([
            'favicon' => 'required|image|mimes:png,ico|max:512',
        ]);

        $result = $this->whiteLabelService->uploadFavicon($businessId, $request->file('favicon'));

        return back()->with('success', 'Favicon uploaded!');
    }

    /**
     * Remove logo
     */
    public function removeLogo(Request $request): RedirectResponse
    {
        $businessId = $request->user()->id;

        $this->whiteLabelService->removeLogo($businessId);

        return back()->with('success', 'Logo removed.');
    }

    /**
     * Validate custom domain
     */
    public function validateDomain(Request $request)
    {
        $validated = $request->validate([
            'domain' => 'required|string|max:255',
        ]);

        $result = $this->whiteLabelService->validateCustomDomain($validated['domain']);

        return response()->json($result);
    }
}
