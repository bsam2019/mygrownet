<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessProfileModel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class SetupController extends Controller
{
    /**
     * Display the setup wizard.
     */
    public function index(Request $request, int $step = 1): Response
    {
        $user = $request->user();
        $business = BizBoostBusinessModel::where('user_id', $user->id)->first();

        // Determine the appropriate step based on business state
        if ($business && $business->onboarding_completed) {
            // Already completed, redirect to dashboard
            return Inertia::render('BizBoost/Setup/Index', [
                'step' => 6,
                'business' => $this->formatBusiness($business),
                'industries' => $this->getIndustries(),
            ]);
        }

        // Validate step parameter
        $step = max(1, min(6, $step));

        // If no business exists, force step 1
        if (!$business && $step > 1) {
            $step = 1;
        }

        return Inertia::render('BizBoost/Setup/Index', [
            'step' => $step,
            'business' => $business ? $this->formatBusiness($business) : null,
            'industries' => $this->getIndustries(),
        ]);
    }

    /**
     * Store business basic information (Step 1).
     */
    public function storeBusinessInfo(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'industry' => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
            'phone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        $user = $request->user();
        
        // Generate unique slug
        $baseSlug = Str::slug($validated['name']);
        $slug = $baseSlug;
        $counter = 1;
        
        $existingBusiness = BizBoostBusinessModel::where('user_id', $user->id)->first();
        
        while (BizBoostBusinessModel::where('slug', $slug)
            ->when($existingBusiness, fn($q) => $q->where('id', '!=', $existingBusiness->id))
            ->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        $business = BizBoostBusinessModel::updateOrCreate(
            ['user_id' => $user->id],
            [
                'name' => $validated['name'],
                'slug' => $slug,
                'industry' => $validated['industry'],
                'description' => $validated['description'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'whatsapp' => $validated['whatsapp'] ?? $validated['phone'] ?? null,
                'email' => $validated['email'] ?? $user->email,
            ]
        );

        // Create business profile if not exists
        BizBoostBusinessProfileModel::firstOrCreate(
            ['business_id' => $business->id],
            ['is_published' => false]
        );

        return redirect()->route('bizboost.setup.step', ['step' => 2])
            ->with('success', 'Business information saved!');
    }

    /**
     * Store location information (Step 2).
     */
    public function storeLocation(Request $request)
    {
        $validated = $request->validate([
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
        ]);

        $user = $request->user();
        $business = BizBoostBusinessModel::where('user_id', $user->id)->first();

        if (!$business) {
            return redirect()->route('bizboost.setup')
                ->with('error', 'Please complete business setup first.');
        }

        $business->update([
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'province' => $validated['province'] ?? null,
            'country' => 'Zambia',
        ]);

        return redirect()->route('bizboost.setup.step', ['step' => 3])
            ->with('success', 'Location saved!');
    }

    /**
     * Store business hours (Step 3).
     */
    public function storeHours(Request $request)
    {
        $validated = $request->validate([
            'business_hours' => 'nullable|array',
            'business_hours.*.open' => 'nullable|string',
            'business_hours.*.close' => 'nullable|string',
            'business_hours.*.closed' => 'nullable|boolean',
        ]);

        $user = $request->user();
        $business = BizBoostBusinessModel::where('user_id', $user->id)->first();

        if (!$business) {
            return redirect()->route('bizboost.setup')
                ->with('error', 'Please complete business setup first.');
        }

        $business->update([
            'business_hours' => $validated['business_hours'] ?? null,
        ]);

        return redirect()->route('bizboost.setup.step', ['step' => 4])
            ->with('success', 'Business hours saved!');
    }

    /**
     * Store social links (Step 4).
     */
    public function storeSocial(Request $request)
    {
        $validated = $request->validate([
            'facebook' => 'nullable|url|max:255',
            'instagram' => 'nullable|url|max:255',
            'twitter' => 'nullable|url|max:255',
            'tiktok' => 'nullable|url|max:255',
            'website' => 'nullable|url|max:255',
        ]);

        $user = $request->user();
        $business = BizBoostBusinessModel::where('user_id', $user->id)->first();

        if (!$business) {
            return redirect()->route('bizboost.setup')
                ->with('error', 'Please complete business setup first.');
        }

        // Filter out empty values
        $socialLinks = array_filter($validated, fn($value) => !empty($value));

        $business->update([
            'social_links' => !empty($socialLinks) ? $socialLinks : null,
            'website' => $validated['website'] ?? null,
        ]);

        return redirect()->route('bizboost.setup.step', ['step' => 5])
            ->with('success', 'Social links saved!');
    }

    /**
     * Upload business logo (Step 5).
     */
    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $user = $request->user();
        $business = BizBoostBusinessModel::where('user_id', $user->id)->first();

        if (!$business) {
            return back()->with('error', 'Business not found');
        }

        // Delete old logo if exists
        if ($business->logo_path) {
            \Storage::disk('public')->delete($business->logo_path);
        }

        $path = $request->file('logo')->store('bizboost/logos', 'public');
        $business->update(['logo_path' => $path]);

        return back()->with('success', 'Logo uploaded successfully');
    }

    /**
     * Complete the setup wizard.
     */
    public function completeSetup(Request $request)
    {
        $user = $request->user();
        $business = BizBoostBusinessModel::where('user_id', $user->id)->first();

        if (!$business) {
            return redirect()->route('bizboost.setup')
                ->with('error', 'Please complete business setup first.');
        }

        $business->update([
            'onboarding_completed' => true,
            'is_active' => true,
        ]);

        return redirect()->route('bizboost.dashboard')
            ->with('success', 'Welcome to BizBoost! Your business is ready to grow. 🚀');
    }

    /**
     * Format business data for frontend.
     */
    private function formatBusiness(BizBoostBusinessModel $business): array
    {
        return [
            'id' => $business->id,
            'name' => $business->name,
            'slug' => $business->slug,
            'industry' => $business->industry,
            'description' => $business->description,
            'phone' => $business->phone,
            'whatsapp' => $business->whatsapp,
            'email' => $business->email,
            'address' => $business->address,
            'city' => $business->city,
            'province' => $business->province,
            'logo_path' => $business->logo_path,
            'business_hours' => $business->business_hours,
            'social_links' => $business->social_links,
            'onboarding_completed' => $business->onboarding_completed,
        ];
    }

    /**
     * Get available industries.
     */
    private function getIndustries(): array
    {
        return [
            'Retail & Shopping',
            'Food & Restaurant',
            'Beauty & Salon',
            'Health & Fitness',
            'Professional Services',
            'Home Services',
            'Automotive',
            'Education & Training',
            'Technology',
            'Agriculture',
            'Manufacturing',
            'Other',
        ];
    }
}
