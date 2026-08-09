<?php

namespace App\Domain\GrowStream\Presentation\Http\Controllers\Web\Creator;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorPlatform;
use App\Domain\GrowStream\Services\TenantUsageMeter;
use App\Domain\GrowStream\Services\TenantPaymentResolver;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CreatorPlatformController
{
    public function __construct(
        private TenantUsageMeter $usageMeter,
        private TenantPaymentResolver $paymentResolver,
    ) {}

    public function show(Request $request): Response|\Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        $orgId = $user->organization_id ?? $user->id;

        $platform = CreatorPlatform::where('organization_id', $orgId)->first();

        // If no active subscribed platform exists
        if (!$platform || !$platform->is_active || $platform->subscription_status !== 'active') {
            $isAdmin = false;
            if (method_exists($user, 'hasRole')) {
                $isAdmin = $user->hasRole('super_admin') || $user->hasRole('admin');
            }
            $isAdmin = $isAdmin || ($user->is_admin ?? false);

            if ($isAdmin) {
                // Auto-provision platform for platform admins
                $platform = CreatorPlatform::updateOrCreate(
                    ['organization_id' => $orgId],
                    [
                        'brand_name' => $user->name . ' Platform',
                        'subdomain' => strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $user->name)),
                        'brand_color' => '#e2571f',
                        'subscription_plan' => 'business',
                        'subscription_status' => 'active',
                        'subscribed_at' => now(),
                        'is_active' => true,
                    ]
                );
            } else {
                return redirect()->route('growstream.hub.subscribe')
                    ->with('info', 'Please choose a Creator Hub subscription plan to launch your platform.');
            }
        }

        $quotaSummary = $this->usageMeter->getUsageSummary($orgId);

        $terminologyService = app(\App\Domain\GrowStream\Services\TenantTerminologyService::class);
        $allCategories = $terminologyService->getAllCategories();

        return Inertia::render('GrowStream/Creator/PlatformSettings', [
            'platform' => $platform,
            'quotaSummary' => $quotaSummary,
            'allCategories' => $allCategories,
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $orgId = $user->organization_id ?? $user->id;

        $validated = $request->validate([
            'brand_name' => 'nullable|string|max:255',
            'category' => 'nullable|string|in:education,business,media,creator',
            'subdomain' => 'nullable|string|max:100|unique:growstream_creator_platforms,subdomain,' . $orgId . ',organization_id',
            'custom_domain' => 'nullable|string|max:255|unique:growstream_creator_platforms,custom_domain,' . $orgId . ',organization_id',
            'brand_color' => 'nullable|string|max:30',
        ]);

        $platform = CreatorPlatform::firstOrCreate(['organization_id' => $orgId]);
        $platform->update($validated);

        return redirect()->back()->with('success', 'Creator Platform settings updated successfully.');
    }
}
